// ย่อรูปโปรไฟล์ในเบราว์เซอร์ก่อนส่งไป server
const avatarInput = document.getElementById('avatar');
const avatarData = document.getElementById('avatar_data');
const avatarPreview = document.getElementById('avatarPreview');
const avatarError = document.getElementById('avatarError');

const AVATAR_MAX_SIZE = 512;
const AVATAR_QUALITY = 0.8;

if (avatarInput && avatarData) {
    avatarInput.addEventListener('change', function () {
        const file = avatarInput.files[0];
        avatarError.textContent = '';

        if (!file) {
            avatarData.value = '';
            return;
        }

        if (!file.type.startsWith('image/')) {
            avatarError.textContent = 'กรุณาเลือกไฟล์รูปภาพ';
            avatarInput.value = '';
            avatarData.value = '';
            return;
        }

        const reader = new FileReader();

        reader.onload = function (e) {
            const img = new Image();

            img.onload = function () {
                const scale = Math.min(1, AVATAR_MAX_SIZE / img.width, AVATAR_MAX_SIZE / img.height);
                const width = Math.round(img.width * scale);
                const height = Math.round(img.height * scale);

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);
                ctx.drawImage(img, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', AVATAR_QUALITY);

                avatarData.value = dataUrl;
                avatarPreview.src = dataUrl;
            };

            img.src = e.target.result;
        };

        reader.readAsDataURL(file);
    });
}