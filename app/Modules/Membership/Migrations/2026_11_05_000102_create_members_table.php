<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('member_code', 30)->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('status', 20)->default('active');
            $table->unsignedInteger('active_loans')->default(0);
            $table->unsignedInteger('max_loans')->default(3);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'active_loans']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
