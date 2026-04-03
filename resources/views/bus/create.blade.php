@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Ajouter un nouveau bus</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('bus.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="immatriculation" class="form-label">Immatriculation</label>
                            <input type="text" class="form-control @error('immatriculation') is-invalid @enderror" id="immatriculation" name="immatriculation" value="{{ old('immatriculation') }}" required>
                            @error('immatriculation')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type_bus_id" class="form-label">Type de Bus</label>
                            <select class="form-select @error('type_bus_id') is-invalid @enderror" id="type_bus_id" name="type_bus_id" required>
                                <option value="">Choisir...</option>
                                @foreach($typesBus as $type)
                                    <option value="{{ $type->id }}" {{ old('type_bus_id') == $type->id ? 'selected' : '' }}>{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                            @error('type_bus_id')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="ligne_nord" name="ligne_nord" value="1" {{ old('ligne_nord') ? 'checked' : '' }}>
                            <label class="form-check-label" for="ligne_nord">Bus assigné à la ligne Nord ?</label>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                        <a href="{{ route('bus.index') }}" class="btn btn-secondary">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
