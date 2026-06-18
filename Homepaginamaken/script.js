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
    const dropdown = document.getElementById("mijnTicketsDropdown");
    const menu = document.getElementById("dropdownMenu");
    menu.classList.toggle("open");
}

document.addEventListener("click", function(e) {
    const dropdown = document.getElementById("mijnTicketsDropdown");
    if (!dropdown) return;
    const menu = document.getElementById("dropdownMenu");
    if (!dropdown.contains(e.target)) {
        menu.classList.remove("open");
    }
});