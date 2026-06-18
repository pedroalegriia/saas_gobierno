<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('capture_lines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('folio', 40)->unique();
            $table->string('service_type', 20);
            $table->unsignedBigInteger('service_id');
            $table->decimal('amount', 12, 2);
            $table->date('expiration_date');
            $table->string('status', 20)->default('PENDING')->index();
            $table->timestamps();

            $table->index(['municipality_id', 'service_type', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capture_lines');
    }
};
