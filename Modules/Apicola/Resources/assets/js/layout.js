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
