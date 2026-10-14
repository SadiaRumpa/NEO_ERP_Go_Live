<?php

namespace App\Services;

use App\Models\Enquiry;
use App\Models\Lead;
use App\Models\Fjob;
use App\Models\Customer;
use App\Models\StockItemVariant;
use App\Models\StockItemRequisition;
use App\Models\JobSchedule;
use App\Models\JobScheduleWorker;
use App\Models\VehicleDetail;
use App\Models\HsToolCheckup;
use App\Models\CertificateTraining;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardMetrics
{
    const CACHE_TTL_SECONDS = 300; // 5 minutes

    /**
     * Calculates week-over-week percentage change and trend.
     *
     * @param string $modelClass
     * @param string|null $statusColumn
     * @param string|null $statusValue
     * @return array
     */
    public static function calculateWeeklyTrend(string $modelClass, ?string $statusColumn = null, ?string $statusValue = null): array
    {
        $startOfThisWeek = Carbon::now()->startOfWeek();
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek   = Carbon::now()->subWeek()->endOfWeek();

        $query = $modelClass::query();
        if ($statusColumn && $statusValue) {
            $query->where($statusColumn, $statusValue);
        }

        $thisWeekCount = (clone $query)->where('created_at', '>=', $startOfThisWeek)->count();
        $lastWeekCount = (clone $query)->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])->count();

        if ($lastWeekCount > 0) {
            $change = (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100;
        } else {
            $change = $thisWeekCount > 0 ? 100 : 0;
        }

        $percentageChange = round($change, 2);

        $trend = 'no change';
        if ($percentageChange > 0) {
            $trend = 'increase';
        } elseif ($percentageChange < 0) {
            $trend = 'decrease';
        }

        return [
            'this_week'       => $thisWeekCount,
            'last_week'       => $lastWeekCount,
            'percentageChange'=> $percentageChange,
            'trend'           => $trend,
        ];
    }

    /**
     * Returns cached metrics for the Admin dashboard.
     *
     * @return array
     */
    public function getAdminMetrics(): array
    {
        return Cache::remember('dashboard_metrics_admin', self::CACHE_TTL_SECONDS, function () {
            $totalEnquiries = Enquiry::count();
            $newEnquiries   = Enquiry::where('enquiry_status', 'new')->count();
            $totalLeads     = Lead::count();
            $ongoingLeads   = Lead::where('lead_status', 'inprogress')->count();
            $totalJobs      = Fjob::count();
            $activeJobs     = Fjob::where('job_status', 'inprogress')->count();
            $completedJobs  = Fjob::where('job_status', 'completed')->count();
            $totalCustomers = Customer::count();

            $pendingRequisitions = StockItemRequisition::whereIn('status', ['pending', 'draft'])->count();
            $lowStockItems       = StockItemVariant::where('quantity', '<', 10)->count();

            $recentEnquiries = Enquiry::with('customer')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $recentJobs = Fjob::with('customer')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $enquiryTrend = self::calculateWeeklyTrend(Enquiry::class);
            $leadTrend    = self::calculateWeeklyTrend(Lead::class);
            $jobTrend     = self::calculateWeeklyTrend(Fjob::class);

            return compact(
                'totalEnquiries',
                'newEnquiries',
                'totalLeads',
                'ongoingLeads',
                'totalJobs',
                'activeJobs',
                'completedJobs',
                'totalCustomers',
                'pendingRequisitions',
                'lowStockItems',
                'recentEnquiries',
                'recentJobs',
                'enquiryTrend',
                'leadTrend',
                'jobTrend'
            );
        });
    }

    /**
     * Returns cached metrics for a staff member.
     *
     * @param int $userId
     * @return array
     */
    public function getStaffMetrics(int $userId): array
    {
        return Cache::remember("dashboard_metrics_staff_{$userId}", self::CACHE_TTL_SECONDS, function () use ($userId) {
            $assignments = JobScheduleWorker::with(['jobSchedule.job'])
                ->where('user_id', $userId)
                ->orderByDesc('assigned_date')
                ->limit(10)
                ->get();

            $assignedCount = JobScheduleWorker::where('user_id', $userId)->where('status', 'assigned')->count();
            $inProgressCount = JobScheduleWorker::where('user_id', $userId)->where('status', 'inprogress')->count();
            $completedCount = JobScheduleWorker::where('user_id', $userId)->where('status', 'completed')->count();

            return compact('assignments', 'assignedCount', 'inProgressCount', 'completedCount');
        });
    }

    /**
     * Returns cached metrics for Health & Safety.
     *
     * @return array
     */
    public function getHealthSafetyMetrics(): array
    {
        return Cache::remember('dashboard_metrics_hs', self::CACHE_TTL_SECONDS, function () {
            $expiringVehicles = VehicleDetail::where(function ($q) {
                $cutoff = now()->addDays(30);
                $q->where('mot_expiry_date', '<=', $cutoff)
                  ->orWhere('tax_expiry_date', '<=', $cutoff)
                  ->orWhere('insurance_expiry_date', '<=', $cutoff);
            })->count();

            $trainingsCount = CertificateTraining::count();
            $toolCheckupsDue = HsToolCheckup::where('status', 'pending')->count();

            $upcomingSchedules = JobSchedule::where('start_date', '>=', now()->toDateString())
                ->orderBy('start_date', 'asc')
                ->limit(5)
                ->get();

            return compact('expiringVehicles', 'trainingsCount', 'toolCheckupsDue', 'upcomingSchedules');
        });
    }
}
