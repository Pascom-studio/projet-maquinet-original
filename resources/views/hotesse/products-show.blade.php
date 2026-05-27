@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Détails du Produit</h2>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Désignation:</strong> {{ $product->designation }}</p>
                    <p><strong>Prix:</strong> {{ number_format($product->prix, 2) }} €</p>
                    <p><strong>Catégorie:</strong> {{ $product->categorie->nom }}</p>
                    <p><strong>Stock disponible:</strong> {{ $product->quantite }}</p>
                </div>
                <div class="col-md-6">
                    @if($product->description)
                        <p><strong>Description:</strong> {{ $product->description }}</p>
                    @endif
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    Retour à la liste
                </a>
            </div>
        </div>
    </div>
</div>
@endsection