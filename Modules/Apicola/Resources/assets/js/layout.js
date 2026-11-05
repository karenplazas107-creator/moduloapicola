function showToast(message) {
            const toast = document.getElementById('apToast');
            const toastText = document.getElementById('apToastText');
            if (!toast || !toastText) return;
            toastText.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function toggleSidebarMenu() {
            document.getElementById('apSidebar').classList.toggle('open-sidebar');
        }

        function toggleSidebarSubmenu(id, el) {
    const parent = el.closest('.ap-nav-item');
    if (!parent) return;
    parent.classList.toggle('open');
}

// ==================== DROPDOWN PERFIL DE USUARIO ====================
function toggleProfileDropdown(e) {
    if (e) {
        e.stopPropagation();
    }
    const menu = document.getElementById('apProfileMenu');
    const chevron = document.getElementById('apProfileChevron');
    if (!menu) return;

    const isOpen = menu.classList.contains('is-open');
    if (isOpen) {
        menu.classList.remove('is-open');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    } else {
        menu.classList.add('is-open');
        if (chevron) chevron.style.transform = 'rotate(180deg)';
    }
}

// Cerrar dropdown al hacer clic en cualquier parte fuera
document.addEventListener('click', (e) => {
    const wrapper = document.querySelector('.ap-profile-dropdown-wrapper');
    const menu = document.getElementById('apProfileMenu');
    const chevron = document.getElementById('apProfileChevron');
    if (wrapper && menu && !wrapper.contains(e.target)) {
        menu.classList.remove('is-open');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
});
