<?php

namespace App\Http\Controllers;

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
        return view('dashboards.enquiry_manager');
    }
}
