<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('traffic_fines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('folio', 80);
            $table->string('plate', 20)->index();
            $table->string('offender_name', 180)->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('status', 30)->default('PENDING')->index();
            $table->date('violation_date');
            $table->timestamps();

            $table->unique(['municipality_id', 'folio']);
            $table->index(['municipality_id', 'plate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traffic_fines');
    }
};
