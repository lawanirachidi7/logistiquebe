@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Modifier le type de bus</h2>
    <form action="{{ route('admin.typebus.update', $typebus) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="libelle" class="form-label">Libellé</label>
            <input type="text" name="libelle" id="libelle" class="form-control" value="{{ old('libelle', $typebus->libelle) }}" required>
            @error('libelle')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Mettre à jour</button>
        <a href="{{ route('admin.typebus.index') }}" class="btn btn-secondary">Annuler</a>
    </form>
</div>
@endsection
