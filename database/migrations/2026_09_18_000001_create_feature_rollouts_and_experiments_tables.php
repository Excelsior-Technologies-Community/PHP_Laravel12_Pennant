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
        // 1. Feature Rollout & Kill-Switch Configurations
        Schema::create('feature_rollouts', function (Blueprint $table) {
            $table->id();
            $table->string('feature_name')->unique();
            $table->unsignedTinyInteger('percentage')->default(0); // 0 to 100%
            $table->boolean('is_killed')->default(false); // 1-Click Panic Kill Switch
            $table->boolean('is_maintenance')->default(false); // Maintenance Mode
            $table->string('maintenance_message')->nullable();
            $table->timestamps();
        });

        // 2. A/B Testing & Variant Experimentation Metrics
        Schema::create('experiment_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('experiment_name');
            $table->string('variant'); // control, variant_a, variant_b
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('converted')->default(false);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['experiment_name', 'variant']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('experiment_metrics');
        Schema::dropIfExists('feature_rollouts');
    }
};
