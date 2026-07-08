const header = document.querySelector(".header");

window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", window.scrollY > 60);
});

document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener("click", function (e) {
        e.preventDefault();

        const target = document.querySelector(this.getAttribute("href"));

        if (target) {
            target.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    });
});

function toggleDropdown(e) {
    e.preventDefault();
    const dropdown = e.currentTarget.closest(".dropdown");
    if (!dropdown) return;

    const menu = dropdown.querySelector(".dropdown-menu");
    if (!menu) return;

    document.querySelectorAll(".dropdown-menu.open").forEach((openMenu) => {
        if (openMenu !== menu) openMenu.classList.remove("open");
    });

    menu.classList.toggle("open");
}

document.addEventListener("click", function(e) {
    document.querySelectorAll(".dropdown-menu.open").forEach((menu) => {
        const dropdown = menu.closest(".dropdown");
        if (!dropdown || !dropdown.contains(e.target)) {
            menu.classList.remove("open");
        }
    });
});

// ── Uitloggen modal ──
function openLogoutModal() {
    document.querySelectorAll(".dropdown-menu.open").forEach(m => m.classList.remove("open"));
    document.getElementById("logoutModal").classList.add("open");
    document.body.style.overflow = "hidden";
}

function closeLogoutModal() {
    document.getElementById("logoutModal").classList.remove("open");
    document.body.style.overflow = "";
}

function closeLogoutModalOutside(e) {
    if (e.target === document.getElementById("logoutModal")) {
        closeLogoutModal();
    }
}

document.addEventListener("keydown", function(e) {
    if (e.key === "Escape") closeLogoutModal();
});