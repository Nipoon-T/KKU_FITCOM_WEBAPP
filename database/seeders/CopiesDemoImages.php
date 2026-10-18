<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

trait CopiesDemoImages
{
    // หารูปใน database/seeders/demo-images จาก "ชื่อไฟล์ไม่รวมนามสกุล" (นามสกุลอะไรก็ได้ เช่น jpg, jfif, jpeg, png)
    // แล้วคัดลอกไป storage/app/public/{$folder} คืนค่า path สำหรับเก็บในฐานข้อมูล (ไม่เจอรูป = null)
    protected function copyDemoImage(string $name, string $folder): ?string
    {
        if ($name === '') {
            return null;
        }

        foreach (File::files(database_path('seeders/demo-images')) as $file) {
            if ($file->getFilenameWithoutExtension() !== $name) {
                continue;
            }

            // jfif / jpeg คือไฟล์ JPEG เปลี่ยนเป็น .jpg ให้เบราว์เซอร์และเซิร์ฟเวอร์รู้จักแน่นอน
            $extension = strtolower($file->getExtension());
            $extension = in_array($extension, ['jfif', 'jpeg', 'jpg'], true) ? 'jpg' : $extension;

            // ตั้งชื่อไฟล์ใหม่เป็น ASCII (กันปัญหาภาษาไทย/ช่องว่างใน URL) และคงที่ รันซ้ำแล้วชื่อเดิม
            $target = $folder.'/demo-'.md5($name).'.'.$extension;
            if (! Storage::disk('public')->exists($target)) {
                Storage::disk('public')->put($target, File::get($file->getPathname()));
            }

            return $target;
        }

        return null;
    }
}
