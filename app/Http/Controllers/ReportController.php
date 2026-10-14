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

        $metrics = [
            'enquiry_to_lead_count' => (int) ($enquiryToLead->total_converted ?? 0),
            'enquiry_to_lead_avg_days' => max(0, $avgEnquiryToLeadDays),
            'lead_to_job_count' => (int) ($leadToJob->total_converted ?? 0),
            'lead_to_job_avg_days' => max(0, $avgLeadToJobDays),
            'total_pipeline_avg_days' => max(0, $avgEnquiryToLeadDays + $avgLeadToJobDays),
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $metrics,
            ]);
        }

        return view('reports.conversion_velocity', compact('metrics'));
    }

    /**
     * Stock valuation and inventory status report.
     */
    public function stockValuation(Request $request)
    {
        $variants = StockItemVariant::with('stockItem.category')->get();

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
