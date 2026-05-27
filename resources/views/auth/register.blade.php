<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - GestCool</title>
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
        /* Empêcher le zoom sur iOS */
        @media screen and (max-width: 768px) {
            input, select, textarea {
                font-size: 16px !important;
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
            <h2 class="text-2xl sm:text-3xl font-bold text-white">Rejoindre GestCool</h2>
            <p class="mt-1 sm:mt-2 text-white/80 text-sm sm:text-base">Créez votre compte</p>
        </div>

        <!-- Register Card -->
        <div class="glass-effect rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-4">
                @csrf

                <!-- Name Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="prenom" class="block text-sm font-medium text-white mb-2">Prénom</label>
                        <input 
                            id="prenom" 
                            name="prenom" 
                            type="text" 
                            required
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                            placeholder="Votre prénom"
                            value="{{ old('prenom') }}"
                        >
                        @error('prenom')
                            <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="nom" class="block text-sm font-medium text-white mb-2">Nom</label>
                        <input 
                            id="nom" 
                            name="nom" 
                            type="text" 
                            required
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                            placeholder="Votre nom"
                            value="{{ old('nom') }}"
                        >
                        @error('nom')
                            <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact & Fonction -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="contact" class="block text-sm font-medium text-white mb-2">Contact</label>
                        <input 
                            id="contact" 
                            name="contact" 
                            type="tel"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                            placeholder="+225 XX XX XX XX"
                            value="{{ old('contact') }}"
                        >
                        @error('contact')
                            <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="fonction" class="block text-sm font-medium text-white mb-2">Fonction</label>
                        <select 
                            id="fonction" 
                            name="fonction"
                            class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        >
                            <option value="">Sélectionnez...</option>
                            <option value="caissier" {{ old('fonction') == 'caissier' ? 'selected' : '' }}>💰 Caissier</option>
                            <option value="gerant" {{ old('fonction') == 'gerant' ? 'selected' : '' }}>👔 Gérant</option>
                            <option value="admin" {{ old('fonction') == 'admin' ? 'selected' : '' }}>🔧 Admin</option>
                        </select>
                        @error('fonction')
                            <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-white mb-2">Email</label>
                    <input 
                        id="email" 
                        name="email" 
                        type="email" 
                        required
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        placeholder="votre@email.com"
                        value="{{ old('email') }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-white mb-2">Mot de passe</label>
                    <input 
                        id="password" 
                        name="password" 
                        type="password" 
                        required
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        placeholder="Minimum 8 caractères"
                    >
                    @error('password')
                        <p class="mt-1 text-sm text-red-300">{{ $message }}</p>
                    @enderror
                    <div class="password-strength mt-1">
                        <div class="text-xs text-white/60" id="password-strength-text">
                            🔒 Le mot de passe doit contenir au moins 8 caractères
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-white mb-2">Confirmer le mot de passe</label>
                    <input 
                        id="password_confirmation" 
                        name="password_confirmation" 
                        type="password" 
                        required
                        class="w-full px-3 sm:px-4 py-2 sm:py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-[#ee8f13] focus:border-transparent transition duration-200 text-sm sm:text-base"
                        placeholder="Confirmez votre mot de passe"
                    >
                    <div class="password-match mt-1">
                        <div class="text-xs text-white/60" id="password-match-text">
                            🔄 Les mots de passe doivent correspondre
                        </div>
                    </div>
                </div>

                <!-- Terms Agreement -->
                <div class="bg-white/5 rounded-lg p-3 border border-white/10">
                    <label class="flex items-start space-x-2">
                        <input 
                            type="checkbox" 
                            name="terms"
                            required
                            class="w-4 h-4 text-[#0b5f37] bg-white/10 border-white/20 rounded focus:ring-[#ee8f13] mt-1 flex-shrink-0"
                        >
                        <span class="text-xs text-white">
                            J'accepte les <a href="#" class="text-[#ee8f13] hover:underline">conditions d'utilisation</a> 
                            et la <a href="#" class="text-[#ee8f13] hover:underline">politique de confidentialité</a>
                        </span>
                    </label>
                    @error('terms')
                        <p class="mt-1 text-xs text-red-300">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Register Button -->
                <button 
                    type="submit"
                    id="register-button"
                    class="w-full bg-gradient-to-r from-[#ee8f13] to-[#cb6ce6] text-white py-3 px-4 rounded-lg font-semibold hover:from-[#d67f11] hover:to-[#b85acf] transform hover:scale-105 transition duration-200 shadow-lg text-sm sm:text-base disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                    S'INSCRIRE
                </button>

                <!-- Login Link -->
                <div class="text-center pt-3">
                    <p class="text-white text-sm">
                        Déjà un compte ? 
                        <a 
                            href="{{ route('login') }}" 
                            class="font-semibold text-[#ee8f13] hover:text-[#cb6ce6] transition duration-200"
                        >
                            Se connecter
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

        <!-- Messages d'alerte -->
        @if($errors->any())
        <div class="bg-red-500/20 border border-red-500/50 rounded-lg p-3">
            <p class="text-red-300 text-sm text-center">
                ❌ Veuillez corriger les erreurs dans le formulaire
            </p>
        </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('password_confirmation');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const passwordMatchText = document.getElementById('password-match-text');
            const registerButton = document.getElementById('register-button');
            const termsCheckbox = document.querySelector('input[name="terms"]');

            // Validation du mot de passe
            function validatePassword() {
                const password = passwordInput.value;
                let strength = 'Faible';
                let color = 'text-red-400';
                
                if (password.length >= 12) {
                    strength = 'Très fort';
                    color = 'text-green-400';
                } else if (password.length >= 10) {
                    strength = 'Fort';
                    color = 'text-green-400';
                } else if (password.length >= 8) {
                    strength = 'Moyen';
                    color = 'text-yellow-400';
                } else if (password.length > 0) {
                    strength = 'Faible';
                    color = 'text-red-400';
                } else {
                    strength = 'Le mot de passe doit contenir au moins 8 caractères';
                    color = 'text-white/60';
                }
                
                passwordStrengthText.innerHTML = `🔒 Force: <span class="${color} font-semibold">${strength}</span>`;
                
                return password.length >= 8;
            }

            // Validation de la confirmation du mot de passe
            function validatePasswordMatch() {
                const password = passwordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                
                if (confirmPassword.length === 0) {
                    passwordMatchText.innerHTML = '🔄 Les mots de passe doivent correspondre';
                    passwordMatchText.className = 'text-xs text-white/60';
                    return false;
                } else if (password === confirmPassword) {
                    passwordMatchText.innerHTML = '✅ Les mots de passe correspondent';
                    passwordMatchText.className = 'text-xs text-green-400';
                    return true;
                } else {
                    passwordMatchText.innerHTML = '❌ Les mots de passe ne correspondent pas';
                    passwordMatchText.className = 'text-xs text-red-400';
                    return false;
                }
            }

            // Validation générale du formulaire
            function validateForm() {
                const isPasswordValid = validatePassword();
                const isPasswordMatch = validatePasswordMatch();
                const isTermsAccepted = termsCheckbox.checked;
                
                if (isPasswordValid && isPasswordMatch && isTermsAccepted) {
                    registerButton.disabled = false;
                } else {
                    registerButton.disabled = true;
                }
            }

            // Événements
            passwordInput.addEventListener('input', validateForm);
            confirmPasswordInput.addEventListener('input', validateForm);
            termsCheckbox.addEventListener('change', validateForm);

            // Animation du logo
            const logo = document.querySelector('.text-xl, .text-2xl');
            if (logo) {
                setInterval(() => {
                    logo.style.transform = 'scale(1.1)';
                    setTimeout(() => {
                        logo.style.transform = 'scale(1)';
                    }, 500);
                }, 2000);
            }

            // Amélioration UX mobile
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.15)';
                });
                input.addEventListener('blur', function() {
                    this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                });
            });

            // Initial validation
            validateForm();
        });

        // Gestion du clavier virtuel sur mobile
        window.addEventListener('resize', function() {
            if (window.innerHeight < 500) {
                document.body.style.paddingBottom = '200px';
            } else {
                document.body.style.paddingBottom = '0';
            }
        });
    </script>
</body>
</html>