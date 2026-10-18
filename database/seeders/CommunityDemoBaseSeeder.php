<?php

namespace Database\Seeders;

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommunityDemoBaseSeeder extends Seeder
{
    use CopiesDemoImages;

    // สร้างคอมมูเดโม่ + เจ้าของกลุ่มเดโม่ + รูปปก (รันซ้ำได้ ไม่สร้างซ้ำ)
    // รูปปกอยู่ที่ database/seeders/demo-images/ แล้วคัดลอกไป storage/app/public/communities
    // รันด้วย: php artisan db:seed --class=CommunityDemoBaseSeeder
    public function run(): void
    {
        // 1) เจ้าของกลุ่มเดโม่ 5 คน (demo.owner1..5@example.com / รหัสผ่าน password)
        $ownerNames = ['เจ้าของกลุ่ม หนึ่ง', 'เจ้าของกลุ่ม สอง', 'เจ้าของกลุ่ม สาม', 'เจ้าของกลุ่ม สี่', 'เจ้าของกลุ่ม ห้า'];
        $owners = [];
        foreach ($ownerNames as $i => $name) {
            $owners[$i + 1] = User::firstOrCreate(
                ['email' => 'demo.owner'.($i + 1).'@example.com'],
                ['name' => $name, 'password' => 'password']
            );
        }

        // 2) ข้อมูลคอมมู (ชื่อ / คำอธิบาย / กีฬา / privacy / เจ้าของ) รูปปกหาจากไฟล์ใน demo-images ที่ชื่อ = ชื่อคอมมู
        $items = [
            [
                'name' => 'KKU Games 2026',
                'description' => 'วันที่ 13-14 กุมภา วันวาเลนไทน์',
                'sport' => null,
                'privacy' => 'public',
                'owner' => 1,
                'position' => '50% 50%',
            ],
            [
                'name' => 'Gym Bro',
                'description' => '18.00 น. ยิมหลังมอ เอฟวรี่เดย์',
                'sport' => 'ฟิตเนส',
                'privacy' => 'public',
                'owner' => 3,
                'position' => '50% 50%',
            ],
            [
                'name' => 'ชมรมยิงปืนวิทย์คอม',
                'description' => 'รันโค้ดเหนื่อยแล้ว ออกมายิงปืนด้วยกันน',
                'sport' => 'กีฬาในร่ม',
                'privacy' => 'private',
                'owner' => 2,
                'position' => '53% 33%',
            ],
            [
                'name' => 'วิ่งเช้ารอบบึงสีฐาน',
                'description' => 'ชวนเพื่อนมาวิ่งรับอากาศเช้ารอบบึงสีฐาน ทุกวันอังคารและพฤหัสบดี 06.00 น. ระยะ 3–5 กม. วิ่งช้าๆ คุยกันได้ มือใหม่มาได้เลย ไม่ทิ้งกันแน่นอน นัดเจอที่ลานหน้าบึง',
                'sport' => 'วิ่ง',
                'privacy' => 'public',
                'owner' => 2,
                'position' => '51% 90%',
            ],
            [
                'name' => 'KKU Marathon Hunters',
                'description' => 'กลุ่มสำหรับสายล่าเหรียญมาราธอนและฮาล์ฟมาราธอน ซ้อมจริงจังสัปดาห์ละ 4 วัน มีตารางซ้อมให้ แชร์ข่าวงานวิ่งทั่วภาคอีสาน รับเฉพาะคนที่วิ่ง 10 กม. ได้ต่อเนื่องแล้ว กรุณาแนะนำตัวตอนขอเข้ากลุ่ม',
                'sport' => 'วิ่ง',
                'privacy' => 'private',
                'owner' => 2,
                'position' => '52% 74%',
            ],
            [
                'name' => 'ปั่นชิลล์รอบมอ',
                'description' => 'ปั่นจักรยานเที่ยวรอบมหาวิทยาลัยขอนแก่นทุกเย็นวันศุกร์ 17.30 น. ระยะทางประมาณ 15 กม. ความเร็วสบายๆ แวะถ่ายรูปและหาของกินหลังปั่น ใช้จักรยานแม่บ้านหรือเสือภูเขาก็มาได้',
                'sport' => 'ปั่นจักรยาน',
                'privacy' => 'public',
                'owner' => 2,
                'position' => '50% 46%',
            ],
            [
                'name' => 'Gym Buddy KKU',
                'description' => 'หาเพื่อนเล่นเวทที่ฟิตเนสมหาวิทยาลัย จับคู่ช่วยกัน spot และเช็คท่าให้กัน มีแชร์โปรแกรมเล่นแยกวันอก หลัง ขา ทุกวันจันทร์–ศุกร์ 18.00 น. เป็นต้นไป ทั้งมือใหม่และสายเล่นมานานมาได้หมด',
                'sport' => 'ฟิตเนส',
                'privacy' => 'public',
                'owner' => 2,
                'position' => '51% 17%',
            ],
            [
                'name' => 'แบดมินตันหลังเลิกเรียน',
                'description' => 'ตีแบดสนุกๆ หลังเลิกเรียนทุกวันพุธ 17.00–19.00 น. ที่ศูนย์กีฬา หมุนเวียนคู่กันเล่น ตีเป็นนิดหน่อยก็มาได้ เตรียมไม้มาเอง ลูกขนไก่หารกันในกลุ่ม',
                'sport' => 'แบดมินตัน',
                'privacy' => 'public',
                'owner' => 2,
                'position' => '49% 43%',
            ],
            [
                'name' => 'ฟุตซอลวิศวะ x วิทยา',
                'description' => 'ทีมฟุตซอลรวมนักศึกษาคณะวิศวะและวิทยาศาสตร์ เตะทุกเย็นวันอังคาร 18.00 น. จองสนามล่วงหน้า จำกัด 15 คนต่อสัปดาห์เพื่อให้ได้ลงเล่นทุกคน ขอเข้ากลุ่มแล้วรอแอดมินอนุมัติ',
                'sport' => 'ฟุตซอล',
                'privacy' => 'private',
                'owner' => 4,
                'position' => '51% 0%',
            ],
            [
                'name' => 'โยคะยามเย็น',
                'description' => 'ผ่อนคลายร่างกายและจิตใจหลังเรียนหนัก ด้วยโยคะพื้นฐาน ยืดเหยียดแก้ปวดหลังจากนั่งนาน ทุกวันพฤหัสบดี 17.30 น. ที่ลานหญ้าใกล้บึงสีฐาน นำเสื่อโยคะมาเอง เหมาะกับมือใหม่ 100%',
                'sport' => 'โยคะ',
                'privacy' => 'public',
                'owner' => 4,
                'position' => '48% 24%',
            ],
            [
                'name' => 'Hoop Night บาสกลางคืน',
                'description' => 'ชู้ตบาสกันใต้แสงไฟสนามกลางแจ้ง ทุกวันจันทร์และศุกร์ 19.00 น. แบ่งทีมเล่น 3 ต่อ 3 หรือ 5 ต่อ 5 ตามจำนวนคน เน้นสนุกและได้เหงื่อ ไม่ต้องเก่งก็มาได้',
                'sport' => 'บาสเกตบอล',
                'privacy' => 'public',
                'owner' => 5,
                'position' => '49% 100%',
            ],
            [
                'name' => 'เทนนิสมือใหม่หัดตี',
                'description' => 'กลุ่มสำหรับคนอยากเริ่มเล่นเทนนิส ฝึกพื้นฐานตั้งแต่จับไม้ ตีโฟร์แฮนด์ แบ็คแฮนด์ จนถึงเสิร์ฟ ทุกวันอาทิตย์ 07.00 น. รับจำนวนจำกัดเพื่อให้โค้ชพี่ๆ ดูแลได้ทั่วถึง',
                'sport' => 'เทนนิส',
                'privacy' => 'private',
                'owner' => 5,
                'position' => '44% 25%',
            ],
            [
                'name' => 'แอโรบิคเพื่อสุขภาพ ชุมชนหนองแวง',
                'description' => 'แอโรบิคเพื่อสุขภาพ สำหรับชาวชุมชนหนองแวง',
                'sport' => 'แอโรบิก',
                'privacy' => 'private',
                'owner' => 2,
                'position' => '51% 0%',
            ],
        ];

        foreach ($items as $item) {
            // ถ้ามีคอมมูชื่อนี้อยู่แล้ว: ไม่สร้างซ้ำ แต่ถ้ายังไม่มีรูปปกให้ใส่ให้
            $existing = Community::where('name', $item['name'])->first();
            if ($existing !== null) {
                if ($existing->cover_image === null) {
                    $cover = $this->copyDemoImage($item['name'], 'communities');
                    if ($cover !== null) {
                        $existing->update(['cover_image' => $cover]);
                    }
                }

                continue;
            }

            $sportId = $item['sport'] ? Sport::firstOrCreate(['name' => $item['sport']])->id : null;
            $coverPath = $this->copyDemoImage($item['name'], 'communities');
            $owner = $owners[$item['owner']];

            $community = Community::create([
                'name' => $item['name'],
                'description' => $item['description'],
                'sport_id' => $sportId,
                'privacy' => $item['privacy'],
                'cover_image' => $coverPath,
                'cover_position' => $item['position'],
                'created_by' => $owner->id,
            ]);

            // เจ้าของเป็นสมาชิก role=owner
            CommunityMember::firstOrCreate(
                ['community_id' => $community->id, 'user_id' => $owner->id],
                ['role' => 'owner', 'status' => 'approved']
            );
        }
    }
}
