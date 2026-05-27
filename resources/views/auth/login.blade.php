<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - GestCool</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #0b5f37 0%, #ee8f13 50%, #8c52ff 100%);
            min-height: 100vh;
        }
        .glass-effect {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        @media (max-width: 640px) {
            .glass-effect {
                background: rgba(255, 255, 255, 0.15);
                backdrop-filter: blur(15px);
            }
        }
    </style>
</head>
<body class="gradient-bg flex items-center justify-center p-4 min-h-screen">
    <div class="max-w-md w-full space-y-6">
        <!-- Header -->
        <div class="text-center">
            <div class="mx-auto w-16 h-16 sm:w-20 sm:h-20 bg-white rounded-full flex items-center justify-center mb-3 sm:mb-4 shadow-lg">
                <span class="text-xl sm:text-2xl">🍊</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white">GestCool</h2>
            <p class="mt-1 sm:mt-2 text-white/80 text-sm sm:text-base"></p>
        </div>

        <!-- Login Card -->
        <div class="glass-effect rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-4 sm:space-y-6">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">
                        Adresse Email
                    </label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required 
                        autofocus
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        placeholder="votre@email.com"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">
                        Mot de passe
                    </label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        placeholder="Votre mot de passe"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
                    <label class="flex items-center">
                        <input 
                            type="checkbox" 
                            name="remember"
                            class="w-4 h-4 text-[#0b5f37] bg-white/10 border-white/20 rounded focus:ring-[#ee8f13]"
                        >
                        <span class="ml-2 text-sm text-white">Se souvenir de moi</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a 
                            href="{{ route('password.request') }}" 
                            class="text-sm text-white hover:text-[#ee8f13] transition duration-200 text-center sm:text-right"
                        >
                            
                        </a>
                    @endif
                </div>

                <!-- Login Button -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-[#ee8f13] to-[#cb6ce6] text-white py-3 px-4 rounded-lg font-semibold hover:from-[#d67f11] hover:to-[#b85acf] transform hover:scale-105 transition duration-200 shadow-lg text-sm sm:text-base"
                >
                    SE CONNECTER
                </button>

                <!-- Register Link -->
                <div class="text-center pt-2">
                    <p class="text-white text-sm">
                        
                        <a 
                            href="{{ route('register') }}" 
                            class="font-semibold text-[#ee8f13] hover:text-[#cb6ce6] transition duration-200"
                        >
                            
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-white/60 text-xs sm:text-sm">
                &copy; Octobre 2025 GestCool
            </p>
        </div>

        <!-- Messages d'alerte -->
        @if($errors->any())
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-3">
            <p class="text-red-300 text-sm text-center">
                ❌ Veuillez vérifier vos informations de connexion
            </p>
        </div>
        @endif

        @if(session('status'))
        <div class="bg-green-500/20 border border-green-500/50 rounded-lg p-3">
            <p class="text-green-300 text-sm text-center">
                ✅ {{ session('status') }}
            </p>
        </div>
        @endif
    </div>

    <script>
        // Amélioration de l'UX mobile
        document.addEventListener('DOMContentLoaded', function() {
            const logo = document.querySelector('.text-xl, .text-2xl');
            
            // Animation du logo
            setInterval(() => {
                if (logo) {
                    logo.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        logo.style.transform = 'scale(1)';
                    }, 500);
                }
            }, 2000);

            // Amélioration du focus sur mobile
            const inputs = document.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
                });
                input.addEventListener('blur', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                });
            });

            // Empêcher le zoom sur les inputs sur iOS
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            if (isIOS) {
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.fontSize = '16px'; // Empêche le zoom automatique
                    });
                });
            }
        });

        // Validation en temps réel
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');

        if (emailInput) {
            emailInput.addEventListener('input', function() {
                if (this.value.includes('@')) {
                    this.classList.add('border-green-300');
                } else {
                    this.classList.remove('border-green-300');
                }
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                if (this.value.length >= 8) {
                    this.classList.add('border-green-300');
                } else {
                    this.classList.remove('border-green-300');
                }
            });
        }
    </script>
</body>
</html>