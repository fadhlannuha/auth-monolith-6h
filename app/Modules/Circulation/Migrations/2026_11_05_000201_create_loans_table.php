<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 40)->unique();
            $table->foreignId('member_id')->constrained('members')->restrictOnDelete();
            $table->string('member_name');
            $table->string('member_code', 30);
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at');
            $table->timestamp('returned_at')->nullable();
            $table->string('status', 20)->default('borrowed');
            $table->decimal('fine_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'due_at']);
            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
