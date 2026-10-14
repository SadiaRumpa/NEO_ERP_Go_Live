<?php

namespace App\Actions\Pipeline;

use App\Models\Lead;
use App\Models\Fjob;
use App\Models\FjobAddress;
use App\Models\FjobImage;
use App\Models\FjobNote;
use App\Models\FjobAttachment;
use App\Models\FjobQuotation;
use App\Enums\LeadStatus;
use App\Enums\JobStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConvertLeadToJob
{
    /**
     * Converts a Lead into a Job within an idempotent transaction.
     * Copies all child records, roofing flags, and worker assignments.
     *
     * @param int $leadId
     * @return Fjob
     */
    public function execute(int $leadId): Fjob
    {
        return DB::transaction(function () use ($leadId) {
            $lead = Lead::with([
                'leadaddress',
                'leadimages',
                'leadnotes',
                'leadattachments',
                'completedquotation',
            ])->lockForUpdate()->findOrFail($leadId);

            // Duplicate guard: check if a job has already been generated
            $existingJob = Fjob::where('lead_id', $lead->id)->first();
            if ($existingJob) {
                abort(409, 'A job has already been generated from this lead.');
            }

            // Create job copying 4 roofing flags and assigned_to
            $fjob = Fjob::create([
                'enquiry_id'         => $lead->enquiry_id ?? 0,
                'enquiry'            => $lead->enquiry,
                'lead_id'            => $lead->id,
                'job_name'           => $lead->lead_name,
                'slug'               => $lead->slug ? ($lead->slug . '-job-' . $lead->id) : ('job-' . Str::random(8)),
                'job_title'          => $lead->lead_title,
                'job_description'    => $lead->lead_description,
                'customer_id'        => $lead->customer_id,
                'job_type'           => $lead->lead_type,
                'job_category'       => $lead->lead_category,
                'job_priority'       => $lead->lead_priority,
                'job_status'         => JobStatus::DRAFT->value,
                'annual_maintenance' => $lead->annual_maintenance,
                'installations'      => $lead->installations,
                'repairs'            => $lead->repairs,
                'testing'            => $lead->testing,
                'assigned_to'        => $lead->assigned_to,
            ]);

            $jobId = $fjob->id;

            // Copy address
            if ($lead->leadaddress) {
                FjobAddress::create([
                    'fjob_id'           => $jobId,
                    'contact_firstname' => $lead->leadaddress->contact_firstname,
                    'contact_lastname'  => $lead->leadaddress->contact_lastname,
                    'contact_email'     => $lead->leadaddress->contact_email,
                    'contact_phone'     => $lead->leadaddress->contact_phone,
                    'contact_mobile'    => $lead->leadaddress->contact_mobile,
                    'address'           => $lead->leadaddress->address,
                    'county'            => $lead->leadaddress->county,
                    'postcode'          => $lead->leadaddress->postcode,
                    'country'           => $lead->leadaddress->country,
                    'location'          => $lead->leadaddress->location,
                ]);
            }

            // Copy images
            if ($lead->leadimages) {
                foreach ($lead->leadimages as $image) {
                    FjobImage::create([
                        'fjob_id'    => $jobId,
                        'image_path' => $image->image_path,
                        'image_name' => $image->image_name,
                        'image_url'  => $image->image_url,
                        'source'     => $image->source,
                    ]);
                }
            }

            // Copy notes
            if ($lead->leadnotes) {
                foreach ($lead->leadnotes as $note) {
                    FjobNote::create([
                        'fjob_id'     => $jobId,
                        'note_by'     => $note->note_by,
                        'note'        => $note->note,
                        'note_type'   => $note->note_type,
                        'note_status' => $note->note_status,
                    ]);
                }
            }

            // Copy accepted quotation snapshot
            if ($lead->completedquotation) {
                FjobQuotation::create([
                    'fjob_id'          => $jobId,
                    'quotation_id'     => $lead->completedquotation->id,
                    'quotation_number' => $lead->completedquotation->quotation_version,
                    'quotation_date'   => $lead->completedquotation->quotation_date,
                    'valid_until'      => $lead->completedquotation->valid_until,
                    'total_amount'     => $lead->completedquotation->total_amount,
                    'status'           => $lead->completedquotation->status,
                ]);
            }

            // Copy attachments
            if ($lead->leadattachments) {
                foreach ($lead->leadattachments as $attachment) {
                    FjobAttachment::create([
                        'fjob_id'         => $jobId,
                        'attachment_name' => $attachment->attachment_name,
                        'description'     => $attachment->description,
                        'attachment_path' => $attachment->attachment_path,
                        'attachment_url'  => $attachment->attachment_url,
                        'attachment_type' => $attachment->attachment_type,
                        'source'          => $attachment->source,
                        'uploaded_by'     => Auth::id() ?? 1,
                    ]);
                }
            }

            // Update lead status to converted_to_job
            $lead->update(['lead_status' => LeadStatus::CONVERTED_TO_JOB->value]);

            return $fjob;
        });
    }
}
