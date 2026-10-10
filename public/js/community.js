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