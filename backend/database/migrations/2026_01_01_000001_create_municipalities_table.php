<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('municipalities', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 180);
            $table->string('slug', 80)->unique();
            $table->string('domain', 180)->nullable()->unique();
            $table->string('logo')->nullable();
            $table->char('primary_color', 7)->default('#0F4C81');
            $table->char('secondary_color', 7)->default('#B08D57');
            $table->string('status', 30)->default('ACTIVE')->index();
            $table->json('settings_json')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('municipalities');
    }
};
