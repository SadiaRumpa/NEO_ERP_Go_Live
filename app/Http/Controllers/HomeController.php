<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
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
        if (auth()->user()->hasRole('Admin')) {
            return $this->adminDashboard();
        } elseif (auth()->user()->hasRole('Enquiry Manager')) {
            return $this->enquiryManagerDashboard();
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
            ->where('enquiry_status', 'new')->orderByDesc('created_at')->limit(5)->get();

        $newLeads = Enquiry::with(['enquiryaddress', 'customer'])
            ->where('enquiry_status', 'lead')->orderByDesc('created_at')->limit(5)->get();


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
            'newLeads' => $newLeads,
        ]);
    }
}
