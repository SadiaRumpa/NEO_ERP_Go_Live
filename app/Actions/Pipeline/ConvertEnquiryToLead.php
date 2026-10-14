<?php

namespace App\Actions\Pipeline;

use App\Models\Enquiry;
use App\Models\EnquiryAddress;
use App\Models\EnquiryImage;
use App\Models\EnquiryNote;
use App\Models\Lead;
use App\Models\LeadAddress;
use App\Models\LeadImage;
use App\Models\LeadNote;
use App\Enums\EnquiryStatus;
use App\Enums\LeadStatus;
use Illuminate\Support\Facades\DB;

class ConvertEnquiryToLead
{
    /**
     * Converts an Enquiry into a Lead within an idempotent transaction.
     *
     * @param int $enquiryId
     * @param array $assignedUsers
     * @return Lead
     */
    public function execute(int $enquiryId, array $assignedUsers = []): Lead
    {
        return DB::transaction(function () use ($enquiryId, $assignedUsers) {
            $enquiry = Enquiry::lockForUpdate()->findOrFail($enquiryId);

            // Duplicate guard: check if a lead has already been generated
            $existingLead = Lead::where('enquiry_id', $enquiry->id)->first();
            if ($existingLead) {
                abort(409, 'A lead has already been generated from this enquiry.');
            }

            // Create lead with roofing flags and assigned users
            $lead = Lead::create([
                'enquiry_id'         => $enquiry->id,
                'enquiry'            => $enquiry->enquiry,
                'lead_name'          => $enquiry->enquiry_name,
                'lead_description'   => $enquiry->enquiry_description,
                'customer_id'        => $enquiry->customer_id,
                'lead_type'          => $enquiry->enquiry_type,
                'lead_category'      => $enquiry->enquiry_category,
                'lead_priority'      => $enquiry->enquiry_priority,
                'lead_status'        => LeadStatus::NEW->value,
                'annual_maintenance' => $enquiry->annual_maintenance,
                'installations'      => $enquiry->installations,
                'repairs'            => $enquiry->repairs,
                'testing'            => $enquiry->testing,
                'assigned_to'        => !empty($assignedUsers) ? json_encode($assignedUsers) : null,
            ]);

            // Copy address
            $enquiryAddress = EnquiryAddress::where('enquiry_id', $enquiry->id)->first();
            if ($enquiryAddress) {
                LeadAddress::create([
                    'lead_id'           => $lead->id,
                    'contact_firstname' => $enquiryAddress->contact_firstname,
                    'contact_lastname'  => $enquiryAddress->contact_lastname,
                    'contact_phone'     => $enquiryAddress->contact_phone,
                    'contact_mobile'    => $enquiryAddress->contact_mobile,
                    'contact_email'     => $enquiryAddress->contact_email,
                    'address'           => $enquiryAddress->address,
                    'county'            => $enquiryAddress->county,
                    'postcode'          => $enquiryAddress->postcode,
                    'country'           => $enquiryAddress->country,
                ]);
            }

            // Copy images
            $images = EnquiryImage::where('enquiry_id', $enquiry->id)->get();
            foreach ($images as $image) {
                LeadImage::create([
                    'lead_id'    => $lead->id,
                    'image_path' => $image->image_path,
                    'image_name' => $image->image_name,
                    'image_url'  => $image->image_url,
                    'source'     => $image->source,
                ]);
            }

            // Copy notes
            $notes = EnquiryNote::where('enquiry_id', $enquiry->id)->get();
            foreach ($notes as $note) {
                LeadNote::create([
                    'lead_id'     => $lead->id,
                    'note_by'     => $note->note_by,
                    'note'        => $note->note,
                    'note_type'   => $note->note_type,
                    'note_status' => $note->note_status,
                ]);
            }

            // Update enquiry status
            $enquiry->update(['enquiry_status' => EnquiryStatus::CONVERTED_TO_LEAD->value]);

            return $lead;
        });
    }
}
