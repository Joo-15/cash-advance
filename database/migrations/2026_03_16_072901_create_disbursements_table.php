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

            $table->decimal('amount', 15, 2);
            $table->timestamp('disbursed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disbursements');
    }
};
