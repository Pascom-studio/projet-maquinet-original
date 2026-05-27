@extends('layouts.app')

@section('title', 'Détails Transaction - Mobile Money')

@section('content')
<div class="container">
    <!-- En-tête -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <div>
            <h2 style="margin: 0; color: #333;">GestCool</h2>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">ID: {{ $mobileMoney->id_transaction }}</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('mobile-money.edit', $mobileMoney) }}" style="padding: 8px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">
                Modifier
            </a>
            <a href="{{ route('mobile-money.index') }}" style="padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">
                Retour
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Colonne principale -->
        <div>
            <!-- Informations de la Transaction -->
            <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                    Informations de la Transaction
                </h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <!-- Informations Client -->
                    <div style="border: 1px solid #e9ecef; padding: 15px; border-radius: 5px;">
                        <h4 style="margin-top: 0; color: #666; font-size: 16px;">Informations Client</h4>
                        <div style="line-height: 2;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Nom complet:</span>
                                <strong>{{ $mobileMoney->prenom }} {{ $mobileMoney->nom }}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Téléphone:</span>
                                <strong style="color: #007bff;">{{ $mobileMoney->telephone }}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">CNIB:</span>
                                <span>{{ $mobileMoney->cnib ?: 'Non renseigné' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Détails Transaction -->
                    <div style="border: 1px solid #e9ecef; padding: 15px; border-radius: 5px;">
                        <h4 style="margin-top: 0; color: #666; font-size: 16px;">Détails Transaction</h4>
                        <div style="line-height: 2;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Type:</span>
                                <span style="padding: 4px 8px; background: {{ $mobileMoney->type_operation == 'depot' ? '#28a745' : '#dc3545' }}; color: white; border-radius: 3px; font-size: 12px;">
                                    {{ $mobileMoney->type_operation == 'depot' ? 'Dépôt' : 'Retrait' }}
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Service:</span>
                                <span style="padding: 4px 8px; background: #17a2b8; color: white; border-radius: 3px; font-size: 12px;">
                                    @if($mobileMoney->nature == 'orange_money')
                                        Orange Money
                                    @elseif($mobileMoney->nature == 'telecel_money')
                                        Telecel Money
                                    @elseif($mobileMoney->nature == 'moov_money')
                                        Moov Money
                                    @elseif($mobileMoney->nature == 'coris_money')
                                        Coris Money
                                    @else
                                        {{ $mobileMoney->nature }}
                                    @endif
                                </span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Statut:</span>
                                <span style="padding: 4px 8px; background: {{ $mobileMoney->statut == 'actif' ? '#28a745' : '#6c757d' }}; color: white; border-radius: 3px; font-size: 12px;">
                                    {{ $mobileMoney->statut == 'actif' ? 'Actif' : 'Annulé' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Montant -->
                <div style="text-align: center; background: #f8f9fa; padding: 20px; border-radius: 5px; border: 1px solid #e9ecef;">
                    <h4 style="margin-top: 0; color: #666;">Montant de la Transaction</h4>
                    <h1 style="margin: 0; color: {{ $mobileMoney->type_operation == 'depot' ? '#28a745' : '#dc3545' }};">
                        {{ number_format($mobileMoney->montant, 0, ',', ' ') }} FCFA
                    </h1>
                </div>

                <!-- Informations Commission -->
                <div style="margin-top: 20px; padding: 15px; background: #e8f5e8; border-radius: 5px; border: 1px solid #28a745;">
                    <h4 style="margin-top: 0; color: #155724; border-bottom: 1px solid #28a745; padding-bottom: 10px;">
                        Détails Commission
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: #666;">Commission nette:</span>
                                <strong style="color: #28a745;">{{ number_format($mobileMoney->commission, 2, ',', ' ') }} FCFA</strong>
                            </div>
                            @if($mobileMoney->commission_brute)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: #666;">Commission brute:</span>
                                <span>{{ number_format($mobileMoney->commission_brute, 2, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                        </div>
                        <div>
                            @if($mobileMoney->taxes)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: #666;">Taxes:</span>
                                <span style="color: #dc3545;">{{ number_format($mobileMoney->taxes, 2, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #666;">Taux effectif:</span>
                                <span style="font-weight: bold;">
                                    @php
                                        $tauxEffectif = $mobileMoney->montant > 0 ? ($mobileMoney->commission / $mobileMoney->montant) * 100 : 0;
                                    @endphp
                                    {{ number_format($tauxEffectif, 2, ',', ' ') }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Informations Système -->
            <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin-bottom: 20px;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                    Informations Système
                </h3>
                <div style="line-height: 2.5;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Caissier:</span>
                        <strong>{{ $mobileMoney->user->name ?? 'N/A' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Admin parent:</span>
                        <strong>{{ $mobileMoney->user->admin->name ?? 'N/A' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Date création:</span>
                        <span>{{ $mobileMoney->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Dernière modif:</span>
                        <span>{{ $mobileMoney->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Modifiable:</span>
                        <span style="padding: 4px 8px; background: {{ $mobileMoney->peut_etre_modifie ? '#28a745' : '#6c757d' }}; color: white; border-radius: 3px; font-size: 12px;">
                            {{ $mobileMoney->peut_etre_modifie ? 'Oui' : 'Non' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">ID Transaction:</span>
                        <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                            {{ $mobileMoney->id_transaction }}
                        </code>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            @if($mobileMoney->statut == 'actif' && $mobileMoney->peut_etre_modifie)
            <div style="background: white; padding: 20px; border-radius: 5px; border: 2px solid #ffc107;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #ffc107; padding-bottom: 10px;">
                    Actions
                </h3>
                
                <!-- Bouton Supprimer -->
                <form action="{{ route('mobile-money.supprimer', $mobileMoney) }}" method="POST" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cette transaction ? Cette action est irréversible et déduira les commissions.')"
                      style="margin-bottom: 15px;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="width: 100%; padding: 10px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                        🗑️ Supprimer Définitivement
                    </button>
                </form>

                <!-- Informations suppression -->
                <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 4px; padding: 10px;">
                    <p style="margin: 0; color: #856404; font-size: 12px; text-align: center;">
                        ⚠️ <strong>Attention:</strong> La suppression est définitive<br>
                        Les commissions seront déduites des statistiques
                    </p>
                </div>
            </div>
            @elseif($mobileMoney->statut != 'actif')
            <div style="background: white; padding: 20px; border-radius: 5px; border: 2px solid #6c757d;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #6c757d; padding-bottom: 10px;">
                    Statut
                </h3>
                <div style="text-align: center; padding: 15px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">🔒</div>
                    <p style="margin: 0; color: #6c757d; font-weight: bold;">
                        Transaction {{ $mobileMoney->statut == 'annulee' ? 'Annulée' : 'Verrouillée' }}
                    </p>
                    <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">
                        Aucune action possible
                    </p>
                </div>
            </div>
            @else
            <div style="background: white; padding: 20px; border-radius: 5px; border: 2px solid #6c757d;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #6c757d; padding-bottom: 10px;">
                    Délai Expiré
                </h3>
                <div style="text-align: center; padding: 15px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">⏰</div>
                    <p style="margin: 0; color: #6c757d; font-weight: bold;">
                        Délai de modification expiré
                    </p>
                    <p style="margin: 5px 0 0 0; color: #999; font-size: 12px;">
                        Plus de 24 heures se sont écoulées
                    </p>
                </div>
            </div>
            @endif

            <!-- Statistiques Rapides -->
            <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                    Statistiques
                </h3>
                <div style="line-height: 2;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Heures écoulées:</span>
                        <strong>{{ $mobileMoney->created_at->diffInHours(now()) }}h</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Jours écoulés:</span>
                        <strong>{{ $mobileMoney->created_at->diffInDays(now()) }}j</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #666;">Heures restantes:</span>
                        <strong style="color: {{ (24 - $mobileMoney->created_at->diffInHours(now())) > 0 ? '#28a745' : '#dc3545' }};">
                            {{ max(0, 24 - $mobileMoney->created_at->diffInHours(now())) }}h
                        </strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Style supplémentaire -->
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    @media (max-width: 768px) {
        .container {
            grid-template-columns: 1fr;
            padding: 10px;
        }
        
        div[style*="grid-template-columns: 2fr 1fr"] {
            grid-template-columns: 1fr;
        }
        
        div[style*="grid-template-columns: 1fr 1fr"] {
            grid-template-columns: 1fr;
        }
    }
    
    /* Amélioration des boutons */
    a[style*="background: #007bff"]:hover {
        background: #0056b3 !important;
        transform: translateY(-1px);
        transition: all 0.2s;
    }
    
    a[style*="background: #6c757d"]:hover {
        background: #545b62 !important;
        transform: translateY(-1px);
        transition: all 0.2s;
    }
    
    button[style*="background: #dc3545"]:hover {
        background: #c82333 !important;
        transform: translateY(-1px);
        transition: all 0.2s;
    }
</style>
@endsection