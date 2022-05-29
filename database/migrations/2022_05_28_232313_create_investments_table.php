<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->date('date')->useCurrent();
            $table->double('amount', 10, 2);
            $table->date('withdrawal_date')->nullable(true)->default(null);
            $table->double('withdrawal_gain', 10, 2)->nullable(true)->default(null);
            $table->double('withdrawal_tax', 10, 2)->nullable(true)->default(null);
            $table->double('withdrawal_balance', 10, 2)->nullable(true)->default(null);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('investments');
    }
};
