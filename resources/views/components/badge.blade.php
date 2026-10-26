@props(['status'])
<span class="badge">{{ config('mythos.status_labels.'.$status, $status) }}</span>
