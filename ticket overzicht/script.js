const header = document.querySelector(".header");

window.addEventListener("scroll", () => {
    header.classList.toggle("scrolled", window.scrollY > 60);
});

document.addEventListener("click", function(e) {
    const dropdown = document.getElementById("mijnTicketsDropdown");
    if (!dropdown) return;
    const menu = document.getElementById("dropdownMenu");
    if (menu && !dropdown.contains(e.target)) {
        menu.classList.remove("open");
    }
});
// Dropdown toggle function
function toggleDropdown(btn) {
    const menu = btn.nextElementSibling;
    if (menu && menu.classList.contains('dropdown-menu')) {
        menu.classList.toggle('open');
        btn.classList.toggle('active');
    }
    // Close other dropdowns
    document.querySelectorAll('.dropdown-menu.open').forEach(m => {
        if (m !== menu) {
            m.classList.remove('open');
            m.previousElementSibling.classList.remove('active');
        }
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-menu.open').forEach(m => {
            m.classList.remove('open');
        });
        document.querySelectorAll('.dropdown-toggle.active').forEach(b => {
            b.classList.remove('active');
        });
    }
});
