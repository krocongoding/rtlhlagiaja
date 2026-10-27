@props([
    'beforeUrl',
    'afterUrl',
    'title' => 'Komparasi Sebelum & Sesudah',
    'beforeLabel' => 'SEBELUM (Kondisi Awal RTLH)',
    'afterLabel' => 'SESUDAH (Rehabilitasi Layak Huni)',
    'id' => null,
])

@php
    $sliderId = $id ?? 'ba-slider-' . uniqid();
@endphp

<div class="before-after-widget" id="{{ $sliderId }}" style="position:relative; width:100%; max-width:100%; border-radius:12px; overflow:hidden; border:1px solid var(--slate-200); box-shadow:var(--shadow-sm); background:#0f172a;">
    @if($title)
        <div style="position:absolute; top:12px; left:12px; z-index:10; background:rgba(15,23,42,0.85); backdrop-filter:blur(4px); color:#ffffff; padding:4px 10px; border-radius:6px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:6px;">
            <i class="fa-solid fa-layer-group" style="color:#38bdf8;"></i> {{ $title }}
        </div>
    @endif

    {{-- BADGE SESUDAH (BACKGROUND / KANAN) --}}
    <div style="position:absolute; top:12px; right:12px; z-index:10; background:rgba(22,101,52,0.9); backdrop-filter:blur(4px); color:#ffffff; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px;">
        <i class="fa-solid fa-circle-check"></i> {{ $afterLabel }}
    </div>

    {{-- CONTAINER FOTO SESUDAH (BACKGROUND LAYER) --}}
    <div style="width:100%; height:320px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center; background:#1e293b;">
        <img
            src="{{ $afterUrl }}"
            alt="{{ $afterLabel }}"
            style="width:100%; height:100%; object-fit:cover; display:block;"
            loading="lazy"
        >
    </div>

    {{-- CONTAINER FOTO SEBELUM (FOREGROUND OVERLAY LAYER - CLIPPED) --}}
    <div class="ba-overlay-layer" style="position:absolute; top:0; left:0; width:50%; height:100%; overflow:hidden; z-index:5; border-right:2px solid #ffffff; box-shadow: 2px 0 8px rgba(0,0,0,0.4);">
        {{-- BADGE SEBELUM --}}
        <div style="position:absolute; bottom:14px; left:12px; z-index:11; background:rgba(225,29,72,0.9); backdrop-filter:blur(4px); color:#ffffff; padding:4px 10px; border-radius:6px; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap;">
            <i class="fa-solid fa-triangle-exclamation"></i> {{ $beforeLabel }}
        </div>

        <img
            src="{{ $beforeUrl }}"
            alt="{{ $beforeLabel }}"
            class="ba-before-img"
            style="width:100%; height:320px; object-fit:cover; display:block; max-width:none;"
            loading="lazy"
        >
    </div>

    {{-- DRAG HANDLE CIRCLE --}}
    <div class="ba-handle" style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); z-index:15; width:36px; height:36px; border-radius:50%; background:#ffffff; color:#0f172a; box-shadow:0 3px 10px rgba(0,0,0,0.5); display:flex; align-items:center; justify-content:center; font-size:14px; pointer-events:none; border:2px solid var(--primary-600);">
        <i class="fa-solid fa-arrows-left-right" style="color:var(--primary-700);"></i>
    </div>

    {{-- NATIVE RANGE SLIDER UNTUK TOUCH & MOUSE SEAMLESS SUPPORT --}}
    <input
        type="range"
        min="0"
        max="100"
        value="50"
        class="ba-range-slider"
        aria-label="Geser untuk membandingkan sebelum dan sesudah"
        style="position:absolute; top:0; left:0; width:100%; height:100%; opacity:0; z-index:20; cursor:ew-resize; margin:0; padding:0;"
        oninput="updateBeforeAfterSlider('{{ $sliderId }}', this.value)"
    >
</div>

@once
@push('scripts')
<script>
    function updateBeforeAfterSlider(containerId, value) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const overlay = container.querySelector('.ba-overlay-layer');
        const handle = container.querySelector('.ba-handle');
        const beforeImg = container.querySelector('.ba-before-img');

        if (overlay) {
            overlay.style.width = value + '%';
        }
        if (handle) {
            handle.style.left = value + '%';
        }
        if (beforeImg) {
            // Jaga rasio gambar sebelum agar tidak terdistorsi saat div mengecil
            beforeImg.style.width = container.offsetWidth + 'px';
        }
    }

    // Inisialisasi lebar gambar sebelum pada resize window
    window.addEventListener('resize', () => {
        document.querySelectorAll('.before-after-widget').forEach(widget => {
            const beforeImg = widget.querySelector('.ba-before-img');
            if (beforeImg) {
                beforeImg.style.width = widget.offsetWidth + 'px';
            }
        });
    });

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.before-after-widget').forEach(widget => {
            const beforeImg = widget.querySelector('.ba-before-img');
            if (beforeImg) {
                beforeImg.style.width = widget.offsetWidth + 'px';
            }
        });
    });
</script>
@endpush
@endonce
