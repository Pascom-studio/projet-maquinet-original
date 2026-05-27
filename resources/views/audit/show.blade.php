@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-[#0b5f37]">Détails de l'Audit</h1>
            <a href="{{ route('audit.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                ← Retour à l'audit
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-6 border-b">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Date/Heure</h3>
                        <p class="text-lg">{{ $audit->created_at->format('d/m/Y H:i:s') }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Utilisateur</h3>
                        <p class="text-lg">{{ $audit->user->prenom }} {{ $audit->user->nom }}</p>
                        <p class="text-sm text-gray-500">{{ $audit->user->fonction }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Action</h3>
                        @php
                            $actionColors = [
                                'created' => 'bg-green-100 text-green-800',
                                'updated' => 'bg-blue-100 text-blue-800', 
                                'deleted' => 'bg-red-100 text-red-800',
                                'restored' => 'bg-yellow-100 text-yellow-800'
                            ];
                            $color = $actionColors[$audit->action] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full {{ $color }}">
                            {{ ucfirst($audit->action) }}
                        </span>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Table</h3>
                        <p class="text-lg">{{ $audit->table_name }}</p>
                        @if($audit->record_id)
                            <p class="text-sm text-gray-500">ID: {{ $audit->record_id }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6">
                <h3 class="text-lg font-semibold text-[#0b5f37] mb-4">Description</h3>
                <p class="text-gray-700">{{ $audit->description }}</p>
            </div>
        </div>

        <!-- Données avant/après -->
        @if($audit->old_data || $audit->new_data)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($audit->old_data)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">📋 Données Avant</h3>
                <div class="bg-red-50 rounded p-4">
                    <pre class="text-sm whitespace-pre-wrap">{{ json_encode($audit->old_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif

            @if($audit->new_data)
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-green-600 mb-4">📋 Données Après</h3>
                <div class="bg-green-50 rounded p-4">
                    <pre class="text-sm whitespace-pre-wrap">{{ json_encode($audit->new_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
@endsection