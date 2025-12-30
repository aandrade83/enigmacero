@extends('layouts.enigmacero')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    // Nota: esto es solo UI (no seguridad). La seguridad real ya vive en el controller,
    // donde se filtran clientes/carpetas cuando el usuario es rol "client".
    $authUser = auth()->user();
    $isClient = $authUser && ($authUser->role === 'client');
    // Para clientes: el dropdown debe quedar bloqueado y preseleccionado.
    // Si por algún motivo no existe client_id, caemos al primer cliente disponible (si lo hay).
    $lockedClientId = $isClient
        ? ($authUser->client_id ?? optional($clients->first())->id)
        : null;
@endphp

<div class="ec-dashboard-layout">
    @include('partials.sidebar')

    <main class="ec-main">
        <div class="ec-content-header">
            <h1>Visualización de Archivos</h1>
        </div>

        <div class="ec-card" style="padding:18px;">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px;">
            <div>
                <label style="font-weight:600; font-size:14px;">Elegir cliente</label>
                <select
                    id="clientSelect"
                    class="enigmacero-input"
                    data-is-client="{{ $isClient ? '1' : '0' }}"
                    data-locked-client="{{ $lockedClientId ?? '' }}"
                    {{ $isClient ? 'disabled' : '' }}
                >
                    @if(!$isClient)
                        <option value="">-- Seleccionar --</option>
                    @endif

                    @foreach($clients as $c)
                        <option
                            value="{{ $c->id }}"
                            @selected($isClient ? ($lockedClientId == $c->id) : false)
                        >
                            {{ $c->name }} ({{ $c->bucket_folder ?? 'sin carpeta' }})
                        </option>
                    @endforeach
                </select>
            </div>

                <div>
                    <label style="font-weight:600; font-size:14px;">Elegir carpeta</label>
                    <select id="folderSelect" class="enigmacero-input" disabled>
                        <option value="">Seleccione un cliente primero</option>
                    </select>
                    <small id="hint" style="color:#6b7280;"></small>
                </div>
            </div>
        </div>

        <div class="ec-card" style="margin-top:14px; padding:0; overflow:hidden;">
            <div style="padding:14px 18px; border-bottom:1px solid rgba(0,0,0,0.06); display:flex; justify-content:space-between; align-items:center;">
                <div style="font-weight:700;">Archivos</div>
                <div id="count" style="color:#6b7280; font-size:13px;"></div>
            </div>

            <div id="emptyState" style="padding:18px; display:none;">
                No hay aún archivos disponibles.
            </div>

            <div style="overflow:auto;">
                <table class="ec-table" id="filesTable" style="width:100%; display:none;">
                    <thead>
                        <tr>
                            <th style="width:40%;">Nombre</th>
                            <th>Peso</th>
                            <th>Creado</th>
                            <th>Modificado</th>
                            <th style="width:150px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="filesTbody"></tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const clientSelect = document.getElementById('clientSelect');
    const folderSelect = document.getElementById('folderSelect');
    const hint = document.getElementById('hint');
    const table = document.getElementById('filesTable');
    const tbody = document.getElementById('filesTbody');
    const emptyState = document.getElementById('emptyState');
    const count = document.getElementById('count');

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	const isClient = (clientSelect.dataset.isClient === '1');

    function fmtBytes(bytes) {
        if (!bytes && bytes !== 0) return '';
        const units = ['B','KB','MB','GB'];
        let i = 0;
        let n = bytes;
        while (n >= 1024 && i < units.length - 1) { n /= 1024; i++; }
        return `${n.toFixed(i === 0 ? 0 : 2)} ${units[i]}`;
    }

    function fmtDate(iso) {
        if (!iso) return '';
        try {
            return new Date(iso).toLocaleString();
        } catch(e) {
            return iso;
        }
    }

    function resetFiles() {
        tbody.innerHTML = '';
        table.style.display = 'none';
        emptyState.style.display = 'none';
        count.textContent = '';
    }

    async function loadFolders(clientId) {
        folderSelect.disabled = true;
        folderSelect.innerHTML = `<option value="">Cargando...</option>`;
        hint.textContent = '';
        resetFiles();

        const res = await fetch(`{{ route('files.folders') }}?client_id=${encodeURIComponent(clientId)}`);
        const data = await res.json();

        const folders = Array.isArray(data.folders) ? data.folders : [];
        if (folders.length === 0) {
            folderSelect.innerHTML = `<option value="">(Sin carpetas)</option>`;
            hint.textContent = 'Este cliente aún no tiene carpetas.';
            folderSelect.disabled = true;
            return;
        }

        folderSelect.innerHTML = `<option value="">-- Seleccionar --</option>` +
            folders.map(f => `<option value="${f}">${f}</option>`).join('');

        folderSelect.disabled = false;
        hint.textContent = 'Seleccione una carpeta para listar archivos.';
    }

    async function loadFiles(clientId, folder) {
        resetFiles();
        count.textContent = 'Cargando...';

        const res = await fetch(`{{ route('files.list') }}?client_id=${encodeURIComponent(clientId)}&folder=${encodeURIComponent(folder)}`);
        const data = await res.json();

        if (!res.ok) {
            count.textContent = '';
            Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo listar archivos.' });
            return;
        }

        const files = Array.isArray(data.files) ? data.files : [];
        count.textContent = `${files.length} archivo(s)`;

        if (files.length === 0) {
            emptyState.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        tbody.innerHTML = files.map(f => {
            const isTxt = (f.name || '').toLowerCase().endsWith('.txt');

            return `
                <tr>
                    <td>
                        <div style="font-weight:700;">${escapeHtml(f.name)}</div>
                        <div style="color:#6b7280; font-size:12px;">${escapeHtml(f.disk_path)}</div>
                    </td>
                    <td>${fmtBytes(f.size_bytes)}</td>
                    <td>${fmtDate(f.created_at)}</td>
                    <td>${fmtDate(f.updated_at)}</td>
                    <td>
                        <div style="display:flex; gap:10px; align-items:center;">
                            ${isTxt ? `<a href="#" data-action="preview" data-file="${encodeURIComponent(f.name)}" title="Preview">👁️</a>` : `<span style="opacity:.35;" title="Preview solo .txt">👁️</span>`}
                            <a href="{{ route('files.download') }}?client_id=${encodeURIComponent(clientId)}&folder=${encodeURIComponent(folder)}&file=${encodeURIComponent(f.name)}" title="Descargar">⬇️</a>
                            <a href="#" data-action="delete" data-file="${encodeURIComponent(f.name)}" title="Eliminar">🗑️</a>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    async function doPreview(clientId, folder, fileName) {
        const res = await fetch(`{{ route('files.preview') }}?client_id=${encodeURIComponent(clientId)}&folder=${encodeURIComponent(folder)}&file=${encodeURIComponent(fileName)}`);
        const data = await res.json();

        if (!res.ok) {
            Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo abrir el preview.' });
            return;
        }

        const extra = data.truncated ? '<div style="margin-top:8px; color:#b45309;">(Mostrando solo una parte del archivo)</div>' : '';

        Swal.fire({
            title: data.name || 'Preview',
            width: 900,
            html: `<pre style="text-align:left; max-height:60vh; overflow:auto; background:#0b1220; color:#e5e7eb; padding:12px; border-radius:10px;">${escapeHtml(data.content || '')}</pre>${extra}`,
            confirmButtonText: 'Cerrar'
        });
    }

    async function doDelete(clientId, folder, fileName) {
        const confirm = await Swal.fire({
            icon: 'warning',
            title: '¿Eliminar archivo?',
            text: fileName,
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirm.isConfirmed) return;

        const res = await fetch(`{{ route('files.delete') }}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({
                client_id: clientId,
                folder: folder,
                file: fileName
            })
        });

        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.ok) {
            Swal.fire({ icon:'error', title:'Error', text: data.error || 'No se pudo eliminar.' });
            return;
        }

        Swal.fire({ icon:'success', title:'Eliminado', timer: 1200, showConfirmButton:false });
        await loadFiles(clientId, folder);
    }

    function escapeHtml(str) {
        return String(str)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    clientSelect.addEventListener('change', async () => {
        const clientId = clientSelect.value;
        folderSelect.value = '';
        resetFiles();

        if (!clientId) {
            folderSelect.disabled = true;
            folderSelect.innerHTML = `<option value="">Seleccione un cliente primero</option>`;
            hint.textContent = '';
            return;
        }

        try {
            await loadFolders(clientId);
        } catch (e) {
            folderSelect.disabled = true;
            folderSelect.innerHTML = `<option value="">Error cargando carpetas</option>`;
            Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la lista de carpetas.' });
        }
    });

	// Si el usuario es cliente, el select viene preseleccionado y bloqueado.
	// Aquí inicializamos carpetas automáticamente.
	if (clientSelect.value) {
	    loadFolders(clientSelect.value).catch(() => {
	        folderSelect.disabled = true;
	        folderSelect.innerHTML = `<option value="">Error cargando carpetas</option>`;
	        Swal.fire({ icon:'error', title:'Error', text:'No se pudo cargar la lista de carpetas.' });
	    });
	}

    folderSelect.addEventListener('change', async () => {
        const clientId = clientSelect.value;
        const folder = folderSelect.value;
        if (!clientId || !folder) return;

        await loadFiles(clientId, folder);
    });

    // Delegación de eventos para acciones en tabla
    document.addEventListener('click', async (e) => {
        const el = e.target.closest('a[data-action]');
        if (!el) return;

        e.preventDefault();

        const action = el.getAttribute('data-action');
        const clientId = clientSelect.value;
        const folder = folderSelect.value;
        const fileName = decodeURIComponent(el.getAttribute('data-file') || '');

        if (!clientId || !folder || !fileName) return;

        if (action === 'preview') {
            await doPreview(clientId, folder, fileName);
        } else if (action === 'delete') {
            await doDelete(clientId, folder, fileName);
        }
    });
});
</script>
@endsection

