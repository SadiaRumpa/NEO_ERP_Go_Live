<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\Lead;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (auth()->user()->hasRole('admin')) {
            return $this->adminDashboard();
        } elseif (auth()->user()->hasRole('enquiry-manager')) {
            return $this->enquiryManagerDashboard();
        } elseif (auth()->user()->hasRole('lead-manager')) {
            return $this->leadManagerDashboard();
        } elseif (auth()->user()->hasRole('accounts-manager')) {
            return $this->accountsManagerDashboard();
        } else {
            abort(403, 'CAN NOT ACCESS TO ROLE');
        }
    }

    public function adminDashboard()
    {
        return view('dashboards.admin');
    }
    public function enquiryManagerDashboard()
    {
        $newenquiriescount  = Enquiry::where('enquiry_status', 'new')->count();
        $canceledenquiriescount  = Enquiry::where('enquiry_status', 'cancel')->count();
        $totalenquiriescount = Enquiry::count();
        $totalcustomerscount = Enquiry::distinct('customer_id')->count('customer_id');

        $startOfThisWeek = Carbon::now()->startOfWeek(); // Monday
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Count for this week
        $thisWeekCount = Enquiry::where('enquiry_status', 'new')
            ->where('created_at', '>=', $startOfThisWeek)
            ->count();

        // Count for last week
        $lastWeekCount = Enquiry::where('enquiry_status', 'new')
            ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->count();

        if ($lastWeekCount > 0) {
            $change = (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100;
        } else {
            $change = $thisWeekCount > 0 ? 100 : 0; // handle divide by zero
        }

        $percentageChange = round($change, 2); // e.g. 25.00

        if ($percentageChange > 0) {
            $trend = 'increase';
        } elseif ($percentageChange < 0) {
            $trend = 'decrease';
        } else {
            $trend = 'no change';
        }

        $newEnquiries = Enquiry::with(['enquiryaddress', 'customer'])
            ->where('enquiry_status', 'new')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $leadsonprogress = Lead::with(['leadaddress', 'customer'])
            ->where('lead_status', 'inprogress')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('dashboards.enquiry_manager')->with([
            'newEnquiries' => $newEnquiries,
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'change_percent' => $percentageChange,
            'trend' => $trend,
            'newenquiriescount' => $newenquiriescount,
            'canceledenquiriescount' => $canceledenquiriescount,
            'totalenquiriescount' => $totalenquiriescount,
            'totalcustomerscount' => $totalcustomerscount,
            'leadsonprogress' => $leadsonprogress,
        ]);
    }

    public function leadManagerDashboard()
    {
        $newleadscount  = Lead::where('lead_status', 'new')->count();
        $ongoingleadscount  = Lead::where('lead_status', 'inprogress')->count();
        $cancelledleadscount  = Lead::where('lead_status', 'cancelled')->count();
        $totalcustomerscount = Lead::distinct('customer_id')->count('customer_id');

        $startOfThisWeek = Carbon::now()->startOfWeek(); // Monday
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Count for this week
        $thisWeekCount = Lead::where('lead_status', 'new')
            ->where('created_at', '>=', $startOfThisWeek)
            ->count();

        // Count for last week
        $lastWeekCount = Lead::where('lead_status', 'new')
            ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->count();

        if ($lastWeekCount > 0) {
            $change = (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100;
        } else {
            $change = $thisWeekCount > 0 ? 100 : 0; // handle divide by zero
        }

        $percentageChange = round($change, 2); // e.g. 25.00

        if ($percentageChange > 0) {
            $trend = 'increase';
        } elseif ($percentageChange < 0) {
            $trend = 'decrease';
        } else {
            $trend = 'no change';
        }

        $newLeads = Lead::with(['leadaddress', 'customer'])
            ->where('lead_status', 'new')->orderByDesc('created_at')->limit(5)->get();

        $ongoingLeads = Lead::with(['leadaddress', 'customer'])
            ->where('lead_status', 'inprogress')->orderByDesc('created_at')->limit(5)->get();


        return view('dashboards.lead_manager')->with([
            'newLeads' => $newLeads,
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'percentageChange' => $percentageChange,
            'trend' => $trend,
            'newleadscount' => $newleadscount,
            'ongoingleadscount' => $ongoingleadscount,
            'cancelledleadscount' => $cancelledleadscount,
            'totalcustomerscount' => $totalcustomerscount,
            'ongoingLeads' => $ongoingLeads,
        ]);
    }

    public function accountsManagerDashboard()
    {
        $newleadscount  = Lead::where('lead_status', 'new')->count();
        $ongoingleadscount  = Lead::where('lead_status', 'inprogress')->count();
        $cancelledleadscount  = Lead::where('lead_status', 'cancelled')->count();
        $totalcustomerscount = Lead::distinct('customer_id')->count('customer_id');

        $startOfThisWeek = Carbon::now()->startOfWeek(); // Monday
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfLastWeek = Carbon::now()->subWeek()->endOfWeek();

        // Count for this week
        $thisWeekCount = Lead::where('lead_status', 'new')
            ->where('created_at', '>=', $startOfThisWeek)
            ->count();

        // Count for last week
        $lastWeekCount = Lead::where('lead_status', 'new')
            ->whereBetween('created_at', [$startOfLastWeek, $endOfLastWeek])
            ->count();

        if ($lastWeekCount > 0) {
            $change = (($thisWeekCount - $lastWeekCount) / $lastWeekCount) * 100;
        } else {
            $change = $thisWeekCount > 0 ? 100 : 0; // handle divide by zero
        }

        $percentageChange = round($change, 2); // e.g. 25.00

        if ($percentageChange > 0) {
            $trend = 'increase';
        } elseif ($percentageChange < 0) {
            $trend = 'decrease';
        } else {
            $trend = 'no change';
        }

        $newLeads = Lead::with(['leadaddress', 'customer'])
            ->where('lead_status', 'new')->orderByDesc('created_at')->limit(5)->get();

        $ongoingLeads = Lead::with(['leadaddress', 'customer'])
            ->where('lead_status', 'completed')->orderByDesc('created_at')->limit(5)->get();


        return view('dashboards.accounts_manager')->with([
            'newLeads' => $newLeads,
            'this_week' => $thisWeekCount,
            'last_week' => $lastWeekCount,
            'percentageChange' => $percentageChange,
            'trend' => $trend,
            'newleadscount' => $newleadscount,
            'ongoingleadscount' => $ongoingleadscount,
            'cancelledleadscount' => $cancelledleadscount,
            'totalcustomerscount' => $totalcustomerscount,
            'ongoingLeads' => $ongoingLeads,
        ]);
    }
}
