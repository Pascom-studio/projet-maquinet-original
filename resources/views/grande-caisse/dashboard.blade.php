@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Grande Caisse Mobile</h1>

        <!-- Grille des comptes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($comptesMobileCaissiers as $compte)
                <div class="bg-white rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    {{ $compte->prenom }} {{ $compte->name }}
                                </h3>
                                <p class="text-sm text-gray-500">{{ $compte->email }}</p>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $compte->est_actif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $compte->est_actif ? 'Actif' : 'Inactif' }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Transactions totales:</span>
                                <span class="font-medium">{{ $compte->transactions_count }}</span>
                            </div>
                        </div>

                        <a href="{{ route('grande-caisse.compte-details', $compte->id) }}" 
                           class="w-full bg-[#0b5f37] text-white py-2 px-4 rounded-lg hover:bg-[#0a4d2c] transition-colors text-center block">
                            👁️ Voir les détails
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-12">
                    <div class="text-gray-400 text-4xl mb-4">🏦</div>
                    <p class="text-gray-500">Aucun compte mobile caissier regroupé</p>
                    <p class="text-sm text-gray-400 mt-2">
                        Les comptes vous seront assignés par le Super Admin
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection