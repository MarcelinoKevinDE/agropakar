<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AgroPakar — Sistem Diagnosis Penyakit Tanaman')</title>
    <meta name="description" content="@yield('meta_description', 'Sistem pakar berbasis Certainty Factor untuk mendeteksi penyakit tanaman secara akurat dan cepat.')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN (replace with compiled asset in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans:    ['Plus Jakarta Sans', 'sans-serif'],
                        display: ['Syne', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                            950: '#052e16',
                        },
                        earth: {
                            50:  '#fafaf5',
                            100: '#f0f0e0',
                            200: '#d6d6b0',
                            300: '#b8b880',
                            400: '#8c8c50',
                            500: '#666630',
                            600: '#4a4a20',
                            700: '#353510',
                            800: '#202008',
                            900: '#0d0d00',
                        },
                    },
                    backgroundImage: {
                        'agro-gradient': 'linear-gradient(135deg, #0a1a0a 0%, #0f2d1a 40%, #0a1f10 100%)',
                        'glass-gradient': 'linear-gradient(135deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 100%)',
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'shimmer': 'shimmer 2s linear infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%':      { transform: 'translateY(-10px)' },
                        },
                        shimmer: {
                            '0%':   { backgroundPosition: '-200% 0' },
                            '100%': { backgroundPosition:  '200% 0' },
                        },
                    },
                },
            },
        }
    </script>

    <style>
        /* ---- Base ---- */
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #060e06;
            color: #e2f0e2;
            min-height: 100vh;
        }

        /* ---- Glassmorphism Card ---- */
        .glass-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.07) 0%, rgba(255,255,255,0.02) 100%);
            border: 1px solid rgba(255,255,255,0.10);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .glass-card-light {
            background: linear-gradient(135deg, rgba(34,197,94,0.10) 0%, rgba(16,185,129,0.05) 100%);
            border: 1px solid rgba(34,197,94,0.20);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        /* ---- Animated Background Orbs ---- */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { width: 600px; height: 600px; background: #16a34a; top: -200px; left: -200px; animation: float 8s ease-in-out infinite; }
        .orb-2 { width: 400px; height: 400px; background: #059669; bottom: -100px; right: -100px; animation: float 10s ease-in-out infinite reverse; }
        .orb-3 { width: 300px; height: 300px; background: #065f46; top: 40%; left: 60%; animation: float 12s ease-in-out infinite; }

        /* ---- Scrollbar ---- */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0a140a; }
        ::-webkit-scrollbar-thumb { background: #16a34a; border-radius: 3px; }

        /* ---- Noise Texture Overlay ---- */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.4;
            pointer-events: none;
            z-index: 0;
        }

        /* ---- Utility ---- */
        .content-layer { position: relative; z-index: 1; }

        /* ---- Transitions ---- */
        .transition-glass {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ---- Spinner ---- */
        @keyframes spin-smooth { to { transform: rotate(360deg); } }
        .spinner { animation: spin-smooth 0.75s linear infinite; }

        /* ---- Progress bar (CF meter) ---- */
        .cf-bar-fill {
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ---- Nav active ---- */
        .nav-link { position: relative; }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: #22c55e;
            transition: width 0.3s ease;
        }
        .nav-link:hover::after,
        .nav-link.active::after { width: 100%; }
    </style>

    @stack('styles')
</head>
<body>

    {{-- Background Orbs --}}
    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>
    <div class="orb orb-3" aria-hidden="true"></div>

    {{-- ============================================================
         NAVBAR
    ============================================================ --}}
    <nav class="content-layer sticky top-0 z-50 border-b border-white/5 glass-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">

                {{-- Brand --}}
                <a href="{{ route('diagnosa.index') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center shadow-lg shadow-brand-900/40 group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <span class="font-display text-xl font-bold text-white tracking-tight">
                        Agro<span class="text-brand-400">Pakar</span>
                    </span>
                </a>

                {{-- Nav Links --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('diagnosa.index') }}"
                       class="nav-link text-sm font-medium text-gray-300 hover:text-brand-400 transition-colors {{ request()->routeIs('diagnosa.index') ? 'text-brand-400 active' : '' }}">
                        Diagnosis
                    </a>
                    <a href="#panduan"
                       class="nav-link text-sm font-medium text-gray-300 hover:text-brand-400 transition-colors">
                        Panduan
                    </a>
                    <a href="#artikel"
                       class="nav-link text-sm font-medium text-gray-300 hover:text-brand-400 transition-colors">
                        Artikel
                    </a>
                </div>

                {{-- CTA --}}
                <a href="{{ route('diagnosa.index') }}"
                   class="hidden md:inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-brand-600 hover:bg-brand-500 text-white text-sm font-semibold transition-all duration-200 shadow-lg shadow-brand-900/30 hover:shadow-brand-700/40 hover:-translate-y-0.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Mulai Diagnosis
                </a>

                {{-- Mobile Menu Button --}}
                <button id="mobileMenuBtn" class="md:hidden p-2 rounded-lg text-gray-400 hover:text-white hover:bg-white/10 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobileMenu" class="md:hidden hidden border-t border-white/5 px-4 py-3 space-y-2">
            <a href="{{ route('diagnosa.index') }}" class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-brand-400 transition-colors">Diagnosis</a>
            <a href="#panduan" class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-brand-400 transition-colors">Panduan</a>
            <a href="#artikel" class="block px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 hover:text-brand-400 transition-colors">Artikel</a>
        </div>
    </nav>

    {{-- ============================================================
         FLASH MESSAGES
    ============================================================ --}}
    @if (session()->hasAny(['success', 'error', 'warning', 'info']))
    <div class="content-layer max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        @foreach (['success' => ['bg-brand-900/50 border-brand-500/30 text-brand-300', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                   'error'   => ['bg-red-900/50 border-red-500/30 text-red-300', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                   'warning' => ['bg-yellow-900/50 border-yellow-500/30 text-yellow-300', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                   'info'    => ['bg-blue-900/50 border-blue-500/30 text-blue-300', 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                  ] as $type => [$classes, $icon])
            @if (session($type))
            <div class="flex items-start gap-3 mb-3 p-4 rounded-xl border glass-card {{ $classes }}">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
                <p class="text-sm font-medium">{{ session($type) }}</p>
            </div>
            @endif
        @endforeach

        @if ($errors->any())
        <div class="flex items-start gap-3 mb-3 p-4 rounded-xl border bg-red-900/50 border-red-500/30 text-red-300 glass-card">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <ul class="list-disc list-inside space-y-1 text-sm font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
    @endif

    {{-- ============================================================
         MAIN CONTENT
    ============================================================ --}}
    <main class="content-layer">
        @yield('content')
    </main>

    {{-- ============================================================
         FOOTER
    ============================================================ --}}
    <footer class="content-layer mt-20 border-t border-white/5 glass-card">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-500 to-brand-700 flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                        </div>
                        <span class="font-display text-lg font-bold text-white">Agro<span class="text-brand-400">Pakar</span></span>
                    </div>
                    <p class="text-sm text-gray-400 leading-relaxed">
                        Sistem pakar berbasis Certainty Factor untuk deteksi penyakit tanaman secara cerdas dan akurat.
                    </p>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Fitur</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li><a href="{{ route('diagnosa.index') }}" class="hover:text-brand-400 transition-colors">Diagnosis Penyakit</a></li>
                        <li><a href="#panduan" class="hover:text-brand-400 transition-colors">Panduan Penggunaan</a></li>
                        <li><a href="#artikel" class="hover:text-brand-400 transition-colors">Artikel Agronomi</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-sm font-semibold text-white mb-4">Teknologi</h4>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li>Certainty Factor Algorithm</li>
                        <li>Laravel 13 Framework</li>
                        <li>Machine Learning Support</li>
                    </ul>
                </div>
            </div>

            <div class="mt-10 pt-6 border-t border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-gray-500">
                    &copy; {{ date('Y') }} AgroPakar. Dikembangkan untuk keperluan akademis dan riset pertanian.
                </p>
                <p class="text-xs text-gray-600">
                    Powered by <span class="text-brand-600">Laravel 13</span> &amp; Certainty Factor
                </p>
            </div>
        </div>
    </footer>

    {{-- Global Scripts --}}
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const mobileMenu    = document.getElementById('mobileMenu');
        if (mobileMenuBtn && mobileMenu) {
            mobileMenuBtn.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Auto-hide flash messages
        setTimeout(() => {
            document.querySelectorAll('[data-flash]').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(-8px)';
                setTimeout(() => el.remove(), 300);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>