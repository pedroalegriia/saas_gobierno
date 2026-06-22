<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('predial_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('property_key', 80);
            $table->string('owner_name', 180);
            $table->string('address');
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('overdue_balance', 12, 2)->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();

            $table->unique(['municipality_id', 'property_key']);
        });

        Schema::create('water_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('municipality_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('contract_number', 80);
            $table->string('customer_name', 180);
            $table->string('address');
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->decimal('overdue_balance', 12, 2)->default(0);
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->timestamps();

            $table->unique(['municipality_id', 'contract_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('water_accounts');
        Schema::dropIfExists('predial_accounts');
    }
};
