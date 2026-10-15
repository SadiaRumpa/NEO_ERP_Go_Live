<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\LaborRateCard;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LaborRateCardController extends Controller
{
    public function index(): View
    {
        $rateCards = LaborRateCard::with('user')->latest('effective_from')->paginate(15);
        return view('labor.rate_cards.index', compact('rateCards'));
    }

    public function create(): View
    {
        $users = User::orderBy('name')->get();
        return view('labor.rate_cards.create', compact('users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_hourly_rate' => 'required|numeric|min:0',
            'overtime_multiplier_1_5' => 'required|numeric|min:1',
            'overtime_multiplier_2_0' => 'required|numeric|min:1',
            'ni_burden_percent' => 'required|numeric|min:0',
            'pension_burden_percent' => 'required|numeric|min:0',
            'overhead_burden_percent' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'is_active' => 'boolean',
        ]);

        LaborRateCard::create($validated);

        return redirect()->route('labor.rate-cards.index')
            ->with('success', 'Labor Rate Card saved successfully.');
    }

    public function edit(LaborRateCard $rateCard): View
    {
        $users = User::orderBy('name')->get();
        return view('labor.rate_cards.edit', compact('rateCard', 'users'));
    }

    public function update(Request $request, LaborRateCard $rateCard): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'base_hourly_rate' => 'required|numeric|min:0',
            'overtime_multiplier_1_5' => 'required|numeric|min:1',
            'overtime_multiplier_2_0' => 'required|numeric|min:1',
            'ni_burden_percent' => 'required|numeric|min:0',
            'pension_burden_percent' => 'required|numeric|min:0',
            'overhead_burden_percent' => 'required|numeric|min:0',
            'effective_from' => 'required|date',
            'is_active' => 'boolean',
        ]);

        $rateCard->update($validated);

        return redirect()->route('labor.rate-cards.index')
            ->with('success', 'Labor Rate Card updated successfully.');
    }
}
