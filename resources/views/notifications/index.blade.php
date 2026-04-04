@extends('layouts.app')

@section('content')
<div class="page-header bg-gradient-primary text-white">
    <div>
        <h1 class="page-title">
            <i class="fas fa-bell me-2"></i>Notifications
        </h1>
        <p class="page-subtitle mb-0">Gérez vos alertes et notifications</p>
    </div>
    <div class="page-header-actions">
        @if($nonLues > 0)
            <button type="button" class="btn btn-light" id="markAllReadBtn">
                <i class="fas fa-check-double me-1"></i> Tout marquer comme lu ({{ $nonLues }})
            </button>
        @endif
    </div>
</div>

<div class="row mb-4">
    <!-- Statistiques rapides -->
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-danger-light text-danger">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-mini-content">
                <div class="stat-mini-value">{{ $notifications->where('niveau', 'danger')->count() }}</div>
                <div class="stat-mini-label">Critiques</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-warning-light text-warning">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-mini-content">
                <div class="stat-mini-value">{{ $notifications->where('niveau', 'warning')->count() }}</div>
                <div class="stat-mini-label">Avertissements</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-primary-light text-primary">
                <i class="fas fa-bell"></i>
            </div>
            <div class="stat-mini-content">
                <div class="stat-mini-value">{{ $nonLues }}</div>
                <div class="stat-mini-label">Non lues</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="stat-mini-card">
            <div class="stat-mini-icon bg-success-light text-success">
                <i class="fas fa-check"></i>
            </div>
            <div class="stat-mini-content">
                <div class="stat-mini-value">{{ $notifications->where('lue', true)->count() }}</div>
                <div class="stat-mini-label">Lues</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('notifications.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous les types</option>
                    @foreach($types as $key => $config)
                        <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                            {{ $config['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Niveau</label>
                <select name="niveau" class="form-select form-select-sm">
                    <option value="">Tous les niveaux</option>
                    <option value="danger" {{ request('niveau') === 'danger' ? 'selected' : '' }}>Critique</option>
                    <option value="warning" {{ request('niveau') === 'warning' ? 'selected' : '' }}>Avertissement</option>
                    <option value="info" {{ request('niveau') === 'info' ? 'selected' : '' }}>Information</option>
                    <option value="success" {{ request('niveau') === 'success' ? 'selected' : '' }}>Succès</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Statut</label>
                <select name="lue" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <option value="non" {{ request('lue') === 'non' ? 'selected' : '' }}>Non lues</option>
                    <option value="oui" {{ request('lue') === 'oui' ? 'selected' : '' }}>Lues</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                        <i class="fas fa-filter me-1"></i> Filtrer
                    </button>
                    <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Liste des notifications -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i>Liste des notifications</span>
        <span class="badge bg-primary">{{ $notifications->total() }} notification(s)</span>
    </div>
    <div class="card-body p-0">
        @if($notifications->isEmpty())
            <div class="empty-state py-5">
                <i class="fas fa-bell-slash"></i>
                <h4>Aucune notification</h4>
                <p class="text-muted">Vous n'avez aucune notification correspondant aux critères</p>
            </div>
        @else
            <div class="notification-list-full">
                @foreach($notifications as $notification)
                    <div class="notification-item-full {{ $notification->lue ? '' : 'unread' }}" 
                         data-id="{{ $notification->id }}">
                        <div class="notif-icon-full {{ $notification->niveau }}">
                            <i class="{{ $notification->icone_complet }}"></i>
                        </div>
                        <div class="notif-content-full">
                            <div class="notif-header-full">
                                <span class="notif-type-badge" style="background: {{ $notification->couleur }}">
                                    {{ $notification->config['label'] }}
                                </span>
                                <span class="notif-time-full">
                                    <i class="far fa-clock me-1"></i>{{ $notification->temps_ecoule }}
                                </span>
                            </div>
                            <div class="notif-title-full">{{ $notification->titre }}</div>
                            <div class="notif-message-full">{{ $notification->message }}</div>
                            @if($notification->lien)
                                <a href="{{ route('notifications.action', $notification) }}" class="notif-link-full">
                                    {{ $notification->lien_texte ?? 'Voir les détails' }} <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                        <div class="notif-actions-full">
                            @if(!$notification->lue)
                                <button type="button" class="btn btn-sm btn-outline-primary mark-read-btn" 
                                        data-id="{{ $notification->id }}" title="Marquer comme lue">
                                    <i class="fas fa-check"></i>
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-outline-danger delete-notif-btn" 
                                    data-id="{{ $notification->id }}" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="card-footer">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .stat-mini-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    
    .stat-mini-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .bg-danger-light { background: #fef2f2; }
    .bg-warning-light { background: #fffbeb; }
    .bg-primary-light { background: #eff6ff; }
    .bg-success-light { background: #f0fdf4; }
    
    .stat-mini-value {
        font-size: 1.4rem;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
    }
    
    .stat-mini-label {
        font-size: 0.8rem;
        color: #64748b;
    }
    
    .notification-list-full {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .notification-item-full {
        display: flex;
        align-items: flex-start;
        padding: 20px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.2s ease;
    }
    
    .notification-item-full:hover {
        background: #f8fafc;
    }
    
    .notification-item-full.unread {
        background: #eff6ff;
        border-left: 4px solid #3b82f6;
    }
    
    .notif-icon-full {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
        margin-right: 15px;
    }
    
    .notif-icon-full.danger { background: #fef2f2; color: #dc2626; }
    .notif-icon-full.warning { background: #fffbeb; color: #d97706; }
    .notif-icon-full.success { background: #f0fdf4; color: #16a34a; }
    .notif-icon-full.info { background: #eff6ff; color: #2563eb; }
    
    .notif-content-full {
        flex: 1;
        min-width: 0;
    }
    
    .notif-header-full {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 6px;
    }
    
    .notif-type-badge {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 4px;
        color: #fff;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .notif-time-full {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    
    .notif-title-full {
        font-weight: 600;
        font-size: 1rem;
        color: #1e293b;
        margin-bottom: 4px;
    }
    
    .notif-message-full {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.5;
    }
    
    .notif-link-full {
        display: inline-block;
        margin-top: 8px;
        font-size: 0.85rem;
        color: #3b82f6;
        text-decoration: none;
        font-weight: 500;
    }
    
    .notif-link-full:hover {
        text-decoration: underline;
    }
    
    .notif-actions-full {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-left: 15px;
    }
    
    .notif-actions-full .btn {
        width: 35px;
        height: 35px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }
    
    @media (max-width: 768px) {
        .notification-item-full {
            flex-wrap: wrap;
        }
        
        .notif-actions-full {
            flex-direction: row;
            width: 100%;
            justify-content: flex-end;
            margin: 10px 0 0 0;
        }
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marquer tout comme lu
    const markAllBtn = document.getElementById('markAllReadBtn');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', function() {
            fetch('{{ route("notifications.marquer-toutes-lues") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.querySelectorAll('.notification-item-full.unread').forEach(el => {
                        el.classList.remove('unread');
                    });
                    document.querySelectorAll('.mark-read-btn').forEach(el => el.remove());
                    markAllBtn.remove();
                    // Rafraîchir les stats
                    location.reload();
                }
            });
        });
    }
    
    // Marquer une notification comme lue
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const item = document.querySelector(`.notification-item-full[data-id="${id}"]`);
            
            fetch(`/notifications/${id}/lue`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.classList.remove('unread');
                    this.remove();
                }
            });
        });
    });
    
    // Supprimer une notification
    document.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Supprimer cette notification ?')) return;
            
            const id = this.dataset.id;
            const item = document.querySelector(`.notification-item-full[data-id="${id}"]`);
            
            fetch(`/notifications/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    item.remove();
                }
            });
        });
    });
});
</script>
@endpush
