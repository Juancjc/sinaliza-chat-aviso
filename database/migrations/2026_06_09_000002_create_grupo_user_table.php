<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('grupo_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grupo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['convidado', 'ativo'])->default('ativo');
            $table->timestamps();
            $table->unique(['grupo_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grupo_user');
    }
};
