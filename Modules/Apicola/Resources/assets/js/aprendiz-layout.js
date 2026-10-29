function toggleSidebarSubmenu(id, el) {
            const parent = el.closest('.ap-nav-item');
            if (!parent) return;
            parent.classList.toggle('open');
        }
