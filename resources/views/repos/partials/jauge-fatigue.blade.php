@php
    $niveau = $niveau ?? 'vert';
    $score = $score ?? 0;
    $size = $size ?? 'md';
    
    $sizeClass = match($size) {
        'sm' => 'width: 35px; height: 35px; font-size: 0.7rem;',
        'lg' => 'width: 70px; height: 70px; font-size: 1.2rem;',
        default => 'width: 50px; height: 50px; font-size: 0.85rem;'
    };
@endphp

<div class="jauge-fatigue jauge-{{ $niveau }}" style="{{ $sizeClass }}" title="Score de fatigue: {{ $score }}%">
    {{ $score }}%
</div>

<style>
    .jauge-fatigue {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }
    .jauge-vert { background: linear-gradient(135deg, #28a745, #20c997); }
    .jauge-jaune { background: linear-gradient(135deg, #ffc107, #e0a800); color: #212529; }
    .jauge-orange { background: linear-gradient(135deg, #fd7e14, #e06600); }
    .jauge-rouge { background: linear-gradient(135deg, #dc3545, #a71d2a); }
</style>
