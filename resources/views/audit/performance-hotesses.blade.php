@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Performance des Hôtesses</h1>

    <!-- Cartes des 3 meilleures performances -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($topPerformances as $index => $performance)
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 
            @if($index === 0) border-green-500 
            @elseif($index === 1) border-blue-500 
            @else border-orange-500 @endif">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">
                    @if($index === 0) 🥇 
                    @elseif($index === 1) 🥈 
                    @else 🥉 @endif
                    {{ $performance['hotesse']->prenom }} {{ $performance['hotesse']->name }}
                </h3>
                <span class="text-sm bg-gray-100 px-2 py-1 rounded">
                    #{{ $index + 1 }}
                </span>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Total Ventes:</span>
                    <span class="font-semibold">{{ number_format($performance['total_ventes'], 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="flex justify-between">
                    <span>Commandes:</span>
                    <span>{{ $performance['total_commandes'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Efficacité:</span>
                    <span class="font-semibold {{ $performance['taux_efficacite'] >= 80 ? 'text-green-600' : ($performance['taux_efficacite'] >= 60 ? 'text-orange-600' : 'text-red-600') }}">
                        {{ $performance['taux_efficacite'] }}%
                    </span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Formulaire de recherche par hôtesse -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Recherche par Hôtesse</h2>
        <form action="{{ route('audit.performance-hotesses') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hôtesse</label>
                <select name="hotesse_id" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Sélectionner une hôtesse</option>
                    @foreach($hotesses as $hotesse)
                        <option value="{{ $hotesse->id }}" {{ request('hotesse_id') == $hotesse->id ? 'selected' : '' }}>
                            {{ $hotesse->prenom }} {{ $hotesse->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date début</label>
                <input type="date" name="date_debut" value="{{ request('date_debut') }}" 
                       class="w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date fin</label>
                <input type="date" name="date_fin" value="{{ request('date_fin') }}" 
                       class="w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 w-full">
                    Rechercher
                </button>
            </div>
        </form>
    </div>

    <!-- Résultats de la recherche -->
    @if($hotessePerformance)
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">
            Performance de {{ $hotessePerformance['hotesse']->prenom }} {{ $hotessePerformance['hotesse']->name }}
            @if($hotessePerformance['periode_debut'])
                <span class="text-sm text-gray-600">
                    ({{ $hotessePerformance['periode_debut'] }} à {{ $hotessePerformance['periode_fin'] }})
                </span>
            @endif
        </h2>

        <!-- Statistiques générales -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ $hotessePerformance['total_commandes'] }}</div>
                <div class="text-sm text-blue-800">Total Commandes</div>
            </div>
            <div class="bg-green-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($hotessePerformance['total_ventes'], 0, ',', ' ') }} FCFA</div>
                <div class="text-sm text-green-800">Chiffre d'Affaires</div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ $hotessePerformance['taux_soldees'] }}%</div>
                <div class="text-sm text-purple-800">Taux de Soldées</div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ $hotessePerformance['tables_affectees'] }}</div>
                <div class="text-sm text-orange-800">Tables Affectées</div>
            </div>
        </div>

        <!-- Produits les plus vendus -->
        <h3 class="text-lg font-semibold mb-4">Top 5 Produits Vendus</h3>
        <div class="overflow-x-auto">
            <table class="w-full bg-white rounded-lg">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-4 py-2 text-left">Produit</th>
                        <th class="px-4 py-2 text-right">Quantité</th>
                        <th class="px-4 py-2 text-right">Montant Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($hotessePerformance['top_produits'] as $produit)
                    <tr class="border-t">
                        <td class="px-4 py-2">{{ $produit['produit']->designation }}</td>
                        <td class="px-4 py-2 text-right">{{ $produit['quantite'] }}</td>
                        <td class="px-4 py-2 text-right">{{ number_format($produit['montant'], 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection