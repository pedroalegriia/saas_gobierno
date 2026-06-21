<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('gateway', 40);
            $table->string('event_id', 120)->nullable()->index();
            $table->string('payment_reference', 120)->nullable()->index();
            $table->string('status', 30)->default('RECEIVED')->index();
            $table->json('headers')->nullable();
            $table->longText('payload');
            $table->text('error_message')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_webhook_events');
    }
};
