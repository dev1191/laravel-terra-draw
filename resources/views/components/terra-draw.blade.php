@props([
    'id' => null,
    'name' => 'geometry',
    'value' => null,
    'center' => null,
    'zoom' => null,
    'mapStyle' => null,
    'modes' => null,
    'initialMode' => null,
    'height' => null,
    'toolbar' => null,
    'editable' => true,
])

@php
    $id = $id ?? 'terra-draw-' . \Illuminate\Support\Str::random(8);
    $center = $center ?? config('terra-draw.center', [0, 0]);
    $zoom = $zoom ?? config('terra-draw.zoom', 2);
    $mapStyle = $mapStyle ?? config('terra-draw.map_style', 'https://demotiles.maplibre.org/style.json');
    $modes = $modes ?? config('terra-draw.modes', ['polygon', 'rectangle', 'circle', 'linestring', 'freehand', 'point', 'select']);
    $initialMode = $initialMode ?? config('terra-draw.initial_mode', 'polygon');
    $height = $height ?? config('terra-draw.height', '450px');
    $toolbar = $toolbar ?? config('terra-draw.toolbar', true);

    $jsonValue = is_array($value) || is_object($value) 
        ? json_encode($value) 
        : (is_string($value) && (str_starts_with(trim($value), '{') || str_starts_with(trim($value), '[')) ? $value : '');
@endphp

<div {{ $attributes->merge(['class' => 'terra-draw-wrapper']) }} style="position: relative; width: 100%;">
    {{-- Hidden input for standard form submission & Livewire binding --}}
    <input 
        type="hidden" 
        name="{{ $name }}" 
        id="{{ $id }}-input" 
        value="{{ $jsonValue }}"
        data-terra-draw-input="{{ $id }}"
    />

    {{-- Optional Drawing Toolbar --}}
    @if($toolbar && $editable)
        <div class="terra-draw-toolbar" data-terra-draw-toolbar="{{ $id }}" style="display: flex; flex-wrap: wrap; gap: 6px; padding: 8px; background: rgba(15, 23, 42, 0.9); border-radius: 8px 8px 0 0; border: 1px solid #334155; border-bottom: none;">
            @if(in_array('polygon', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'polygon' ? 'active' : '' }}" data-mode="polygon">Polygon</button>
            @endif
            @if(in_array('rectangle', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'rectangle' ? 'active' : '' }}" data-mode="rectangle">Rectangle</button>
            @endif
            @if(in_array('circle', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'circle' ? 'active' : '' }}" data-mode="circle">Circle</button>
            @endif
            @if(in_array('linestring', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'linestring' ? 'active' : '' }}" data-mode="linestring">Line</button>
            @endif
            @if(in_array('freehand', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'freehand' ? 'active' : '' }}" data-mode="freehand">Freehand</button>
            @endif
            @if(in_array('point', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'point' ? 'active' : '' }}" data-mode="point">Point</button>
            @endif
            @if(in_array('select', $modes))
                <button type="button" class="terra-draw-btn {{ $initialMode === 'select' ? 'active' : '' }}" data-mode="select">Select / Edit</button>
            @endif
            <button type="button" class="terra-draw-btn terra-draw-btn-clear" data-action="clear" style="margin-left: auto;">Clear</button>
        </div>
    @endif

    {{-- Map Canvas Container --}}
    <div
        id="{{ $id }}"
        class="terra-draw-map"
        data-terra-draw
        data-id="{{ $id }}"
        data-name="{{ $name }}"
        data-center="{{ json_encode($center) }}"
        data-zoom="{{ $zoom }}"
        data-map-style="{{ $mapStyle }}"
        data-modes="{{ json_encode($modes) }}"
        data-initial-mode="{{ $initialMode }}"
        data-editable="{{ $editable ? 'true' : 'false' }}"
        style="width: 100%; height: {{ $height }}; border-radius: {{ $toolbar && $editable ? '0 0 8px 8px' : '8px' }}; overflow: hidden; border: 1px solid #334155;"
    ></div>
</div>