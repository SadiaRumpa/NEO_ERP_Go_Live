<?php

namespace App\Http\Controllers;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use App\Models\User;
use App\Notifications\QuotationAcceptedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class QuotationPortalController extends Controller
{
    /**
     * Display quotation details on the public customer-facing acceptance portal.
     * Guarded by Laravel's signed route signature.
     */
    public function show(Request $request, int $id)
    {
        $quotation = Quotation::with(['customer', 'jobType', 'lead', 'sections.items'])->findOrFail($id);

        return view('portal.quotations.show', compact('quotation'));
    }

    /**
     * Handle POST submissions directly from the signed portal show page.
     */
    public function handlePost(Request $request, int $id)
    {
        if ($request->input('action') === 'reject') {
            return $this->reject($request, $id);
        }

        return $this->accept($request, $id);
    }

    /**
     * Customer digitally signs and accepts the quotation.
     * Guarded by Laravel's signed route signature.
     */
    public function accept(Request $request, int $id)
    {
        $validated = $request->validate([
            'signer_name'      => 'required|string|max:255',
            'signer_signature' => 'required|string',
            'terms_accepted'   => 'required',
        ]);

        $quotation = DB::transaction(function () use ($id, $validated, $request) {
            $quote = Quotation::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($quote->status === QuotationStatus::ACCEPTED->value) {
                abort(409, 'This quotation has already been accepted and digitally signed.');
            }

            if (in_array($quote->status, [QuotationStatus::REJECTED->value, QuotationStatus::CANCELLED->value])) {
                abort(422, "This quotation cannot be accepted because its status is '{$quote->status}'.");
            }

            $quote->update([
                'status'            => QuotationStatus::ACCEPTED->value,
                'signed_at'         => now(),
                'signer_name'       => $validated['signer_name'],
                'signer_signature'  => $validated['signer_signature'],
                'signer_ip'         => $request->ip(),
                'signer_user_agent' => $request->userAgent(),
            ]);

            return $quote;
        });

        // Notify Admins
        try {
            $admins = User::role('super_admin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new QuotationAcceptedNotification($quotation));
            }
        } catch (\Throwable $e) {
            // Log notification error without failing customer transaction
            logger()->error("Failed to notify admins of quotation acceptance: " . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation accepted and digitally signed successfully.',
                'signed_at' => $quotation->signed_at,
            ]);
        }

        return redirect()->back()->with('success', 'Quotation accepted and digitally signed successfully! Our team will contact you shortly.');
    }

    /**
     * Customer declines or requests a revision on the quotation.
     * Guarded by Laravel's signed route signature.
     */
    public function reject(Request $request, int $id)
    {
        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $quotation = DB::transaction(function () use ($id, $validated) {
            $quote = Quotation::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($quote->status === QuotationStatus::ACCEPTED->value) {
                abort(409, 'This quotation has already been accepted and cannot be declined.');
            }

            $quote->update([
                'status'           => QuotationStatus::REJECTED->value,
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            return $quote;
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Quotation status updated to declined.',
            ]);
        }

        return redirect()->back()->with('info', 'Your response has been recorded. Our team has been notified.');
    }

    /**
     * Staff endpoint to generate an expiring temporary signed URL for customer acceptance.
     */
    public function generatePortalLink(Request $request, int $id): JsonResponse
    {
        $quotation = Quotation::findOrFail($id);

        // Generate temporary signed URL valid for 30 days
        $portalUrl = URL::temporarySignedRoute(
            'portal.quotations.show',
            now()->addDays(30),
            ['quotation' => $quotation->id]
        );

        return response()->json([
            'success'    => true,
            'portal_url' => $portalUrl,
            'valid_until' => now()->addDays(30)->toIso8601String(),
        ]);
    }
}
