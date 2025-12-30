@extends('layouts.enigmacero')

@php
    $user = auth()->user();
    $isClient = $user && ($user->role === 'client');

    // Ajuste: si tu User NO tiene client_id, decime cuál es el campo/relación real.
    $lockedClientId = $isClient ? ($user->client_id ?? null) : null;
@endphp

@section('content')
<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header">
            <h1>Carga de Archivos</h1>
        </div>

        {{-- SweetAlert de éxito --}}
        @if(session('success'))
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Listo',
                        text: @json(session('success')),
                        confirmButtonText: 'OK'
                    });
                });
            </script>
        @endif

        {{-- Errores --}}
        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        confirmButtonText: 'OK'
                    });
                });
            </script>
        @endif

        <div class="ec-card" style="padding:18px;">
            <form method="POST" action="{{ route('uploads.store') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
                    <div>
                        <label style="font-weight:600; font-size:14px;">Elija el cliente</label>

                        <select
                            name="client_id"
                            id="clientSelect"
                            class="enigmacero-input"
                            {{ $isClient ? 'disabled' : '' }}
                            data-locked="{{ $isClient ? '1' : '0' }}"
                        >
                            @if(!$isClient)
                                <option value="">-- Seleccionar --</option>
                            @endif

                            @foreach($clients as $c)
                                <option
                                    value="{{ $c->id }}"
                                    {{ ($lockedClientId && (int)$lockedClientId === (int)$c->id) ? 'selected' : '' }}
                                >
                                    {{ $c->name }} ({{ $c->bucket_folder ?? 'sin carpeta' }})
                                </option>
                            @endforeach
                        </select>

                        {{-- Si está disabled, igual debe viajar al backend --}}
                        @if($isClient && $lockedClientId)
                            <input type="hidden" name="client_id" value="{{ $lockedClientId }}">
                        @endif
                    </div>

                    <div>
                        <label style="font-weight:600; font-size:14px;">Carpeta destino</label>
                        <select name="target_folder" id="folderSelect" class="enigmacero-input" disabled>
                            <option value="">Seleccione un cliente primero</option>
                        </select>
                        <small id="folderHint" style="color:#6b7280;"></small>
                    </div>
                </div>

                <div id="filesBlock" style="margin-top:16px; display:none;">
                    <label style="font-weight:600; font-size:14px;">Archivos</label>
                    <input type="file" name="files[]" multiple class="enigmacero-input">
                </div>

                <div style="margin-top:12px; display:flex; gap:10px;">
                    <button type="submit" class="enigmacero-btn-primary" id="btnUpload" disabled>
                        Subir archivos
                    </button>
                    <a href="{{ route('dashboard') }}" class="enigmacero-btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const clientSelect = document.getElementById('clientSelect');
    const folderSelect = document.getElementById('folderSelect');
    const filesBlock   = document.getElementById('filesBlock');
    const btnUpload    = document.getElementById('btnUpload');
    const folderHint   = document.getElementById('folderHint');

    function setFoldersState(enabled, html, hint='') {
        folderSelect.disabled = !enabled;
        folderSelect.innerHTML = html;
        folderHint.textContent = hint;
        filesBlock.style.display = enabled ? 'block' : 'none';
        btnUpload.disabled = !enabled;
    }

    async function loadFolders(clientId) {
        if (!clientId) {
            setFoldersState(false, '<option value="">Seleccione un cliente primero</option>');
            return;
        }

        setFoldersState(false, '<option value="">Cargando...</option>');

        try {
            const res = await fetch(`{{ route('uploads.folders') }}?client_id=${encodeURIComponent(clientId)}`);
            const data = await res.json();

            // data.current y data.folders deben venir del endpoint
            const current = data.current || '';
            const currentLabel = current ? `Mes actual (${current})` : 'Mes actual';

            let options = '';
            options += `<option value="__CURRENT__">${currentLabel}</option>`;

            if (Array.isArray(data.folders) && data.folders.length > 0) {
                options += `<option value="" disabled>────────────</option>`;
                data.folders.forEach(f => {
                    options += `<option value="${f}">${f}</option>`;
                });
                setFoldersState(true, options, 'Si eliges “Mes actual”, se crea la carpeta automáticamente.');
            } else {
                setFoldersState(true, options, 'No hay carpetas previas. Se usará “Mes actual”.');
                folderSelect.value = '__CURRENT__';
            }
        } catch (e) {
            setFoldersState(false, '<option value="">Error cargando carpetas</option>');
            Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la lista de carpetas.' });
        }
    }

    // Cambio manual (admin/employee)
    clientSelect.addEventListener('change', async () => {
        await loadFolders(clientSelect.value);
    });

    // ✅ CLAVE: si ya viene preseleccionado (client o futuro preselect admin), cargamos carpetas al entrar
    if (clientSelect.value) {
        loadFolders(clientSelect.value);
    }

    // Validación básica al enviar
    document.getElementById('uploadForm').addEventListener('submit', (e) => {
        // Si el select está disabled, igual hay hidden input, así que esto funciona bien.
        const clientId = clientSelect.value || (document.querySelector('input[name="client_id"]')?.value ?? '');

        if (!clientId) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Falta cliente', text:'Seleccione un cliente.' });
            return;
        }
        if (!folderSelect.value) {
            e.preventDefault();
            Swal.fire({ icon:'warning', title:'Falta carpeta', text:'Seleccione una carpeta destino.' });
            return;
        }
    });
});
</script>
@endsection
