<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Fjob;
use App\Models\Lead;
use App\Models\StockCategory;
use App\Models\StockItem;
use App\Models\StockItemVariant;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Pipeline conversion velocity report.
     */
    public function conversionVelocity(Request $request)
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $diffSqlLead = $isSqlite 
            ? 'AVG(JULIANDAY(leads.created_at) - JULIANDAY(enquiries.created_at))' 
            : 'AVG(DATEDIFF(leads.created_at, enquiries.created_at))';
        $diffSqlJob = $isSqlite 
            ? 'AVG(JULIANDAY(fjobs.created_at) - JULIANDAY(leads.created_at))' 
            : 'AVG(DATEDIFF(fjobs.created_at, leads.created_at))';

        // Calculate Enquiry -> Lead velocity
        $enquiryToLead = DB::table('leads')
            ->join('enquiries', 'leads.enquiry_id', '=', 'enquiries.id')
            ->select(
                DB::raw('COUNT(leads.id) as total_converted'),
                DB::raw("{$diffSqlLead} as avg_days")
            )
            ->first();

        // Calculate Lead -> Job velocity
        $leadToJob = DB::table('fjobs')
            ->join('leads', 'fjobs.lead_id', '=', 'leads.id')
            ->select(
                DB::raw('COUNT(fjobs.id) as total_converted'),
                DB::raw("{$diffSqlJob} as avg_days")
            )
            ->first();

        $avgEnquiryToLeadDays = round((float) ($enquiryToLead->avg_days ?? 0), 1);
        $avgLeadToJobDays = round((float) ($leadToJob->avg_days ?? 0), 1);

        $totalEnquiries = DB::table('enquiries')->count();
        $totalLeads = DB::table('leads')->count();
        $totalJobs = DB::table('fjobs')->count();

        $metrics = [
            'enquiry_to_lead_count' => (int) ($enquiryToLead->total_converted ?? 0),
            'enquiry_to_lead_avg_days' => max(0, $avgEnquiryToLeadDays),
            'lead_to_job_count' => (int) ($leadToJob->total_converted ?? 0),
            'lead_to_job_avg_days' => max(0, $avgLeadToJobDays),
            'total_pipeline_avg_days' => max(0, round($avgEnquiryToLeadDays + $avgLeadToJobDays, 1)),
            'total_enquiries' => $totalEnquiries,
            'total_leads' => $totalLeads,
            'total_jobs' => $totalJobs,
            'enquiry_to_lead_rate' => $totalEnquiries > 0 ? round(($enquiryToLead->total_converted / $totalEnquiries) * 100, 1) : 0,
            'lead_to_job_rate' => $totalLeads > 0 ? round(($leadToJob->total_converted / $totalLeads) * 100, 1) : 0,
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $metrics,
            ]);
        }

        // Recent pipeline conversions for the report view table
        $recentConversions = DB::table('fjobs')
            ->join('leads', 'fjobs.lead_id', '=', 'leads.id')
            ->leftJoin('enquiries', 'fjobs.enquiry_id', '=', 'enquiries.id')
            ->leftJoin('customers', 'fjobs.customer_id', '=', 'customers.id')
            ->select([
                'fjobs.id as job_id',
                'fjobs.slug as job_slug',
                'fjobs.job_name',
                'fjobs.job_title',
                'fjobs.job_status',
                'fjobs.job_priority',
                'customers.company_name',
                'enquiries.enquiry_name',
                'enquiries.created_at as enquiry_created_at',
                'leads.lead_name',
                'leads.created_at as lead_created_at',
                'fjobs.created_at as job_created_at',
            ])
            ->orderBy('fjobs.created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                $enqTime = $row->enquiry_created_at ? Carbon::parse($row->enquiry_created_at) : null;
                $leadTime = $row->lead_created_at ? Carbon::parse($row->lead_created_at) : null;
                $jobTime = $row->job_created_at ? Carbon::parse($row->job_created_at) : null;

                $enqToLeadDays = ($enqTime && $leadTime) ? abs(round($leadTime->diffInHours($enqTime) / 24, 1)) : 0;
                $leadToJobDays = ($leadTime && $jobTime) ? abs(round($jobTime->diffInHours($leadTime) / 24, 1)) : 0;
                $totalDays = ($enqTime && $jobTime) ? abs(round($jobTime->diffInHours($enqTime) / 24, 1)) : 0;

                return (object) [
                    'job_id' => $row->job_id,
                    'job_slug' => $row->job_slug,
                    'job_name' => $row->job_name,
                    'job_title' => $row->job_title,
                    'job_status' => $row->job_status,
                    'job_priority' => $row->job_priority,
                    'company_name' => $row->company_name ?? 'N/A',
                    'enquiry_name' => $row->enquiry_name ?? ('ENQ-' . str_pad($row->job_id, 4, '0', STR_PAD_LEFT)),
                    'enquiry_date' => $enqTime ? $enqTime->format('d M Y') : '—',
                    'lead_name' => $row->lead_name ?? ('LEAD-' . str_pad($row->job_id, 4, '0', STR_PAD_LEFT)),
                    'lead_date' => $leadTime ? $leadTime->format('d M Y') : '—',
                    'job_date' => $jobTime ? $jobTime->format('d M Y') : '—',
                    'enq_to_lead_days' => $enqToLeadDays,
                    'lead_to_job_days' => $leadToJobDays,
                    'total_days' => $totalDays,
                ];
            });

        return view('reports.conversion_velocity', compact('metrics', 'recentConversions'));
    }

    /**
     * Stock valuation and inventory status report.
     */
    public function stockValuation(Request $request)
    {
        $variants = StockItemVariant::with(['stockItem.category', 'warehouse'])
            ->orderBy('stock_item_id')
            ->get();

        $totalValuation = 0;
        $totalQuantity = 0;
        $lowStockCount = 0;
        $categoryBreakdown = [];

        foreach ($variants as $v) {
            $qty = (float) ($v->quantity ?? 0);
            $cost = (float) ($v->avg_price ?? 0);
            $val = $qty * $cost;

            $totalQuantity += $qty;
            $totalValuation += $val;

            if ($qty <= 10) {
                $lowStockCount++;
            }

            $catName = $v->stockItem?->category?->name ?? 'Uncategorized';
            if (!isset($categoryBreakdown[$catName])) {
                $categoryBreakdown[$catName] = [
                    'category'    => $catName,
                    'item_count'  => 0,
                    'total_stock' => 0,
                    'total_value' => 0,
                ];
            }
            $categoryBreakdown[$catName]['item_count']++;
            $categoryBreakdown[$catName]['total_stock'] += $qty;
            $categoryBreakdown[$catName]['total_value'] += $val;
        }

        $metrics = [
            'total_valuation'    => round($totalValuation, 2),
            'total_quantity'     => $totalQuantity,
            'low_stock_count'    => $lowStockCount,
            'category_breakdown' => array_values($categoryBreakdown),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $metrics,
            ]);
        }

        return view('reports.stock_valuation', compact('metrics', 'variants'));
    }

    /**
     * Stream CSV export of stock ledger valuation.
     */
    public function exportStock(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Stock_Valuation_' . date('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Item Name', 'Variant Name', 'Category', 'Current Stock', 'Unit Cost (GBP)', 'Total Valuation (GBP)']);

            $variants = StockItemVariant::with('stockItem.category')->lazy();

            foreach ($variants as $v) {
                $qty = (float) ($v->quantity ?? 0);
                $cost = (float) ($v->avg_price ?? 0);
                $val = round($qty * $cost, 2);

                fputcsv($file, [
                    $v->id,
                    $v->stockItem?->name ?? 'N/A',
                    $v->name,
                    $v->stockItem?->category?->name ?? 'Uncategorized',
                    $qty,
                    number_format($cost, 2, '.', ''),
                    number_format($val, 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Stream CSV export of work orders / jobs.
     */
    public function exportJobs(): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Jobs_Summary_' . date('Y-m-d') . '.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Job ID', 'Customer', 'Status', 'Maintenance', 'Installations', 'Repairs', 'Testing', 'Created At']);

            $jobs = Fjob::with('customer')->lazy();

            foreach ($jobs as $job) {
                fputcsv($file, [
                    $job->id,
                    $job->customer?->company_name ?? 'N/A',
                    $job->job_status,
                    $job->annual_maintenance ? 'Yes' : 'No',
                    $job->installations ? 'Yes' : 'No',
                    $job->repairs ? 'Yes' : 'No',
                    $job->testing ? 'Yes' : 'No',
                    $job->created_at?->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
