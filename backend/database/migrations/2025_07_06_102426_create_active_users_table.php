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
        Schema::create('active_users', function (Blueprint $table) {
            $table->id(); // This creates an unsigned big integer by default
            $table->unsignedBigInteger('user_id'); // Ensure this matches the type of users.id
            $table->string('url')->nullable();
            $table->timestamp('last_activity');
            $table->timestamps();

            // Adding the foreign key constraint
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_users');
    }
};
