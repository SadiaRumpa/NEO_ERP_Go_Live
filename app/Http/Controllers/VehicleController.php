<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VehicleCategory;
use App\Models\VehicleDetail;
use Yajra\DataTables\Facades\DataTables;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $data = VehicleDetail::with(['category'])->select('vehicle_details.*');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('registration_display', function ($row) {
                    $badgeClass = match($row->status) {
                        'Available' => 'badge bg-label-success',
                        'Assigned' => 'badge bg-label-primary',
                        'under-maintenance' => 'badge bg-label-warning',
                        default => 'badge bg-label-secondary',
                    };
                    $label = match($row->status) {
                        'under-maintenance' => 'Maintenance',
                        default => $row->status ?? 'Available',
                    };
                    return '<div class="d-flex align-items-center gap-2">
                                <span class="fw-bold font-monospace text-heading fs-6">' . e($row->registration_no) . '</span>
                                <span class="' . $badgeClass . '">' . e($label) . '</span>
                            </div>';
                })
                ->orderColumn('registration_display', function ($query, $order) {
                    $query->orderBy('registration_no', $order);
                })
                ->filterColumn('registration_display', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('registration_no', 'like', "%{$keyword}%")
                          ->orWhere('status', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('make_model_display', function ($row) {
                    $engineYear = array_filter([$row->engine, $row->year ? "({$row->year})" : null]);
                    $subtext = !empty($engineYear) ? '<small class="text-muted d-block">' . e(implode(' ', $engineYear)) . '</small>' : '';
                    return '<div>
                                <span class="fw-semibold text-heading">' . e($row->manufacturer . ' ' . $row->model) . '</span>
                                ' . $subtext . '
                            </div>';
                })
                ->orderColumn('make_model_display', function ($query, $order) {
                    $query->orderBy('manufacturer', $order)->orderBy('model', $order);
                })
                ->filterColumn('make_model_display', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('manufacturer', 'like', "%{$keyword}%")
                          ->orWhere('model', 'like', "%{$keyword}%")
                          ->orWhere('engine', 'like', "%{$keyword}%")
                          ->orWhere('year', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('category_name', function ($row) {
                    return '<span class="badge bg-label-info">' . e($row->category->category_name ?? 'Commercial Fleet') . '</span>';
                })
                ->orderColumn('category_name', function ($query, $order) {
                    $query->leftJoin('vehicle_categories', 'vehicle_details.category_id', '=', 'vehicle_categories.id')
                          ->orderBy('vehicle_categories.category_name', $order);
                })
                ->filterColumn('category_name', function ($query, $keyword) {
                    $query->whereHas('category', function ($q) use ($keyword) {
                        $q->where('category_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('mot_due', function ($row) {
                    return $row->mot_expiry_date ? '<span class="fw-medium">' . \Carbon\Carbon::parse($row->mot_expiry_date)->format('d M Y') . '</span>' : '<span class="text-muted">-</span>';
                })
                ->orderColumn('mot_due', function ($query, $order) {
                    $query->orderBy('mot_expiry_date', $order);
                })
                ->filterColumn('mot_due', function ($query, $keyword) {
                    $query->where('mot_expiry_date', 'like', "%{$keyword}%");
                })
                ->addColumn('tax_due', function ($row) {
                    return $row->tax_expiry_date ? '<span class="fw-medium">' . \Carbon\Carbon::parse($row->tax_expiry_date)->format('d M Y') . '</span>' : '<span class="text-muted">-</span>';
                })
                ->orderColumn('tax_due', function ($query, $order) {
                    $query->orderBy('tax_expiry_date', $order);
                })
                ->filterColumn('tax_due', function ($query, $keyword) {
                    $query->where('tax_expiry_date', 'like', "%{$keyword}%");
                })
                ->addColumn('service_due', function ($row) {
                    return $row->next_service_date ? '<span class="fw-medium">' . \Carbon\Carbon::parse($row->next_service_date)->format('d M Y') . '</span>' : '<span class="text-muted">-</span>';
                })
                ->orderColumn('service_due', function ($query, $order) {
                    $query->orderBy('next_service_date', $order);
                })
                ->filterColumn('service_due', function ($query, $keyword) {
                    $query->where('next_service_date', 'like', "%{$keyword}%");
                })
                ->addColumn('action', function ($row) {
                    $showUrl = route('vehicles.show', $row->id);
                    $overviewUrl = route('vehicle-overviews.index', $row->id);
                    $editUrl = route('vehicles.edit', $row->id);
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-1">
                            <a href="' . $overviewUrl . '" class="btn btn-sm btn-icon btn-outline-info" title="Fleet Overview"><i class="ti tabler-chart-bar"></i></a>
                            <a href="' . $showUrl . '" class="btn btn-sm btn-icon btn-outline-primary" title="View Details"><i class="ti tabler-eye"></i></a>
                            <a href="' . $editUrl . '" class="btn btn-sm btn-icon btn-outline-secondary" title="Edit Vehicle"><i class="ti tabler-edit"></i></a>
                            <button class="btn btn-sm btn-icon btn-outline-danger delete" data-id="' . $row->id . '" title="Delete"><i class="ti tabler-trash"></i></button>
                        </div>
                    ';
                })
                ->rawColumns(['registration_display', 'make_model_display', 'category_name', 'mot_due', 'tax_due', 'service_due', 'action'])
                ->make(true);
        }

        return view('vehicledetails.index');
    }

    public function create()
    {
        $vehicleCategories = VehicleCategory::where('is_active', true)->get();
        return view('vehicledetails.create')->with('vehicleCategories', $vehicleCategories);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:vehicle_categories,id',
            'registration_no' => 'required|string|max:255|unique:vehicle_details,registration_no',
            'serial_no' => 'nullable|string|max:255',
            'vin_number' => 'nullable|string|max:255|unique:vehicle_details,vin_number',
            'mechanical_code' => 'nullable|string|max:255',
            'electronic_code' => 'nullable|string|max:255',
            'radio_code' => 'nullable|string|max:255',
            'deadlock_key_duplication_codes' => 'nullable|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'engine' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'fuel_type' => 'nullable|string|max:50',
            'mileage' => 'nullable|numeric|min:0',
            'tyre_size_front' => 'nullable|string|max:50',
            'tyre_size_rear' => 'nullable|string|max:50',
            'mot_expiry_date' => 'nullable|date',
            'tax_expiry_date' => 'nullable|date',
            'insurance_expiry_date' => 'nullable|date',
            'loler_expire_date' => 'nullable|date',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date',
        ]);

        $vwchileDetail = new VehicleDetail();
        $vwchileDetail->fill($request->all());
        $vwchileDetail->status = 'Available'; // Default status
        $vwchileDetail->save();

        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function show($id)
    {
        $vehicle = VehicleDetail::with(['category'])->findOrFail($id);
        return view('vehicledetails.show')->with('vehicle', $vehicle);
    }

    public function edit($id)
    {
        $vehicle = VehicleDetail::with(['category'])->findOrFail($id);
        $vehicleCategories = VehicleCategory::where('is_active', true)->get();
        return view('vehicledetails.edit')->with(['vehicle' => $vehicle, 'vehicleCategories' => $vehicleCategories]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:vehicle_categories,id',
            'registration_no' => 'required|string|max:255|unique:vehicle_details,registration_no,' . $id,
            'vin_number' => 'nullable|string|max:255|unique:vehicle_details,vin_number,' . $id,
            'serial_no' => 'nullable|string|max:255',
            'mechanical_code' => 'nullable|string|max:255',
            'electronic_code' => 'nullable|string|max:255',
            'radio_code' => 'nullable|string|max:255',
            'deadlock_key_duplication_codes' => 'nullable|string|max:255',
            'manufacturer' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'engine' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:50',
            'year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'fuel_type' => 'nullable|string|max:50',
            'mileage' => 'nullable|numeric|min:0',
            'tyre_size_front' => 'nullable|string|max:50',
            'tyre_size_rear' => 'nullable|string|max:50',
            'mot_expiry_date' => 'nullable|date',
            'tax_expiry_date' => 'nullable|date',
            'insurance_expiry_date' => 'nullable|date',
            'loler_expire_date' => 'nullable|date',
            'last_service_date' => 'nullable|date',
            'next_service_date' => 'nullable|date',
            'status' => 'string|max:50',
        ]);

        $vehicle = VehicleDetail::findOrFail($id);
        $vehicle->fill($request->all());
        $vehicle->save();

        return redirect()->route('vehicles.show', $id)->with('success', 'Vehicle updated successfully.');
    }

    public function destroy($id)
    {
        $vehicle = VehicleDetail::findOrFail($id);
        $vehicle->delete();

        return response()->json(['success' => 'Vehicle deleted successfully.']);
    }
}
