@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-users-cog"></i>
                Gestion des utilisateurs
            </h1>
            <p class="page-subtitle">Administrez les comptes et les rôles</p>
        </div>
        <div class="page-header-actions">
            <a href="{{ route('configuration.users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Nouvel utilisateur
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped datatable">
                    <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Rôle</th>
                                    <th>Statut</th>
                                    <th>Créé le</th>
                                    <th class="no-sort no-export">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-2" style="width: 35px; height: 35px; font-size: 14px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            {{ $user->name }}
                                            @if(auth()->id() === $user->id)
                                                <span class="badge bg-info ms-2">Vous</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @if($user->role === 'admin')
                                            <span class="badge bg-danger">{{ $user->role_label }}</span>
                                        @elseif($user->role === 'manager')
                                            <span class="badge bg-warning text-dark">{{ $user->role_label }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $user->role_label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->actif)
                                            <span class="badge bg-success">Actif</span>
                                        @else
                                            <span class="badge bg-danger">Inactif</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('configuration.users.edit', $user) }}" class="btn btn-warning btn-sm" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            
                                            @if(auth()->id() !== $user->id)
                                                <form action="{{ route('configuration.users.toggle', $user) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-{{ $user->actif ? 'secondary' : 'success' }} btn-sm" 
                                                            title="{{ $user->actif ? 'Désactiver' : 'Activer' }}">
                                                        <i class="fas fa-{{ $user->actif ? 'ban' : 'check' }}"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('configuration.users.reset-password', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Réinitialiser le mot de passe de cet utilisateur ?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-info btn-sm" title="Réinitialiser MDP">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                </form>
                                                
                                                <form action="{{ route('configuration.users.destroy', $user) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}
</style>
@endsection
