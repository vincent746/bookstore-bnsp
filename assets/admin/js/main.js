
/* ── SIDEBAR TOGGLE ── */
let sidebarOpen = true;

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const header = document.getElementById('header');
    const main = document.getElementById('mainContent');
    const overlay = document.getElementById('overlay');
    const icon = document.getElementById('hamburgerIcon');
    const isMobile = window.innerWidth <= 768;

    sidebarOpen = !sidebarOpen;

    if (sidebarOpen) {
        sidebar.classList.remove('collapsed');
        if (!isMobile) {
            header.classList.remove('expanded');
            main.classList.remove('expanded');
        } else {
            overlay.classList.add('show');
        }
        icon.classList.replace('bi-list', 'bi-x-lg');
    } else {
        sidebar.classList.add('collapsed');
        header.classList.add('expanded');
        main.classList.add('expanded');
        overlay.classList.remove('show');
        icon.classList.replace('bi-x-lg', 'bi-list');
    }
}

function closeSidebar() {
    if (sidebarOpen) toggleSidebar();
}

if (window.innerWidth <= 768) {
    sidebarOpen = true;
    toggleSidebar();
}

/* ── USER DROPDOWN ── */
function toggleDropdown() {
    const ud = document.getElementById('userDropdown');
    ud.classList.toggle('open');
}

document.addEventListener('click', function (e) {
    const ud = document.getElementById('userDropdown');
    if (ud && !ud.contains(e.target)) ud.classList.remove('open');
});
