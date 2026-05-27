@extends('layouts.app')

@section('title', 'Modifier Transaction - Mobile Money')

@section('content')
<div class="container">
    <!-- En-tête -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
        <div>
            <h2 style="margin: 0; color: #333;">GestCool</h2>
            <p style="margin: 5px 0 0 0; color: #666; font-size: 14px;">ID: {{ $mobileMoney->id_transaction }}</p>
        </div>
        <div>
            <a href="{{ route('mobile-money.show', $mobileMoney) }}" style="padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 14px;">
                Voir détails
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
        <!-- Formulaire principal -->
        <div>
            <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                    Modifier les informations
                </h3>
                
                @if($mobileMoney->peut_etre_modifie)
                <form action="{{ route('mobile-money.update', $mobileMoney) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Informations personnelles -->
                    <h4 style="color: #666; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin: 20px 0 15px 0;">
                        Informations Personnelles
                    </h4>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label for="prenom" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Prénom <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" id="prenom" name="prenom" 
                                   value="{{ old('prenom', $mobileMoney->prenom) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   required>
                            @error('prenom')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="nom" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Nom <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" id="nom" name="nom" 
                                   value="{{ old('nom', $mobileMoney->nom) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   required>
                            @error('nom')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label for="telephone" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Téléphone <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" id="telephone" name="telephone" 
                                   value="{{ old('telephone', $mobileMoney->telephone) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   required>
                            @error('telephone')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="cnib" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                CNIB
                            </label>
                            <input type="text" id="cnib" name="cnib" 
                                   value="{{ old('cnib', $mobileMoney->cnib) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            @error('cnib')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Détails transaction -->
                    <h4 style="color: #666; border-bottom: 1px solid #e9ecef; padding-bottom: 10px; margin: 30px 0 15px 0;">
                        Détails de la Transaction
                    </h4>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
                        <div>
                            <label for="type_operation" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Type d'opération <span style="color: #dc3545;">*</span>
                            </label>
                            <select id="type_operation" name="type_operation" 
                                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                                <option value="depot" {{ old('type_operation', $mobileMoney->type_operation) == 'depot' ? 'selected' : '' }}>Dépôt</option>
                                <option value="retrait" {{ old('type_operation', $mobileMoney->type_operation) == 'retrait' ? 'selected' : '' }}>Retrait</option>
                            </select>
                            @error('type_operation')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="nature" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Service <span style="color: #dc3545;">*</span>
                            </label>
                            <select id="nature" name="nature" 
                                    style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" required>
                                <option value="orange_money" {{ old('nature', $mobileMoney->nature) == 'orange_money' ? 'selected' : '' }}>Orange Money</option>
                                <option value="telecel_money" {{ old('nature', $mobileMoney->nature) == 'telecel_money' ? 'selected' : '' }}>Telecel Money</option>
                                <option value="moov_money" {{ old('nature', $mobileMoney->nature) == 'moov_money' ? 'selected' : '' }}>Moov Money</option>
                            </select>
                            @error('nature')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 30px;">
                        <div>
                            <label for="montant" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                Montant (FCFA) <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="number" step="0.01" id="montant" name="montant" 
                                   value="{{ old('montant', $mobileMoney->montant) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   required>
                            @error('montant')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label for="id_transaction" style="display: block; margin-bottom: 5px; color: #333; font-weight: bold;">
                                ID Transaction <span style="color: #dc3545;">*</span>
                            </label>
                            <input type="text" id="id_transaction" name="id_transaction" 
                                   value="{{ old('id_transaction', $mobileMoney->id_transaction) }}"
                                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;"
                                   required>
                            @error('id_transaction')
                                <div style="color: #dc3545; font-size: 12px; margin-top: 5px;">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Actions -->
                    <div style="display: flex; gap: 10px; justify-content: flex-end; border-top: 1px solid #e9ecef; padding-top: 20px;">
                        <a href="{{ route('mobile-money.show', $mobileMoney) }}" 
                           style="padding: 10px 20px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                            Annuler
                        </a>
                        <button type="submit" 
                                style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                            Mettre à jour
                        </button>
                    </div>
                </form>
                @else
                <!-- Message si non modifiable -->
                <div style="text-align: center; padding: 40px 20px; background: #f8f9fa; border-radius: 5px; margin: 20px 0;">
                    <h4 style="color: #ffc107; margin-bottom: 15px;">Modification impossible</h4>
                    <p style="color: #666; margin-bottom: 20px;">
                        Cette transaction ne peut plus être modifiée car le délai de 24 heures a été dépassé.
                    </p>
                    <a href="{{ route('mobile-money.show', $mobileMoney) }}" 
                       style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 4px;">
                        Voir les détails
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Sidebar informations -->
        <div>
            <div style="background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd;">
                <h3 style="margin-top: 0; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px;">
                    Informations
                </h3>
                
                <div style="background: #d1ecf1; padding: 15px; border-radius: 4px; margin-bottom: 20px;">
                    <p style="margin: 0; color: #0c5460; font-size: 14px;">
                        <strong>Note importante :</strong> Les transactions ne peuvent être modifiées que dans les 24 heures suivant leur création.
                    </p>
                </div>
                
                <div style="line-height: 2.5;">
                    <div style="display: flex; justify-content: space-between; background: #f8f9fa; padding: 8px 12px; border-radius: 4px;">
                        <span style="color: #666;">Statut:</span>
                        <span style="padding: 4px 8px; background: {{ $mobileMoney->statut == 'actif' ? '#28a745' : '#6c757d' }}; color: white; border-radius: 3px; font-size: 12px;">
                            {{ $mobileMoney->statut == 'actif' ? 'Actif' : 'Annulé' }}
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; background: #f8f9fa; padding: 8px 12px; border-radius: 4px;">
                        <span style="color: #666;">Crée le:</span>
                        <span>{{ $mobileMoney->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; background: #f8f9fa; padding: 8px 12px; border-radius: 4px;">
                        <span style="color: #666;">Modifiable:</span>
                        <span style="padding: 4px 8px; background: {{ $mobileMoney->peut_etre_modifie ? '#28a745' : '#6c757d' }}; color: white; border-radius: 3px; font-size: 12px;">
                            {{ $mobileMoney->peut_etre_modifie ? 'Oui' : 'Non' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection