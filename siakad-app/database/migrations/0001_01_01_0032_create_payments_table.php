<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('payment_number', 50)->unique();
            $table->foreignUuid('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('paid_by', 100)->nullable();
            $table->foreignUuid('payment_method_id')->nullable()->constrained('payment_methods')->nullOnDelete();
            $table->string('payment_channel', 30)->default('backend');
            $table->decimal('amount', 12, 2);
            $table->decimal('admin_fee', 12, 2)->default(0);
            $table->string('gateway_ref', 100)->nullable();
            $table->string('gateway_status', 30)->nullable();
            $table->string('proof_file', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->text('reject_reason')->nullable();
            $table->date('payment_date');
            $table->timestamp('paid_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['invoice_id']);
            $table->index(['student_id']);
            $table->index(['status', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('0001_01_01_0032_payments');
    }
};
