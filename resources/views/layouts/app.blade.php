<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GestCool - Gestion de Stock</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --navbar-bg: #{{ $themeColors['navbar_bg'] ?? '0b5f37' }};
            --footer-bg: #{{ $themeColors['footer_bg'] ?? '0b5f37' }};
            --primary-text: #{{ $themeColors['primary_text'] ?? 'ffffff' }};
            --hover-color: #{{ $themeColors['hover_color'] ?? 'ee8f13' }};
        }
        
        .nav-active {
            background-color: var(--hover-color);
        }
        
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1 0 auto;
            width: 100%;
        }
        
        footer {
            flex-shrink: 0;
            width: 100%;
        }
        
        /* Navigation mobile CORRIGÉE */
        @media (max-width: 768px) {
            .mobile-menu {
                position: fixed;
                top: 64px;
                left: 0;
                right: 0;
                background: var(--navbar-bg);
                max-height: 0;
                overflow: hidden;
                transition: max-height 0.3s ease-out;
                z-index: 1000;
                display: flex;
                flex-direction: column;
            }
            
            .mobile-menu.open {
                max-height: calc(100vh - 64px);
                overflow-y: auto;
            }
            
            .nav-item {
                padding: 16px 20px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
                display: flex;
                align-items: center;
                text-decoration: none;
                color: inherit;
                background: transparent;
                width: 100%;
                text-align: left;
                cursor: pointer;
            }
            
            .nav-item:hover {
                background-color: var(--hover-color);
            }
            
            .mobile-user-section {
                background: rgba(0, 0, 0, 0.2);
                padding: 16px 20px;
                margin-top: 8px;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            
            /* Bouton de déconnexion CORRIGÉ */
            .mobile-logout-fixed {
                position: sticky;
                bottom: 0;
                background: var(--navbar-bg);
                border-top: 2px solid rgba(255, 255, 255, 0.2);
                padding: 16px 20px;
                margin-top: auto; /* Pousse le bouton en bas */
            }
            
            .mobile-logout-button {
                width: 100%;
                background: #dc2626 !important;
                color: white;
                padding: 12px 16px;
                border-radius: 6px;
                font-weight: 600;
                display: flex;
                align-items: center;
                justify-content: center;
                border: none;
                cursor: pointer;
            }
            
            .mobile-logout-button:hover {
                background: #b91c1c !important;
            }
            
            body.menu-open {
                overflow: hidden;
            }

            /* Empêcher le débordement */
            .mobile-menu-content {
                flex: 1;
                overflow-y: auto;
            }
        }

        .menu-icon {
            transition: transform 0.3s ease;
        }
        
        .menu-icon.open {
            transform: rotate(90deg);
        }

        /* Correction du z-index */
        .sticky {
            z-index: 40;
        }
    </style>
</head>
<body class="bg-gray-50 flex flex-col min-h-screen">
    <!-- Navigation -->
    <nav class="bg-[var(--navbar-bg)] text-[var(--primary-text)] shadow-lg flex-shrink-0 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo et menu mobile -->
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">🍊 GestCool</h1>
                    
                    <button class="md:hidden ml-4 p-2 rounded-lg hover:bg-[var(--hover-color)] transition-colors" 
                            id="mobile-menu-button"
                            aria-label="Ouvrir le menu">
                        <svg class="w-6 h-6 menu-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Navigation desktop -->
                <div class="hidden md:flex space-x-1">
                    <!-- Dashboard -->
                    <a href="{{ route('dashboard') }}" 
                       class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                        Dashboard
                    </a>
                    
                    @auth
                        <!-- Mobile Money - UNIQUEMENT pour Mobile Caissier -->
                        @if(Auth::user()->isMobileCaissier())
                            <a href="{{ route('mobile-money.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('mobile-money.index') ? 'nav-active' : '' }}">
                                📱 Transactions
                            </a>
                            <a href="{{ route('mobile-money.historique') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('mobile-money.historique') ? 'nav-active' : '' }}">
                                Historique
                            </a>
                            <a href="{{ route('mobile-money.historique-commission') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('mobile-money.historique-commission') ? 'nav-active' : '' }}">
                                Commissions
                            </a>
                            <a href="{{ route('mobile-money.gestion') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('mobile-money.gestion') ? 'nav-active' : '' }}">
                                📦 Stock
                            </a>
                        @elseif(Auth::user()->isCaissier() || Auth::user()->isGerant() || Auth::user()->isAdmin())
                            <!-- Ventes et Commandes pour Caissier, Gérant, Admin -->
                            <a href="{{ route('ventes.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('ventes.index') || request()->routeIs('ventes.create') || request()->routeIs('ventes.show') || request()->routeIs('ventes.edit') ? 'nav-active' : '' }}">
                                Ventes
                            </a>
                            <a href="{{ route('commandes.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('commandes.*') ? 'nav-active' : '' }}">
                                Commandes
                            </a>
                            <a href="{{ route('ventes.historique') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->is('ventes/history/overview') || request()->is('ventes/history/*') ? 'nav-active' : '' }}">
                                Historique Ventes
                            </a>
                        @endif
                        
                        <!-- Gestion pour Admin et Gérant -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
                            <a href="{{ route('tables.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('tables.*') ? 'nav-active' : '' }}">
                                Tables
                            </a>
                            <a href="{{ route('products.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('products.*') ? 'nav-active' : '' }}">
                                Produits
                            </a>
                            <a href="{{ route('categories.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('categories.*') ? 'nav-active' : '' }}">
                                Catégories
                            </a>
                            <a href="{{ route('audit.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('audit.*') ? 'nav-active' : '' }}">
                                Audit
                            </a>
                        @endif
                        
                        <!-- Administration pour Admin et SuperAdmin -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                            <a href="{{ route('users.index') }}" 
                               class="px-3 py-2 rounded hover:bg-[var(--hover-color)] {{ request()->routeIs('users.*') ? 'nav-active' : '' }}">
                                Utilisateurs
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Section utilisateur desktop -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- Statut Caisse pour Caissier, Gérant, Admin et Mobile Caissier -->
                        @if(Auth::user()->isCaissier() || Auth::user()->isGerant() || Auth::user()->isAdmin() )
                            @php
                                $caisse_ouverte = \App\Models\Caisse::where('user_id', Auth::id())
                                                                  ->where('statut', 'ouverte')
                                                                  ->exists();
                            @endphp
                            <a href="{{ route('caisse.index') }}" 
                               class="hidden sm:flex px-3 py-1 rounded text-sm font-semibold 
                                      {{ $caisse_ouverte ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                {{ $caisse_ouverte ? '🏦 Caisse Ouverte' : '🔒 Caisse Fermée' }}
                            </a>
                        @endif

                        <!-- Informations utilisateur -->
                        <div class="hidden sm:block text-sm">
                            <span class="font-semibold">{{ Auth::user()->prenom }}</span>
                            <span class="bg-[#8c52ff] px-2 py-1 rounded text-xs">{{ Auth::user()->fonction }}</span>
                        </div>
                        
                        <!-- Bouton déconnexion desktop -->
                        <div class="hidden md:block">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="bg-[var(--hover-color)] hover:opacity-80 px-4 py-2 rounded text-sm text-white font-semibold transition-all">
                                    🚪 Déconnexion
                                </button>
                            </form>
                        </div>
                    @else
                        <!-- Liens pour les utilisateurs non connectés -->
                        <a href="{{ route('login') }}" class="text-sm hover:text-[var(--hover-color)]">Connexion</a>
                        <a href="{{ route('register') }}" 
                           class="bg-[var(--hover-color)] hover:opacity-80 px-3 py-2 rounded text-sm text-white">
                            Inscription
                        </a>
                    @endauth
                </div>
            </div>
            
            <!-- Menu mobile CORRIGÉ -->
            <div class="mobile-menu md:hidden" id="mobile-menu">
                <div class="mobile-menu-content">
                    <!-- Navigation principale -->
                    <a href="{{ route('dashboard') }}" 
                       class="nav-item {{ request()->routeIs('dashboard') ? 'nav-active' : '' }}">
                        📊 Dashboard
                    </a>
                    
                    @auth
                        <!-- Mobile Money - UNIQUEMENT pour Mobile Caissier -->
                        @if(Auth::user()->isMobileCaissier())
                            <a href="{{ route('mobile-money.index') }}" 
                               class="nav-item {{ request()->routeIs('mobile-money.index') ? 'nav-active' : '' }}">
                                📱 Transactions Mobile
                            </a>
                            <a href="{{ route('mobile-money.historique') }}" 
                               class="nav-item {{ request()->routeIs('mobile-money.historique') ? 'nav-active' : '' }}">
                                📈 Historique Transactions
                            </a>
                            <a href="{{ route('mobile-money.historique-commission') }}" 
                               class="nav-item {{ request()->routeIs('mobile-money.historique-commission') ? 'nav-active' : '' }}">
                                💸 Historique Commissions
                            </a>
                            <a href="{{ route('mobile-money.gestion') }}" 
                               class="nav-item {{ request()->routeIs('mobile-money.gestion') ? 'nav-active' : '' }}">
                                📦 Gestion Stock
                            </a>
                        @elseif(Auth::user()->isCaissier() || Auth::user()->isGerant() || Auth::user()->isAdmin())
                            <!-- Ventes et Commandes pour Caissier, Gérant, Admin -->
                            <a href="{{ route('ventes.index') }}" 
                               class="nav-item {{ request()->routeIs('ventes.index') || request()->routeIs('ventes.create') || request()->routeIs('ventes.show') || request()->routeIs('ventes.edit') ? 'nav-active' : '' }}">
                                💰 Ventes
                            </a>
                            <a href="{{ route('commandes.index') }}" 
                               class="nav-item {{ request()->routeIs('commandes.*') ? 'nav-active' : '' }}">
                                📋 Commandes
                            </a>
                            <a href="{{ route('ventes.historique') }}" 
                               class="nav-item {{ request()->is('ventes/history/overview') || request()->is('ventes/history/*') ? 'nav-active' : '' }}">
                                📈 Historique Ventes
                            </a>
                        @endif
                        
                        <!-- Gestion pour Admin et Gérant -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isGerant())
                            <a href="{{ route('tables.index') }}" 
                               class="nav-item {{ request()->routeIs('tables.*') ? 'nav-active' : '' }}">
                                🪑 Tables
                            </a>
                            <a href="{{ route('products.index') }}" 
                               class="nav-item {{ request()->routeIs('products.*') ? 'nav-active' : '' }}">
                                📦 Produits
                            </a>
                            <a href="{{ route('categories.index') }}" 
                               class="nav-item {{ request()->routeIs('categories.*') ? 'nav-active' : '' }}">
                                🏷️ Catégories
                            </a>
                            <a href="{{ route('audit.index') }}" 
                               class="nav-item {{ request()->routeIs('audit.*') ? 'nav-active' : '' }}">
                                🔍 Audit
                            </a>
                        @endif
                        
                        <!-- Administration pour Admin et SuperAdmin -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isSuperAdmin())
                            <a href="{{ route('users.index') }}" 
                               class="nav-item {{ request()->routeIs('users.*') ? 'nav-active' : '' }}">
                                👥 Utilisateurs
                            </a>
                        @endif
                    @endauth
                    
                    <!-- Section utilisateur mobile -->
                    @auth
                        <div class="mobile-user-section">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-[var(--hover-color)] rounded-full flex items-center justify-center font-semibold">
                                        {{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}{{ strtoupper(substr(Auth::user()->nom, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ Auth::user()->prenom }} {{ Auth::user()->nom }}</div>
                                        <span class="bg-[#8c52ff] px-2 py-1 rounded text-xs">{{ Auth::user()->fonction }}</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Statut Caisse mobile -->
                            @if(Auth::user()->isCaissier() || Auth::user()->isGerant() || Auth::user()->isAdmin() )
                                @php
                                    $caisse_ouverte = \App\Models\Caisse::where('user_id', Auth::id())
                                                                      ->where('statut', 'ouverte')
                                                                      ->exists();
                                @endphp
                                <a href="{{ route('caisse.index') }}" 
                                   class="w-full flex items-center justify-center px-4 py-2 rounded text-sm font-semibold mb-2
                                          {{ $caisse_ouverte ? 'bg-green-500 text-white' : 'bg-red-500 text-white' }}">
                                    {{ $caisse_ouverte ? '🏦 Caisse Ouverte' : '🔒 Caisse Fermée' }}
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>
                
                <!-- Bouton de déconnexion CORRIGÉ - SÉPARÉ DU CONTENU -->
                @auth
                <div class="mobile-logout-fixed">
                    <button type="button" 
                            class="mobile-logout-button"
                            onclick="handleMobileLogout()">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        Déconnexion
                    </button>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="flex-1 w-full py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
            <!-- Messages flash -->
            @if(session('success'))
                <div class="bg-[#25D366] text-white p-4 rounded mb-6 shadow">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-500 text-white p-4 rounded mb-6 shadow">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="bg-yellow-500 text-white p-4 rounded mb-6 shadow">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        {{ session('warning') }}
                    </div>
                </div>
            @endif

            @if(session('info'))
                <div class="bg-blue-500 text-white p-4 rounded mb-6 shadow">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('info') }}
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-[var(--footer-bg)] text-[var(--primary-text)] py-4 mt-8 flex-shrink-0">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-2 md:space-y-0">
                <div class="text-sm text-center md:text-left">
                    <span class="font-semibold">🍊 GestCool</span>
                </div>
             <div class="text-xs opacity-80 flex justify-between items-center" style="color:#ea912f;">

  <!-- Gauche -->
 <div>
  &copy; Mai 2025 By 
  <span style="color:#ffffff; font-weight:900; font-size:1.2rem; letter-spacing:1px;">
    GTL'SOLUS
  </span>
  <br>

  AVEC NOUS C'EST POSSIBLE...
</div>

  <!-- Droite -->
  <div>
    <a href="https://wa.me/22678606156?text={{ urlencode('Bonjour! Je suis ' . auth()->user()->name . ' et je vous contacte depuis lapplication GESTCOOL') }}"
   target="_blank"
   style="color:#ea912f; text-decoration:none; font-weight:600;">
   Nous contacter
</a>
  </div>

</div>
            </div>
        </div>
    </footer>

    <!-- Formulaire de déconnexion caché -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>
        // Gestion SIMPLIFIÉE et CORRECTE du menu mobile
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const body = document.body;
            const menuIcon = mobileMenuButton.querySelector('.menu-icon');
            
            function toggleMenu() {
                const isOpen = mobileMenu.classList.toggle('open');
                menuIcon.classList.toggle('open', isOpen);
                body.classList.toggle('menu-open', isOpen);
                
                // Changer l'icône
                if (isOpen) {
                    menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
                    mobileMenuButton.setAttribute('aria-label', 'Fermer le menu');
                } else {
                    menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
                    mobileMenuButton.setAttribute('aria-label', 'Ouvrir le menu');
                }
            }
            
            // Ouvrir/fermer le menu
            mobileMenuButton.addEventListener('click', function(e) {
                e.stopPropagation();
                e.preventDefault();
                toggleMenu();
            });
            
            // Fermer le menu en cliquant à l'extérieur
            document.addEventListener('click', function(event) {
                if (mobileMenu.classList.contains('open') && 
                    !mobileMenu.contains(event.target) && 
                    !mobileMenuButton.contains(event.target)) {
                    toggleMenu();
                }
            });
            
            // Fermer le menu en appuyant sur Echap
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && mobileMenu.classList.contains('open')) {
                    toggleMenu();
                }
            });
            
            // Fermer le menu après avoir cliqué sur un lien de navigation
            mobileMenu.querySelectorAll('.nav-item').forEach(link => {
                link.addEventListener('click', function(e) {
                    // Ne pas fermer si c'est un lien de caisse
                    if (!this.href.includes('caisse')) {
                        setTimeout(() => {
                            toggleMenu();
                        }, 300);
                    }
                });
            });
        });

        // Gestion CORRECTE de la déconnexion mobile
        function handleMobileLogout() {
            if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                // Fermer le menu mobile d'abord
                const mobileMenu = document.getElementById('mobile-menu');
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const menuIcon = mobileMenuButton.querySelector('.menu-icon');
                
                if (mobileMenu.classList.contains('open')) {
                    mobileMenu.classList.remove('open');
                    menuIcon.classList.remove('open');
                    menuIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
                    document.body.classList.remove('menu-open');
                }
                
                // Soumettre le formulaire de déconnexion
                document.getElementById('logout-form').submit();
            }
        }
        
        // Auto-dismiss des alertes
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.bg-\\[\\#25D366\\], .bg-red-500, .bg-yellow-500, .bg-blue-500');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                }, 5000);
            });
        });
    </script>
    
    @yield('scripts')
   
</body>
</html>