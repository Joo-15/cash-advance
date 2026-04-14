<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disbursements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_advance_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('finance_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->decimal('amount', 15, 2);        // Dana dicairkan
            $table->decimal('total_spent', 15, 2)->nullable(); // Dana dihabiskan

            $table->enum('report_status', [
                'not_submitted', // Belum laporan
                'submitted',     // Sudah submit, menunggu review
                'approved',      // Disetujui
                'rejected',      // Ditolak
            ])->default('not_submitted');

            $table->text('report_notes')->nullable();
            $table->text('finance_notes')->nullable();

            $table->timestamp('disbursed_at');
            $table->timestamp('submitted_at')->nullable();  // Opsional
            $table->timestamp('approved_at')->nullable();   // Opsional
            $table->timestamps();

            // Index untuk performa
            $table->index('report_status');
            $table->index('cash_advance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
