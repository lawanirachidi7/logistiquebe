@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1 class="page-title">
                <i class="fas fa-cogs"></i>
                Paramètres de l'application
            </h1>
            <p class="page-subtitle">Configurez les préférences de votre système</p>
        </div>
        <div class="page-header-actions">
            <form action="{{ route('configuration.settings.reset') }}" method="POST" onsubmit="return confirm('Réinitialiser tous les paramètres aux valeurs par défaut ?');" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-undo"></i> Réinitialiser
                </button>
            </form>
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

    <div class="row">
        <div class="col-lg-12">
            {{-- Section Logo --}}
            <div class="card mb-4">
                <div class="card-header bg-primary">
                    <i class="fas fa-image me-2"></i>Logo de l'entreprise
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="p-4 bg-light rounded-3" style="min-height: 120px; display: flex; align-items: center; justify-content: center;">
                                @if(file_exists(public_path('images/logo.png')))
                                    <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="Logo actuel" style="max-height: 100px; max-width: 100%;">
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="mb-0">Aucun logo</p>
                                    </div>
                                @endif
                            </div>
                            <small class="text-muted d-block mt-2">Aperçu du logo actuel</small>
                        </div>
                        <div class="col-md-8">
                            <form action="{{ route('configuration.logo.upload') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="logo" class="form-label">
                                        <i class="fas fa-cloud-upload-alt me-1"></i>Télécharger un nouveau logo
                                    </label>
                                    <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                           id="logo" name="logo" accept="image/*">
                                    @error('logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Formats acceptés: PNG, JPG, GIF, SVG. Taille max: 2 Mo.</small>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-1"></i>Télécharger le logo
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('configuration.settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                {{-- Informations de l'entreprise --}}
                @if(isset($settings['company']))
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-building me-2"></i>Informations de l'entreprise
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings['company'] as $setting)
                            <div class="col-md-6 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">{{ $setting->label }}</label>
                                <input type="text" class="form-control" id="{{ $setting->key }}" 
                                       name="{{ $setting->key }}" value="{{ $setting->value }}" placeholder="{{ $setting->label }}">
                                @if($setting->description)
                                    <small class="text-muted">{{ $setting->description }}</small>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Paramètres d'affichage --}}
                @if(isset($settings['display']))
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-desktop me-2"></i>Paramètres d'affichage
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings['display'] as $setting)
                            <div class="col-md-4 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">{{ $setting->label }}</label>
                                @if($setting->type === 'boolean')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" 
                                               name="{{ $setting->key }}" value="1" {{ $setting->value ? 'checked' : '' }}>
                                    </div>
                                @elseif($setting->key === 'date_format')
                                    <select class="form-select" id="{{ $setting->key }}" name="{{ $setting->key }}">
                                        <option value="d/m/Y" {{ $setting->value === 'd/m/Y' ? 'selected' : '' }}>31/12/2026</option>
                                        <option value="Y-m-d" {{ $setting->value === 'Y-m-d' ? 'selected' : '' }}>2026-12-31</option>
                                        <option value="d-m-Y" {{ $setting->value === 'd-m-Y' ? 'selected' : '' }}>31-12-2026</option>
                                        <option value="d.m.Y" {{ $setting->value === 'd.m.Y' ? 'selected' : '' }}>31.12.2026</option>
                                    </select>
                                @elseif($setting->key === 'time_format')
                                    <select class="form-select" id="{{ $setting->key }}" name="{{ $setting->key }}">
                                        <option value="H:i" {{ $setting->value === 'H:i' ? 'selected' : '' }}>14:30 (24h)</option>
                                        <option value="h:i A" {{ $setting->value === 'h:i A' ? 'selected' : '' }}>02:30 PM (12h)</option>
                                    </select>
                                @else
                                    <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}" 
                                           class="form-control" id="{{ $setting->key }}" 
                                           name="{{ $setting->key }}" value="{{ $setting->value }}">
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                {{-- Paramètres de programmation --}}
                @if(isset($settings['programming']))
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-calendar-alt me-2"></i>Paramètres de programmation
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($settings['programming'] as $setting)
                            <div class="col-md-4 mb-3">
                                <label for="{{ $setting->key }}" class="form-label">{{ $setting->label }}</label>
                                @if($setting->type === 'boolean')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" 
                                               name="{{ $setting->key }}" value="1" {{ $setting->value ? 'checked' : '' }}>
                                    </div>
                                @else
                                    <input type="{{ $setting->type === 'integer' ? 'number' : 'text' }}" 
                                           class="form-control" id="{{ $setting->key }}" 
                                           name="{{ $setting->key }}" value="{{ $setting->value }}"
                                           @if($setting->type === 'integer') min="0" max="23" @endif>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="d-flex gap-2 mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-1"></i>Enregistrer les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
