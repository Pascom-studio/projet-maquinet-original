@extends('layouts.app')

@section('content')
<div class="py-4">
    <div class="px-3 sm:px-6">
        <!-- En-tête -->
        <div class="mb-4">
            <h1 class="text-xl sm:text-2xl font-bold text-[#0b5f37]">Gestion de Caisse</h1>
            <p class="text-sm text-gray-600 mt-1">Gérez l'ouverture, la fermeture et les mouvements de caisse</p>
        </div>

        <!-- Statut de la caisse actuelle -->
        @if($caisse_actuelle)
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <div class="flex-1">
                    <div class="flex items-center mb-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                        <h3 class="text-lg font-semibold text-green-800">✅ Caisse Ouverte - {{ Auth::user()->prenom }} {{ Auth::user()->name }}</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-green-600">Ouverte le:</span>
                            <span class="font-medium text-green-800 ml-1">{{ $caisse_actuelle->date_ouverture->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-green-600">Solde d'ouverture:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->solde_ouverture, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span class="text-green-600">Total ventes:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->total_ventes, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span class="text-green-600">Total commandes soldées:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->total_commandes_soldees, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span class="text-green-600">Total approvisionnements:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->total_approvisionnements, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span class="text-green-600">Total retraits:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->total_retraits, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span class="text-green-600">Total dépenses:</span>
                            <span class="font-medium text-green-800 ml-1">{{ number_format($caisse_actuelle->total_depenses, 0, ',', ' ') }} F</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-green-600 font-bold">Solde actuel:</span>
                            <span class="font-bold text-green-800 text-lg ml-2">{{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                </div>
                <form action="{{ route('caisse.fermer') }}" method="POST" class="sm:w-auto w-full">
                    @csrf
                    <button type="submit" 
                            class="bg-red-600 text-white px-4 py-3 rounded hover:bg-red-700 font-semibold w-full sm:w-auto text-center"
                            onclick="return confirm('Êtes-vous sûr de vouloir fermer la caisse? Le solde de fermeture sera: {{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA')">
                        🔒 Fermer la Caisse
                    </button>
                </form>
            </div>
        </div>

        <!-- Section des mouvements de caisse -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <!-- Approvisionnement d'un caissier -->
            @if(auth()->user()->isAdmin() || auth()->user()->isGerant())
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                    <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-2">💰</span>
                    Approvisionner un Caissier
                </h4>
                <form action="{{ route('caisse.approvisionnement') }}" method="POST" id="form-approvisionnement">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">Caissier à approvisionner *</label>
                            <select name="user_id" required
                                    class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Sélectionner un caissier</option>
                                @foreach($caissiers as $caissier)
                                    @if($caissier->id != Auth::id())
                                    <option value="{{ $caissier->id }}" data-solde="{{ $caissier->solde_caisse ?? 0 }}">
                                        {{ $caissier->prenom }} {{ $caissier->name }} 
                                        @if($caissier->caisse_ouverte)
                                        - 💰 {{ number_format($caissier->solde_caisse ?? 0, 0, ',', ' ') }} FCFA
                                        @else
                                        - 🔒 Caisse fermée
                                        @endif
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">Montant *</label>
                            <input type="number" name="montant" required min="1" step="1" 
                                   max="{{ $caisse_actuelle->solde_actuel }}"
                                   class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Montant en FCFA">
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-blue-600">Votre solde: {{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA</span>
                                <span id="nouveau-solde-appro" class="text-green-600 font-medium"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-blue-700 mb-1">Motif *</label>
                            <input type="text" name="motif" required
                                   class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Motif de l'approvisionnement...">
                        </div>
                        <button type="submit" 
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 font-semibold text-sm">
                            💰 Approvisionner le Caissier
                        </button>
                    </div>
                </form>
            </div>

            <!-- Retrait d'un caissier -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-orange-800 mb-3 flex items-center">
                    <span class="bg-orange-100 text-orange-600 p-2 rounded-lg mr-2">💸</span>
                    Retrait d'un Caissier
                </h4>
                <form action="{{ route('caisse.retrait') }}" method="POST" id="form-retrait">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-orange-700 mb-1">Caissier à retirer *</label>
                            <select name="user_id" required
                                    class="w-full border border-orange-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">Sélectionner un caissier</option>
                                @foreach($caissiers as $caissier)
                                    @if($caissier->id != Auth::id() && $caissier->caisse_ouverte && ($caissier->solde_caisse ?? 0) > 0)
                                    <option value="{{ $caissier->id }}" data-solde="{{ $caissier->solde_caisse ?? 0 }}">
                                        {{ $caissier->prenom }} {{ $caissier->name }} 
                                        - 💰 {{ number_format($caissier->solde_caisse ?? 0, 0, ',', ' ') }} FCFA
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <p class="text-xs text-orange-600 mt-1">
                                Seuls les caissiers avec une caisse ouverte et un solde positif sont affichés
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-orange-700 mb-1">Montant *</label>
                            <input type="number" name="montant" required min="1" step="1" 
                                   class="w-full border border-orange-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Montant en FCFA" id="montant-retrait">
                            <div class="flex justify-between text-xs mt-1">
                                <span id="solde-caissier" class="text-orange-600">Solde du caissier: 0 FCFA</span>
                                <span id="nouveau-solde-retrait" class="text-green-600 font-medium"></span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-orange-700 mb-1">Motif *</label>
                            <input type="text" name="motif" required
                                   class="w-full border border-orange-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-orange-500 focus:border-orange-500"
                                   placeholder="Motif du retrait...">
                        </div>
                        <button type="submit" 
                                class="w-full bg-orange-600 text-white px-4 py-2 rounded hover:bg-orange-700 font-semibold text-sm">
                            💸 Retirer du Caissier
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Dépense -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <h4 class="text-lg font-semibold text-red-800 mb-3 flex items-center">
                    <span class="bg-red-100 text-red-600 p-2 rounded-lg mr-2">🧾</span>
                    Dépense
                </h4>
                <form action="{{ route('caisse.depense') }}" method="POST">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-1">Montant *</label>
                            <input type="number" name="montant" required min="1" step="1" max="{{ $caisse_actuelle->solde_actuel }}"
                                   class="w-full border border-red-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Montant en FCFA">
                            <p class="text-xs text-red-600 mt-1">
                                Solde disponible: {{ number_format($caisse_actuelle->solde_actuel, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-1">Description *</label>
                            <input type="text" name="description" required
                                   class="w-full border border-red-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500"
                                   placeholder="Nature de la dépense...">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-red-700 mb-1">Catégorie</label>
                            <select name="categorie" class="w-full border border-red-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">
                                <option value="frais_generaux">Frais généraux</option>
                                <option value="achat_matiere">Achat matière première</option>
                                <option value="entretien">Entretien & Réparation</option>
                                <option value="salaires">Salaires & Charges</option>
                                <option value="autres">Autres dépenses</option>
                                 <option value="autres">depôt bank</option>
                            </select>
                        </div>
                        <button type="submit" 
                                class="w-full bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-semibold text-sm">
                            💸 Enregistrer Dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @else
        <!-- Caisse fermée - Option d'ouverture -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-2xl">🔒</span>
                </div>
                <h3 class="text-lg font-semibold text-yellow-800 mb-2">Caisse Fermée</h3>
                <p class="text-yellow-700 mb-4">La caisse est actuellement fermée. Vous devez l'ouvrir pour effectuer des opérations.</p>
                
                @php
                    // CORRECTION : Pour l'Admin, calculer le solde de fermeture précédent
                    $solde_fermeture_precedent = 0;
                    $solde_ouverture_auto = 0;
                    
                    if (Auth::user()->isAdmin()) {
                        $derniere_caisse = App\Models\Caisse::where('user_id', Auth::id())
                                                           ->where('statut', 'fermee')
                                                           ->orderBy('date_fermeture', 'desc')
                                                           ->first();
                        $solde_fermeture_precedent = $derniere_caisse->solde_fermeture ?? 0;
                    } else {
                        // Pour caissier/gérant : logique inchangée
                        if (Auth::user()->isCaissier() || Auth::user()->isGerant()) {
                            $derniere_caisse = App\Models\Caisse::where('user_id', Auth::id())
                                                               ->where('statut', 'fermee')
                                                               ->orderBy('date_fermeture', 'desc')
                                                               ->first();
                            $solde_ouverture_auto = $derniere_caisse->solde_fermeture ?? 0;
                        }
                    }
                @endphp
                
                <form action="{{ route('caisse.ouvrir') }}" method="POST">
                    @csrf
                    
                    @if(Auth::user()->isAdmin())
                   
                    <div class="max-w-md mx-auto space-y-4">
                        @if($solde_fermeture_precedent > 0)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <div class="text-center">
                                <p class="text-sm text-blue-800 mb-1">
                                    Solde de fermeture précédent:
                                </p>
                                <p class="font-bold text-lg text-blue-600">
                                    {{ number_format($solde_fermeture_precedent, 0, ',', ' ') }} FCFA
                                </p>
                            </div>
                        </div>
                        @endif
                        
                        <div>
                            <label class="block text-sm font-medium text-yellow-700 mb-1">
                                Montant supplémentaire à ajouter *
                            </label>
                            <input type="number" name="solde_ouverture" required min="0" step="1"
                                   class="w-full border border-yellow-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-yellow-500 focus:border-yellow-500"
                                   placeholder="Montant supplémentaire en FCFA" value="0">
                            <p class="text-xs text-yellow-600 mt-1">
                                @if($solde_fermeture_precedent > 0)
                                Solde total d'ouverture: <span class="font-medium">{{ number_format($solde_fermeture_precedent, 0, ',', ' ') }} FCFA</span> + montant saisi
                                @else
                                Première ouverture de caisse - le montant saisi constituera le solde initial
                                @endif
                            </p>
                        </div>
                        <button type="submit" 
                                class="w-full bg-yellow-600 text-white px-4 py-3 rounded hover:bg-yellow-700 font-semibold">
                            🔓 Ouvrir la Caisse
                        </button>
                    </div>
                    @else
                    <!-- Pour le caissier et gérant : ouverture automatique (inchangé) -->
                    <div class="max-w-md mx-auto space-y-4">
                        <div class="bg-white border border-yellow-300 rounded-lg p-4">
                            <div class="text-center">
                                <p class="text-sm text-yellow-800 mb-2">
                                    @if($solde_ouverture_auto > 0)
                                    Solde de fermeture précédent: 
                                    <span class="font-bold text-lg text-green-600">
                                        {{ number_format($solde_ouverture_auto, 0, ',', ' ') }} FCFA
                                    </span>
                                    @else
                                    <span class="text-yellow-600">
                                        @if(Auth::user()->isGerant())
                                        Première ouverture de caisse en tant que gérant
                                        @else
                                        Première ouverture de caisse
                                        @endif
                                    </span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-600">
                                    La caisse s'ouvrira avec le solde de fermeture précédent
                                </p>
                            </div>
                        </div>
                        
                        <!-- Champ caché pour le solde d'ouverture -->
                        <input type="hidden" name="solde_ouverture" value="{{ $solde_ouverture_auto }}">
                        
                        <button type="submit" 
                                class="w-full bg-yellow-600 text-white px-4 py-3 rounded hover:bg-yellow-700 font-semibold">
                            @if($solde_ouverture_auto > 0)
                            🔓 Ouvrir la Caisse ({{ number_format($solde_ouverture_auto, 0, ',', ' ') }} FCFA)
                            @else
                            🔓 Ouvrir la Caisse
                            @endif
                        </button>
                    </div>
                    @endif
                </form>
            </div>
        </div>
        @endif

        <!-- Liste des caissiers avec leurs soldes (pour admin/gérant) -->
        @if((auth()->user()->isAdmin() || auth()->user()->isGerant()) && $caissiers->count() > 0)
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-4 py-3 border-b">
                <h3 class="text-lg font-semibold text-[#0b5f37] flex items-center">
                    <span class="bg-indigo-100 text-indigo-600 p-2 rounded-lg mr-3">👥</span>
                    Soldes des Caissiers
                </h3>
            </div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($caissiers as $caissier)
                        @if($caissier->id != Auth::id())
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">{{ $caissier->prenom }} {{ $caissier->name }}</h4>
                                <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800">
                                    {{ $caissier->role }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                <div class="flex justify-between items-center">
                                    <span>Solde caisse:</span>
                                    <span class="font-semibold text-lg {{ $caissier->solde_caisse > 0 ? 'text-[#0b5f37]' : 'text-gray-500' }}">
                                        {{ number_format($caissier->solde_caisse ?? 0, 0, ',', ' ') }} FCFA
                                    </span>
                                </div>
                                @if($caissier->caisse_ouverte)
                                <div class="flex items-center mt-1">
                                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-green-600 text-xs">Caisse ouverte</span>
                                </div>
                                @else
                                <div class="flex items-center mt-1">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full mr-2"></div>
                                    <span class="text-gray-500 text-xs">Caisse fermée</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Historique des caisses -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b">
                <h3 class="text-lg font-semibold text-[#0b5f37] flex items-center">
                    <span class="bg-purple-100 text-purple-600 p-2 rounded-lg mr-3">📋</span>
                    Historique de vos Caisses
                </h3>
            </div>
            
            <!-- Version desktop -->
            <div class="hidden sm:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Ouverture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date Fermeture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solde Ouverture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Solde Fermeture</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Commandes Soldées</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dépenses</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($caisses as $caisse)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $caisse->date_ouverture->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $caisse->date_fermeture ? $caisse->date_fermeture->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ number_format($caisse->solde_ouverture, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $caisse->solde_fermeture ? number_format($caisse->solde_fermeture, 0, ',', ' ') . ' F' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ number_format($caisse->total_commandes_soldees, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ number_format($caisse->total_depenses, 0, ',', ' ') }} F
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $caisse->statut === 'ouverte' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($caisse->statut) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Version mobile -->
            <div class="sm:hidden divide-y divide-gray-200">
                @foreach($caisses as $caisse)
                <div class="p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $caisse->statut === 'ouverte' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($caisse->statut) }}
                            </span>
                            <p class="text-xs text-gray-500 mt-1">
                                Ouverte: {{ $caisse->date_ouverture->format('d/m H:i') }}
                            </p>
                            @if($caisse->date_fermeture)
                            <p class="text-xs text-gray-500">
                                Fermée: {{ $caisse->date_fermeture->format('d/m H:i') }}
                            </p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">
                                {{ number_format($caisse->solde_ouverture, 0, ',', ' ') }} F
                            </p>
                            <p class="text-xs text-gray-600">
                                → {{ $caisse->solde_fermeture ? number_format($caisse->solde_fermeture, 0, ',', ' ') . ' F' : 'En cours' }}
                            </p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-xs text-gray-600">
                        <div>
                            <span>Commandes soldées:</span>
                            <span class="font-medium">{{ number_format($caisse->total_commandes_soldees, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span>Ventes:</span>
                            <span class="font-medium">{{ number_format($caisse->total_ventes, 0, ',', ' ') }} F</span>
                        </div>
                        <div>
                            <span>Dépenses:</span>
                            <span class="font-medium">{{ number_format($caisse->total_depenses, 0, ',', ' ') }} F</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const soldeActuel = {{ $caisse_actuelle->solde_actuel ?? 0 }};
    
    // Gestion de l'approvisionnement
    const formAppro = document.getElementById('form-approvisionnement');
    const montantAppro = formAppro?.querySelector('input[name="montant"]');
    const nouveauSoldeAppro = document.getElementById('nouveau-solde-appro');
    
    if (montantAppro && nouveauSoldeAppro) {
        montantAppro.addEventListener('input', function() {
            const montant = parseFloat(this.value) || 0;
            const nouveauSolde = soldeActuel - montant;
            
            if (montant > soldeActuel) {
                nouveauSoldeAppro.textContent = '❌ Solde insuffisant';
                nouveauSoldeAppro.className = 'text-red-600 font-medium';
            } else {
                nouveauSoldeAppro.textContent = `Nouveau solde: ${nouveauSolde.toLocaleString()} FCFA`;
                nouveauSoldeAppro.className = 'text-green-600 font-medium';
            }
        });
    }
    
    // Gestion du retrait
    const formRetrait = document.getElementById('form-retrait');
    const selectCaissier = formRetrait?.querySelector('select[name="user_id"]');
    const montantRetrait = document.getElementById('montant-retrait');
    const soldeCaissierSpan = document.getElementById('solde-caissier');
    const nouveauSoldeRetrait = document.getElementById('nouveau-solde-retrait');
    
    if (selectCaissier && montantRetrait) {
        let soldeCaissierActuel = 0;
        
        selectCaissier.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            soldeCaissierActuel = parseFloat(selectedOption.dataset.solde) || 0;
            soldeCaissierSpan.textContent = `Solde du caissier: ${soldeCaissierActuel.toLocaleString()} FCFA`;
            montantRetrait.setAttribute('max', soldeCaissierActuel);
            updateCalculRetrait();
        });
        
        montantRetrait.addEventListener('input', updateCalculRetrait);
        
        function updateCalculRetrait() {
            const montant = parseFloat(montantRetrait.value) || 0;
            const nouveauSoldeCaissier = soldeCaissierActuel - montant;
            const nouveauSoldeAdmin = soldeActuel + montant;
            
            if (montant > soldeCaissierActuel) {
                nouveauSoldeRetrait.textContent = '❌ Montant trop élevé';
                nouveauSoldeRetrait.className = 'text-red-600 font-medium';
            } else {
                nouveauSoldeRetrait.textContent = `Nouveau solde caissier: ${nouveauSoldeCaissier.toLocaleString()} FCFA`;
                nouveauSoldeRetrait.className = 'text-green-600 font-medium';
            }
        }
    }
    
    // Validation pour les dépenses
    const depenseMontant = document.querySelector('input[name="montant"][max]');
    if (depenseMontant) {
        depenseMontant.addEventListener('input', function() {
            const max = parseFloat(this.getAttribute('max')) || 0;
            const valeur = parseFloat(this.value) || 0;
            
            if (valeur > max) {
                this.classList.add('border-red-500', 'bg-red-50');
            } else {
                this.classList.remove('border-red-500', 'bg-red-50');
            }
        });
    }

    // Formatage automatique des montants
    const montantInputs = document.querySelectorAll('input[type="number"]');
    montantInputs.forEach(input => {
        input.addEventListener('blur', function(e) {
            const value = parseFloat(e.target.value);
            if (!isNaN(value) && value >= 0) {
                e.target.value = Math.floor(value);
            }
        });
    });
});
</script>

<style>
/* Améliorations pour mobile */
@media (max-width: 640px) {
    .bg-white {
        margin: 0 -0.5rem;
    }
}

/* Animation pour les cartes */
.bg-white {
    transition: all 0.2s ease;
}

.bg-white:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Style pour les indicateurs de statut */
.w-3.h-3 {
    flex-shrink: 0;
}
</style>
@endsection