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



// ===== ตัวนับตัวอักษร (ช่องที่มี data-counter) =====
document.querySelectorAll('[data-counter]').forEach(function (field) {
    const counter = document.getElementById(field.dataset.counter);

    function update() {
        counter.textContent = field.value.length; // จำนวนตัวอักษรปัจจุบัน
    }

    field.addEventListener('input', update); // พิมพ์แล้วอัปเดต
    update();                                 // แสดงค่าตอนเปิดหน้า (หน้า edit มีข้อความเดิม)
});


// ===== รูปปก: คลิกกล่องเพื่อเลือกรูป แล้วย่อ + บีบอัดก่อนส่ง =====
const coverBox = document.getElementById('coverBox');                 // กล่องเส้นประ
const coverInput = document.getElementById('cover_image');            // ช่องเลือกไฟล์ (ซ่อน)
const coverData = document.getElementById('cover_image_data');        // hidden เก็บรูปที่บีบแล้ว
const coverPreview = document.getElementById('coverPreview');         // รูป preview
const coverPlaceholder = document.getElementById('coverPlaceholder'); // ข้อความ "คลิกเพื่ออัปโหลด"
const coverPickBtn = document.getElementById('coverPickBtn');         // ปุ่ม "เปลี่ยนรูป"
const coverError = document.getElementById('coverError');             // ข้อความ error

const MAX_SIZE = 1200;  // ด้านที่ยาวที่สุดไม่เกิน 1200px
const QUALITY = 0.8;    // คุณภาพ JPEG (0–1)

// ทำงานเฉพาะหน้าที่มีกล่องรูปปก (create / edit)
if (coverBox && coverInput && coverData) {

    // คลิกที่กล่อง → เปิดหน้าต่างเลือกไฟล์
    coverBox.addEventListener('click', function () {
        coverInput.click();
    });

    // คลิกปุ่ม "เปลี่ยนรูป" → เปิดหน้าต่างเลือกไฟล์
    coverPickBtn.addEventListener('click', function (e) {
        e.stopPropagation(); // กันไม่ให้ไปเรียก click ของกล่องซ้ำ (หน้าต่างจะเปิด 2 รอบ)
        coverInput.click();
    });

    // เมื่อเลือกไฟล์แล้ว
    coverInput.addEventListener('change', function () {
        const file = coverInput.files[0];
        coverError.textContent = ''; // ล้าง error เก่า

        if (!file) {
            return; // กดยกเลิก → ใช้รูปเดิมต่อ
        }

        // ไม่ใช่ไฟล์รูป → แจ้งเตือน
        if (!file.type.startsWith('image/')) {
            coverError.textContent = 'กรุณาเลือกไฟล์รูปภาพ';
            coverInput.value = '';
            return;
        }

        const reader = new FileReader();

        // 1) อ่านไฟล์เสร็จ → โหลดเป็นรูป
        reader.onload = function (e) {
            const img = new Image();

            // 2) โหลดรูปเสร็จ → ย่อด้วย canvas
            img.onload = function () {
                // อัตราย่อ: รูปเล็กกว่า 1200px อยู่แล้ว = ไม่ย่อ (scale = 1)
                const scale = Math.min(1, MAX_SIZE / img.width, MAX_SIZE / img.height);
                const width = Math.round(img.width * scale);
                const height = Math.round(img.height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';               // พื้นขาว (PNG พื้นใสจะไม่เป็นสีดำ)
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                // 3) แปลงเป็น JPEG แบบบีบอัด → ข้อความ Base64
                const dataUrl = canvas.toDataURL('image/jpeg', QUALITY);

                // 4) ใส่ค่าส่งไปกับฟอร์ม + อัปเดตหน้าตากล่อง
                coverData.value = dataUrl;
                coverPreview.src = dataUrl;
                coverPreview.style.display = 'block';    // แสดงรูป
                coverPlaceholder.style.display = 'none'; // ซ่อนข้อความ "คลิกเพื่ออัปโหลด"
                coverPickBtn.style.display = 'block';    // แสดงปุ่ม "เปลี่ยนรูป"
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
}