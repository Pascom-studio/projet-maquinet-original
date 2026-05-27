@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Liste des Produits</h2>
    
    <!-- Filtrage par catégorie -->
    <div class="mb-3">
        <label>Filtrer par catégorie:</label>
        <select id="categoryFilter" class="form-select">
            <option value="">Toutes les catégories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->nom }}</option>
            @endforeach
        </select>
    </div>

    <div class="row" id="productsContainer">
        @foreach($products as $product)
            <div class="col-md-4 mb-3 product-item" data-category="{{ $product->categorie_id }}">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $product->designation }}</h5>
                        <p class="card-text">
                            <strong>Prix:</strong> {{ number_format($product->prix, 2) }} €<br>
                            <strong>Catégorie:</strong> {{ $product->categorie->nom }}<br>
                            <strong>Stock:</strong> {{ $product->quantite }}
                        </p>
                        <a href="{{ route('products.show', $product) }}" class="btn btn-info btn-sm">
                            Voir détails
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.getElementById('categoryFilter').addEventListener('change', function() {
    const categoryId = this.value;
    const products = document.querySelectorAll('.product-item');
    
    products.forEach(product => {
        if (!categoryId || product.dataset.category === categoryId) {
            product.style.display = 'block';
        } else {
            product.style.display = 'none';
        }
    });
});
</script>
@endsection