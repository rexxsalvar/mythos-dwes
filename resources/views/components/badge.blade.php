@props(['status'])
<span class="badge" data-status="{{ $status }}">{{ config('mythos.status_labels.'.$status, $status) }}</span>
