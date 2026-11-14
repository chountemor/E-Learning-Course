/* eLearning College — Login / Register popup */
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('authModal');
    const frame = document.getElementById('authModalFrame');
    const closeBtn = document.getElementById('authModalClose');
    const loginTab = document.getElementById('authModalLogin');
    const registerTab = document.getElementById('authModalRegister');
    if (!modal || !frame) return;

    const base = modal.dataset.base || '';
    let currentMode = 'login';

    function src(mode) {
        return base + '/Login/' + (mode === 'register' ? 'register.php' : 'login.php') + '?popup=1';
    }

    function open(mode) {
        currentMode = mode === 'register' ? 'register' : 'login';
        frame.src = src(currentMode);
        modal.hidden = false;
        document.body.classList.add('auth-modal-open');
        loginTab?.classList.toggle('active', currentMode === 'login');
        registerTab?.classList.toggle('active', currentMode === 'register');
        setTimeout(() => closeBtn?.focus(), 30);
    }

    function close() {
        modal.hidden = true;
        document.body.classList.remove('auth-modal-open');
        frame.src = 'about:blank';
    }

    document.querySelectorAll('[data-auth-modal]').forEach(el => {
        el.addEventListener('click', function (e) {
            e.preventDefault();
            open(this.dataset.authModal || 'login');
        });
    });

    loginTab?.addEventListener('click', () => open('login'));
    registerTab?.addEventListener('click', () => open('register'));
    closeBtn?.addEventListener('click', close);

    modal.addEventListener('click', e => {
        if (e.target === modal) close();
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.hidden) close();
    });

    window.addEventListener('message', function (event) {
        if (!event.data || typeof event.data !== 'object') return;
        if (event.data.type === 'auth-switch') open(event.data.mode || 'login');
        if (event.data.type === 'auth-close') close();
    });

    // Allow protected pages to request the popup automatically.
    if (new URLSearchParams(window.location.search).get('login') === '1') {
        open('login');
    }
});
