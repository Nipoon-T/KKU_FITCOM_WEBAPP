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


// ===== รูปปก: คลิกเพื่อเลือกรูป / ลากเพื่อปรับตำแหน่ง / ย่อ+บีบอัดก่อนส่ง =====
const coverBox = document.getElementById('coverBox');                 // กล่องเส้นประ
const coverInput = document.getElementById('cover_image');            // ช่องเลือกไฟล์ (ซ่อน)
const coverData = document.getElementById('cover_image_data');        // hidden: รูปที่บีบแล้ว
const coverPosition = document.getElementById('cover_position');      // hidden: ตำแหน่งรูป เช่น "50% 30%"
const coverPreview = document.getElementById('coverPreview');         // รูป preview
const coverPlaceholder = document.getElementById('coverPlaceholder'); // ข้อความ "คลิกเพื่ออัปโหลด"
const coverHint = document.getElementById('coverHint');               // ป้าย "ลากรูปเพื่อปรับตำแหน่ง"
const coverPickBtn = document.getElementById('coverPickBtn');         // ปุ่ม "เปลี่ยนรูป"
const coverError = document.getElementById('coverError');             // ข้อความ error

const MAX_SIZE = 1200;  // ด้านที่ยาวที่สุดไม่เกิน 1200px
const QUALITY = 0.8;    // คุณภาพ JPEG (0–1)

if (coverBox && coverInput && coverData) {

    // มีรูปอยู่แล้วตั้งแต่เปิดหน้าไหม (หน้า edit หรือฟอร์มเด้งกลับ) → ดูจากว่ารูปถูกซ่อนหรือเปล่า
    let hasCover = coverPreview.style.display !== 'none';

    // ----- เลือกรูป -----

    // คลิกกล่อง: ยังไม่มีรูป → เปิดหน้าต่างเลือกไฟล์ / มีรูปแล้ว → ไม่ทำอะไร (ใช้ลากแทน)
    coverBox.addEventListener('click', function () {
        if (!hasCover) {
            coverInput.click();
        }
    });

    // ปุ่ม "เปลี่ยนรูป"
    coverPickBtn.addEventListener('click', function (e) {
        e.stopPropagation(); // กันไม่ให้ไปเรียก click ของกล่องซ้ำ
        coverInput.click();
    });
    coverPickBtn.addEventListener('mousedown', function (e) {
        e.stopPropagation(); // กดปุ่มแล้วไม่ต้องเริ่มลากรูป
    });

    // เลือกไฟล์แล้ว → ย่อ + บีบอัด
    coverInput.addEventListener('change', function () {
        const file = coverInput.files[0];
        coverError.textContent = '';

        if (!file) {
            return; // กดยกเลิก → ใช้รูปเดิมต่อ
        }

        if (!file.type.startsWith('image/')) {
            coverError.textContent = 'กรุณาเลือกไฟล์รูปภาพ';
            coverInput.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            const img = new Image();

            img.onload = function () {
                // ย่อรูปด้วย canvas (รูปเล็กกว่า 1200px อยู่แล้ว = ไม่ย่อ)
                const scale = Math.min(1, MAX_SIZE / img.width, MAX_SIZE / img.height);
                const width = Math.round(img.width * scale);
                const height = Math.round(img.height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', QUALITY);

                // ใส่ค่าส่งไปกับฟอร์ม
                coverData.value = dataUrl;

                // รูปใหม่ → เริ่มที่กึ่งกลาง
                coverPosition.value = '50% 50%';
                coverPreview.style.objectPosition = '50% 50%';

                // อัปเดตหน้าตากล่อง
                coverPreview.src = dataUrl;
                coverPreview.style.display = 'block';
                coverPlaceholder.style.display = 'none';
                coverHint.style.display = 'block';
                coverPickBtn.style.display = 'block';
                hasCover = true;
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });

    // ----- ลากเพื่อปรับตำแหน่ง -----

    // แปลงข้อความ "50% 30%" → { x: 50, y: 30 }
    function parsePosition(value) {
        const parts = value.split(' ');
        const x = parseFloat(parts[0]);
        const y = parseFloat(parts[1]);
        return { x: isNaN(x) ? 50 : x, y: isNaN(y) ? 50 : y };
    }

    let dragging = false;              // กำลังลากอยู่ไหม
    let startPos = { x: 50, y: 50 };   // ตำแหน่งรูปตอนเริ่มลาก (%)
    let startPoint = { x: 0, y: 0 };   // ตำแหน่งเมาส์/นิ้วตอนเริ่มลาก (px)

    // เริ่มลาก
    function startDrag(clientX, clientY) {
        if (!hasCover) return; // ยังไม่มีรูป ไม่ต้องลาก
        dragging = true;
        startPos = parsePosition(coverPosition.value);
        startPoint = { x: clientX, y: clientY };
    }

    // ระหว่างลาก: คำนวณระยะที่เลื่อน แล้วแปลงเป็น % ของกล่อง
    function moveDrag(clientX, clientY) {
        if (!dragging) return;

        const rect = coverBox.getBoundingClientRect();
        const deltaX = ((clientX - startPoint.x) / rect.width) * 100;
        const deltaY = ((clientY - startPoint.y) / rect.height) * 100;

        // ลากขวา = เห็นส่วนซ้ายของรูปมากขึ้น จึงใช้ "ลบ" / จำกัดค่าไว้ที่ 0–100
        const x = Math.round(Math.min(100, Math.max(0, startPos.x - deltaX)));
        const y = Math.round(Math.min(100, Math.max(0, startPos.y - deltaY)));

        coverPreview.style.objectPosition = x + '% ' + y + '%'; // เลื่อนรูปทันที
        coverPosition.value = x + '% ' + y + '%';                // เก็บค่าไว้ส่งไปกับฟอร์ม
    }

    // ปล่อยเมาส์/นิ้ว
    function endDrag() {
        dragging = false;
    }

    // เมาส์
    coverBox.addEventListener('mousedown', function (e) { startDrag(e.clientX, e.clientY); });
    window.addEventListener('mousemove', function (e) { moveDrag(e.clientX, e.clientY); });
    window.addEventListener('mouseup', endDrag);

    // นิ้ว (มือถือ)
    coverBox.addEventListener('touchstart', function (e) {
        startDrag(e.touches[0].clientX, e.touches[0].clientY);
    });
    coverBox.addEventListener('touchmove', function (e) {
        moveDrag(e.touches[0].clientX, e.touches[0].clientY);
        if (dragging) e.preventDefault(); // กันไม่ให้หน้าเว็บเลื่อนตามตอนลากรูป
    }, { passive: false });
    coverBox.addEventListener('touchend', endDrag);
}