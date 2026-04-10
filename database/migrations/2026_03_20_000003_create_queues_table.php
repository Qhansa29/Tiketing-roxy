<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->date('queue_date');
            $table->unsignedInteger('sequence_no');
            $table->string('queue_number', 30)->unique();

            $table->string('customer_name', 100);
            $table->string('customer_phone', 25);
            $table->string('device_type', 100);
            $table->text('complaint');

            $table->enum('status', ['waiting', 'called', 'in_service', 'done', 'cancelled'])
                ->default('waiting');

            $table->dateTime('called_at')->nullable();
            $table->dateTime('service_started_at')->nullable();
            $table->dateTime('service_finished_at')->nullable();

            $table->timestamps();

            $table->unique(['queue_date', 'sequence_no']);
            $table->index(['queue_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
