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
        menuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('show');
            menuToggle.classList.toggle('active');
        });

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
                title: "¿Qué es el Apiario SENA?",
                subtitle: "1 único apiario tecnificado con 20 colmenas a cargo de Sergio Barrera",
                lead: "Un apiario (o colmenar) es el espacio físico debidamente acondicionado donde se ubica y maneja el conjunto de colmenas de abejas melíferas (<em>Apis mellifera</em>). En el Centro de Formación Agroindustrial La Angostura disponemos de <strong>un único apiario centralizado</strong>, diseñado bajo rigurosos parámetros técnicos y de bioseguridad, el cual alberga actualmente <strong>20 colmenas tecnificadas</strong> a cargo del apicultor <strong>Sergio Barrera</strong>.",
                importance: "Concentrar las 20 colmenas en un apiario técnicamente planificado permite estandarizar las inspecciones sanitarias, garantizar una distancia de bioseguridad reglamentaria (> 200 m de caminos y corrales), optimizar las fuentes de néctar del centro y amortiguar el calor del Huila con microclima adecuado.",
                senaNote: "En el Centro La Angostura contamos con 1 único apiario con 20 colmenas activas a cargo del apicultor Sergio Barrera, donde los aprendices realizan prácticas reales de manejo técnico, multiplicación, nutrición y cosecha de miel.",
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
                title: "¿Cómo se produce y cosecha la Miel?",
                subtitle: "De la flor al panal y del panal a la mesa con inocuidad",
                lead: "La miel es la sustancia dulce natural que las abejas producen a partir del néctar de las flores. Las abejas lo transforman combinándolo con enzimas específicas de su organismo, lo deshidratan en los alvéolos del panal mediante corrientes de aire generadas por su aleteo y lo sellan con cera pura (operculado) al alcanzar su madurez.",
                importance: "Es un alimento energético extraordinario, rico en bioflavonoides, enzimas activas y agentes bactericidas naturales. Una cosecha higiénica bajo Buenas Prácticas de Manufactura asegura su inocuidad y mantiene intactas sus propiedades nutricionales sin necesidad de calentamiento.",
                senaNote: "En la sala de extracción de La Angostura, los aprendices emplean centrífugas radiales en acero inoxidable y refractómetros digitales para certificar miel virgen sin adulteración.",
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
                title: "¿Cómo se manejan las Colmenas en el SENA?",
                subtitle: "Monitoreo integral de más de 20 colmenas tecnificadas",
                lead: "La colmena tecnificada (modelo Langstroth) es una estructura modular de cuadros móviles que respeta el 'espacio abeja' (6 a 9 mm). En nuestro apiario del SENA, cada una de las <strong>más de 20 colmenas</strong> alberga poblaciones que superan los 50.000 individuos perfectamente jerarquizados: la reina fértil, miles de obreras y los zánganos.",
                importance: "El seguimiento cuadro por cuadro a cada una de las más de 20 colmenas permite monitorear el vigor reproductivo de la reina, calcular las reservas de miel y polen, planificar la alimentación de sostén y mantener la sanidad del colmenar.",
                senaNote: "Con más de 20 colmenas activas en el apiario del SENA, los aprendices aprenden lectura de cuadros, monitoreo sanitario y registros digitales de producción.",
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
                title: "¿Qué es el Calendario Floral?",
                subtitle: "La brújula botánica para sincronizar el apiario con la floración",
                lead: "El calendario floral es el registro sistemático y cronológico de los periodos de floración de las especies vegetales melíferas (productoras de néctar) y poliníferas (productoras de polen) presentes en el radio de pecoreo de las colmenas (zona de Campoalegre y cuenca del río Neiva).",
                importance: "Es la herramienta más estratégica del apicultor: permite preparar las colmenas semanas antes del gran flujo floral para que coincidan con la máxima población de pecoreadoras y colocar las alzas melarias a tiempo, evitando la pérdida de cosechas.",
                senaNote: "En La Angostura investigamos y caracterizamos la fenología del Matarratón, Guácimo, Cují, Café y Cítricos para optimizar el calendario productivo regional.",
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
                title: "¿Por qué es crucial el Inventario en Apicultura?",
                subtitle: "Equipos de protección, herramientas e insumos al servicio de la seguridad",
                lead: "El inventario apícola comprende el control, mantenimiento preventivo y desinfección de los equipos de protección personal (overoles, caretas, guantes), herramientas de forja (ahumadores, espátulas) y material biológico y de madera (cámaras, alzas, marcos y cera).",
                importance: "El trabajo con abejas defensivas exige que ningún equipo falle en campo: un velo roto o un ahumador que se apague pone en riesgo al operario. Además, el control riguroso de herramientas evita la transmisión cruzada de patógenos entre apiarios.",
                senaNote: "En el taller y bodega apícola del SENA, los aprendices fabrican alzas, arman y alambran marcos, estampan cera pura de opérculo y desinfectan herramientas.",
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
