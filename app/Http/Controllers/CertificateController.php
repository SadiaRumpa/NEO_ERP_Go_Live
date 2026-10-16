<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\CourseCertificate;

class CertificateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CourseCertificate::orderBy('id', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('certificate_name', function ($row) {
                    return '<div class="d-flex align-items-center gap-2">
                                <i class="ti tabler-certificate text-primary fs-5"></i>
                                <span class="fw-bold text-heading">' . e($row->certificate_name) . '</span>
                            </div>';
                })
                ->editColumn('status', function ($row) {
                    $st = strtolower($row->status ?? 'active');
                    $badgeClass = match($st) {
                        'active' => 'badge bg-label-success',
                        'expired' => 'badge bg-label-danger',
                        'pending' => 'badge bg-label-warning',
                        default => 'badge bg-label-primary',
                    };
                    return '<span class="' . $badgeClass . '">' . e(ucfirst($st)) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex align-items-center gap-1">
                                <a class="btn btn-sm btn-icon btn-outline-primary edit" href="' . route('certificates.edit', $row->id) . '" title="Edit"><i class="ti tabler-edit"></i></a>
                                <button class="btn btn-sm btn-icon btn-outline-danger delete" data-id="' . $row->id . '" title="Delete"><i class="ti tabler-trash"></i></button>
                            </div>';
                })
                ->rawColumns(['certificate_name', 'status', 'action'])
                ->make(true);
        }

        return view('certificates.index');
    }

    public function create()
    {
        return view('certificates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'certificatename' => 'required|string|max:255',
            'certificatedetails' => 'nullable|string',
            'certificatevalidity' => 'required|string',
        ]);
        $certificate = new CourseCertificate();
        $certificate->certificate_name = $request->input('certificatename');
        $certificate->description = $request->input('certificatedetails');
        $certificate->authorized_by = $request->input('conductedby');
        $certificate->validity_period = $request->input('certificatevalidity');
        $certificate->status = 'active';
        $certificate->save();

        return redirect()->route('certificates.index')->with('success', 'Certificate created successfully.');
    }

    public function edit($id)
    {
        $certificate = CourseCertificate::findOrFail($id);
        return view('certificates.edit', compact('certificate'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'certificatename' => 'required|string|max:255',
            'certificatedetails' => 'nullable|string',
            'certificatevalidity' => 'required|string',
        ]);
        $certificate = CourseCertificate::findOrFail($id);
        $certificate->certificate_name = $request->input('certificatename');
        $certificate->description = $request->input('certificatedetails');
        $certificate->authorized_by = $request->input('conductedby');
        $certificate->validity_period = $request->input('certificatevalidity');
        $certificate->status = $request->input('status');
        $certificate->save();

        return redirect()->route('certificates.edit', $certificate->id)->with('success', 'Certificate updated successfully.');
    }

    public function show($id)
    {
        $certificate = CourseCertificate::findOrFail($id);
        return view('certificates.edit', compact('certificate'));
    }

    public function destroy($id)
    {
        $certificate = CourseCertificate::findOrFail($id);
        $certificate->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'success', 'message' => 'Certificate deleted successfully.']);
        }
        return redirect()->route('certificates.index')->with('success', 'Certificate deleted successfully.');
    }
}
