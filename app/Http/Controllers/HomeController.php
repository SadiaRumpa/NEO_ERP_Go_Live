<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Http\Request;

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
        $newEnquiries = Enquiry::with(['enquiryaddress', 'customer'])
            ->where('enquiry_status', 'new')->orderByDesc('created_at')->limit(5)->get();
        return view('dashboards.enquiry_manager')->with([
            'newEnquiries' => $newEnquiries,
        ]);
    }
}
