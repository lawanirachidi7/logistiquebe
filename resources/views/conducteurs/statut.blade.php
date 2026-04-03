@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <h1>Statut des Conducteurs</h1>

            <div class="table-responsive">
                <table class="table table-striped table-bordered datatable">
                    <thead>
                        <tr>
                            <th class="row-num">N°</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Disponible</th>
                            <th>En repos</th>
                            <th>Voyages consécutifs (Jour)</th>
                            <th>Voyages consécutifs (Nuit)</th>
                            <th>Dernier repos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($conducteurs as $c)
                        <tr>
                            <td></td>
                            <td>{{ $c->nom }}</td>
                            <td>{{ $c->prenom }}</td>
                            <td>
                                @if($c->estDisponible())
                                    <span class="badge bg-success">Oui</span>
                                @else
                                    <span class="badge bg-danger">Non</span>
                                @endif
                            </td>
                            <td>{{ $c->estEnRepos() ? 'Oui' : 'Non' }}</td>
                            <td>{{ $c->voyagesConsecutifs('Jour') }}</td>
                            <td>{{ $c->voyagesConsecutifs('Nuit') }}</td>
                            <td>{{ $c->dernierRepos() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
