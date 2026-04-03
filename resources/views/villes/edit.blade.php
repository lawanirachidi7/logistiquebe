@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Éditer la ville {{ $ville->nom }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('villes.update', $ville->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="nom" class="form-label">Nom de la ville</label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $ville->nom) }}" required>
                            @error('nom')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="actif" name="actif" value="1" {{ old('actif', $ville->actif) ? 'checked' : '' }}>
                            <label class="form-check-label" for="actif">Ville active</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('villes.index') }}" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
