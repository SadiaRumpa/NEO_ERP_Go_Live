<?php

namespace App\Http\Controllers;

use App\Models\Fjob;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class JobPackController extends Controller
{
    /**
     * Generate and download a consolidated PDF job pack for a work order.
     */
    public function downloadPdf(Request $request, int $id)
    {
        $job = Fjob::with([
            'customer',
            'addresses',
            'schedules.workers.user',
            'notes',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('jobs.pdf.job_pack', compact('job'));
        $pdf->setPaper('a4', 'portrait');

        if ($request->query('stream')) {
            return $pdf->stream("JobPack-{$job->id}.pdf");
        }

        return $pdf->download("JobPack-{$job->id}.pdf");
    }
}
