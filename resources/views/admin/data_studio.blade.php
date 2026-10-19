@extends('layouts.app')

@section('title', 'Data Management & CSV Studio — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">Bulk Data Import, Export &amp; ETL Engine</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">CSV Schema Mapping, Deduplication Interception &amp; Bulk Migration</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Data Management &amp; CSV Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Perform high-volume data operations: batch CSV record uploads with auto-column mapping, deduplication policies, and filtered exports.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Exporting all data tables to ZIP bundle...')">
                <i data-lucide="download" style="width: 14px; height: 14px;"></i>
                <span>Export Full Backup</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('+ Import CSV wizard initiated!')">
                <i data-lucide="upload" style="width: 14px; height: 14px;"></i>
                <span>+ Start CSV Import</span>
            </button>
        </div>
    </div>

    <!-- Drag & Drop CSV Import Area -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem; text-align: center; border: 2px dashed var(--zinc-300); background: var(--zinc-50); cursor: pointer;" onclick="showToast('Select a CSV file from your computer!')">
        <div style="width: 48px; height: 48px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem auto;">
            <i data-lucide="file-spreadsheet" style="width: 24px; height: 24px;"></i>
        </div>
        <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0 0 0.25rem 0;">Drag and drop your CSV file here</h3>
        <p style="font-size: 0.8rem; color: var(--zinc-500); margin: 0 0 0.75rem 0;">Supports UTF-8 CSV files up to 50MB. Auto-detects headers and data types.</p>
        <button type="button" class="btn btn-secondary btn-sm" style="font-weight: 600;">Browse Computer</button>
    </div>

    <!-- Recent Import & Export Job Ledger -->
    <div class="card" style="padding: 1.25rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Recent Import &amp; Export Job History</h3>
            <span class="badge badge-green">ETL Engine Ready</span>
        </div>

        <div class="card-table-wrapper">
            <table class="data-table" id="data-jobs-table">
                <thead>
                    <tr>
                        <th>Job ID &amp; Filename</th>
                        <th>Target Entity</th>
                        <th>Job Type</th>
                        <th>Processed Rows</th>
                        <th>Success Rate</th>
                        <th>Timestamp (UTC)</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>JOB-9912: q3_enterprise_leads.csv</strong></td>
                        <td><span class="badge badge-blue">Leads</span></td>
                        <td><span class="badge badge-indigo">CSV Import</span></td>
                        <td>1,240 Rows</td>
                        <td><span class="badge badge-green">100% Success</span></td>
                        <td style="font-family: monospace; font-size: 0.75rem;">2026-10-12 18:24</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Viewing error logs for JOB-9912')">View Log</button>
                        </td>
                    </tr>

                    <tr>
                        <td><strong>JOB-9840: accounts_financial_export.csv</strong></td>
                        <td><span class="badge badge-blue">Accounts</span></td>
                        <td><span class="badge badge-purple">Bulk Export</span></td>
                        <td>4 Accounts</td>
                        <td><span class="badge badge-green">100% Exported</span></td>
                        <td style="font-family: monospace; font-size: 0.75rem;">2026-10-11 11:05</td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('Re-downloading export file...')">Download</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
