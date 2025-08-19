<?php

use Apileon\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->createTable('users', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('email');
            $table->index('status');
            $table->index(['status', 'email_verified_at']);
        });
    }

    public function down(): void
    {
        $this->dropTable('users');
    }
}
