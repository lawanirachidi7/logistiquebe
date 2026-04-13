@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">
            <i class="fas fa-cogs"></i> Types de bus
            <span class="badge bg-primary ms-2" title="Nombre de types">{{ $types->count() }}</span>
        </h2>
        <a href="{{ route('admin.typebus.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Ajouter un type
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    @endif
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle" id="typebus-table">
            <thead class="table-dark">
                <tr>
                    <th style="width:60px">#</th>
                    <th>Libellé</th>
                    <th style="width:160px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($types as $type)
                <tr>
                    <td><span class="badge bg-secondary">{{ $type->id }}</span></td>
                    <td class="fw-bold">{{ $type->libelle }}</td>
                    <td>
                        <a href="{{ route('admin.typebus.edit', $type) }}" class="btn  btn-warning me-1">
                            <i class="fas fa-edit"></i> 
                        </a>
                        <form action="{{ route('admin.typebus.destroy', $type) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Supprimer ce type ?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn  btn-danger" type="submit">
                                <i class="fas fa-trash"></i> 
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center text-muted">Aucun type</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@push('scripts')
<script>
    if (window.jQuery && $.fn.DataTable) {
        $('#typebus-table').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/fr-FR.json'
            },
            responsive: true,
            pageLength: 10,
            ordering: true,
            order: [[0, 'asc']]
        });
    }
</script>
@endpush
@endsection
