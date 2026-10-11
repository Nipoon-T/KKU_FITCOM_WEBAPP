// ===== อนุมัติ / ปฏิเสธสมาชิก แบบ AJAX (ไม่ reload หน้า) =====

// หาทุกฟอร์มที่ติด class "js-member-action" (ฟอร์มอนุมัติ/ปฏิเสธ)
const memberForms = document.querySelectorAll('.js-member-action');

memberForms.forEach(function (form) {
    // เมื่อกดปุ่มในฟอร์ม
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // หยุดการส่งฟอร์มแบบปกติ (จะได้ไม่ reload หน้า)

        const button = form.querySelector('button');
        button.disabled = true; // ปิดปุ่มชั่วคราว กันกดซ้ำ

        // ส่งข้อมูลไปที่ URL เดิมของฟอร์ม (route approve / reject)
        fetch(form.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json', // บอก Laravel ว่าขอคำตอบเป็น JSON
            },
            body: new FormData(form), // ข้อมูลในฟอร์ม (มี _token จาก @csrf อยู่แล้ว)
        })
            .then(function (response) {
                // ถ้า server ตอบ error (เช่น 403 ไม่มีสิทธิ์) ให้ไปที่ catch
                if (!response.ok) {
                    throw new Error('request failed');
                }
                return response.json(); // แปลงคำตอบเป็น object
            })
            .then(function (data) {
                // 1) ลบแถวของสมาชิกคนนี้ออกจากหน้า
                form.closest('.member-row').remove();

                // 2) ลดตัวเลขจำนวนคำขอลง 1
                const countEl = document.getElementById('pendingCount');
                const newCount = Number(countEl.textContent) - 1;
                countEl.textContent = newCount;

                // 3) ถ้าไม่เหลือคำขอแล้ว แสดงข้อความ "ไม่มีคำขอ"
                if (newCount === 0) {
                    document.getElementById('pendingEmpty').style.display = 'block';
                }

                // 4) แสดงข้อความที่ Controller ส่งมา เช่น "อนุมัติ ZIOOZ แล้ว"
                showMessage(data.message, 'success');
            })
            .catch(function () {
                button.disabled = false; // เปิดปุ่มให้กดใหม่ได้
                showMessage('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', 'error');
            });
    });
});

// ฟังก์ชันแสดงข้อความแจ้งผลบนหน้า
function showMessage(text, type) {
    const box = document.getElementById('ajaxMessage');
    box.textContent = text;            // ใส่ข้อความ
    box.className = 'alert ' + type;   // ใช้สีตาม CSS: alert success / alert error
    box.style.display = 'block';       // แสดงกล่อง
}



// ===== ย่อ + บีบอัดรูปปกในเบราว์เซอร์ ก่อนส่งไป server =====
// ใช้ในหน้า create และ edit (หน้าอื่นที่ไม่มีช่องรูปปก โค้ดนี้จะไม่ทำงาน)

const coverInput = document.getElementById('cover_image');       // ช่องเลือกไฟล์
const coverData = document.getElementById('cover_image_data');   // hidden input เก็บรูปที่บีบแล้ว
const coverPreview = document.getElementById('coverPreview');    // รูป preview
const coverError = document.getElementById('coverError');        // ข้อความ error ฝั่ง JS

const MAX_SIZE = 1200;   // ด้านที่ยาวที่สุดไม่เกิน 1200px (พอสำหรับรูปปก)
const QUALITY = 0.8;     // คุณภาพ JPEG 0–1 (0.8 = ชัดพอ ไฟล์เล็ก)

// ทำงานเฉพาะหน้าที่มีช่องรูปปก
if (coverInput && coverData) {
    coverInput.addEventListener('change', function () {
        const file = coverInput.files[0];
        coverError.textContent = ''; // ล้าง error เก่า

        // ยกเลิกการเลือกไฟล์ → ล้างค่า
        if (!file) {
            coverData.value = '';
            coverPreview.style.display = 'none';
            return;
        }

        // ไม่ใช่ไฟล์รูป → แจ้งเตือน
        if (!file.type.startsWith('image/')) {
            coverError.textContent = 'กรุณาเลือกไฟล์รูปภาพ';
            coverInput.value = '';
            coverData.value = '';
            return;
        }

        const reader = new FileReader();

        // 1) อ่านไฟล์เสร็จ → เอาไปโหลดเป็นรูป
        reader.onload = function (e) {
            const img = new Image();

            // 2) โหลดรูปเสร็จ → ย่อขนาดด้วย canvas
            img.onload = function () {
                // คำนวณอัตราย่อ: ถ้ารูปเล็กกว่า 1200px อยู่แล้ว ไม่ต้องย่อ (scale = 1)
                const scale = Math.min(1, MAX_SIZE / img.width, MAX_SIZE / img.height);
                const width = Math.round(img.width * scale);
                const height = Math.round(img.height * scale);

                // สร้าง canvas (กระดาษวาดรูปที่มองไม่เห็น) ตามขนาดใหม่
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';            // ระบายพื้นขาวก่อน (PNG พื้นใสจะไม่กลายเป็นสีดำ)
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height); // วาดรูปลงไปในขนาดใหม่

                // 3) แปลงเป็น JPEG แบบบีบอัด → ได้ข้อความ "data:image/jpeg;base64,...."
                const dataUrl = canvas.toDataURL('image/jpeg', QUALITY);

                coverData.value = dataUrl;            // ใส่ใน hidden input เพื่อส่งไปกับฟอร์ม
                coverPreview.src = dataUrl;           // แสดง preview ด้วยรูปที่บีบแล้ว
                coverPreview.style.display = 'block';
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
}