@extends('layouts.app')

@section('title', 'Page Layouts Designer — DealSync Admin')

@section('content')
<div class="max-w-[1600px] mx-auto px-4 sm:px-5 py-3.5 flex flex-col gap-3 animate-fade-in" id="plStudioRoot">
    @include('partials.admin_nav')

    <!-- Unified Control Header (Exact React 1:1 Parity) -->
    <div id="plHeaderControls"></div>

    <!-- 6 Segmented Sub-Tabs Strip -->
    <div id="plSubTabsStrip"></div>

    <!-- Active Tab Canvas Content -->
    <div id="plTabContent"></div>

    <!-- Hidden Server-Side Hydration Payload -->
    <script id="plServerData" type="application/json">
        {!! json_encode([
            'initialModule' => $initialModule,
            'customObjects' => $customObjects,
            'profiles'      => $profiles,
            'layouts'       => $layouts,
            'assignments'   => $assignments,
        ]) !!}
    </script>
</div>

<!-- Page Layouts Client Script -->
<script src="{{ asset('js/page-layouts.js') }}?v=2.1"></script>
@endsection
