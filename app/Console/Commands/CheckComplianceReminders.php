<?php

namespace App\Console\Commands;

use App\Models\HsToolCheckup;
use App\Models\User;
use App\Models\VehicleDetail;
use App\Notifications\ComplianceReminderNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckComplianceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compliance:check-reminders {--days=30 : Window of days to scan for upcoming compliance expiries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan vehicle MOT, tax, insurance, and tool checkups to dispatch compliance notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $today = Carbon::today();
        $targetDate = Carbon::today()->addDays($days);

        $this->info("Scanning compliance expiries from {$today->toDateString()} to {$targetDate->toDateString()}...");

        $recipients = User::role(['super_admin', 'health_and_safety_manager'])->get();
        if ($recipients->isEmpty()) {
            $recipients = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['super_admin', 'admin']);
            })->get();
        }

        if ($recipients->isEmpty()) {
            $this->warn('No compliance recipient users found with super_admin or health_and_safety_manager roles.');
            return self::SUCCESS;
        }

        $dispatched = 0;

        // 1. Vehicle MOT Expiries
        $motVehicles = VehicleDetail::whereNotNull('mot_expiry_date')
            ->whereBetween('mot_expiry_date', [$today->toDateString(), $targetDate->toDateString()])
            ->get();

        foreach ($motVehicles as $v) {
            $daysLeft = $today->diffInDays(Carbon::parse($v->mot_expiry_date), false);
            Notification::send($recipients, new ComplianceReminderNotification(
                'Vehicle MOT Expiry',
                "Vehicle {$v->registration_no} ({$v->model})",
                $v->mot_expiry_date,
                (int) $daysLeft
            ));
            $dispatched++;
        }

        // 2. Vehicle Road Tax Expiries
        $taxVehicles = VehicleDetail::whereNotNull('tax_expiry_date')
            ->whereBetween('tax_expiry_date', [$today->toDateString(), $targetDate->toDateString()])
            ->get();

        foreach ($taxVehicles as $v) {
            $daysLeft = $today->diffInDays(Carbon::parse($v->tax_expiry_date), false);
            Notification::send($recipients, new ComplianceReminderNotification(
                'Vehicle Road Tax Expiry',
                "Vehicle {$v->registration_no} ({$v->model})",
                $v->tax_expiry_date,
                (int) $daysLeft
            ));
            $dispatched++;
        }

        // 3. Vehicle Insurance Expiries
        $insVehicles = VehicleDetail::whereNotNull('insurance_expiry_date')
            ->whereBetween('insurance_expiry_date', [$today->toDateString(), $targetDate->toDateString()])
            ->get();

        foreach ($insVehicles as $v) {
            $daysLeft = $today->diffInDays(Carbon::parse($v->insurance_expiry_date), false);
            Notification::send($recipients, new ComplianceReminderNotification(
                'Vehicle Insurance Expiry',
                "Vehicle {$v->registration_no} ({$v->model})",
                $v->insurance_expiry_date,
                (int) $daysLeft
            ));
            $dispatched++;
        }

        // 4. Tool & Equipment Checkups
        $toolCheckups = HsToolCheckup::whereNotNull('next_checkup_date')
            ->whereBetween('next_checkup_date', [$today->toDateString(), $targetDate->toDateString()])
            ->get();

        foreach ($toolCheckups as $tool) {
            $daysLeft = $today->diffInDays(Carbon::parse($tool->next_checkup_date), false);
            Notification::send($recipients, new ComplianceReminderNotification(
                'Tool Health & Safety Checkup Due',
                $tool->tool_name ?? "Tool #{$tool->hs_tool_id}",
                $tool->next_checkup_date,
                (int) $daysLeft
            ));
            $dispatched++;
        }

        $this->info("Compliance check complete. Dispatched {$dispatched} reminder notifications.");

        return self::SUCCESS;
    }
}
