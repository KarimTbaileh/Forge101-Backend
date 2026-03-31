<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('target_muscle', 100);
            $table->text('description');
            $table->string('image_url', 255)->nullable();
            $table->string('video_url', 255)->nullable();
            $table->string('difficulty', 20);
            $table->string('category', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
