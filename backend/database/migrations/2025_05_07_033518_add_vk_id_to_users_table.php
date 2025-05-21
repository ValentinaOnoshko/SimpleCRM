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
        Schema::table('users', static function (Blueprint $table) {
            $table->string('vk_id')->nullable()->after('email');
            $table->string('ok_id')->nullable()->after('vk_id');
            $table->string('mailru_id')->nullable()->after('ok_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', static function (Blueprint $table) {
            $table->dropColumn(['vk_id', 'ok_id', 'mailru_id']);
        });
    }
};
