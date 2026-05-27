@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-[#0b5f37]">Ventes Multiples</h1>
        @php
            $caisse_ouverte = \App\Models\Caisse::where('user_id', Auth::id())->where('statut', 'ouverte')->exists();
        @endphp
        <a href="{{ route('ventes-multiples.create') }}" 
           class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] {{ !$caisse_ouverte && (Auth::user()->isCaissier()) ? 'opacity-50 cursor-not-allowed' : '' }}"
           @if(!$caisse_ouverte && Auth::user()->isCaissier()) onclick="event.preventDefault(); alert('Veuillez ouvrir votre caisse d\\'abord');" @endif>
            ➕ Nouvelle Vente Multiple
        </a>
    </div>

    <!-- Filtrage -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">🔍 Filtres</h3>
        <form method="GET" action="{{ route('ventes-multiples.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="date_debut" class="block text-sm font-medium text-gray-700">Date début</label>
                <input type="date" name="date_debut" id="date_debut" 
                       value="{{ request('date_debut') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
            </div>
            <div>
                <label for="date_fin" class="block text-sm font-medium text-gray-700">Date fin</label>
                <input type="date" name="date_fin" id="date_fin" 
                       value="{{ request('date_fin') }}"
                       class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
            </div>
            
            @if(Auth::user()->isGerant() || Auth::user()->isAdmin())
            <div>
                <label for="user_id" class="block text-sm font-medium text-gray-700">Caissier</label>
                <select name="user_id" id="user_id" 
                        class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-[#0b5f37] focus:border-[#0b5f37]">
                    <option value="">Tous les caissiers</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->prenom }} {{ $user->nom }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            
            <div class="flex items-end space-x-2">
                <button type="submit" class="bg-[#0b5f37] text-white px-4 py-2 rounded hover:bg-[#0a4d2c] w-full">
                    Filtrer
                </button>
                <a href="{{ route('ventes-multiples.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Vente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caissier</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nb Produits</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($ventes as $vente)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $vente->numero_vente }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $vente->user->prenom }} {{ $vente->user->nom }}
                        @if($vente->user_id === Auth::id())
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded ml-2">Vous</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $vente->lignes->count() }} produits
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#0b5f37]">
                        {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $vente->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="{{ route('ventes-multiples.show', $vente->id) }}" class="text-[#0b5f37] hover:text-[#0a4d2c] mr-3">
                            👁️ Voir
                        </a>
                        <a href="{{ route('ventes-multiples.receipt', $vente->id) }}" target="_blank" class="text-blue-600 hover:text-blue-900 mr-3">
                            🖨️ Reçu
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Aucune vente multiple trouvée pour les critères sélectionnés.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistiques -->
    @if($ventes->count() > 0)
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-[#8c52ff] text-white p-4 rounded">
            <h3 class="text-lg font-semibold">Total Ventes</h3>
            <p class="text-2xl font-bold">{{ number_format($ventes->sum('montant_total'), 0, ',', ' ') }} FCFA</p>
        </div>
        
        <div class="bg-[#ee8f13] text-white p-4 rounded">
            <h3 class="text-lg font-semibold">Nombre de Ventes</h3>
            <p class="text-2xl font-bold">{{ $ventes->count() }}</p>
        </div>
        
        <div class="bg-[#0b5f37] text-white p-4 rounded">
            <h3 class="text-lg font-semibold">Produits Vendus</h3>
            <p class="text-2xl font-bold">{{ $ventes->sum(function($vente) { return $vente->lignes->sum('quantite'); }) }}</p>
        </div>

        <div class="bg-[#cb6ce6] text-white p-4 rounded">
            <h3 class="text-lg font-semibold">Moyenne par vente</h3>
            <p class="text-2xl font-bold">
                {{ $ventes->count() > 0 ? number_format($ventes->avg('montant_total'), 0, ',', ' ') : 0 }} FCFA
            </p>
        </div>
    </div>
    @endif
</div>
@endsection