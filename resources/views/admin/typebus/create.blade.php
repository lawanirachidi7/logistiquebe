@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Ajouter un type de bus</h2>
    <form action="{{ route('admin.typebus.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="libelle" class="form-label">Libellé</label>
            <input type="text" name="libelle" id="libelle" class="form-control" value="{{ old('libelle') }}" required>
            @error('libelle')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
        <a href="{{ route('admin.typebus.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
