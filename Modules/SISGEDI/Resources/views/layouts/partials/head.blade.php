<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SISGEDI | {{ $title ?? 'Gestión Documental' }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('general/assets/img/cefaempresa.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="{{ asset('libs/Fontawesome6/css/fontawesome.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/solid.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/brands.css') }}" rel="stylesheet">
    <link href="{{ asset('libs/Fontawesome6/css/v5-font-face.css') }}" rel="stylesheet">

    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('AdminLTE/plugins/toastr/toastr.min.css') }}">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        sena: {
                            green:  '#39A900',
                            lime:   '#62E31D',
                            navy:   '#001A29',
                            navymd: '#002336',
                            navylt: '#00324D',
                            navydk: '#00131E',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'ui-sans-serif', 'system-ui'],
                    }
                }
            }
        }
    </script>

    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: #f0f4f8; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f0f4f8; }
        ::-webkit-scrollbar-thumb { background: #39A900; border-radius: 3px; }

        /* Shimmer verde */
        .shimmer-green {
            height: 3px;
            background: linear-gradient(90deg, #39A900, #62E31D, #39A900, #00324D, #39A900);
            background-size: 300% 100%;
            animation: shimmer 4s linear infinite;
            position: sticky;
            top: 67px;
            z-index: 49;
        }
        @keyframes shimmer { 0%{background-position:100%} 100%{background-position:-100%} }

        /* Nav links desktop */
        .nav-link-top {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .85rem;
            border-radius: .5rem;
            color: rgba(255,255,255,.8);
            font-size: .83rem;
            font-weight: 500;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        .nav-link-top:hover { background: rgba(57,169,0,.18); color: #62E31D; }
        .nav-active { background: rgba(57,169,0,.2); color: #62E31D; font-weight: 600; }

        /* Dropdown nav */
        .dropdown-link-top {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1rem;
            color: rgba(255,255,255,.8);
            font-size: .82rem;
            font-weight: 500;
            transition: background .15s, color .15s;
        }
        .dropdown-link-top:hover {
            background: rgba(57,169,0,.2);
            color: #62E31D;
        }

        /* Nav links móvil */
        .mobile-link-top {
            display: flex;
            align-items: center;
            gap: .6rem;
            padding: .65rem .75rem;
            border-radius: .5rem;
            color: rgba(255,255,255,.75);
            font-size: .85rem;
            font-weight: 500;
            transition: background .15s, color .15s;
        }
        .mobile-link-top:hover { background: rgba(57,169,0,.18); color: #62E31D; }

        /* Hover lift */
        .hover-lift { transition: transform .25s ease, box-shadow .25s ease; }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 14px 32px rgba(0,0,0,.12) !important;
        }

        /* Hero overlay */
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(135deg,
                rgba(0,35,55,.9) 0%,
                rgba(57,169,0,.6) 100%);
        }

        /* Section titles */
        .section-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #001A29;
            position: relative;
            padding-bottom: .75rem;
            margin-bottom: 1rem;
        }
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 48px; height: 3px;
            background: #39A900;
            border-radius: 2px;
        }
        .section-title.centered::after { left: 50%; transform: translateX(-50%); }

        /* Pulse verde */
        @keyframes pulse-green {
            0%   { box-shadow: 0 0 0 0 rgba(57,169,0,.7); }
            70%  { box-shadow: 0 0 0 10px rgba(57,169,0,0); }
            100% { box-shadow: 0 0 0 0 rgba(57,169,0,0); }
        }
        .pulse-green { animation: pulse-green 2.5s infinite; }

        /* Breadcrumb container */
        .breadcrumb-bar {
            background: rgba(0,26,41,.04);
            border-bottom: 1px solid rgba(0,26,41,.07);
            padding: .6rem 0;
        }
    </style>
</head>
