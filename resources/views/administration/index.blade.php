<x-layout title="Administración">
    <div class="page-heading"><p class="eyebrow">VISTA GENERAL</p><h1>Administración</h1><p class="muted">Resumen de registros y perfiles de acceso de Mythos.</p></div>
    <div class="stats-grid">@foreach($counts as $label => $count)<div class="panel stat"><span class="muted">{{ $label }}</span><strong>{{ $count }}</strong></div>@endforeach</div>
    <section class="panel"><h2>Perfiles de acceso</h2><dl class="details">@foreach($roles as $role)<div><dt>{{ config('mythos.role_labels.'.$role->name, $role->name) }}</dt><dd>{{ $role->users_count }} {{ $role->users_count === 1 ? 'cuenta' : 'cuentas' }}</dd></div>@endforeach</dl></section>
</x-layout>
