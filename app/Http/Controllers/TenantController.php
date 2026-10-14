<?php

namespace App\Http\Controllers;

use App\Models\BusinessInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TenantController extends Controller
{
    /**
     * Switch active business tenant in the current session.
     */
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_id' => 'nullable|integer',
        ]);

        $businessId = $validated['business_id'] ?? null;

        if ($businessId) {
            $business = BusinessInfo::find($businessId);
            if (!$business) {
                return back()->with('error', 'Selected business branch not found.');
            }
            session(['current_business_id' => $business->id]);
            return back()->with('success', "Switched business context to '{$business->business_name}'.");
        }

        // Only super_admin can view all branches combined
        if (Auth::user()?->hasRole('super_admin')) {
            session()->forget('current_business_id');
            return back()->with('success', 'Switched to global multi-branch view.');
        }

        return back()->with('error', 'Unauthorized to view across all business entities.');
    }
}
