@extends('layouts.app')

@section('title', 'Document & Billing Templates Studio — DealSync Admin')

@section('content')
<!-- Max-Width Studio Container with balanced margins matching Modern DealSync Layout -->
<div class="max-w-[1400px] mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 pb-16 space-y-6 animate-fade-in">
    @include('partials.admin_nav')

    <!-- Studio Mount Root Container -->
    <div id="doc-studio-root" class="w-full">
        <!-- Initial skeleton / loading placeholder before JS hydration -->
        <div class="space-y-6 animate-pulse">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                    <div class="space-y-2">
                        <div class="w-64 h-6 bg-slate-200 dark:bg-slate-800 rounded"></div>
                        <div class="w-96 h-4 bg-slate-200 dark:bg-slate-800 rounded"></div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <div class="w-28 h-9 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                    <div class="w-28 h-9 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                </div>
            </div>

            <div class="grid grid-cols-4 gap-4">
                <div class="h-20 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                <div class="h-20 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                <div class="h-20 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                <div class="h-20 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
            </div>

            <div class="h-96 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl flex items-center justify-center text-slate-400 text-sm font-medium">
                Loading Document &amp; Billing Templates Studio...
            </div>
        </div>
    </div>
</div>

<!-- CSRF & Initial Server Data Injections -->
<script>
    window.CSRF_TOKEN = '{{ csrf_token() }}';
    window.__DOC_TEMPLATES_DATA__ = {
        templates: @json($templates),
        profiles: @json($profiles),
        governanceRights: @json($governanceRights)
    };
</script>

<!-- Dedicated Document Templates Studio JavaScript Engine -->
<script src="{{ asset('js/document_templates.js') }}?v=1.0"></script>
<script>
    if (typeof initDocumentTemplatesStudio === 'function') {
        initDocumentTemplatesStudio();
    }
</script>
@endsection
