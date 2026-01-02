@extends("layout")
@section("content")
    <h2>Liste des produits</h2>
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- @dump(session()->all()) --}}
    {{-- @dd(session()->all()) --}}

    @foreach($products as $singleProduct)
        <div class="card mb-3">
            <div class="card-body">
                <h5 class="card-title">{{ $singleProduct['name'] }}</h5>
                <p class="card-text">
                    <strong>Prix:</strong> {{ $singleProduct['price'] }}<br>
                    <strong>Quantité:</strong> {{ $singleProduct['stock'] }}<br>
                    @if ($singleProduct['availability'] == 'in_stock')
                        <span class="badge bg-success">En stock</span>
                    @elseif ($singleProduct['availability'] == 'limited_stock')
                        <span class="badge bg-warning">Stock limité</span>
                    @else
                        <span class="badge bg-danger">Stock épuisé</span>
                    @endif
                </p>
            </div>
            <div class="bordered-div p-3">
                <a href="{{ route("supprimer-element", ["id" => $singleProduct['id']]) }}" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Supprimer
                </a>
                <a href="{{ route("editer-element", $singleProduct['id']) }}" class="btn btn-info">
                    <i class="fas fa-edit"></i> Editer
                </a>
            </div>
        </div>
    @endforeach

@endsection