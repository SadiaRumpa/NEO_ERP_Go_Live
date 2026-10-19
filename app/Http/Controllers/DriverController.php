<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DriverDetail;
use App\Models\VehicleAssignDetail;
use App\Models\VehicleDetail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{

    public function index(Request $request)
    {
        // Fetch unassigned vehicles always, so it's available for both AJAX and view
        $unassignedvehicles = VehicleDetail::whereIn('status', ['Available', 'Under Maintenance'])->get();

        if ($request->ajax() || $request->wantsJson()) {
            $data = DriverDetail::with(['user', 'vehicleAssigned.vehicle'])->select('driver_details.*');

            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->user ? $row->user->name : 'N/A';
                })
                ->addColumn('license_no', function ($row) {
                    return $row->license_no;
                })
                ->addColumn('license_expiry', function ($row) {
                    return $row->license_expiry ? $row->license_expiry : 'N/A';
                })
                ->addColumn('vehicle_assigned', function ($row) {
                    if ($row->vehicleAssigned && $row->vehicleAssigned->vehicle) {
                        return $row->vehicleAssigned->vehicle->registration_no . " (" . ($row->vehicleAssigned->assigned_date ?? '') . " to " . ($row->vehicleAssigned->unassigned_date ?? 'present') . ") ";
                    }
                    return 'Not Assigned';
                })
                ->filterColumn('user_name', function ($query, $keyword) {
                    $query->whereHas('user', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('user_name', function ($query, $order) {
                    $query->leftJoin('users', 'driver_details.user_id', '=', 'users.id')
                        ->orderBy('users.name', $order);
                })
                ->filterColumn('vehicle_assigned', function ($query, $keyword) {
                    $query->whereHas('vehicleAssigned.vehicle', function ($q) use ($keyword) {
                        $q->where('registration_no', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('vehicle_assigned', function ($query, $order) {
                    $query->leftJoin('vehicle_assign_details', function ($join) {
                        $join->on('driver_details.id', '=', 'vehicle_assign_details.user_id')
                            ->where('vehicle_assign_details.status', '=', 'active');
                    })
                    ->leftJoin('vehicle_details', 'vehicle_assign_details.vehicle_id', '=', 'vehicle_details.id')
                    ->orderBy('vehicle_details.registration_no', $order);
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('drivers.show', $row->id);

                    // Check if driver already has a vehicle assigned
                    if ($row->vehicleAssigned) {
                        $assignBtn = '<button class="unassugnvehicle btn btn-danger btn-sm" data-id="' . $row->id . '">Unassign Vehicle</button>';
                    } else {
                        $assignBtn = '<button class="assignvehicle btn btn-warning btn-sm" data-id="' . $row->id . '">Assign Vehicle</button>';
                    }

                    return '
                        ' . $assignBtn . '
                        <a href="' . $showUrl . '" class="show btn btn-primary btn-sm">Show</a>
                        <button class="delete btn btn-danger btn-sm" data-id="' . $row->id . '">Delete</button>
                    ';
                })

                ->rawColumns(['action'])
                ->make(true);
        }

        return view('driverdetails.index', compact('unassignedvehicles'));
    }



    public function create()
    {
        $users = User::role('staff')->orderBy('name', 'asc')->get();
        return view('driverdetails.create')->with(['users' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_no' => 'required|unique:driver_details,license_no',
            'license_issue_date' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'license_category' => 'nullable|string|max:10',
            'digital_tacho_card' => 'nullable|boolean',
            'dbs_check_passed' => 'nullable|boolean',
            'medical_check_passed' => 'nullable|boolean',
        ]);

        DriverDetail::create($validated);

        return redirect()->route('drivers.index')->with('success', 'Driver details created successfully.');
    }

    public function show($id)
    {
        $driver = DriverDetail::with(['user'])->findOrFail($id);
        $vehicledetails = VehicleAssignDetail::with(['vehicle'])
            ->where('user_id', $driver->id)
            ->orderBy('assigned_date', 'desc')
            ->get();

        return view('driverdetails.show', compact('driver', 'vehicledetails'));
    }

    public function edit($id)
    {
        $driver = DriverDetail::findOrFail($id);
        $users = User::role('staff')->orderBy('name', 'asc')->get();
        return view('driverdetails.edit', compact('driver', 'users'));
    }

    public function update(Request $request, $id)
    {
        $driver = DriverDetail::findOrFail($id);
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'license_no' => 'required|unique:driver_details,license_no,' . $id,
            'license_issue_date' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'license_category' => 'nullable|string|max:10',
            'digital_tacho_card' => 'nullable|boolean',
            'dbs_check_passed' => 'nullable|boolean',
            'medical_check_passed' => 'nullable|boolean',
        ]);

        $driver->update($validated);

        if ($request->ajax()) {
            return response()->json(['success' => 'success', 'message' => 'Driver details updated successfully.']);
        }

        return redirect()->route('drivers.show', $driver->id)->with('success', 'Driver details updated successfully.');
    }

    public function destroy($id)
    {
        $driver = DriverDetail::findOrFail($id);
        $driver->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'success', 'message' => 'Driver deleted successfully.']);
        }

        return redirect()->route('drivers.index')->with('success', 'Driver deleted successfully.');
    }

    public function assignVehicle(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'vehicle_id' => 'required|exists:vehicle_details,id',
            'assigned_date' => 'required|date',
            'unassigned_date' => 'nullable|date',
        ]);

        // Support either driver_details.id or user_id
        $driver = DriverDetail::find($request->user_id);
        $driverId = $driver ? $driver->id : $request->user_id;

        // Check if the vehicle is already assigned
        $existingAssignment = VehicleAssignDetail::where('vehicle_id', $request->vehicle_id)
            ->where('status', 'active')
            ->first();

        if ($existingAssignment) {
            return redirect()->back()->withErrors(['vehicle_id' => 'This vehicle is already assigned.']);
        }

        DB::transaction(function () use ($request, $driverId) {
            $vehicleAssignDetail = new VehicleAssignDetail();
            $vehicleAssignDetail->vehicle_id = $request->vehicle_id;
            $vehicleAssignDetail->user_id = $driverId;
            $vehicleAssignDetail->assigned_date = $request->assigned_date;
            $vehicleAssignDetail->unassigned_date = $request->unassigned_date;
            $vehicleAssignDetail->status = 'active';
            $vehicleAssignDetail->save();

            // Update the vehicle status to 'assigned'
            $vehicle = VehicleDetail::findOrFail($request->vehicle_id);
            $vehicle->status = 'Assigned';
            $vehicle->save();
        });
        return redirect()->route('drivers.index')->with('success', 'Vehicle assigned successfully.');
    }

    public function unassignVehicle(Request $request, $id)
    {
        $driver = DriverDetail::findOrFail($id);
        $vehicleAssignDetail = VehicleAssignDetail::where('user_id', $driver->id)
            ->where('status', 'active')
            ->first();
        if (!$vehicleAssignDetail) {
            return redirect()->back()->withErrors(['error' => 'No active vehicle assignment found for this driver.']);
        }

        DB::transaction(function () use ($vehicleAssignDetail) {
            // Update the vehicle assignment status to 'inactive'
            $vehicleAssignDetail->status = 'inactive';
            $vehicleAssignDetail->save();

            // Update the vehicle status to 'available'
            $vehicle = VehicleDetail::findOrFail($vehicleAssignDetail->vehicle_id);
            $vehicle->status = 'Available';
            $vehicle->save();
        });

        return response()->json(['message' => 'Vehicle successfully unassigned.']);
    }
}
