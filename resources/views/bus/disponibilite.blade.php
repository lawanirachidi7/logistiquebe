@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h1>Disponibilité des Bus</h1>

            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('bus.disponibilite.update') }}" method="POST">
                @csrf
                <div class="table-responsive">
                    <table class="table table-striped table-bordered datatable">
                        <thead>
                            <tr>
                                <th class="row-num">N°</th>
                                <th>Immatriculation</th>
                                <th>Type</th>
                                <th>Ligne Nord</th>
                                <th class="text-center no-sort no-export">Disponible</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bus as $b)
                            <tr>
                                <td></td>
                                <td>{{ $b->immatriculation }}</td>
                                <td>{{ $b->typeBus->libelle ?? 'N/A' }}</td>
                                <td>
                                    @if($b->ligne_nord)
                                        <span class="badge bg-info text-dark">Oui</span>
                                    @else
                                        <span class="badge bg-secondary">Non</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="disponible[{{ $b->id }}]" id="disponible_{{ $b->id }}" {{ $b->disponible ? 'checked' : '' }}>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                    <button type="submit" class="btn btn-primary">Enregistrer les disponibilités</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
