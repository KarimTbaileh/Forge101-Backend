<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_exercise_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_plan_id')->constrained('user_plans')->onDelete('cascade');
            $table->foreignId('exercise_id')->constrained('exercises')->onDelete('cascade');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_plan_id', 'exercise_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_exercise_progress');
    }
};
