<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                if (!Schema::hasColumn('quotations', 'signed_at')) {
                    $table->dateTime('signed_at')->nullable()->after('remarks');
                }
                if (!Schema::hasColumn('quotations', 'signer_name')) {
                    $table->string('signer_name')->nullable()->after('signed_at');
                }
                if (!Schema::hasColumn('quotations', 'signer_signature')) {
                    $table->longText('signer_signature')->nullable()->after('signer_name');
                }
                if (!Schema::hasColumn('quotations', 'signer_ip')) {
                    $table->string('signer_ip')->nullable()->after('signer_signature');
                }
                if (!Schema::hasColumn('quotations', 'signer_user_agent')) {
                    $table->text('signer_user_agent')->nullable()->after('signer_ip');
                }
                if (!Schema::hasColumn('quotations', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('signer_user_agent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $table) {
                $columns = ['signed_at', 'signer_name', 'signer_signature', 'signer_ip', 'signer_user_agent', 'rejection_reason'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('quotations', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
