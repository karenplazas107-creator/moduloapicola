const csrfToken = window.UsuariosConfig?.csrfToken || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
const routeStore = window.UsuariosConfig?.routeStore || '';
const routeSearchPerson = window.UsuariosConfig?.routeSearchPerson || '';
const baseUsersUrl = window.UsuariosConfig?.baseUsersUrl || '/apicola/usuarios';
const cursosMap = window.UsuariosConfig?.coursesMap || {};

    // Al cambiar la ficha, autocompletar el programa si existe en el sistema
    function onFichaChanged() {
        const fichaVal = document.getElementById('ficha').value.trim();
        if (cursosMap[fichaVal]) {
            document.getElementById('programa').value = cursosMap[fichaVal];
        }
    }

    // Selección interactiva de rol
    function seleccionarRolPill(slug) {
        document.getElementById('role_slug').value = slug;
        const btnAprendiz = document.getElementById('btnRoleAprendiz');
        const btnAdmin = document.getElementById('btnRoleAdmin');
        const fichaReq = document.getElementById('fichaReqBadge');
        const progReq = document.getElementById('progReqBadge');
        const inputFicha = document.getElementById('ficha');
        const inputProg = document.getElementById('programa');

        if (slug === 'apicola.admin') {
            btnAdmin.classList.add('active');
            btnAprendiz.classList.remove('active', 'aprendiz');
            if (fichaReq) fichaReq.style.display = 'none';
            if (progReq) progReq.style.display = 'none';
            if (inputFicha) inputFicha.required = false;
            if (inputProg) inputProg.required = false;
        } else {
            btnAprendiz.classList.add('active', 'aprendiz');
            btnAdmin.classList.remove('active');
            if (fichaReq) fichaReq.style.display = 'inline';
            if (progReq) progReq.style.display = 'inline';
            if (inputFicha) inputFicha.required = true;
            if (inputProg) inputProg.required = true;
        }
    }

    // Alternar visibilidad de contraseña
    function togglePasswordVisibility() {
        const passInput = document.getElementById('password');
        const eyeIcon = document.getElementById('iconEye');
        if (passInput.type === 'password') {
            passInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }

    // Abrir Modal para Registrar Nuevo Usuario
    function abrirModalCrear() {
        document.getElementById('formUsuario').reset();
        document.getElementById('userId').value = '';
        document.getElementById('modalTag').innerText = 'REGISTRO';
        document.getElementById('modalTitle').innerText = 'Registrar Nuevo Usuario';
        document.getElementById('modalSubtitle').innerText = 'Ingresa los datos personales y de formación para conceder acceso a la plataforma.';
        document.getElementById('btnSubmitText').innerText = 'Registrar Usuario';
        
        // Contraseña obligatoria en creación
        document.getElementById('password').required = true;
        document.getElementById('labelPassword').innerText = 'CONTRASEÑA *';
        document.getElementById('passwordHint').style.display = 'none';

        // Documento editable
        document.getElementById('document_number').readOnly = false;

        // Ocultar alerta de SICA
        const alertFound = document.getElementById('alertPersonFound');
        if (alertFound) alertFound.style.display = 'none';

        // Rol por defecto: Aprendiz
        seleccionarRolPill('apicola.aprendiz');

        document.getElementById('modalUsuarioBackdrop').classList.add('is-open');
    }

    // Cerrar modal
    function cerrarModalUsuario() {
        document.getElementById('modalUsuarioBackdrop').classList.remove('is-open');
    }

    // Consulta automática de persona en SICA por documento
    async function autoConsultarPersona() {
        const docNumber = document.getElementById('document_number').value.trim();
        if (!docNumber || docNumber.length < 5) return;
        if (document.getElementById('userId').value) return; // Si estamos editando, omitir

        const spinner = document.getElementById('docSearchSpinner');
        if (spinner) spinner.style.display = 'inline-block';

        try {
            const response = await fetch(routeSearchPerson, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ document_number: docNumber })
            });

            const data = await response.json();

            if (data.success && data.found) {
                const p = data.person;
                if (p.first_name) document.getElementById('first_name').value = p.first_name;
                if (p.last_name) document.getElementById('last_name').value = p.last_name;
                if (p.personal_email) document.getElementById('email').value = p.personal_email;
                if (p.ficha) {
                    document.getElementById('ficha').value = p.ficha;
                    onFichaChanged();
                }
                if (p.programa) document.getElementById('programa').value = p.programa;

                const alertFound = document.getElementById('alertPersonFound');
                const textFound = document.getElementById('textPersonFound');
                if (alertFound && textFound) {
                    if (data.has_user) {
                        if (data.has_apicola) {
                            textFound.innerHTML = `<strong>Persona encontrada en SICA:</strong> ${p.first_name} ${p.last_name || ''} ya tiene usuario (<code>${data.user.nickname}</code>) con acceso a Apícola. Al guardar se actualizará su información y rol.`;
                        } else {
                            textFound.innerHTML = `<strong>Usuario SICA detectado:</strong> ${p.first_name} ${p.last_name || ''} (<code>${data.user.nickname}</code>). Al guardar se le otorgará acceso directo al módulo Apícola.`;
                        }
                        document.getElementById('password').required = false;
                        document.getElementById('labelPassword').innerText = 'CONTRASEÑA (OPCIONAL - CONSERVA LA DE SICA)';
                        document.getElementById('passwordHint').style.display = 'block';
                    } else {
                        textFound.innerHTML = `<strong>Persona registrada en SICA:</strong> ${p.first_name} ${p.last_name || ''}. Se creará su cuenta de usuario para ingresar a Apícola.`;
                        document.getElementById('password').required = true;
                        document.getElementById('labelPassword').innerText = 'CONTRASEÑA *';
                        document.getElementById('passwordHint').style.display = 'none';
                    }
                    alertFound.style.display = 'flex';
                }
            } else {
                const alertFound = document.getElementById('alertPersonFound');
                if (alertFound) alertFound.style.display = 'none';
            }
        } catch (error) {
            console.error(error);
        } finally {
            if (spinner) spinner.style.display = 'none';
        }
    }

    // Editar Usuario
    async function editarUsuario(id) {
        try {
            const response = await fetch(`${baseUsersUrl}/${id}`, {
                headers: { 'Accept': 'application/json' }
            });
            const data = await response.json();

            if (data.success && data.user) {
                const u = data.user;
                const p = u.person || {};

                document.getElementById('userId').value = u.id;
                document.getElementById('modalTag').innerText = 'EDICIÓN';
                document.getElementById('modalTitle').innerText = 'Editar Usuario';
                document.getElementById('modalSubtitle').innerText = 'Actualiza los datos personales, formación o credenciales del usuario.';
                document.getElementById('btnSubmitText').innerText = 'Actualizar Usuario';

                // Campos
                document.getElementById('first_name').value = u.first_name || p.first_name || '';
                document.getElementById('last_name').value = u.last_name || p.last_name || (p.first_last_name ? (p.first_last_name + ' ' + (p.second_last_name || '')) : '');
                document.getElementById('document_number').value = u.document_number || p.document_number || '';
                document.getElementById('document_number').readOnly = true;

                document.getElementById('ficha').value = u.ficha || p.ficha || '';
                document.getElementById('programa').value = u.programa || p.programa || '';
                if (!document.getElementById('programa').value && document.getElementById('ficha').value) {
                    onFichaChanged();
                }

                document.getElementById('email').value = u.email;

                // Contraseña opcional en edición
                document.getElementById('password').value = '';
                document.getElementById('password').required = false;
                document.getElementById('labelPassword').innerText = 'CAMBIAR CONTRASEÑA (OPCIONAL)';
                document.getElementById('passwordHint').style.display = 'block';

                // Ocultar alerta de SICA
                const alertFound = document.getElementById('alertPersonFound');
                if (alertFound) alertFound.style.display = 'none';

                // Rol
                seleccionarRolPill(u.role_slug || 'apicola.aprendiz');

                document.getElementById('modalUsuarioBackdrop').classList.add('is-open');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo cargar la información del usuario.',
                confirmButtonColor: '#059669'
            });
        }
    }

    // Guardar Usuario (Crear o Actualizar)
    async function guardarUsuario(event) {
        event.preventDefault();

        const userId = document.getElementById('userId').value;
        const isEdit = Boolean(userId);
        const url = isEdit ? `${baseUsersUrl}/${userId}` : routeStore;
        const method = isEdit ? 'PUT' : 'POST';

        const btnSubmit = document.getElementById('btnSubmitUsuario');
        const originalText = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        btnSubmit.disabled = true;

        const payload = {
            first_name: document.getElementById('first_name').value,
            last_name: document.getElementById('last_name').value,
            document_number: document.getElementById('document_number').value,
            ficha: document.getElementById('ficha').value,
            programa: document.getElementById('programa').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value,
            role_slug: document.getElementById('role_slug').value,
        };

        try {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (response.ok && data.success) {
                cerrarModalUsuario();
                Swal.fire({
                    icon: 'success',
                    title: '¡Operación Exitosa!',
                    text: data.message,
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                let errorMsg = data.message || 'Ocurrió un error al procesar el formulario.';
                if (data.errors) {
                    const firstKey = Object.keys(data.errors)[0];
                    errorMsg = data.errors[firstKey][0];
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Verifica los datos',
                    text: errorMsg,
                    confirmButtonColor: '#059669'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error de red',
                text: 'No se pudo comunicar con el servidor.',
                confirmButtonColor: '#059669'
            });
        } finally {
            btnSubmit.innerHTML = originalText;
            btnSubmit.disabled = false;
        }
    }

    // Activar o Desactivar Usuario
    async function toggleEstadoUsuario(id, accion) {
        const confirmResult = await Swal.fire({
            title: accion === 'desactivar' ? '¿Desactivar usuario?' : '¿Activar usuario?',
            text: accion === 'desactivar' 
                ? 'El usuario no podrá iniciar sesión en la plataforma mientras esté inactivo.' 
                : 'El usuario podrá volver a ingresar al módulo Apícola normalmente.',
            icon: accion === 'desactivar' ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: accion === 'desactivar' ? '#ef4444' : '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: accion === 'desactivar' ? 'Sí, desactivar' : 'Sí, activar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirmResult.isConfirmed) return;

        try {
            const response = await fetch(`${baseUsersUrl}/${id}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Estado actualizado',
                    text: data.message,
                    confirmButtonColor: '#059669'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No permitido',
                    text: data.message || 'No fue posible cambiar el estado del usuario.',
                    confirmButtonColor: '#059669'
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error al intentar cambiar el estado.',
                confirmButtonColor: '#059669'
            });
        }
    }

    // Filtros en tiempo real en la tabla
    function aplicarFiltros() {
        const query = document.getElementById('searchInput').value.toLowerCase().trim();
        const roleFilter = document.getElementById('filterRole').value;
        const statusFilter = document.getElementById('filterEstado').value;

        const rows = document.querySelectorAll('#usersTableBody tr[data-id]');

        rows.forEach(row => {
            const textContent = row.textContent.toLowerCase();
            const role = row.getAttribute('data-role');
            const status = row.getAttribute('data-status');

            const matchesQuery = !query || textContent.includes(query);
            const matchesRole = roleFilter === 'all' || role === roleFilter;
            const matchesStatus = statusFilter === 'all' || status === statusFilter;

            if (matchesQuery && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    document.getElementById('searchInput').addEventListener('input', aplicarFiltros);
    document.getElementById('filterRole').addEventListener('change', aplicarFiltros);
    document.getElementById('filterEstado').addEventListener('change', aplicarFiltros);
