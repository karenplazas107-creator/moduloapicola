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


// ==================== MOTIVO OBLIGATORIO AL DESACTIVAR / ELIMINAR ====================
// Muestra un cuadro que obliga a escribir por qué se desactiva o elimina algo.
// Devuelve una Promise con el motivo (texto) o null si el usuario cancela.
async function pedirMotivoBaja(opciones) {
    const o = Object.assign({
        titulo: '¿Desactivar?',
        texto: '',
        confirmar: 'Sí, desactivar',
        color: '#dc2626'
    }, opciones || {});

    if (typeof Swal === 'undefined') {
        const m = (window.prompt(`${o.titulo}\n${o.texto}\n\nEscribe el motivo (mínimo 5 caracteres):`) || '').trim();
        return m.length >= 5 ? m : null;
    }

    const res = await Swal.fire({
        title: o.titulo,
        html: `<div style="text-align:left; color:#475569; font-size:0.92rem; margin-bottom:6px;">${o.texto}</div>`,
        icon: 'warning',
        input: 'textarea',
        inputLabel: 'Motivo (obligatorio)',
        inputPlaceholder: 'Describe por qué se realiza esta acción...',
        inputAttributes: { maxlength: 500, 'aria-label': 'Motivo' },
        showCancelButton: true,
        confirmButtonColor: o.color,
        cancelButtonColor: '#64748b',
        confirmButtonText: o.confirmar,
        cancelButtonText: 'Cancelar',
        inputValidator: (value) => {
            const v = (value || '').trim();
            if (!v) return 'Debes escribir el motivo.';
            if (v.length < 5) return 'El motivo debe tener al menos 5 caracteres.';
            return null;
        }
    });

    return res.isConfirmed ? (res.value || '').trim() : null;
}
window.pedirMotivoBaja = pedirMotivoBaja;
