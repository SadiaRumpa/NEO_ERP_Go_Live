<?php

namespace App\Http\Controllers;

use App\Models\VehicleCheckCategory;
use App\Models\VehicleDetail;
use Illuminate\Http\Request;

class VehicleOverviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($vehicleId)
    {
        $vehicle = VehicleDetail::findOrFail($vehicleId);
        $categories = VehicleCheckCategory::with([
            'child',
            'child.checklists',
            'child.checklists.attributes'
        ])->where('is_parent', true)->get();

        // return $categories;
        return view('vehicle_overviews.create', ['vehicle' => $vehicle, 'categories' => $categories]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
