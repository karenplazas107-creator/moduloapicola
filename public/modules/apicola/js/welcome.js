// Navbar scroll effect
        const navbar = document.getElementById('apNavbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const menuToggle = document.getElementById('apMenuToggle');
        const navLinks = document.getElementById('apNavLinks');
        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', () => {
                navLinks.classList.toggle('show');
                menuToggle.classList.toggle('active');
            });
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.ap-feature-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });

        // Datos pedagógicos completos para las 5 áreas apícolas
        const apFeaturesData = {
            apiarios: {
                badge: "Apiario Central • SENA La Angostura",
                icon: "fa-map-marked-alt",
                photo: "/modules/apicola/img/apiario_campo.jpg",
                photoTag: "Centro La Angostura • Apiario Central",
                photoDesc: "Georreferenciación y radio de pecoreo de 3 km a la redonda",
                title: "¿Qué es el Apiario SENA?",
                subtitle: "1 único apiario tecnificado con 20 colmenas a cargo de Sergio Barrera",
                lead: "Un apiario (o colmenar) es el espacio físico debidamente acondicionado donde se ubica y maneja el conjunto de colmenas de abejas melíferas (<em>Apis mellifera</em>). En el Centro de Formación Agroindustrial La Angostura disponemos de <strong>un único apiario centralizado</strong>, diseñado bajo rigurosos parámetros técnicos y de bioseguridad, el cual alberga actualmente <strong>20 colmenas tecnificadas</strong> a cargo del apicultor <strong>Sergio Barrera</strong>.",
                importance: "Concentrar las 20 colmenas en un apiario técnicamente planificado permite estandarizar las inspecciones sanitarias, garantizar una distancia de bioseguridad reglamentaria (> 200 m de caminos y corrales), optimizar las fuentes de néctar del centro y amortiguar el calor del Huila con microclima adecuado.",
                senaNote: "En el Centro La Angostura contamos con 1 único apiario con 20 colmenas activas a cargo del apicultor Sergio Barrera, donde los aprendices realizan prácticas reales de manejo técnico, multiplicación, nutrición y cosecha de miel.",
                galleryHeading: "Condiciones Técnicas del Apiario Central:",
                gallery: [
                    { name: "20 Colmenas", epoca: "Activas", tipo: "Tipo Langstroth", icon: "fa-boxes-stacked", color: "#10b981" },
                    { name: "Orientación Este", epoca: "Sol Matinal", tipo: "Salidas Tempranas", icon: "fa-compass", color: "#f59e0b" },
                    { name: "Aislamiento > 200m", epoca: "Bioseguro", tipo: "Comunidad Protegida", icon: "fa-shield-halved", color: "#0284c7" },
                    { name: "Bebederos Limpios", epoca: "Permanente", tipo: "Termorregulación 35°C", icon: "fa-droplet", color: "#8b5cf6" }
                ],
                points: [
                    { icon: "fa-cubes-stacked", title: "20 Colmenas Tecnificadas", desc: "Población de 20 colmenas tipo Langstroth sobre bases individuales con trampas antihormigas a cargo de Sergio Barrera." },
                    { icon: "fa-compass", title: "Orientación y Piqueras", desc: "Orientadas al Este para aprovechar la luz matutina e impulsar las salidas tempranas de pecoreo." },
                    { icon: "fa-droplet", title: "Fuentes de Agua Limpia", desc: "Bebederos limpios e hidratación permanente para que las abejas termorregulen el nido a 35°C." },
                    { icon: "fa-shield-halved", title: "Bioseguridad y Barreras", desc: "Aislamiento superior a 200 metros y barreras vegetales vivas que obligan a las abejas a ganar altura." }
                ]
            },
            miel: {
                badge: "Cosecha & Trazabilidad de Miel",
                icon: "fa-flask",
                photo: "/modules/apicola/img/miel_cosecha.jpg",
                photoTag: "Inocuidad y Maduración • SENA CEFA",
                photoDesc: "Maduración natural en celdas operculadas y extracción en frío",
                title: "¿Cómo se produce y cosecha la Miel?",
                subtitle: "De la flor al panal y del panal a la mesa con inocuidad",
                lead: "La miel es la sustancia dulce natural que las abejas producen a partir del néctar de las flores. Las abejas lo transforman combinándolo con enzimas específicas de su organismo, lo deshidratan en los alvéolos del panal mediante corrientes de aire generadas por su aleteo y lo sellan con cera pura (operculado) al alcanzar su madurez.",
                importance: "Es un alimento energético extraordinario, rico en bioflavonoides, enzimas activas y agentes bactericidas naturales. Una cosecha higiénica bajo Buenas Prácticas de Manufactura asegura su inocuidad y mantiene intactas sus propiedades nutricionales sin necesidad de calentamiento.",
                senaNote: "En la sala de extracción de La Angostura, los aprendices emplean centrífugas radiales en acero inoxidable y refractómetros digitales para certificar miel virgen sin adulteración.",
                galleryHeading: "Parámetros de Calidad de la Miel Pura:",
                gallery: [
                    { name: "Operculado > 80%", epoca: "Madurez", tipo: "Sello Natural Cera", icon: "fa-circle-check", color: "#10b981" },
                    { name: "Humedad < 18.5%", epoca: "Norma", tipo: "Control Refractómetro", icon: "fa-droplet", color: "#f59e0b" },
                    { name: "Centrifugado Frío", epoca: "Inocuo", tipo: "Conserva Enzimas", icon: "fa-gears", color: "#0284c7" },
                    { name: "Trazabilidad", epoca: "Origen", tipo: "Lote y Floración", icon: "fa-barcode", color: "#8b5cf6" }
                ],
                points: [
                    { icon: "fa-check-double", title: "Operculado Maduro (>80%)", desc: "Se cosechan únicamente panales donde la mayor parte de las celdas ya han sido selladas con cera por las abejas." },
                    { icon: "fa-gauge-high", title: "Humedad Menor al 18.5%", desc: "Medición rigurosa con refractómetro para garantizar que la miel no fermente por acción de levaduras." },
                    { icon: "fa-gears", title: "Extracción en Frío", desc: "Centrifugado mecánico en frío que extrae la miel sin romper los panales de cera, permitiendo su reutilización." },
                    { icon: "fa-certificate", title: "Trazabilidad de Origen", desc: "Registro del apiario, fecha de castra y floración botánica predominante para garantizar pureza total." }
                ]
            },
            colmenas: {
                badge: "Manejo Técnico de Colmenas",
                icon: "fa-hive",
                photo: "/modules/apicola/img/colmenas_langstroth.jpg",
                photoTag: "Colmenas Langstroth • Más de 20 tecnificadas",
                photoDesc: "Estructura modular con cámara de cría y alzas melarias",
                title: "¿Cómo se manejan las Colmenas en el SENA?",
                subtitle: "Monitoreo integral de más de 20 colmenas tecnificadas",
                lead: "La colmena tecnificada (modelo Langstroth) es una estructura modular de cuadros móviles que respeta el 'espacio abeja' (6 a 9 mm). En nuestro apiario del SENA, cada una de las <strong>más de 20 colmenas</strong> alberga poblaciones que superan los 50.000 individuos perfectamente jerarquizados: la reina fértil, miles de obreras y los zánganos.",
                importance: "El seguimiento cuadro por cuadro a cada una de las más de 20 colmenas permite monitorear el vigor reproductivo de la reina, calcular las reservas de miel y polen, planificar la alimentación de sostén y mantener la sanidad del colmenar.",
                senaNote: "Con más de 20 colmenas activas en el apiario del SENA, los aprendices aprenden lectura de cuadros, monitoreo sanitario y registros digitales de producción.",
                galleryHeading: "Arquitectura Modular de la Colmena:",
                gallery: [
                    { name: "Cámara de Cría", epoca: "Nivel 1", tipo: "Postura Reina", icon: "fa-crown", color: "#10b981" },
                    { name: "Rejilla Excluidora", epoca: "Filtro", tipo: "Miel Pura en Alzas", icon: "fa-layer-group", color: "#f59e0b" },
                    { name: "Alzas Melarias", epoca: "Nivel 2 y 3", tipo: "Depósito de Miel", icon: "fa-box-archive", color: "#0284c7" },
                    { name: "Piquera Regulable", epoca: "Entrada", tipo: "Ventilación y Guardia", icon: "fa-door-open", color: "#8b5cf6" }
                ],
                points: [
                    { icon: "fa-crown", title: "Reina y Vigor Reproductivo", desc: "Reinas fecundadas de alta calidad genética que aseguran postura uniforme y colonias dóciles." },
                    { icon: "fa-layer-group", title: "Cámara de Cría vs Alzas", desc: "Separación clara entre la cámara inferior para reproducción y las alzas superiores para miel pura." },
                    { icon: "fa-users-gear", title: "Población y Cosecha", desc: "Monitoreo constante del volumen de abejas pecoreadoras para sincronizar la cosecha con las floraciones." },
                    { icon: "fa-stethoscope", title: "Sanidad y Control de Varroa", desc: "Muestreos periódicos para verificar niveles de infestación y aplicar manejos zootécnicos oportunos." }
                ]
            },
            floral: {
                badge: "Fenología & Nutrición Apícola",
                icon: "fa-seedling",
                photo: "/modules/apicola/img/bee_hero.jpg",
                photoTag: "Flora Melífera y Polinífera • Huila",
                photoDesc: "Sincronización del pecoreo con las curvas de floración",
                title: "¿Qué es el Calendario Floral?",
                subtitle: "La brújula botánica para sincronizar el apiario con la floración",
                lead: "El calendario floral es el registro sistemático y cronológico de los periodos de floración de las especies vegetales melíferas (productoras de néctar) y poliníferas (productoras de polen) presentes en el radio de pecoreo de las colmenas (zona de Campoalegre y cuenca del río Neiva).",
                importance: "Es la herramienta más estratégica del apicultor: permite preparar las colmenas semanas antes del gran flujo floral para que coincidan con la máxima población de pecoreadoras y colocar las alzas melarias a tiempo, evitando la pérdida de cosechas.",
                senaNote: "En La Angostura investigamos y caracterizamos la fenología del Matarratón, Guácimo, Cují, Café y Cítricos para optimizar el calendario productivo regional.",
                galleryHeading: "Especies Botánicas Melíferas Clave (Huila):",
                gallery: [
                    { name: "Matarratón", epoca: "Ene - Mar", tipo: "Néctar + Polen", icon: "fa-tree", color: "#10b981" },
                    { name: "Guácimo", epoca: "Feb - Abr", tipo: "Sostén Melífero", icon: "fa-leaf", color: "#f59e0b" },
                    { name: "Cují / Trupillo", epoca: "Jul - Ago", tipo: "Mielada Ámbar", icon: "fa-spa", color: "#0284c7" },
                    { name: "Café y Cítricos", epoca: "Abr & Oct", tipo: "Aroma y Calidad", icon: "fa-seedling", color: "#8b5cf6" }
                ],
                points: [
                    { icon: "fa-calendar-week", title: "Curva de Floración Anual", desc: "Diferenciación entre floraciones principales de mielada y floraciones de sostén en periodos lluviosos." },
                    { icon: "fa-spa", title: "Néctar vs Polen", desc: "Identificación de especies que impulsan el desarrollo de la cría (polen) y las que llenan las alzas (néctar)." },
                    { icon: "fa-bowl-food", title: "Alimentación Estratégica", desc: "Planificación de alimentación de sostén (jarabes y tortas proteicas) en épocas críticas para evitar la fuga de enjambres." },
                    { icon: "fa-leaf", title: "Conservación de Cercas Vivas", desc: "Fomento de siembra y protección de especies melíferas nativas para garantizar alimento permanente." }
                ]
            },
            inventario: {
                badge: "Bioseguridad & Herramientas",
                icon: "fa-box",
                photo: "/modules/apicola/img/inventario_herramientas.jpg",
                photoTag: "Indumentaria y Herramientas Técnicas",
                photoDesc: "Bioseguridad, ahumadores técnicos y control de existencias",
                title: "¿Por qué es crucial el Inventario en Apicultura?",
                subtitle: "Equipos de protección, herramientas e insumos al servicio de la seguridad",
                lead: "El inventario apícola comprende el control, mantenimiento preventivo y desinfección de los equipos de protección personal (overoles, caretas, guantes), herramientas de forja (ahumadores, espátulas) y material biológico y de madera (cámaras, alzas, marcos y cera).",
                importance: "El trabajo con abejas defensivas exige que ningún equipo falle en campo: un velo roto o un ahumador que se apague pone en riesgo al operario. Además, el control riguroso de herramientas evita la transmisión cruzada de patógenos entre apiarios.",
                senaNote: "En el taller y bodega apícola del SENA, los aprendices fabrican alzas, arman y alambran marcos, estampan cera pura de opérculo y desinfectan herramientas.",
                galleryHeading: "Equipamiento Técnico Esencial:",
                gallery: [
                    { name: "Overol y Careta", epoca: "Seguridad", tipo: "Dril Blanco / Malla", icon: "fa-vest", color: "#10b981" },
                    { name: "Ahumador Técnico", epoca: "Manejo", tipo: "Humo Frío Eucalipto", icon: "fa-smog", color: "#f59e0b" },
                    { name: "Palanca Apícola", epoca: "Inspección", tipo: "Acero Inoxidable", icon: "fa-screwdriver-wrench", color: "#0284c7" },
                    { name: "Cera Estampada", epoca: "Producción", tipo: "Guía Cera Pura", icon: "fa-certificate", color: "#8b5cf6" }
                ],
                points: [
                    { icon: "fa-vest", title: "Indumentaria Certificada", desc: "Overoles de dril o telas ventiladas de color blanco, caretas con malla metálica fija y guantes flexibles." },
                    { icon: "fa-smog", title: "Ahumador Técnico", desc: "Genera humo blanco y frío con viruta o eucalipto, bloqueando las feromonas de alarma de las abejas." },
                    { icon: "fa-toolbox", title: "Espátula o Palanca", desc: "Herramienta de acero inoxidable para despegar marcos fijados con propóleo sin causar sacudidas violentas." },
                    { icon: "fa-pump-soap", title: "Desinfección de Material", desc: "Flameado o desinfección de herramientas antes de pasar de una colmena a otra para prevenir enfermedades." }
                ]
            }
        };

        function selectFeature(featureKey) {
            const data = apFeaturesData[featureKey];
            if (!data) return;

            // Marcar tarjeta activa con el estilo verde claro
            document.querySelectorAll('.ap-feature-card').forEach(card => {
                if (card.getAttribute('data-feature') === featureKey) {
                    card.classList.add('active-feature');
                } else {
                    card.classList.remove('active-feature');
                }
            });

            // Actualizar textos e íconos en el panel de detalle
            document.getElementById('apDetailBadge').innerHTML = `<i class="fas fa-info-circle"></i> ${data.badge}`;
            document.getElementById('apDetailBigIcon').className = `fas ${data.icon}`;
            document.getElementById('apDetailTitle').textContent = data.title;
            document.getElementById('apDetailSubtitle').textContent = data.subtitle;
            document.getElementById('apDetailLead').innerHTML = data.lead;
            document.getElementById('apDetailImportance').innerHTML = data.importance;
            document.getElementById('apDetailSenaNote').innerHTML = data.senaNote;

            // Actualizar fotografía temática y badges
            const photoEl = document.getElementById('apDetailPhoto');
            const photoTagText = document.getElementById('apDetailPhotoTagText');
            const photoDesc = document.getElementById('apDetailPhotoDesc');
            if (photoEl && data.photo) {
                photoEl.src = data.photo;
            }
            if (photoTagText && data.photoTag) {
                photoTagText.textContent = data.photoTag;
            }
            if (photoDesc && data.photoDesc) {
                photoDesc.textContent = data.photoDesc;
            }

            // Inyectar galería de especies / elementos visuales
            const galleryWrap = document.getElementById('apDetailGalleryWrap');
            const galleryGrid = document.getElementById('apDetailGalleryGrid');
            const galleryHeading = document.getElementById('apGalleryHeadingText');
            if (galleryHeading && data.galleryHeading) {
                galleryHeading.textContent = data.galleryHeading;
            }
            if (galleryGrid && data.gallery && data.gallery.length > 0) {
                galleryGrid.innerHTML = '';
                data.gallery.forEach(item => {
                    const card = document.createElement('div');
                    card.className = 'ap-gallery-card';
                    card.style.borderLeftColor = item.color || '#10b981';
                    card.innerHTML = `
                        <div class="ap-gallery-icon" style="background: ${item.color}18; color: ${item.color};">
                            <i class="fas ${item.icon}"></i>
                        </div>
                        <div class="ap-gallery-info">
                            <strong>${item.name}</strong>
                            <div class="ap-gallery-meta">
                                <span class="ap-gallery-tag-epoca">${item.epoca}</span>
                                <span class="ap-gallery-tag-tipo">${item.tipo}</span>
                            </div>
                        </div>
                    `;
                    galleryGrid.appendChild(card);
                });
                if (galleryWrap) galleryWrap.style.display = 'block';
            } else if (galleryWrap) {
                galleryWrap.style.display = 'none';
            }

            // Inyectar los 4 puntos clave
            const pointsList = document.getElementById('apPointsList');
            pointsList.innerHTML = '';
            data.points.forEach(pt => {
                const ptCard = document.createElement('div');
                ptCard.className = 'ap-point-card';
                ptCard.innerHTML = `
                    <div class="ap-point-icon"><i class="fas ${pt.icon}"></i></div>
                    <div class="ap-point-info">
                        <strong>${pt.title}</strong>
                        <p>${pt.desc}</p>
                    </div>
                `;
                pointsList.appendChild(ptCard);
            });

            // Mostrar el contenedor con animación

            // Mostrar el contenedor con animación
            const detailWrapper = document.getElementById('apFeatureDetailWrapper');
            detailWrapper.classList.remove('is-open');
            void detailWrapper.offsetWidth; // Reflow
            detailWrapper.classList.add('is-open');

            // Desplazamiento suave al panel de detalle
            setTimeout(() => {
                const yOffset = -70;
                const y = detailWrapper.getBoundingClientRect().top + window.pageYOffset + yOffset;
                window.scrollTo({ top: y, behavior: 'smooth' });
            }, 60);
        }

        function closeFeatureDetail() {
            const detailWrapper = document.getElementById('apFeatureDetailWrapper');
            detailWrapper.classList.remove('is-open');
            document.querySelectorAll('.ap-feature-card').forEach(card => {
                card.classList.remove('active-feature');
            });
        }
