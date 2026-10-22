@extends('layouts.app')

@php
    $leadTitle = (!empty($lead['company']) ? $lead['company'] . ' | ' : '') . ($lead['first_name'] ?? $lead['name'] ?? 'Lead');
@endphp

@section('title', $leadTitle . ' — Lead Detail — DealSync CRM')

@section('content')
<!-- Lead 360 Fullscreen View Container (Covers entire screen: header, footer, and sidebar) -->
<div id="lead-show-page-root" class="fixed inset-0 z-[99999] bg-[#f8fafc] dark:bg-zinc-950 overflow-y-auto w-screen h-screen">
    @include('leads.partials.fullscreen_detail')
</div>

<!-- Safe JSON hydration for SPA Navigation & Detail Actions -->
@php
    $leadDetailPayload = [
        'lead' => $lead,
        'customFields' => $customFields ?? [],
        'fieldPermissions' => !empty($fieldPermissions) ? $fieldPermissions : (object)[],
        'users' => $users ?? [],
        'sources' => $sources ?? [],
        'pageLayout' => $pageLayout ?? null,
    ];
@endphp
<script>
    window.__LEAD_DETAIL_DATA__ = {!! json_encode($leadDetailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!};
</script>
<script src="{{ asset('js/lead_detail.js') }}?v=4.3"></script>
@endsection
