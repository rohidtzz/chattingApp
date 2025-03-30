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
        Schema::create('Messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sender_id')->nullable();
            $table->uuid('receiver_id')->nullable();

            $table->string('username')->nullable();
            $table->text('message');
            $table->string('room')->nullable()->comment('Optional chat room or conversation identifier');
            $table->timestamps();

            // Foreign key constraint (optional)
            // $table->foreign('sender_id')
            //       ->references('id')
            //       ->on('users')
            //       ->onDelete('set null');

            // $table->foreign('receiver_id')
            //         ->references('id')
            //         ->on('users')
            //         ->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Messages');
    }
};
