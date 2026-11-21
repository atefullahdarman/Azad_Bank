<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 50);
            $table->string('address');
            $table->string('identity_document', 100)->nullable()->unique();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('account_number', 100)->unique();
            $table->decimal('balance', 16, 2)->default(0);
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 32)->unique();
            $table->foreignId('account_id')->constrained('accounts')->restrictOnDelete();
            $table->foreignId('destination_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            $table->string('account_number', 100);
            $table->string('customer_name');
            $table->string('type', 20);
            $table->decimal('amount', 16, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['date', 'status']);
            $table->index(['account_id', 'date']);
        });

        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 32)->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('customer_name');
            $table->decimal('amount', 16, 2);
            $table->date('date');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('customers');
    }
};
