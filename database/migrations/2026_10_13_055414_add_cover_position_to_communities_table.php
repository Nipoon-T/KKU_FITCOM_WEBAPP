<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // เพิ่มคอลัมน์เก็บตำแหน่งรูปปก เช่น "50% 30%" (ค่าเริ่มต้น = กึ่งกลาง)
    public function up(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->string('cover_position')->default('50% 50%')->after('cover_image');
        });
    }

    // ถ้า rollback ให้ลบคอลัมน์ออก
    public function down(): void
    {
        Schema::table('communities', function (Blueprint $table) {
            $table->dropColumn('cover_position');
        });
    }
};
