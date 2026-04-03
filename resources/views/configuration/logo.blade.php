@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-image"></i> Gestion du logo
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="text-center mb-4">
                        <h5>Logo actuel</h5>
                        <div class="border rounded p-4 bg-light">
                            @if(file_exists(public_path('images/logo.png')))
                                <img src="{{ asset('images/logo.png') }}?v={{ time() }}" alt="Logo actuel" style="max-height: 100px;">
                            @else
                                <p class="text-muted mb-0"><i class="fas fa-image fa-3x"></i><br>Aucun logo</p>
                            @endif
                        </div>
                    </div>

                    <form action="{{ route('configuration.logo.upload') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="logo" class="form-label">Nouveau logo</label>
                            <input type="file" class="form-control @error('logo') is-invalid @enderror" 
                                   id="logo" name="logo" accept="image/*" required>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formats acceptés: PNG, JPG, JPEG, GIF, SVG. Taille max: 2 Mo.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Aperçu</label>
                            <div class="border rounded p-4 bg-light text-center" id="preview-container" style="display: none;">
                                <img id="logo-preview" src="#" alt="Aperçu" style="max-height: 100px;">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Télécharger le logo
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('logo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-preview').src = e.target.result;
            document.getElementById('preview-container').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
