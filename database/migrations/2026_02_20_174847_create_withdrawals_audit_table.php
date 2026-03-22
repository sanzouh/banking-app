<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('withdrawals_audit', function (Blueprint $table) {
            $table->id();
            $table->string('action_type', 10);
            $table->timestamp('created_at')->useCurrent(); //Set the TIMESTAMP column to use CURRENT_TIMESTAMP as default value
            /*             $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP')); // DB::raw() allows raw SQL to be injected directly without Eloquent processing it */
            $table->bigInteger('withdraw_num');
            $table->bigInteger('account_num');
            $table->string('client_name');
            $table->decimal('old_amount', 13, 2)->nullable();
            $table->decimal('new_amount', 13, 2)->nullable();
            $table->string('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals_audit');
    }
};
