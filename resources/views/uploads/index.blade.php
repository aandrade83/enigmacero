@extends('layouts.enigmacero')

@php
    $user = auth()->user();
    $isClient = $user && ($user->role === 'client');
    $lockedClientId = $isClient ? ($user->client_id ?? null) : null;
    $lockedClient = ($isClient && isset($clients)) ? $clients->firstWhere('id', $lockedClientId) : null;
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
                        <select name="client_id" id="clientSelect"
        class="enigmacero-input"
        data-locked="{{ $isClient ? '1' : '0' }}"
        {{ $isClient ? 'disabled' : 'required' }}>
    @if($isClient)
        @if($lockedClient)
            <option value="{{ $lockedClient->id }}" selected>
                {{ $lockedClient->name }} ({{ $lockedClient->bucket_folder ?? 'sin carpeta' }})
            </option>
        @else
            <option value="" selected>Cliente no asignado</option>
        @endif
    @else
        <option value="">-- Seleccionar --</option>
        @foreach($clients as $c)
            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->bucket_folder ?? 'sin carpeta' }})</option>
        @endforeach
    @endif
</select>
@if($isClient && $lockedClientId)
    {{-- Importante: los inputs disabled no se envían, por eso agregamos este hidden --}}
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
    const filesBlock = document.getElementById('filesBlock');
    const btnUpload = document.getElementById('btnUpload');
    const folderHint = document.getElementById('folderHint');

    function setFoldersState(enabled, html, hint='') {
        folderSelect.disabled = !enabled;
        folderSelect.innerHTML = html;
        folderHint.textContent = hint;
        filesBlock.style.display = enabled ? 'block' : 'none';
        btnUpload.disabled = !enabled;
    }

    const isLockedClient = clientSelect.dataset.locked === '1';

    const handleClientChange = async () => {
        const clientId = clientSelect.value;
        
                if (!clientId) {
                    setFoldersState(false, '<option value="">Seleccione un cliente primero</option>');
                    return;
                }
        
                setFoldersState(false, '<option value="">Cargando...</option>');
        
                try {
                    const res = await fetch(`{{ route('uploads.folders') }}?client_id=${encodeURIComponent(clientId)}`);
                    const data = await res.json();
        
                    const currentLabel = `Mes actual (${data.current})`;
        
                    let options = '';
                    // Siempre damos la opción de mes actual
                    options += `<option value="__CURRENT__">${currentLabel}</option>`;
        
                    if (Array.isArray(data.folders) && data.folders.length > 0) {
                        options += `<option value="" disabled>────────────</option>`;
                        data.folders.forEach(f => {
                            options += `<option value="${f}">${f}</option>`;
    };

    if (!isLockedClient) {
        clientSelect.addEventListener('change', handleClientChange);
    }

    // Si es un usuario cliente, el select viene bloqueado pero aún podemos cargar carpetas automáticamente
    if (clientSelect.value) {
        handleClientChange();
    } else {
        setFoldersState(false, '<option value="">Seleccione un cliente primero</option>');
    }

setFoldersState(true, options, 'Si eliges “Mes actual”, se crea la carpeta automáticamente.');
            } else {
                setFoldersState(true, options, 'No hay carpetas previas. Se usará “Mes actual”.');
                folderSelect.value = '__CURRENT__';
            }

        } catch (e) {
            setFoldersState(false, '<option value="">Error cargando carpetas</option>');
            Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la lista de carpetas.' });
        }
    });

    // Validación básica al enviar
    document.getElementById('uploadForm').addEventListener('submit', (e) => {
        if (!clientSelect.value) {
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

