/* =========================================================
   eLEARNING COLLEGE
   FOOTER JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       BACK TO TOP
    ====================================================== */

    const backToTop = document.getElementById("backToTop");

    if (backToTop) {

        const toggleBackToTop = function () {

            if (window.scrollY > 350) {

                backToTop.classList.add("show");

            } else {

                backToTop.classList.remove("show");

            }

        };


        window.addEventListener(
            "scroll",
            toggleBackToTop,
            { passive: true }
        );


        backToTop.addEventListener("click", function () {

            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });

        });

    }


    /* =====================================================
       FOOTER CURRENT YEAR
       Optional fallback
    ====================================================== */

    const currentYear = document.getElementById("currentYear");

    if (currentYear) {

        currentYear.textContent =
            new Date().getFullYear();

    }


    /* =====================================================
       SOCIAL BUTTON RIPPLE EFFECT
    ====================================================== */

    const socialButtons =
        document.querySelectorAll(".social-btn");


    socialButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            button.classList.remove("social-click");

            void button.offsetWidth;

            button.classList.add("social-click");

        });

    });


    /* =====================================================
       FOOTER LINK ACTIVE STATE
    ====================================================== */

    const footerLinks =
        document.querySelectorAll(".footer-menu a");


    const currentPage =
        window.location.pathname.split("/").pop();


    footerLinks.forEach(function (link) {

        const linkPage =
            link.getAttribute("href");


        if (
            linkPage &&
            linkPage !== "#" &&
            linkPage === currentPage
        ) {

            link.classList.add("active");

        }

    });

});