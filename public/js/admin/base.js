/**
 * =========================================
 * ADMIN BASE JAVASCRIPT
 * Sidebar toggle and responsive behavior
 * =========================================
 */

document.addEventListener("DOMContentLoaded", function () {
    // =========================================
    // Sidebar Toggle for Mobile
    // =========================================
    const sidebarToggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".admin-sidebar");

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener("click", function () {
            sidebar.classList.toggle("show");
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener("click", function (event) {
            const isClickInside =
                sidebar.contains(event.target) ||
                sidebarToggle.contains(event.target);

            if (!isClickInside && window.innerWidth <= 992) {
                sidebar.classList.remove("show");
            }
        });
    }

    // =========================================
    // Active Link Highlighting
    // =========================================
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll(".sidebar-nav .nav-item");

    navLinks.forEach((link) => {
        if (link.getAttribute("href") === currentPath) {
            link.classList.add("active");
        }
    });

    // =========================================
    // Auto-hide Flash Messages
    // =========================================
    const flashMessages = document.querySelectorAll(".alert");

    flashMessages.forEach((message) => {
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(message);
            bsAlert.close();
        }, 5000);
    });

    // =========================================
    // Responsive Table Wrapper
    // =========================================
    const tables = document.querySelectorAll("table");

    tables.forEach((table) => {
        if (!table.closest(".table-responsive")) {
            const wrapper = document.createElement("div");
            wrapper.className = "table-responsive";
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });

    // =========================================
    // Confirm Dialog Enhancement
    // =========================================
    const confirmForms = document.querySelectorAll('form[onsubmit*="confirm"]');

    confirmForms.forEach((form) => {
        form.addEventListener("submit", function (event) {
            const message = form
                .getAttribute("onsubmit")
                .match(/confirm\('(.*)'\)/)[1];
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // =========================================
    // Smooth Scroll to Top
    // =========================================
    const scrollToTopBtn = document.createElement("button");
    scrollToTopBtn.className = "scroll-to-top";
    scrollToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollToTopBtn.style.cssText = `
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #2563eb;
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
        transition: all 0.3s ease;
    `;

    document.body.appendChild(scrollToTopBtn);

    window.addEventListener("scroll", function () {
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.display = "flex";
        } else {
            scrollToTopBtn.style.display = "none";
        }
    });

    scrollToTopBtn.addEventListener("click", function () {
        window.scrollTo({
            top: 0,
            behavior: "smooth",
        });
    });

    scrollToTopBtn.addEventListener("mouseenter", function () {
        this.style.transform = "scale(1.1)";
    });

    scrollToTopBtn.addEventListener("mouseleave", function () {
        this.style.transform = "scale(1)";
    });

    // =========================================
    // Console Log
    // =========================================
    console.log(
        "%c InternMatch Admin Panel Loaded",
        "color: #2563eb; font-size: 16px; font-weight: bold;"
    );
});
