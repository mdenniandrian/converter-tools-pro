<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action_type'); // doc_convert | remove_bg
            $table->string('target_format');
            $table->string('input_s3_key');
            $table->string('output_s3_key');
            $table->string('output_filename')->nullable();
            $table->enum('status', ['pending', 'processing', 'done', 'failed', 'downloaded', 'expired_or_deleted'])
                  ->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
