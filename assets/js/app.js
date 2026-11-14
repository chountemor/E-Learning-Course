/* =========================================================
   eLEARNING COLLEGE — APP UI
========================================================= */
const body = document.body;

// Keep inline onclick handlers safe and available immediately.
window.toggleMobileMenu = function () {
    const nav = document.getElementById('mainNav');
    if (!nav) return;
    nav.classList.toggle('active');
    const button = document.querySelector('.mobile-menu-btn');
    if (button) {
        const isOpen = nav.classList.contains('active');
        button.setAttribute('aria-expanded', String(isOpen));
        button.setAttribute('aria-label', isOpen ? 'Close Menu' : 'Open Menu');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    const themeToggle = document.getElementById('themeToggle') || document.getElementById('toggle');

    themeToggle?.addEventListener('click', () => {
        body.classList.add('theme-changing');
        body.classList.toggle('dark');
        try {
            localStorage.setItem(
                'elearning-theme',
                body.classList.contains('dark') ? 'dark' : 'light'
            );
        } catch (e) {}
        setTimeout(() => body.classList.remove('theme-changing'), 300);
    });


    document.querySelectorAll('.main-nav a').forEach(link => {
        link.addEventListener('click', () => {
            document.getElementById('mainNav')?.classList.remove('active');
        });
    });

    // Small entrance animation for cards.
    const items = document.querySelectorAll('.cat-card,.course,.feature-card,.credential-card');
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: .08 });

        items.forEach(item => {
            item.classList.add('reveal-item');
            observer.observe(item);
        });
    }

    // Notification popover — works for guests, students and admins.
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationPanel = document.getElementById('notificationPanel');

    if (notificationToggle && notificationPanel) {
        notificationToggle.addEventListener('click', event => {
            event.stopPropagation();
            const open = !notificationPanel.hasAttribute('hidden');

            if (open) {
                notificationPanel.setAttribute('hidden', '');
            } else {
                notificationPanel.removeAttribute('hidden');
            }

            notificationToggle.setAttribute('aria-expanded', String(!open));
        });

        notificationPanel.addEventListener('click', event => event.stopPropagation());

        document.addEventListener('click', () => {
            notificationPanel.setAttribute('hidden', '');
            notificationToggle.setAttribute('aria-expanded', 'false');
        });
    }
});


/* =========================================================
   eLEARNING COLLEGE — MODERN MICRO INTERACTIONS
========================================================= */
document.addEventListener('DOMContentLoaded', () => {
    // Add a small scrolled state to the sticky header.
    const header = document.querySelector('.header');
    const updateHeader = () => {
        if (!header) return;
        header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    updateHeader();
    window.addEventListener('scroll', updateHeader, {passive:true});

    // Smooth scrolling for same-page anchors.
    document.querySelectorAll('a[href*="#"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const url = new URL(this.href, window.location.href);
            if (url.pathname !== window.location.pathname || !url.hash) return;
            const target = document.querySelector(url.hash);
            if (!target) return;
            e.preventDefault();
            target.scrollIntoView({behavior:'smooth', block:'start'});
        });
    });

    // Modern button feedback without adding external libraries.
    document.querySelectorAll('.btn, .login-button, .register-button').forEach(button => {
        button.addEventListener('click', () => {
            button.classList.add('is-pressed');
            window.setTimeout(() => button.classList.remove('is-pressed'), 180);
        });
    });
});
/* =========================================================
   DIPLOMAS & CERTIFICATES MENU
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const diplomaBtn = document.getElementById("diplomaMenuBtn");
    const diplomaPanel = document.getElementById("diplomaPanel");
    const diplomaClose = document.getElementById("diplomaClose");

    if (!diplomaBtn || !diplomaPanel) {
        return;
    }


    function openDiplomaPanel() {

        diplomaPanel.classList.add("active");

        diplomaPanel.setAttribute(
            "aria-hidden",
            "false"
        );

        diplomaBtn.classList.add("active");

        diplomaBtn.setAttribute(
            "aria-expanded",
            "true"
        );

    }


    function closeDiplomaPanel() {

        diplomaPanel.classList.remove("active");

        diplomaPanel.setAttribute(
            "aria-hidden",
            "true"
        );

        diplomaBtn.classList.remove("active");

        diplomaBtn.setAttribute(
            "aria-expanded",
            "false"
        );

    }


    /* Button */

    diplomaBtn.addEventListener("click", function (event) {

        event.preventDefault();

        event.stopPropagation();

        if (
            diplomaPanel.classList.contains("active")
        ) {

            closeDiplomaPanel();

        } else {

            openDiplomaPanel();

        }

    });


    /* Close */

    if (diplomaClose) {

        diplomaClose.addEventListener(
            "click",
            function () {

                closeDiplomaPanel();

            }
        );

    }


    /* Click outside */

    document.addEventListener(
        "click",
        function (event) {

            if (
                !diplomaPanel.contains(event.target) &&
                !diplomaBtn.contains(event.target)
            ) {

                closeDiplomaPanel();

            }

        }
    );


    /* ESC */

    document.addEventListener(
        "keydown",
        function (event) {

            if (event.key === "Escape") {

                closeDiplomaPanel();

            }

        }
    );


    /* Don't close when clicking inside */

    diplomaPanel.addEventListener(
        "click",
        function (event) {

            event.stopPropagation();

        }
    );

});
