<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié - GestCool</title>
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
                <span class="text-xl sm:text-2xl">🔐</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Mot de passe oublié</h2>
            <p class="mt-1 sm:mt-2 text-white/80 text-sm sm:text-base">GestCool - Système de gestion de stock</p>
        </div>

        <!-- Password Reset Card -->
        <div class="glass-effect rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-8">
            <!-- Instructions -->
            <div class="mb-6 text-center">
                <p class="text-white/80 text-sm sm:text-base">
                    Mot de passe oublié ? Aucun problème. Indiquez-nous votre adresse email et nous vous enverrons un lien de réinitialisation.
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
            <div class="mb-4 bg-green-500/20 border border-green-500/50 rounded-lg p-3">
                <p class="text-green-300 text-sm text-center">
                    ✅ {{ session('status') }}
                </p>
            </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-[#ee8f13] to-[#cb6ce6] text-white py-3 px-4 rounded-lg font-semibold hover:from-[#d67f11] hover:to-[#b85acf] transform hover:scale-105 transition duration-200 shadow-lg text-sm sm:text-base"
                >
                    📧 Envoyer le lien de réinitialisation
                </button>

                <!-- Back to Login Link -->
                <div class="text-center pt-4">
                    <p class="text-white text-sm">
                        <a 
                            href="{{ route('login') }}" 
                            class="font-semibold text-[#ee8f13] hover:text-[#cb6ce6] transition duration-200 flex items-center justify-center"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Retour à la connexion
                        </a>
                    </p>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center">
            <p class="text-white/60 text-xs sm:text-sm">
                &copy; 2024 GestCool - Gestion de Stock Alimentaire
            </p>
        </div>

        <!-- Error Messages -->
        @if($errors->any())
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-3">
            <p class="text-red-300 text-sm text-center">
                ❌ Veuillez vérifier votre adresse email
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
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('focus', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
                });
                emailInput.addEventListener('blur', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                });

                // Validation en temps réel
                emailInput.addEventListener('input', function() {
                    if (this.value.includes('@')) {
                        this.classList.add('border-green-300');
                    } else {
                        this.classList.remove('border-green-300');
                    }
                });
            }

            // Empêcher le zoom sur iOS
            const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
            if (isIOS) {
                const inputs = document.querySelectorAll('input');
                inputs.forEach(input => {
                    input.addEventListener('focus', function() {
                        this.style.fontSize = '16px';
                    });
                });
            }
        });

        // Confirmation avant envoi
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const emailInput = document.getElementById('email');
                if (emailInput && !emailInput.value.includes('@')) {
                    e.preventDefault();
                    alert('Veuillez entrer une adresse email valide.');
                    emailInput.focus();
                }
            });
        }
    </script>
</body>
</html>