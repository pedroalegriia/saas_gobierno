<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('capture_line_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('gateway', 40);
            $table->string('method', 30);
            $table->decimal('amount', 12, 2);
            $table->string('reference', 120)->nullable()->unique();
            $table->string('status', 30)->default('PENDING')->index();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->string('folio', 60)->unique();
            $table->string('concept', 180);
            $table->decimal('amount', 12, 2);
            $table->string('pdf_path');
            $table->timestamp('issued_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
        Schema::dropIfExists('payments');
    }
};
