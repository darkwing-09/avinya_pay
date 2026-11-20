<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_signals', function (Blueprint $table) {
            $table->id();
            $table->string('source', 30)->default('notification'); // gmail, notification, sms, webhook
            $table->string('sender', 120)->nullable(); // e.g. no-reply@paytm.com, net.one97.paytm
            $table->text('raw_text');
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('utr', 40)->nullable()->index();
            $table->string('order_id', 40)->nullable()->index();
            $table->string('payer_vpa', 100)->nullable();
            $table->unsignedBigInteger('matched_order_id')->nullable()->index();
            $table->string('status', 20)->default('unmatched')->index(); // unmatched, matched, ignored
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_signals');
    }
};
