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
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('member_code', 100)->unique();
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('phone', 25)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active');
            $table->decimal('balance', 15, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();
            $table->index('member_code');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
