@extends('layouts.app')

@section('title', 'Page Layouts Designer — DealSync Admin')

@section('content')
<div class="admin-page-container animate-fade-in">
    @include('partials.admin_nav')

    <!-- Top Action Header -->
    <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="badge badge-indigo" style="font-size: 0.7rem; font-weight: 700;">UI Section &amp; Field Placement Designer</span>
                <span style="font-size: 0.725rem; color: var(--zinc-500);">Dynamic 1-Column &amp; 2-Column Section Grids, Required Flags &amp; Tab Ordering</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.03em; margin: 0; line-height: 1.2;">
                Page Layouts Designer Studio
            </h1>
            <p style="font-size: 0.8125rem; color: var(--zinc-500); margin: 0.25rem 0 0 0;">
                Customize record detail screens with drag-and-drop section groupings, field positioning, required field enforcement, and related lists.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 0.6rem;">
            <button type="button" class="btn btn-secondary btn-sm" onclick="showToast('+ Add Layout Section modal opened!')">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>+ Add Section</span>
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="showToast('Page layout changes saved successfully!')">
                <i data-lucide="save" style="width: 14px; height: 14px;"></i>
                <span>Save Layout Changes</span>
            </button>
        </div>
    </div>

    <!-- Active Layout Canvas Demonstration: Opportunities Main Layout -->
    <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; border-bottom: 1px solid var(--zinc-200); padding-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <span style="font-size: 0.8rem; font-weight: 700; color: var(--zinc-500);">SELECT MODULE LAYOUT:</span>
                <select class="form-control" style="font-size: 0.825rem; font-weight: 600; padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-300);">
                    <option value="opportunities" selected>Opportunities (Commercial Pipeline Layout)</option>
                    <option value="leads">Leads (Inbound Qualification Layout)</option>
                    <option value="accounts">Accounts (360 Master Organization Layout)</option>
                    <option value="purchase_deals">Purchase Deals (Procurement PO Layout)</option>
                </select>
            </div>
            <span class="badge badge-purple">Active Production Layout</span>
        </div>

        <!-- Section 1: Record Header Highlights -->
        <div style="background: var(--zinc-50); border: 1px dashed var(--zinc-300); border-radius: 10px; padding: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--zinc-900); margin: 0;">
                    1. Key Highlights &amp; Commercial Terms (2-Column Grid)
                </h4>
                <span class="badge badge-blue">Collapsible: Yes</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Opportunity Name</strong>
                    </div>
                    <span class="badge badge-red" style="font-size: 0.65rem;">Required</span>
                </div>

                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Deal Amount ($ USD)</strong>
                    </div>
                    <span class="badge badge-red" style="font-size: 0.65rem;">Required</span>
                </div>

                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Customer Account</strong>
                    </div>
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">Lookup</span>
                </div>

                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Pipeline Stage</strong>
                    </div>
                    <span class="badge badge-blue" style="font-size: 0.65rem;">Dropdown</span>
                </div>
            </div>
        </div>

        <!-- Section 2: Procurement Coupling -->
        <div style="background: var(--zinc-50); border: 1px dashed var(--zinc-300); border-radius: 10px; padding: 1.25rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--zinc-900); margin: 0;">
                    2. Procurement PO Coupling &amp; Logistics Bridge (2-Column Grid)
                </h4>
                <span class="badge badge-blue">Collapsible: Yes</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Linked Procurement Deal (PO)</strong>
                    </div>
                    <span class="badge badge-indigo" style="font-size: 0.65rem;">Required Lookup</span>
                </div>

                <div style="background: #ffffff; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--zinc-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.7rem; color: var(--zinc-500);">FIELD LABEL</div>
                        <strong style="font-size: 0.85rem; color: var(--zinc-900);">Target Close Date</strong>
                    </div>
                    <span class="badge badge-gray" style="font-size: 0.65rem;">Date</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
