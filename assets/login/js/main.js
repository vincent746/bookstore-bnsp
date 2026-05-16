
const form = document.getElementById('loginForm');
const emailEl = document.getElementById('email');
const passwordEl = document.getElementById('password');
const submitBtn = document.getElementById('submitBtn');
const btnText = document.getElementById('btnText');
const loginAlert = document.getElementById('loginAlert');
const alertMsg = document.getElementById('alertMsg');
const togglePwd = document.getElementById('togglePwd');
const eyeIcon = document.getElementById('eyeIcon');

togglePwd.addEventListener('click', () => {
    const isPassword = passwordEl.type === 'password';
    passwordEl.type = isPassword ? 'text' : 'password';
    eyeIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
});

form.addEventListener('submit', (e) => {
    const email = emailEl.value.trim();
    const pwd = passwordEl.value;

    if (!email || !pwd) {
        e.preventDefault();
        alertMsg.textContent = 'Email dan kata sandi wajib diisi.';
        loginAlert.classList.add('show');
        return;
    }

    submitBtn.disabled = true;
    btnText.textContent = 'Memverifikasi...';
});

emailEl.addEventListener('input', () => loginAlert.classList.remove('show'));
passwordEl.addEventListener('input', () => loginAlert.classList.remove('show'));
