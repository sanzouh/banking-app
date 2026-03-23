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
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->bigInteger('withdraw_num')->primary();
            $table->integer('check_num')->unique();
            $table->bigInteger('account_num');
            $table->foreign('account_num')->references('account_num')->on('clients')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('amount', 13, 2);
            $table->bigInteger('user_id');
            $table
                ->foreign('user_id')
                ->references('id_user')
                ->on('users')
                ->onDelete('no action')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('withdrawals');
    }
};
