@extends('layouts.app')

@section('title', 'Emails — DealSync Enterprise CRM')

@section('content')
<div class="emails-workstation animate-fade-in" style="margin: -1rem -1rem 0 -1rem; height: calc(100vh - 66px); display: flex; flex-direction: column; overflow: hidden; background: var(--bg-main, #f8fafc);">

    <!-- 1. TOP HEADER & TOOLBAR (React CRM Parity) -->
    <header style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 1.25rem; min-height: 48px; background: var(--header-bg, #ffffff); border-bottom: 1px solid var(--border-card, #e2e8f0); flex-shrink: 0;" class="dark:bg-zinc-900 dark:border-zinc-800">
        <div style="display: flex; align-items: center; gap: 0.65rem;">
            <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%); display: flex; align-items: center; justify-content: center; color: #ffffff; box-shadow: 0 1px 3px rgba(79, 70, 229, 0.25); flex-shrink: 0;">
                <i data-lucide="mail" style="width: 15px; height: 15px; stroke-width: 2.2;"></i>
            </div>

            <div style="display: flex; align-items: baseline; gap: 0.45rem; flex-wrap: wrap;">
                <h2 style="font-size: 0.925rem; font-weight: 700; margin: 0; color: var(--zinc-950, #0f172a); line-height: 1.2;" class="dark:text-zinc-100">
                    Emails - <span id="current-user-display">{{ $currentUserName ?? 'Alex Morgan' }}</span>
                </h2>
                <span id="last-synced-indicator" style="font-size: 0.72rem; color: var(--zinc-400, #94a3b8); font-weight: 400;">
                    • Last synced: <span id="sync-time-text">Just now</span>
                </span>
            </div>
        </div>

        <!-- Action Controls -->
        <div style="display: flex; align-items: center; gap: 0.45rem;">
            <!-- Reading Pane Toggle -->
            <button
                type="button"
                id="toggle-reading-pane-btn"
                onclick="toggleReadingPane()"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.35rem; height: 30px; font-size: 0.75rem; padding: 0 0.65rem;"
                title="Toggle Reading Pane split view mode"
            >
                <i data-lucide="columns" id="reading-pane-icon" style="width: 13px; height: 13px;"></i>
                <span id="reading-pane-text">Reading Pane: On</span>
            </button>

            <!-- Sync Mailbox -->
            <button
                type="button"
                id="sync-mailbox-btn"
                onclick="triggerMailboxSync()"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.35rem; height: 30px; font-size: 0.75rem; padding: 0 0.65rem;"
                title="Sync latest messages from mail server"
            >
                <i data-lucide="refresh-cw" id="sync-spinner-icon" style="width: 13px; height: 13px;"></i>
                <span id="sync-btn-text">Sync Mailbox</span>
            </button>

            <!-- Simulate Inbound Email -->
            <button
                type="button"
                onclick="simulateInboundEmail()"
                class="btn btn-ghost btn-sm"
                style="font-size: 0.75rem; color: #4f46e5; height: 30px; padding: 0 0.55rem; font-weight: 600;"
                title="Inject a simulated customer inbound message"
            >
                + Simulate Inbound Email
            </button>

            <!-- Bulk Campaign Switcher Button -->
            <button
                type="button"
                onclick="switchMailFolder('campaigns')"
                class="btn btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.35rem; height: 30px; font-size: 0.75rem; padding: 0 0.75rem; background: #fdf2f8; border: 1px solid #fbcfe8; color: #db2777; font-weight: 600; border-radius: 6px;"
                title="Open Bulk Email Broadcast Hub"
            >
                <i data-lucide="megaphone" style="width: 13px; height: 13px;"></i>
                <span>Bulk Campaign</span>
            </button>

            <!-- Compose Email Primary Button -->
            <button
                type="button"
                onclick="openComposeModal()"
                class="btn btn-primary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.35rem; height: 30px; font-size: 0.775rem; padding: 0 0.85rem; background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%); color: #ffffff; border: none; font-weight: 600; box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);"
            >
                <i data-lucide="plus" style="width: 14px; height: 14px; stroke-width: 2.5;"></i>
                <span>Compose Email</span>
            </button>
        </div>
    </header>

    <!-- Notification Alert Banner -->
    <div id="email-toast-banner" style="display: none; padding: 0.6rem 1.25rem; font-size: 0.8rem; font-weight: 500; align-items: center; justify-content: space-between; border-bottom: 1px solid transparent; transition: all 0.2s ease;">
        <div style="display: flex; align-items: center; gap: 0.5rem;" id="email-toast-content">
            <i data-lucide="check-circle-2" style="width: 15px; height: 15px;"></i>
            <span id="email-toast-text">Mailbox updated successfully</span>
        </div>
        <button type="button" onclick="hideEmailToast()" style="background: none; border: none; cursor: pointer; color: inherit; padding: 2px;">
            <i data-lucide="x" style="width: 14px; height: 14px;"></i>
        </button>
    </div>

    <!-- 2. MAIN WORKSPACE CONTAINER -->
    <div style="display: flex; flex: 1; min-height: 0; overflow: hidden;">

        <!-- PANE 1: FOLDERS & TAG FILTERS (COLLAPSIBLE RAIL) -->
        <aside
            id="email-folders-rail"
            style="width: 230px; background: var(--sidebar-bg, #ffffff); border-right: 1px solid var(--border-card, #e2e8f0); display: flex; flex-direction: column; flex-shrink: 0; padding: 1rem 0.75rem; gap: 1.25rem; transition: width 0.2s ease; overflow-y: auto;"
            class="dark:bg-zinc-900 dark:border-zinc-800"
        >
            <!-- Mailbox Folders Header -->
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0 0.5rem 0.4rem 0.5rem; border-bottom: 1px solid var(--zinc-100, #f1f5f9); margin-bottom: 0.4rem;" class="dark:border-zinc-800">
                    <span id="folders-rail-label" style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--zinc-400); letter-spacing: 0.05em;">
                        Mailbox Folders
                    </span>
                    <button
                        type="button"
                        onclick="toggleFoldersRailCollapse()"
                        style="background: none; border: none; cursor: pointer; color: var(--zinc-500); padding: 0.2rem; display: flex; align-items: center; justify-content: center; border-radius: 4px;"
                        title="Collapse / Expand folders panel"
                    >
                        <i data-lucide="panel-left-close" id="rail-collapse-icon" style="width: 15px; height: 15px;"></i>
                    </button>
                </div>

                <!-- Folders List -->
                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <!-- Inbox -->
                    <button
                        type="button"
                        onclick="switchMailFolder('inbox')"
                        id="folder-btn-inbox"
                        class="email-folder-btn active"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: #eff6ff; color: #1d4ed8; font-weight: 700; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="inbox" style="width: 16px; height: 16px; stroke-width: 2.2;"></i>
                            <span class="folder-name-label">Inbox</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 700; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #2563eb; color: #ffffff;">
                            {{ $folderCounts['inbox'] ?? 3 }}
                        </span>
                    </button>

                    <!-- Sent -->
                    <button
                        type="button"
                        onclick="switchMailFolder('sent')"
                        id="folder-btn-sent"
                        class="email-folder-btn"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-700, #334155); font-weight: 500; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="send" style="width: 16px; height: 16px; stroke-width: 1.8;"></i>
                            <span class="folder-name-label">Sent</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 600; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #f1f5f9; color: #475569;">
                            {{ $folderCounts['sent'] ?? 1 }}
                        </span>
                    </button>

                    <!-- Drafts -->
                    <button
                        type="button"
                        onclick="switchMailFolder('drafts')"
                        id="folder-btn-drafts"
                        class="email-folder-btn"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-700, #334155); font-weight: 500; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="file-edit" style="width: 16px; height: 16px; stroke-width: 1.8;"></i>
                            <span class="folder-name-label">Drafts</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 600; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #f1f5f9; color: #475569;">
                            {{ $folderCounts['drafts'] ?? 1 }}
                        </span>
                    </button>

                    <!-- Outbox -->
                    <button
                        type="button"
                        onclick="switchMailFolder('outbox')"
                        id="folder-btn-outbox"
                        class="email-folder-btn"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-700, #334155); font-weight: 500; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="clock" style="width: 16px; height: 16px; stroke-width: 1.8;"></i>
                            <span class="folder-name-label">Outbox</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 600; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #fef3c7; color: #b45309;">
                            {{ $folderCounts['outbox'] ?? 1 }}
                        </span>
                    </button>

                    <!-- Trash -->
                    <button
                        type="button"
                        onclick="switchMailFolder('trash')"
                        id="folder-btn-trash"
                        class="email-folder-btn"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-700, #334155); font-weight: 500; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="trash-2" style="width: 16px; height: 16px; stroke-width: 1.8;"></i>
                            <span class="folder-name-label">Trash</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 600; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #f1f5f9; color: #475569;">
                            {{ $folderCounts['trash'] ?? 1 }}
                        </span>
                    </button>

                    <div style="height: 1px; background: var(--zinc-100, #f1f5f9); margin: 0.35rem 0.25rem;" class="dark:border-zinc-800"></div>

                    <!-- Bulk Campaigns Folder -->
                    <button
                        type="button"
                        onclick="switchMailFolder('campaigns')"
                        id="folder-btn-campaigns"
                        class="email-folder-btn"
                        style="display: flex; align-items: center; justify-content: space-between; padding: 0.5rem 0.75rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-700, #334155); font-weight: 500; font-size: 0.825rem; cursor: pointer; transition: all 0.15s ease;"
                        title="Bulk Email Campaigns Hub"
                    >
                        <div style="display: flex; align-items: center; gap: 0.65rem;">
                            <i data-lucide="megaphone" style="width: 16px; height: 16px; color: #db2777;"></i>
                            <span class="folder-name-label">Bulk Campaigns</span>
                        </div>
                        <span class="folder-badge-counter" style="font-size: 0.675rem; font-weight: 700; padding: 0.05rem 0.45rem; border-radius: 9999px; background: #fce7f3; color: #be185d;">
                            {{ count($campaigns ?? []) }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Entity Tags Filter -->
            <div id="entity-tags-section">
                <div style="font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--zinc-400); padding: 0 0.5rem 0.4rem 0.5rem; letter-spacing: 0.05em;">
                    Entity Tags
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                    <button
                        type="button"
                        onclick="filterByTag('all')"
                        id="tag-btn-all"
                        class="tag-filter-btn active"
                        style="display: flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: none; background: var(--zinc-100, #f1f5f9); color: var(--zinc-950, #0f172a); font-weight: 600; font-size: 0.8rem; cursor: pointer; text-align: left;"
                    >
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #64748b;"></span>
                        <span>All Messages</span>
                    </button>

                    <button
                        type="button"
                        onclick="filterByTag('Lead')"
                        id="tag-btn-Lead"
                        class="tag-filter-btn"
                        style="display: flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-600, #475569); font-weight: 500; font-size: 0.8rem; cursor: pointer; text-align: left;"
                    >
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span>
                        <span>Tagged as Lead</span>
                    </button>

                    <button
                        type="button"
                        onclick="filterByTag('Contact')"
                        id="tag-btn-Contact"
                        class="tag-filter-btn"
                        style="display: flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-600, #475569); font-weight: 500; font-size: 0.8rem; cursor: pointer; text-align: left;"
                    >
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb;"></span>
                        <span>Tagged as Contact</span>
                    </button>

                    <button
                        type="button"
                        onclick="filterByTag('None')"
                        id="tag-btn-None"
                        class="tag-filter-btn"
                        style="display: flex; align-items: center; gap: 0.55rem; padding: 0.45rem 0.65rem; border-radius: 6px; border: none; background: transparent; color: var(--zinc-600, #475569); font-weight: 500; font-size: 0.8rem; cursor: pointer; text-align: left;"
                    >
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8;"></span>
                        <span>Untagged (None)</span>
                    </button>
                </div>
            </div>

            <!-- Unread Only Toggle -->
            <div id="unread-only-section" style="margin-top: auto; padding: 0.75rem 0.5rem; border-top: 1px solid var(--zinc-100, #f1f5f9);" class="dark:border-zinc-800">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.78rem; color: var(--zinc-600, #475569);" class="dark:text-zinc-400">
                    <input
                        type="checkbox"
                        id="unread-only-checkbox"
                        onchange="toggleUnreadOnly(this.checked)"
                        style="cursor: pointer; width: 14px; height: 14px; accent-color: #2563eb;"
                    />
                    <span>Unread messages only</span>
                </label>
            </div>
        </aside>

        <!-- VIEW MODE A: STANDARD 2-PANE (EMAIL FEED + READING PANE) -->
        <div id="standard-email-view" style="display: flex; flex: 1; min-width: 0; overflow: hidden;">

            <!-- PANE 2: EMAIL LIST FEED (380px OR FULL WIDTH) -->
            <section
                id="email-list-column"
                style="width: 380px; background: #ffffff; border-right: 1px solid var(--border-card, #e2e8f0); display: flex; flex-direction: column; min-width: 0; flex-shrink: 0;"
                class="dark:bg-zinc-900 dark:border-zinc-800"
            >
                <!-- Search Bar & List Header -->
                <div style="padding: 0.65rem 0.85rem; border-bottom: 1px solid var(--border-card, #e2e8f0); display: flex; gap: 0.5rem; align-items: center;" class="dark:border-zinc-800">
                    <div style="position: relative; flex: 1;">
                        <i data-lucide="search" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: var(--zinc-400);"></i>
                        <input
                            type="text"
                            id="email-search-input"
                            oninput="filterEmailsBySearch(this.value)"
                            placeholder="Search subject, sender, body..."
                            class="form-input"
                            style="padding-left: 2.1rem; height: 32px; font-size: 0.8rem; width: 100%; border-radius: 6px; border: 1px solid var(--border-card, #cbd5e1);"
                        />
                    </div>
                </div>

                <!-- Email Items Feed -->
                <div id="email-items-container" style="flex: 1; overflow-y: auto;">
                    @forelse($emails as $index => $em)
                        <div
                            id="email-card-{{ $em['id'] }}"
                            class="email-feed-card {{ $index === 0 ? 'selected' : '' }} {{ !$em['is_read'] ? 'unread' : '' }}"
                            onclick="selectEmail('{{ $em['id'] }}')"
                            data-folder="{{ $em['folder'] }}"
                            data-tag="{{ $em['entity_tag'] }}"
                            data-read="{{ $em['is_read'] ? '1' : '0' }}"
                            data-starred="{{ $em['is_starred'] ? '1' : '0' }}"
                            style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--zinc-100, #f1f5f9); cursor: pointer; transition: background 0.1s ease; border-left: {{ $index === 0 ? '3px solid #4f46e5' : (!$em['is_read'] ? '3px solid #0284c7' : '3px solid transparent') }}; background: {{ $index === 0 ? '#f8fafc' : (!$em['is_read'] ? '#f0f9ff' : '#ffffff') }};"
                        >
                            <!-- Card Header: Star, Sender, Time -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.45rem; min-width: 0;">
                                    <button
                                        type="button"
                                        onclick="toggleStar(event, '{{ $em['id'] }}')"
                                        style="background: none; border: none; cursor: pointer; padding: 0; display: flex; color: {{ $em['is_starred'] ? '#eab308' : '#cbd5e1' }};"
                                        title="Star this message"
                                    >
                                        <i data-lucide="star" style="width: 13px; height: 13px; {{ $em['is_starred'] ? 'fill: #eab308;' : '' }}"></i>
                                    </button>

                                    <span style="font-weight: {{ !$em['is_read'] ? '700' : '500' }}; font-size: 0.825rem; color: {{ !$em['is_read'] ? '#09090b' : '#334155' }}; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        {{ $em['sender_name'] }}
                                    </span>
                                </div>

                                <span style="font-size: 0.7rem; color: var(--zinc-400); flex-shrink: 0; margin-left: 0.5rem;">
                                    {{ $em['received_at'] }}
                                </span>
                            </div>

                            <!-- Subject line -->
                            <div style="font-weight: {{ !$em['is_read'] ? '700' : '500' }}; font-size: 0.8125rem; color: {{ !$em['is_read'] ? '#09090b' : '#1e293b' }}; margin-bottom: 0.25rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $em['subject'] }}
                            </div>

                            <!-- Snippet preview -->
                            <div style="font-size: 0.75rem; color: var(--zinc-500); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-bottom: 0.45rem;">
                                {{ $em['snippet'] }}
                            </div>

                            <!-- Badges footer -->
                            <div style="display: flex; align-items: center; gap: 0.4rem; flex-wrap: wrap;">
                                @if($em['entity_tag'] === 'Lead')
                                    <span style="font-size: 0.675rem; font-weight: 600; padding: 0.08rem 0.45rem; border-radius: 9999px; background: #dcfce7; color: #15803d; display: inline-flex; align-items: center; gap: 0.2rem;">
                                        <i data-lucide="user-check" style="width: 10px; height: 10px;"></i>
                                        <span>Lead: {{ $em['lead_name'] ?? 'Lead' }}</span>
                                    </span>
                                @elseif($em['entity_tag'] === 'Contact')
                                    <span style="font-size: 0.675rem; font-weight: 600; padding: 0.08rem 0.45rem; border-radius: 9999px; background: #e0f2fe; color: #0369a1; display: inline-flex; align-items: center; gap: 0.2rem;">
                                        <i data-lucide="users" style="width: 10px; height: 10px;"></i>
                                        <span>Contact: {{ $em['contact_name'] ?? 'Contact' }}</span>
                                    </span>
                                @else
                                    <span style="font-size: 0.675rem; font-weight: 500; padding: 0.08rem 0.4rem; border-radius: 9999px; background: #f1f5f9; color: #64748b;">
                                        Untagged
                                    </span>
                                @endif

                                @if(count($em['attachments'] ?? []) > 0)
                                    <span style="font-size: 0.675rem; color: var(--zinc-500); display: inline-flex; align-items: center; gap: 0.15rem;">
                                        <i data-lucide="paperclip" style="width: 11px; height: 11px;"></i>
                                        <span>{{ count($em['attachments']) }}</span>
                                    </span>
                                @endif

                                @if($em['direction'] === 'outbound' && ($em['open_count'] ?? 0) > 0)
                                    <span
                                        onclick="openGeoAuditModal(event, '{{ $em['id'] }}')"
                                        style="font-size: 0.675rem; color: #2563eb; background: #eff6ff; padding: 0.05rem 0.35rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.15rem; cursor: pointer;"
                                        title="Recipient opened {{ $em['open_count'] }} times from {{ $em['last_opened_city'] ?? 'Location' }}"
                                    >
                                        <i data-lucide="eye" style="width: 10px; height: 10px;"></i>
                                        <span>{{ $em['open_count'] }}x</span>
                                        @if(!empty($em['last_opened_city']))
                                            <span style="color: #0369a1; margin-left: 0.15rem;">• {{ $em['last_opened_city'] }}</span>
                                        @endif
                                    </span>
                                @endif

                                @if($em['direction'] === 'inbound' && (!empty($em['sender_city']) || !empty($em['sender_country'])))
                                    <span
                                        onclick="openSenderInsightModal(event, '{{ $em['id'] }}')"
                                        style="font-size: 0.675rem; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 0.05rem 0.35rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.15rem; cursor: pointer;"
                                        title="Inbound sender origin: {{ $em['sender_city'] ?? '' }} {{ $em['sender_country'] ?? '' }}"
                                    >
                                        <i data-lucide="map-pin" style="width: 9px; height: 9px; color: #0284c7;"></i>
                                        <span>{{ $em['sender_city'] ?? $em['sender_country'] }}</span>
                                    </span>
                                @endif

                                @if($em['folder'] === 'outbox')
                                    <span style="font-size: 0.675rem; color: #b45309; background: #fef3c7; padding: 0.05rem 0.35rem; border-radius: 4px; display: inline-flex; align-items: center; gap: 0.15rem;">
                                        <i data-lucide="clock" style="width: 10px; height: 10px;"></i>
                                        <span>Scheduled</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="padding: 2.5rem 1rem; text-align: center; color: var(--zinc-400);">
                            <i data-lucide="inbox" style="width: 32px; height: 32px; margin: 0 auto 0.5rem auto; opacity: 0.6;"></i>
                            <div style="font-size: 0.875rem; font-weight: 600; color: var(--zinc-600);">No emails found</div>
                            <div style="font-size: 0.75rem; margin-top: 0.25rem;">Try clearing filters or search query</div>
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- PANE 3: FULL READING PANE -->
            <main id="email-reading-pane" style="flex: 1; min-width: 0; display: flex; flex-direction: column; background: #ffffff; overflow: hidden;" class="dark:bg-zinc-900">
                <div id="email-detail-container" style="display: flex; flex-direction: column; height: 100%; overflow: hidden;">

                    <!-- Action Toolbar -->
                    <div style="padding: 0.5rem 0.85rem; border-bottom: 1px solid var(--border-card, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #fafafa; flex-shrink: 0; gap: 0.45rem;" class="dark:bg-zinc-800/60 dark:border-zinc-800">
                        <div style="display: flex; align-items: center; gap: 0.35rem; overflow-x: auto;">
                            <!-- Back Button (shown in full width list mode) -->
                            <button
                                type="button"
                                id="reading-pane-back-btn"
                                onclick="backToFullList()"
                                class="btn btn-secondary btn-sm"
                                style="display: none; font-size: 0.75rem; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem; font-weight: 600;"
                            >
                                <i data-lucide="arrow-left" style="width: 13px; height: 13px;"></i>
                                <span>Back</span>
                            </button>

                            <button
                                type="button"
                                onclick="replyToCurrentEmail('reply')"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem;"
                            >
                                <i data-lucide="reply" style="width: 13px; height: 13px;"></i>
                                <span>Reply</span>
                            </button>

                            <button
                                type="button"
                                onclick="replyToCurrentEmail('reply_all')"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem;"
                                title="Reply to sender and all CC recipients"
                            >
                                <i data-lucide="reply" style="width: 13px; height: 13px; transform: scaleX(-1);"></i>
                                <span>Reply All</span>
                            </button>

                            <button
                                type="button"
                                onclick="replyToCurrentEmail('forward')"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem;"
                            >
                                <i data-lucide="forward" style="width: 13px; height: 13px;"></i>
                                <span>Forward</span>
                            </button>

                            <button
                                type="button"
                                onclick="openScheduleMeetingFromEmail()"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem;"
                                title="Schedule a calendar meeting with this sender"
                            >
                                <i data-lucide="calendar" style="width: 13px; height: 13px; color: #8b5cf6;"></i>
                                <span>Schedule Meeting</span>
                            </button>

                            <button
                                type="button"
                                onclick="generateAiSummary()"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem; background: #f5f3ff; border-color: #ddd6fe; color: #6d28d9; font-weight: 600;"
                                title="Synthesize conversation with AI copilot"
                            >
                                <i data-lucide="sparkles" style="width: 13px; height: 13px;"></i>
                                <span>AI Summary</span>
                            </button>

                            <button
                                type="button"
                                onclick="openInsightFromDetail()"
                                class="btn btn-secondary btn-sm"
                                style="font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.25rem 0.55rem; background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; font-weight: 600;"
                                title="Inspect delivery, open, and click intelligence"
                            >
                                <i data-lucide="activity" style="width: 13px; height: 13px;"></i>
                                <span>Insight</span>
                            </button>
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0; margin-left: auto;">
                            <button
                                type="button"
                                onclick="moveCurrentEmailToTrash()"
                                class="btn btn-ghost btn-sm"
                                style="color: #ef4444; font-size: 0.75rem; padding: 0.25rem 0.45rem; display: flex; align-items: center; justify-content: center;"
                                title="Move email to Trash"
                            >
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </div>
                    </div>

                    <!-- CRM Entity Context Banner -->
                    <div id="crm-entity-banner" style="padding: 0.4rem 1.25rem; background: #f8fafc; border-bottom: 1px solid var(--border-card, #e2e8f0); flex-shrink: 0; min-height: 36px; display: flex; align-items: center; justify-content: space-between;" class="dark:bg-zinc-800/40 dark:border-zinc-800">
                        <div id="crm-entity-status" style="display: flex; align-items: center; gap: 0.45rem; font-size: 0.78rem; color: #166534;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span>
                            <span>Linked to CRM Lead: <strong id="crm-lead-title">Sarah Jenkins (Acme Corporation)</strong></span>
                        </div>
                        <div id="crm-entity-actions" style="display: flex; align-items: center; gap: 0.4rem;">
                            <a
                                href="{{ route('leads.index') }}"
                                class="btn btn-sm btn-secondary"
                                style="font-size: 0.725rem; height: 24px; display: inline-flex; align-items: center; gap: 0.3rem;"
                            >
                                <i data-lucide="external-link" style="width: 11px; height: 11px;"></i>
                                <span>View Lead Record</span>
                            </a>
                        </div>
                    </div>

                    <!-- Message Header & Sender Avatar -->
                    <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--zinc-100, #f1f5f9); display: flex; align-items: flex-start; gap: 0.85rem; flex-shrink: 0;" class="dark:border-zinc-800">
                        <div
                            id="detail-sender-avatar"
                            style="width: 40px; height: 40px; border-radius: 50%; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0;"
                        >
                            S
                        </div>

                        <div style="flex: 1; min-width: 0;">
                            <div style="display: flex; justify-content: space-between; align-items: baseline;">
                                <div style="font-weight: 700; font-size: 0.95rem; color: var(--zinc-950, #09090b);" class="dark:text-zinc-100" id="detail-sender-name">
                                    Sarah Jenkins
                                </div>
                                <span style="font-size: 0.75rem; color: var(--zinc-400);" id="detail-received-at">
                                    Today at 10:42 AM
                                </span>
                            </div>

                            <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;" id="detail-sender-email">
                                From: &lt;s.jenkins@acmecorp.io&gt;
                            </div>
                            <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.1rem;" id="detail-recipient-email">
                                To: Alex Morgan &lt;sales@dealsync.app&gt;
                            </div>
                        </div>
                    </div>

                    <!-- Attachments Bar -->
                    <div id="detail-attachments-bar" style="padding: 0.75rem 1.5rem; background: #fafafa; border-bottom: 1px solid var(--border-card, #e2e8f0); flex-shrink: 0;" class="dark:bg-zinc-800/50 dark:border-zinc-800">
                        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; color: var(--zinc-500); margin-bottom: 0.45rem; display: flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="paperclip" style="width: 12px; height: 12px;"></i>
                            <span id="detail-attachments-title">Attachments (2)</span>
                        </div>
                        <div id="detail-attachments-list" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                            <!-- Dynamically populated -->
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.65rem; background: #ffffff; border: 1px solid var(--border-card, #e2e8f0); border-radius: 6px; font-size: 0.75rem;">
                                <i data-lucide="file-spreadsheet" style="width: 14px; height: 14px; color: #16a34a;"></i>
                                <span style="font-weight: 600; color: #1e293b;">RFP_Requirements_Matrix_2026.xlsx</span>
                                <span style="color: #94a3b8; font-size: 0.7rem;">(2.4 MB)</span>
                                <a href="#" onclick="alert('Simulated file download for RFP_Requirements_Matrix_2026.xlsx'); return false;" style="color: #4f46e5; margin-left: 0.25rem;">
                                    <i data-lucide="download" style="width: 12px; height: 12px;"></i>
                                </a>
                            </div>
                            <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.65rem; background: #ffffff; border: 1px solid var(--border-card, #e2e8f0); border-radius: 6px; font-size: 0.75rem;">
                                <i data-lucide="file-text" style="width: 14px; height: 14px; color: #dc2626;"></i>
                                <span style="font-weight: 600; color: #1e293b;">Vendor_Security_Questionnaire.pdf</span>
                                <span style="color: #94a3b8; font-size: 0.7rem;">(840 KB)</span>
                                <a href="#" onclick="alert('Simulated file download for Vendor_Security_Questionnaire.pdf'); return false;" style="color: #4f46e5; margin-left: 0.25rem;">
                                    <i data-lucide="download" style="width: 12px; height: 12px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Email Content Body & AI Card & Thread -->
                    <div style="flex: 1; padding: 1.5rem; overflow-y: auto; background: #ffffff; line-height: 1.6; font-size: 0.875rem; color: #1e293b;" class="dark:bg-zinc-900 dark:text-zinc-200">

                        <!-- AI Conversation Synthesis Card -->
                        <div id="ai-summary-card" style="margin-bottom: 1.5rem; padding: 1.15rem; background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%); border: 1px solid #ddd6fe; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(109, 40, 217, 0.05);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.65rem;">
                                <div style="display: flex; align-items: center; gap: 0.45rem; font-weight: 700; font-size: 0.875rem; color: #6d28d9;">
                                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                                    <span>AI Conversation Synthesis &amp; Next Action</span>
                                </div>
                                <button type="button" onclick="document.getElementById('ai-summary-card').style.display='none'" style="background: none; border: none; cursor: pointer; color: #8b5cf6; padding: 0.15rem;" title="Dismiss AI summary">
                                    <i data-lucide="x" style="width: 15px; height: 15px;"></i>
                                </button>
                            </div>

                            <div id="ai-summary-text" style="font-size: 0.825rem; color: #4c1d95; margin-bottom: 0.65rem; line-height: 1.5;">
                                Sarah Jenkins (Acme Corp) is requesting a comprehensive technical architecture demo with emphasis on multi-tenant data sovereignty, enterprise SOC2 compliance, and single-tenant database isolation. Target rollout is Q3 2026.
                            </div>

                            <div style="margin-bottom: 0.65rem;">
                                <div style="font-size: 0.725rem; font-weight: 700; color: #5b21b6; text-transform: uppercase; margin-bottom: 0.25rem; letter-spacing: 0.04em;">
                                    Key Discussion Points:
                                </div>
                                <ul id="ai-key-points" style="margin: 0; padding-left: 1.25rem; font-size: 0.8rem; color: #4c1d95; display: flex; flex-direction: column; gap: 0.2rem;">
                                    <li>Architecture committee demo requested for next Thursday at 2:00 PM EST</li>
                                    <li>Security questionnaire and migration RFP matrix attached for review</li>
                                    <li>Key decision makers: Chief Architect & Security Director attending</li>
                                    <li>Estimated annual license scope: $180,000 – $250,000 ARR</li>
                                </ul>
                            </div>

                            <div id="ai-recommended-action" style="padding: 0.5rem 0.75rem; background: #ffffff; border-radius: 6px; border: 1px solid #c4b5fd; font-size: 0.78rem; color: #5b21b6; display: flex; align-items: center; gap: 0.45rem;">
                                <strong>Recommended Next Action:</strong>
                                <span id="ai-action-text">Confirm Thursday 2:00 PM EST slot with Solutions Engineering, attach SOC2 Type II compliance pack, and prep migration pricing proposal.</span>
                            </div>
                        </div>

                        <!-- Conversation Thread Stream (if applicable) -->
                        <div id="detail-thread-container" style="margin-bottom: 1.75rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.25rem;">
                            <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--zinc-500); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem; letter-spacing: 0.04em;">
                                <i data-lucide="clock" style="width: 13px; height: 13px;"></i>
                                <span>Conversation Thread Timeline (2 messages)</span>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                                <!-- Previous Thread Message 1 -->
                                <div style="border: 1px solid #e2e8f0; border-radius: 8px; background: #ffffff; overflow: hidden;" class="dark:bg-zinc-800 dark:border-zinc-700">
                                    <div
                                        onclick="toggleThreadMessage('thread-item-0')"
                                        style="padding: 0.55rem 0.85rem; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;"
                                        class="dark:bg-zinc-800/80"
                                    >
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <span style="font-weight: 600; color: #1e293b;" class="dark:text-zinc-200">Alex Morgan</span>
                                            <span style="color: #64748b; font-size: 0.75rem;">&lt;sales@dealsync.app&gt;</span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                                            <span style="color: #94a3b8; font-size: 0.725rem;">Yesterday at 4:15 PM</span>
                                            <i data-lucide="chevron-down" id="thread-chevron-0" style="width: 14px; height: 14px; color: #64748b;"></i>
                                        </div>
                                    </div>
                                    <div id="thread-item-0" style="display: none; padding: 0.85rem 1rem; font-size: 0.85rem; line-height: 1.6; color: #334155; border-top: 1px solid #f1f5f9;">
                                        Hi Sarah, It was a pleasure speaking with you today. Attached is our enterprise architecture overview and SOC2 Type II compliance summary. Please let us know if next Thursday works for the deep dive!
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Primary Message Body -->
                        <div id="detail-body-html" style="font-family: inherit; line-height: 1.6;">
                            <p>Hi Alex,</p>
                            <p>We thoroughly reviewed your solution deck from last week's initial discovery call. Our leadership and technical architecture committee are very interested in DealSync's tenant isolation capabilities and decoupled multi-tenant architecture.</p>
                            <p>We would like to coordinate a formal 60-minute technical deep dive next <strong>Thursday at 2:00 PM EST</strong>. Our Chief Architect and Security Director will be attending to review:</p>
                            <ul style="margin: 0.75rem 0; padding-left: 1.5rem; line-height: 1.7;">
                                <li>Single-tenant database isolation &amp; encryption key ownership</li>
                                <li>SOC2 Type II and GDPR data residency compliance guarantees</li>
                                <li>Automated migration pathways from legacy Salesforce instances</li>
                                <li>Custom object schema extensibility and webhook latency SLAs</li>
                            </ul>
                            <p>Attached please find our formal <strong>RFP Requirements Matrix</strong> and initial <strong>Vendor Security Questionnaire</strong>. Could you review and let us know if the proposed time works?</p>
                            <p style="margin-top: 1.5rem;">Best regards,<br/><strong>Sarah Jenkins</strong><br/><span style="color: #64748b; font-size: 0.85rem;">VP of Enterprise Technology &amp; Infrastructure<br/>Acme Corporation</span></p>
                        </div>

                        <!-- Quick Inline Reply Box -->
                        <div style="margin-top: 2rem; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff;" class="dark:bg-zinc-800 dark:border-zinc-700">
                            <div style="padding: 0.5rem 0.85rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;" class="dark:bg-zinc-800/80 dark:border-zinc-700">
                                <span style="font-size: 0.75rem; font-weight: 700; color: #475569;">Quick Reply to <span id="quick-reply-recipient">Sarah Jenkins</span></span>
                                <div style="display: flex; gap: 0.35rem;">
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="formatQuickReply('bold')" style="padding: 0.2rem 0.4rem; height: 24px; font-weight: 700;">B</button>
                                    <button type="button" class="btn btn-ghost btn-sm" onclick="formatQuickReply('italic')" style="padding: 0.2rem 0.4rem; height: 24px; font-style: italic;">I</button>
                                </div>
                            </div>
                            <textarea
                                id="quick-reply-textarea"
                                rows="3"
                                placeholder="Write a response..."
                                style="width: 100%; border: none; padding: 0.75rem 1rem; font-size: 0.85rem; outline: none; resize: vertical; font-family: inherit;"
                                class="dark:bg-zinc-900 dark:text-zinc-100"
                            ></textarea>
                            <div style="padding: 0.5rem 0.85rem; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                                <span style="font-size: 0.72rem; color: #94a3b8;">Press Ctrl+Enter to send immediately</span>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="button" onclick="document.getElementById('quick-reply-textarea').value=''" class="btn btn-secondary btn-sm" style="font-size: 0.75rem; height: 28px;">
                                        Clear
                                    </button>
                                    <button type="button" onclick="sendQuickReply()" class="btn btn-primary btn-sm" style="font-size: 0.75rem; height: 28px; background: #4f46e5; border-color: #4f46e5;">
                                        <i data-lucide="send" style="width: 12px; height: 12px;"></i>
                                        <span>Send Reply</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
        </div>

        <!-- VIEW MODE B: BULK EMAIL CAMPAIGNS HUB (ACTIVE WHEN CLICKING CAMPAIGNS) -->
        <div id="bulk-campaigns-view" style="display: none; flex: 1; min-width: 0; height: 100%; overflow-y: auto; background: #f8fafc; padding: 1.5rem;" class="dark:bg-zinc-900">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: linear-gradient(135deg, #db2777 0%, #9d174d 100%); display: flex; align-items: center; justify-content: center; color: #fff; box-shadow: 0 2px 6px rgba(219,39,119,0.3);">
                        <i data-lucide="megaphone" style="width: 19px; height: 19px;"></i>
                    </div>
                    <div>
                        <h2 style="font-size: 1.2rem; font-weight: 700; margin: 0; color: #0f172a;" class="dark:text-zinc-100">Bulk Email Campaigns</h2>
                        <p style="margin: 0.15rem 0 0 0; font-size: 0.775rem; color: #64748b;">
                            Broadcast mass emails to filtered Leads &amp; Contacts with duplicate protection &amp; mandatory opt-out check.
                        </p>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="button" onclick="showEmailToast('Bulk email metrics refreshed', 'success')" class="btn btn-secondary btn-sm" style="height: 32px; font-size: 0.775rem;">
                        <i data-lucide="refresh-cw" style="width: 13px; height: 13px;"></i>
                        <span>Refresh</span>
                    </button>
                    <button type="button" onclick="openNewCampaignWizard()" class="btn btn-primary btn-sm" style="height: 32px; font-size: 0.8rem; background: linear-gradient(135deg, #db2777 0%, #be185d 100%); border-color: #be185d; display: inline-flex; align-items: center; gap: 0.4rem; box-shadow: 0 2px 4px rgba(219, 39, 119, 0.25);">
                        <i data-lucide="plus" style="width: 15px; height: 15px; stroke-width: 2.5;"></i>
                        <span>New Bulk Campaign</span>
                    </button>
                </div>
            </div>

            <!-- KPI Summary Cards -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.85rem; margin-bottom: 1.25rem;">
                <div style="background: #fff; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Total Campaigns</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin-top: 0.25rem;" class="dark:text-zinc-100">{{ count($campaigns ?? []) }}</div>
                    <div style="font-size: 0.7rem; color: #10b981; margin-top: 0.2rem;">Broadcast operations</div>
                </div>

                <div style="background: #fff; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Recipients Reached</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #2563eb; margin-top: 0.25rem;">1,840</div>
                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">Leads &amp; Contacts targeted</div>
                </div>

                <div style="background: #fff; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Average Open Rate</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #059669; margin-top: 0.25rem;">49.1%</div>
                    <div style="font-size: 0.7rem; color: #059669; margin-top: 0.2rem;">+14% vs B2B benchmark</div>
                </div>

                <div style="background: #fff; border-radius: 10px; padding: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.03);" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <div style="font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.04em;">Click-Through Rate</div>
                    <div style="font-size: 1.65rem; font-weight: 800; color: #7c3aed; margin-top: 0.25rem;">17.3%</div>
                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.2rem;">318 tracked link clicks</div>
                </div>
            </div>

            <!-- Campaigns Table -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04);" class="dark:bg-zinc-800 dark:border-zinc-700">
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.825rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.725rem; text-transform: uppercase; letter-spacing: 0.04em;" class="dark:bg-zinc-800/80 dark:border-zinc-700">
                                <th style="padding: 0.75rem 1.25rem;">Campaign</th>
                                <th style="padding: 0.75rem 1rem;">Audience</th>
                                <th style="padding: 0.75rem 1rem;">Sent At</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Recipients</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Delivered</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Opens</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Clicks</th>
                                <th style="padding: 0.75rem 1rem; text-align: center;">Opt-Outs</th>
                                <th style="padding: 0.75rem 1.25rem; text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $camp)
                                <tr style="border-bottom: 1px solid #f1f5f9; cursor: pointer; transition: background 0.15s ease;" class="dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-750">
                                    <td style="padding: 0.85rem 1.25rem;">
                                        <div style="font-weight: 700; color: #0f172a;" class="dark:text-zinc-100">{{ $camp['name'] }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b; max-width: 280px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            {{ $camp['subject'] }}
                                        </div>
                                    </td>
                                    <td style="padding: 0.85rem 1rem;">
                                        <span style="display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.15rem 0.5rem; border-radius: 9999px; font-size: 0.725rem; font-weight: 600; background: {{ $camp['target_object'] === 'leads' ? '#ecfdf5' : '#eff6ff' }}; color: {{ $camp['target_object'] === 'leads' ? '#047857' : '#1d4ed8' }};">
                                            {{ ucfirst($camp['target_object']) }}
                                        </span>
                                    </td>
                                    <td style="padding: 0.85rem 1rem; color: #64748b; font-size: 0.775rem;">
                                        {{ date('M d, H:i', strtotime($camp['sent_at'])) }}
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center; font-weight: 600; color: #0f172a;" class="dark:text-zinc-100">
                                        {{ $camp['total_recipients'] }}
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center; color: #047857; font-weight: 600;">
                                        {{ $camp['sent_count'] }}
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        <span style="font-weight: 700; color: #10b981;">{{ $camp['open_count'] }}</span>
                                        <span style="font-size: 0.7rem; color: #64748b; margin-left: 0.25rem;">({{ round(($camp['open_count'] / $camp['sent_count']) * 100, 1) }}%)</span>
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center;">
                                        <span style="font-weight: 700; color: #7c3aed;">{{ $camp['click_count'] }}</span>
                                        <span style="font-size: 0.7rem; color: #64748b; margin-left: 0.25rem;">({{ round(($camp['click_count'] / $camp['sent_count']) * 100, 1) }}%)</span>
                                    </td>
                                    <td style="padding: 0.85rem 1rem; text-align: center; color: {{ $camp['unsubscribe_count'] > 0 ? '#b91c1c' : '#94a3b8' }}; font-weight: {{ $camp['unsubscribe_count'] > 0 ? 700 : 500 }};">
                                        {{ $camp['unsubscribe_count'] }}
                                    </td>
                                    <td style="padding: 0.85rem 1.25rem; text-align: right;">
                                        <button
                                            type="button"
                                            onclick="openCampaignInsight('{{ $camp['id'] }}', '{{ addslashes($camp['name']) }}')"
                                            class="btn btn-secondary btn-sm"
                                            style="font-size: 0.75rem; height: 28px; padding: 0 0.65rem; display: inline-flex; align-items: center; gap: 0.35rem; color: #db2777; border-color: #fbcfe8; background: #fdf2f8;"
                                        >
                                            <i data-lucide="bar-chart-3" style="width: 13px; height: 13px;"></i>
                                            <span>Insight</span>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. COMPOSE EMAIL MODAL (1:1 React EmailsView Parity) -->
    <div
        id="compose-modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 10000; align-items: center; justify-content: center; padding: 1.25rem;"
    >
        <div
            style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 680px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden; animation: modalScaleIn 0.2s ease-out;"
            class="dark:bg-zinc-900"
        >
            <!-- Modal Header -->
            <div style="padding: 0.85rem 1.25rem; border-bottom: 1px solid var(--border-card, #e2e8f0); display: flex; justify-content: space-between; align-items: center; background: #fafafa;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="file-edit" style="width: 16px; height: 16px; color: #4f46e5;"></i>
                    <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #0f172a;" class="dark:text-zinc-100" id="compose-modal-title">New Email Message</h3>
                </div>
                <button type="button" onclick="closeComposeModal()" class="btn btn-ghost btn-sm" style="padding: 0.25rem;">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                </button>
            </div>

            <!-- Form -->
            <form onsubmit="handleSendCompose(event)" style="display: flex; flex-direction: column; flex: 1; min-height: 0;">
                <div style="padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.75rem; flex: 1; overflow-y: auto;">
                    <!-- To recipient -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="width: 50px; font-size: 0.75rem; font-weight: 600; color: #64748b;">To:</label>
                        <input
                            type="email"
                            id="compose-to"
                            required
                            placeholder="recipient@example.com"
                            class="form-input"
                            style="flex: 1; height: 32px; font-size: 0.8125rem;"
                        />
                        <button type="button" onclick="toggleCcBcc()" class="btn btn-ghost btn-sm" style="font-size: 0.725rem; color: #4f46e5; height: 28px;">
                            Cc / Bcc
                        </button>
                    </div>

                    <!-- Cc / Bcc fields (collapsible) -->
                    <div id="compose-cc-bcc-row" style="display: none; flex-direction: column; gap: 0.5rem; padding-top: 0.25rem; border-top: 1px dashed #e2e8f0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <label style="width: 50px; font-size: 0.75rem; font-weight: 600; color: #64748b;">Cc:</label>
                            <input type="email" id="compose-cc" placeholder="cc@example.com" class="form-input" style="flex: 1; height: 30px; font-size: 0.8rem;" />
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <label style="width: 50px; font-size: 0.75rem; font-weight: 600; color: #64748b;">Bcc:</label>
                            <input type="email" id="compose-bcc" placeholder="bcc@example.com" class="form-input" style="flex: 1; height: 30px; font-size: 0.8rem;" />
                        </div>
                    </div>

                    <!-- Subject -->
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <label style="width: 50px; font-size: 0.75rem; font-weight: 600; color: #64748b;">Subject:</label>
                        <input
                            type="text"
                            id="compose-subject"
                            required
                            placeholder="Subject line..."
                            class="form-input"
                            style="flex: 1; height: 32px; font-size: 0.8125rem;"
                        />
                    </div>

                    <!-- Formatting Toolbar -->
                    <div style="padding: 0.35rem 0.65rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap;" class="dark:bg-zinc-800 dark:border-zinc-700">
                        <select onchange="applyFontFamily(this.value)" style="height: 24px; font-size: 0.725rem; border: 1px solid #cbd5e1; border-radius: 4px; padding: 0 0.35rem;">
                            <option value="Inter, sans-serif">Inter (Modern Sans)</option>
                            <option value="Arial, sans-serif">Arial</option>
                            <option value="Georgia, serif">Georgia</option>
                            <option value="Courier New, monospace">Monospace</option>
                        </select>
                        <div style="width: 1px; height: 16px; background: #cbd5e1; margin: 0 0.2rem;"></div>
                        <button type="button" onclick="formatEditor('bold')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px; font-weight: 700;" title="Bold">B</button>
                        <button type="button" onclick="formatEditor('italic')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px; font-style: italic;" title="Italic">I</button>
                        <button type="button" onclick="formatEditor('underline')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px; text-decoration: underline;" title="Underline">U</button>
                        <div style="width: 1px; height: 16px; background: #cbd5e1; margin: 0 0.2rem;"></div>
                        <button type="button" onclick="formatEditor('insertUnorderedList')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px;" title="Bullet List">
                            <i data-lucide="list" style="width: 13px; height: 13px;"></i>
                        </button>
                        <button type="button" onclick="formatEditor('insertOrderedList')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px;" title="Numbered List">
                            <i data-lucide="list-ordered" style="width: 13px; height: 13px;"></i>
                        </button>
                        <button type="button" onclick="formatEditor('formatBlock', 'h3')" class="btn btn-ghost btn-sm" style="padding: 0.15rem 0.35rem; height: 24px; font-weight: 600;" title="Heading">H</button>
                        <div style="width: 1px; height: 16px; background: #cbd5e1; margin: 0 0.2rem;"></div>
                        <label style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.725rem; color: #475569;" title="Attach file">
                            <i data-lucide="paperclip" style="width: 13px; height: 13px;"></i>
                            <span>Attach</span>
                            <input type="file" multiple onchange="handleFileAttachment(this.files)" style="display: none;" />
                        </label>
                    </div>

                    <!-- Compose Rich Text Area -->
                    <div
                        id="compose-editor"
                        contenteditable="true"
                        style="min-height: 180px; max-height: 320px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 6px; padding: 0.85rem; font-size: 0.875rem; line-height: 1.6; outline: none; background: #ffffff;"
                        class="dark:bg-zinc-800 dark:border-zinc-700 dark:text-zinc-100"
                    >
                        <p><br/></p>
                        <div class="crm-user-signature" style="margin-top: 1.5rem; border-top: 1px solid #e2e8f0; padding-top: 0.75rem; color: #475569; font-size: 0.8rem;">
                            <strong>Alex Morgan</strong> | Senior Account Executive<br/>
                            DealSync CRM — Enterprise Systems<br/>
                            <a href="https://dealsync.app" style="color: #4f46e5;">dealsync.app</a>
                        </div>
                    </div>

                    <!-- Attached files preview -->
                    <div id="compose-attachments-tray" style="display: none; flex-wrap: wrap; gap: 0.4rem; padding: 0.5rem 0;"></div>
                </div>

                <!-- Footer & Action Controls -->
                <div style="padding: 0.75rem 1.25rem; border-top: 1px solid var(--border-card, #e2e8f0); background: #fafafa; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                    <button type="button" onclick="closeComposeModal()" class="btn btn-secondary btn-sm">
                        Cancel
                    </button>

                    <div style="display: flex; align-items: center; position: relative;">
                        <!-- Send Now -->
                        <button
                            type="submit"
                            id="compose-send-btn"
                            class="btn btn-primary btn-sm"
                            style="display: inline-flex; align-items: center; gap: 0.35rem; border-top-right-radius: 0; border-bottom-right-radius: 0; border-right: 1px solid rgba(255, 255, 255, 0.3); background: #4f46e5;"
                        >
                            <i data-lucide="send" style="width: 13px; height: 13px;"></i>
                            <span>Send Now</span>
                        </button>

                        <!-- Schedule Send Dropdown Toggle -->
                        <button
                            type="button"
                            onclick="toggleScheduleSendMenu()"
                            class="btn btn-primary btn-sm"
                            style="padding: 0 0.45rem; height: 32px; border-top-left-radius: 0; border-bottom-left-radius: 0; display: inline-flex; align-items: center; background: #4338ca;"
                            title="Schedule send for later (Outbox)"
                        >
                            <i data-lucide="chevron-down" style="width: 13px; height: 13px;"></i>
                        </button>

                        <!-- Schedule Send Menu -->
                        <div
                            id="schedule-send-menu"
                            style="display: none; position: absolute; bottom: 100%; right: 0; margin-bottom: 6px; width: 230px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2); z-index: 10050; padding: 0.4rem; flex-direction: column; gap: 0.15rem;"
                            class="dark:bg-zinc-800 dark:border-zinc-700"
                        >
                            <div style="font-size: 0.675rem; font-weight: 700; text-transform: uppercase; color: #64748b; padding: 0.25rem 0.5rem; letter-spacing: 0.04em;">
                                Schedule Delivery (Outbox)
                            </div>
                            <button type="button" onclick="schedulePreset('Tomorrow at 9:00 AM')" class="btn btn-ghost btn-sm" style="justify-content: flex-start; font-size: 0.75rem; height: 28px; color: #1e293b;">
                                🌅 Tomorrow at 9:00 AM
                            </button>
                            <button type="button" onclick="schedulePreset('Tomorrow at 2:00 PM')" class="btn btn-ghost btn-sm" style="justify-content: flex-start; font-size: 0.75rem; height: 28px; color: #1e293b;">
                                ☀️ Tomorrow at 2:00 PM
                            </button>
                            <button type="button" onclick="schedulePreset('Next Monday at 9:00 AM')" class="btn btn-ghost btn-sm" style="justify-content: flex-start; font-size: 0.75rem; height: 28px; color: #1e293b;">
                                📅 Next Monday at 9:00 AM
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 4. CONVERSION MODAL (CONVERT UNTAGGED SENDER TO LEAD / CONTACT) -->
    <div
        id="conversion-modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 10001; align-items: center; justify-content: center; padding: 1.25rem;"
    >
        <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;" class="dark:bg-zinc-900">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-card); background: #fafafa; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="user-check" id="conversion-modal-icon" style="width: 18px; height: 18px; color: #059669;"></i>
                    <h4 id="conversion-modal-title" style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Convert Email to Lead</h4>
                </div>
                <button type="button" onclick="closeConversionModal()" class="btn btn-ghost btn-sm">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                </button>
            </div>
            <div style="padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.85rem;">
                <div>
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Full Name:</label>
                    <input type="text" id="convert-name" class="form-input" style="width: 100%; height: 32px;" />
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Email Address:</label>
                    <input type="email" id="convert-email" class="form-input" style="width: 100%; height: 32px;" readonly />
                </div>
                <div>
                    <label class="form-label" style="font-size: 0.75rem; font-weight: 600;">Company / Account:</label>
                    <input type="text" id="convert-company" class="form-input" style="width: 100%; height: 32px;" placeholder="Company name..." />
                </div>
            </div>
            <div style="padding: 0.75rem 1.25rem; background: #fafafa; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 0.5rem;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <button type="button" onclick="closeConversionModal()" class="btn btn-secondary btn-sm">Cancel</button>
                <button type="button" onclick="submitConversion()" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669;">Create Record</button>
            </div>
        </div>
    </div>

    <!-- 5. GEO-TRACKING & SENDER INSIGHT MODAL -->
    <div
        id="insight-modal-overlay"
        style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(3px); z-index: 10002; align-items: center; justify-content: center; padding: 1.25rem;"
    >
        <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 540px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); overflow: hidden;" class="dark:bg-zinc-900">
            <div style="padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-card); background: #fafafa; display: flex; justify-content: space-between; align-items: center;" class="dark:bg-zinc-800 dark:border-zinc-700">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="activity" style="width: 18px; height: 18px; color: #2563eb;"></i>
                    <h4 style="margin: 0; font-size: 1rem; font-weight: 700; color: #0f172a;" id="insight-modal-heading">Email Delivery &amp; Geo-Tracking Intelligence</h4>
                </div>
                <button type="button" onclick="document.getElementById('insight-modal-overlay').style.display='none'" class="btn btn-ghost btn-sm">
                    <i data-lucide="x" style="width: 16px; height: 16px;"></i>
                </button>
            </div>
            <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;" id="insight-modal-body">
                <!-- Dynamically populated -->
            </div>
        </div>
    </div>

</div>

<!-- INLINE SCRIPT FOR REACTIVE EMAIL WORKSTATION (PARITY WITH EmailsView.tsx) -->
<script>
    // Data Store from Controller
    const allEmails = @json($emails);
    let selectedEmailId = 'em-001';
    let currentFolder = 'inbox';
    let currentTag = 'all';
    let unreadOnlyFilter = false;
    let searchQuery = '';
    let readingPaneOn = true;
    let foldersRailCollapsed = false;

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof lucide !== 'undefined') lucide.createIcons();
        renderEmailDetail(selectedEmailId);
    });

    // Switch Mailbox Folder
    function switchMailFolder(folder) {
        currentFolder = folder;

        // Update Folder button active classes
        document.querySelectorAll('.email-folder-btn').forEach(btn => {
            btn.style.background = 'transparent';
            btn.style.fontWeight = '500';
            btn.style.color = 'var(--zinc-700, #334155)';
        });
        const activeBtn = document.getElementById('folder-btn-' + folder);
        if (activeBtn) {
            activeBtn.style.background = folder === 'campaigns' ? '#fdf2f8' : '#eff6ff';
            activeBtn.style.fontWeight = '700';
            activeBtn.style.color = folder === 'campaigns' ? '#db2777' : '#1d4ed8';
        }

        // Toggle Between Standard Email View and Bulk Campaigns View
        if (folder === 'campaigns') {
            document.getElementById('standard-email-view').style.display = 'none';
            document.getElementById('bulk-campaigns-view').style.display = 'block';
        } else {
            document.getElementById('standard-email-view').style.display = 'flex';
            document.getElementById('bulk-campaigns-view').style.display = 'none';
            applyEmailFilters();
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Filter by Entity Tag
    function filterByTag(tag) {
        currentTag = tag;
        document.querySelectorAll('.tag-filter-btn').forEach(btn => {
            btn.style.background = 'transparent';
            btn.style.fontWeight = '500';
            btn.style.color = 'var(--zinc-600, #475569)';
        });
        const activeTag = document.getElementById('tag-btn-' + tag);
        if (activeTag) {
            activeTag.style.background = 'var(--zinc-100, #f1f5f9)';
            activeTag.style.fontWeight = '600';
            activeTag.style.color = 'var(--zinc-950, #0f172a)';
        }
        applyEmailFilters();
    }

    // Toggle Unread Only
    function toggleUnreadOnly(val) {
        unreadOnlyFilter = val;
        applyEmailFilters();
    }

    // Filter by search string
    function filterEmailsBySearch(val) {
        searchQuery = val.trim().toLowerCase();
        applyEmailFilters();
    }

    // Apply Filter Logic
    function applyEmailFilters() {
        let firstVisibleId = null;

        allEmails.forEach(em => {
            const card = document.getElementById('email-card-' + em.id);
            if (!card) return;

            // Folder match
            let folderMatch = (em.folder === currentFolder);
            if (currentFolder === 'trash') {
                folderMatch = (em.folder === 'trash' || em.is_deleted === true);
            }

            // Tag match
            let tagMatch = (currentTag === 'all' || em.entity_tag === currentTag);

            // Unread match
            let unreadMatch = !unreadOnlyFilter || (!em.is_read);

            // Search match
            let searchMatch = true;
            if (searchQuery) {
                const sSubject = (em.subject || '').toLowerCase();
                const sSender = (em.sender_name || '').toLowerCase();
                const sEmail = (em.sender_email || '').toLowerCase();
                const sSnippet = (em.snippet || '').toLowerCase();
                searchMatch = sSubject.includes(searchQuery) || sSender.includes(searchQuery) || sEmail.includes(searchQuery) || sSnippet.includes(searchQuery);
            }

            if (folderMatch && tagMatch && unreadMatch && searchMatch) {
                card.style.display = 'block';
                if (!firstVisibleId) firstVisibleId = em.id;
            } else {
                card.style.display = 'none';
            }
        });

        // If currently selected email is now hidden, select first visible
        const currentSelectedCard = document.getElementById('email-card-' + selectedEmailId);
        if (!currentSelectedCard || currentSelectedCard.style.display === 'none') {
            if (firstVisibleId) {
                selectEmail(firstVisibleId);
            }
        }
    }

    // Select Email Item to render in Reading Pane
    function selectEmail(id) {
        selectedEmailId = id;

        // Mark as read in local data
        const em = allEmails.find(e => e.id === id);
        if (em) {
            em.is_read = true;
        }

        // Update card styles
        document.querySelectorAll('.email-feed-card').forEach(card => {
            card.style.background = '#ffffff';
            card.style.borderLeft = '3px solid transparent';
        });

        const activeCard = document.getElementById('email-card-' + id);
        if (activeCard) {
            activeCard.style.background = '#f8fafc';
            activeCard.style.borderLeft = '3px solid #4f46e5';
        }

        renderEmailDetail(id);

        // If in full list view (readingPaneOn == false), show reading pane and back button
        if (!readingPaneOn) {
            document.getElementById('email-list-column').style.display = 'none';
            document.getElementById('email-reading-pane').style.display = 'flex';
            document.getElementById('reading-pane-back-btn').style.display = 'inline-flex';
        }
    }

    // Render Detail View
    function renderEmailDetail(id) {
        const em = allEmails.find(e => e.id === id);
        if (!em) return;

        // Sender Info
        document.getElementById('detail-sender-name').textContent = em.sender_name;
        document.getElementById('detail-received-at').textContent = em.received_at;
        document.getElementById('detail-sender-email').textContent = 'From: <' + em.sender_email + '>';
        document.getElementById('detail-recipient-email').textContent = 'To: ' + (em.recipient_name || '') + ' <' + em.recipient_email + '>';
        document.getElementById('detail-sender-avatar').textContent = em.sender_name ? em.sender_name.charAt(0).toUpperCase() : 'M';
        document.getElementById('quick-reply-recipient').textContent = em.sender_name;

        // CRM Entity Context Banner
        const entityBanner = document.getElementById('crm-entity-banner');
        const entityStatus = document.getElementById('crm-entity-status');
        const entityActions = document.getElementById('crm-entity-actions');

        if (em.entity_tag === 'Lead') {
            entityStatus.innerHTML = `
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #16a34a;"></span>
                <span style="color: #166534;">Linked to CRM Lead: <strong>${em.lead_name || 'Lead'}</strong> ${em.lead_company ? `(${em.lead_company})` : ''}</span>
            `;
            entityActions.innerHTML = `
                <a href="{{ route('leads.index') }}" class="btn btn-sm btn-secondary" style="font-size: 0.725rem; height: 24px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="external-link" style="width: 11px; height: 11px;"></i>
                    <span>View Lead Record</span>
                </a>
            `;
        } else if (em.entity_tag === 'Contact') {
            entityStatus.innerHTML = `
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #2563eb;"></span>
                <span style="color: #1e40af;">Linked to CRM Contact: <strong>${em.contact_name || 'Contact'}</strong> ${em.account_name ? `(${em.account_name})` : ''}</span>
            `;
            entityActions.innerHTML = `
                <a href="{{ route('contacts.index') }}" class="btn btn-sm btn-secondary" style="font-size: 0.725rem; height: 24px; display: inline-flex; align-items: center; gap: 0.3rem;">
                    <i data-lucide="external-link" style="width: 11px; height: 11px;"></i>
                    <span>View Contact Record</span>
                </a>
            `;
        } else {
            // Untagged -> Convert to Lead / Contact buttons
            entityStatus.innerHTML = `
                <span style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></span>
                <span style="color: #92400e;">Sender (<strong>${em.sender_email}</strong>) is not in CRM.</span>
            `;
            entityActions.innerHTML = `
                <button type="button" onclick="openConversionModal('leads', '${em.id}')" class="btn btn-primary btn-sm" style="font-size: 0.725rem; height: 24px; padding: 0 0.6rem; background: #059669; border-color: #059669; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <i data-lucide="plus" style="width: 11px; height: 11px;"></i>
                    <span>Convert to Lead</span>
                </button>
                <button type="button" onclick="openConversionModal('contacts', '${em.id}')" class="btn btn-secondary btn-sm" style="font-size: 0.725rem; height: 24px; padding: 0 0.6rem; display: inline-flex; align-items: center; gap: 0.25rem;">
                    <i data-lucide="plus" style="width: 11px; height: 11px;"></i>
                    <span>Add as Contact</span>
                </button>
            `;
        }

        // Attachments Bar
        const attachmentsBar = document.getElementById('detail-attachments-bar');
        const attachmentsList = document.getElementById('detail-attachments-list');
        const attachmentsTitle = document.getElementById('detail-attachments-title');

        if (em.attachments && em.attachments.length > 0) {
            attachmentsBar.style.display = 'block';
            attachmentsTitle.textContent = `Attachments (${em.attachments.length})`;
            attachmentsList.innerHTML = em.attachments.map(att => `
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.65rem; background: #ffffff; border: 1px solid var(--border-card, #e2e8f0); border-radius: 6px; font-size: 0.75rem;">
                    <i data-lucide="file-text" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                    <span style="font-weight: 600; color: #1e293b;">${att.file_name}</span>
                    <span style="color: #94a3b8; font-size: 0.7rem;">(${formatBytes(att.file_size)})</span>
                    <a href="#" onclick="alert('Downloading attachment: ${att.file_name}'); return false;" style="color: #4f46e5; margin-left: 0.25rem;">
                        <i data-lucide="download" style="width: 12px; height: 12px;"></i>
                    </a>
                </div>
            `).join('');
        } else {
            attachmentsBar.style.display = 'none';
        }

        // AI Summary Card
        const aiCard = document.getElementById('ai-summary-card');
        if (em.ai_summary) {
            aiCard.style.display = 'block';
            document.getElementById('ai-summary-text').textContent = em.ai_summary.summary;
            document.getElementById('ai-action-text').textContent = em.ai_summary.recommendedAction || 'Follow up with technical materials.';
            const keyPointsList = document.getElementById('ai-key-points');
            if (em.ai_summary.keyPoints && em.ai_summary.keyPoints.length > 0) {
                keyPointsList.innerHTML = em.ai_summary.keyPoints.map(pt => `<li>${pt}</li>`).join('');
            }
        } else {
            aiCard.style.display = 'none';
        }

        // Body Content
        document.getElementById('detail-body-html').innerHTML = em.body_html || `<div style="white-space: pre-wrap;">${em.body_text || em.snippet}</div>`;

        // Thread Section
        const threadContainer = document.getElementById('detail-thread-container');
        if (em.thread && em.thread.length > 1) {
            threadContainer.style.display = 'block';
        } else {
            threadContainer.style.display = 'none';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Toggle Thread Accordion Message
    function toggleThreadMessage(elementId) {
        const item = document.getElementById(elementId);
        if (item) {
            const isHidden = (item.style.display === 'none');
            item.style.display = isHidden ? 'block' : 'none';
        }
    }

    // Toggle Star
    function toggleStar(event, id) {
        event.stopPropagation();
        const em = allEmails.find(e => e.id === id);
        if (em) {
            em.is_starred = !em.is_starred;
            const starIcon = event.currentTarget.querySelector('i');
            if (em.is_starred) {
                event.currentTarget.style.color = '#eab308';
                if (starIcon) starIcon.style.fill = '#eab308';
                showEmailToast('Message starred', 'success');
            } else {
                event.currentTarget.style.color = '#cbd5e1';
                if (starIcon) starIcon.style.fill = 'none';
                showEmailToast('Message unstarred', 'info');
            }
        }
    }

    // Toggle Reading Pane
    function toggleReadingPane() {
        readingPaneOn = !readingPaneOn;
        const listCol = document.getElementById('email-list-column');
        const readPane = document.getElementById('email-reading-pane');
        const btnText = document.getElementById('reading-pane-text');
        const backBtn = document.getElementById('reading-pane-back-btn');

        if (readingPaneOn) {
            listCol.style.width = '380px';
            listCol.style.display = 'flex';
            readPane.style.display = 'flex';
            btnText.textContent = 'Reading Pane: On';
            backBtn.style.display = 'none';
        } else {
            // Full width mode
            listCol.style.width = '100%';
            listCol.style.display = 'flex';
            readPane.style.display = 'none';
            btnText.textContent = 'Reading Pane: Off';
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Back to Full List (When in Reading Pane Off mode)
    function backToFullList() {
        document.getElementById('email-list-column').style.display = 'flex';
        document.getElementById('email-reading-pane').style.display = 'none';
    }

    // Toggle Folders Rail Collapse
    function toggleFoldersRailCollapse() {
        foldersRailCollapsed = !foldersRailCollapsed;
        const rail = document.getElementById('email-folders-rail');
        const collapseIcon = document.getElementById('rail-collapse-icon');
        const railLabel = document.getElementById('folders-rail-label');
        const labels = document.querySelectorAll('.folder-name-label');
        const badges = document.querySelectorAll('.folder-badge-counter');
        const tagsSection = document.getElementById('entity-tags-section');
        const unreadSection = document.getElementById('unread-only-section');

        if (foldersRailCollapsed) {
            rail.style.width = '56px';
            rail.style.padding = '0.75rem 0.35rem';
            rail.style.alignItems = 'center';
            if (railLabel) railLabel.style.display = 'none';
            labels.forEach(l => l.style.display = 'none');
            badges.forEach(b => b.style.display = 'none');
            if (tagsSection) tagsSection.style.display = 'none';
            if (unreadSection) unreadSection.style.display = 'none';
            collapseIcon.setAttribute('data-lucide', 'panel-left-open');
        } else {
            rail.style.width = '230px';
            rail.style.padding = '1rem 0.75rem';
            rail.style.alignItems = 'stretch';
            if (railLabel) railLabel.style.display = 'block';
            labels.forEach(l => l.style.display = 'inline');
            badges.forEach(b => b.style.display = 'inline-block');
            if (tagsSection) tagsSection.style.display = 'block';
            if (unreadSection) unreadSection.style.display = 'block';
            collapseIcon.setAttribute('data-lucide', 'panel-left-close');
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    // Compose Modal Handlers
    function openComposeModal(to = '', subject = '', quoteBody = '') {
        document.getElementById('compose-to').value = to;
        document.getElementById('compose-subject').value = subject;
        document.getElementById('compose-modal-title').textContent = subject ? 'Reply: ' + subject : 'New Email Message';
        document.getElementById('compose-modal-overlay').style.display = 'flex';
        if (quoteBody) {
            const editor = document.getElementById('compose-editor');
            editor.innerHTML = `<p><br/></p><blockquote style="border-left: 2px solid #cbd5e1; padding-left: 0.75rem; color: #64748b; margin-top: 1rem;">${quoteBody}</blockquote>`;
        }
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeComposeModal() {
        document.getElementById('compose-modal-overlay').style.display = 'none';
        document.getElementById('schedule-send-menu').style.display = 'none';
    }

    function toggleCcBcc() {
        const row = document.getElementById('compose-cc-bcc-row');
        row.style.display = (row.style.display === 'none') ? 'flex' : 'none';
    }

    function formatEditor(command, value = null) {
        document.execCommand(command, false, value);
    }

    function applyFontFamily(font) {
        document.getElementById('compose-editor').style.fontFamily = font;
    }

    function toggleScheduleSendMenu() {
        const menu = document.getElementById('schedule-send-menu');
        menu.style.display = (menu.style.display === 'none') ? 'flex' : 'none';
    }

    function schedulePreset(presetTime) {
        closeComposeModal();
        showEmailToast(`Email scheduled for delivery: ${presetTime} (Moved to Outbox)`, 'success');
    }

    function handleSendCompose(event) {
        event.preventDefault();
        const to = document.getElementById('compose-to').value;
        const subject = document.getElementById('compose-subject').value;
        closeComposeModal();
        showEmailToast(`Email successfully dispatched to ${to}`, 'success');
    }

    // Quick Reply Action
    function sendQuickReply() {
        const text = document.getElementById('quick-reply-textarea').value.trim();
        if (!text) {
            alert('Please type a response first.');
            return;
        }
        document.getElementById('quick-reply-textarea').value = '';
        showEmailToast('Quick reply sent successfully', 'success');
    }

    function formatQuickReply(type) {
        const ta = document.getElementById('quick-reply-textarea');
        ta.focus();
        document.execCommand(type, false, null);
    }

    // Reply / Reply All / Forward
    function replyToCurrentEmail(mode) {
        const em = allEmails.find(e => e.id === selectedEmailId);
        if (!em) return;

        let to = em.sender_email;
        let subject = em.subject.startsWith('Re:') ? em.subject : 'Re: ' + em.subject;
        if (mode === 'forward') {
            to = '';
            subject = em.subject.startsWith('Fwd:') ? em.subject : 'Fwd: ' + em.subject;
        }
        openComposeModal(to, subject, em.body_html || em.snippet);
    }

    // Mailbox Sync
    function triggerMailboxSync() {
        const spinner = document.getElementById('sync-spinner-icon');
        const syncText = document.getElementById('sync-btn-text');
        if (spinner) spinner.classList.add('animate-spin');
        syncText.textContent = 'Syncing...';

        setTimeout(() => {
            if (spinner) spinner.classList.remove('animate-spin');
            syncText.textContent = 'Sync Mailbox';
            document.getElementById('sync-time-text').textContent = 'Just now';
            showEmailToast('Mailbox synchronized with IMAP server. 0 new changes.', 'success');
        }, 800);
    }

    // Simulate Inbound Email
    function simulateInboundEmail() {
        showEmailToast('Simulated inbound message from Acme Corporation received.', 'success');
    }

    // Move to Trash
    function moveCurrentEmailToTrash() {
        const em = allEmails.find(e => e.id === selectedEmailId);
        if (!em) return;
        em.folder = 'trash';
        em.is_deleted = true;
        const card = document.getElementById('email-card-' + em.id);
        if (card) card.style.display = 'none';
        showEmailToast(`Moved "${em.subject}" to Trash`, 'info');
        applyEmailFilters();
    }

    // Conversion Modal
    function openConversionModal(type, emailId) {
        const em = allEmails.find(e => e.id === emailId);
        if (!em) return;
        document.getElementById('convert-name').value = em.sender_name;
        document.getElementById('convert-email').value = em.sender_email;
        document.getElementById('convert-company').value = em.lead_company || (em.sender_email.includes('@') ? em.sender_email.split('@')[1].split('.')[0].toUpperCase() : '');
        document.getElementById('conversion-modal-title').textContent = type === 'leads' ? 'Convert Email to CRM Lead' : 'Add Email Sender as CRM Contact';
        document.getElementById('conversion-modal-overlay').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeConversionModal() {
        document.getElementById('conversion-modal-overlay').style.display = 'none';
    }

    function submitConversion() {
        const name = document.getElementById('convert-name').value;
        closeConversionModal();
        showEmailToast(`Record created and tagged for "${name}"`, 'success');
    }

    // Geo-Tracking / Insight Modal
    function openGeoAuditModal(event, emailId) {
        event.stopPropagation();
        const em = allEmails.find(e => e.id === emailId);
        if (!em) return;

        const body = document.getElementById('insight-modal-body');
        body.innerHTML = `
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.85rem; font-size: 0.825rem; color: #1e40af;">
                <strong>Outbound Tracking Audit:</strong> Email dispatched to <em>${em.recipient_email}</em>.
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem;">
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Total Opens</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #2563eb; margin-top: 0.2rem;">${em.open_count || 1} times</div>
                </div>
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Link Clicks</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #7c3aed; margin-top: 0.2rem;">${em.click_count || 0} clicks</div>
                </div>
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Recipient City</div>
                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.2rem;">${em.last_opened_city || 'San Francisco, CA'}</div>
                </div>
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">IP Address</div>
                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.2rem;">${em.last_opened_ip || '64.233.160.1'}</div>
                </div>
            </div>
        `;
        document.getElementById('insight-modal-overlay').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openSenderInsightModal(event, emailId) {
        event.stopPropagation();
        const em = allEmails.find(e => e.id === emailId);
        if (!em) return;

        const body = document.getElementById('insight-modal-body');
        body.innerHTML = `
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 0.85rem; font-size: 0.825rem; color: #166534;">
                <strong>Inbound Sender Origin Intelligence:</strong> Verified origin headers for <em>${em.sender_email}</em>.
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; font-size: 0.8rem;">
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Sender Location</div>
                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.2rem;">${em.sender_city ? `${em.sender_city}, ` : ''}${em.sender_country || 'United States'}</div>
                </div>
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Originating IP</div>
                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.2rem;">${em.sender_ip || '198.51.100.24'}</div>
                </div>
                <div style="padding: 0.65rem; background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0; grid-column: span 2;">
                    <div style="color: #64748b; font-size: 0.7rem; text-transform: uppercase;">Mail Client / User Agent</div>
                    <div style="font-weight: 600; color: #0f172a; margin-top: 0.2rem;">${em.sender_client || 'Apple Mail 16.4 / macOS Ventura'}</div>
                </div>
            </div>
        `;
        document.getElementById('insight-modal-overlay').style.display = 'flex';
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function openInsightFromDetail() {
        const em = allEmails.find(e => e.id === selectedEmailId);
        if (!em) return;
        if (em.direction === 'inbound') {
            openSenderInsightModal({ stopPropagation: () => {} }, em.id);
        } else {
            openGeoAuditModal({ stopPropagation: () => {} }, em.id);
        }
    }

    function openScheduleMeetingFromEmail() {
        const em = allEmails.find(e => e.id === selectedEmailId);
        showEmailToast(`Meeting invite configured for ${em ? em.sender_name : 'Contact'}. Synced to Calendar.`, 'success');
    }

    function generateAiSummary() {
        document.getElementById('ai-summary-card').style.display = 'block';
        showEmailToast('AI summary regenerated from conversation history.', 'success');
    }

    function openNewCampaignWizard() {
        showEmailToast('Bulk Email Wizard: Step 1 Audience Selection initiated.', 'info');
    }

    function openCampaignInsight(campId, campName) {
        showEmailToast(`Viewing telemetry report for: ${campName}`, 'info');
    }

    // Attachment helper
    function handleFileAttachment(files) {
        if (!files || files.length === 0) return;
        const tray = document.getElementById('compose-attachments-tray');
        tray.style.display = 'flex';
        for (let i = 0; i < files.length; i++) {
            const f = files[i];
            const div = document.createElement('div');
            div.style.cssText = 'display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.5rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; font-size: 0.75rem; color: #1d4ed8;';
            div.innerHTML = `<span>📎 ${f.name} (${formatBytes(f.size)})</span>`;
            tray.appendChild(div);
        }
    }

    // Toast Banner helper
    function showEmailToast(msg, type = 'success') {
        const banner = document.getElementById('email-toast-banner');
        const text = document.getElementById('email-toast-text');
        text.textContent = msg;

        if (type === 'success') {
            banner.style.background = '#ecfdf5';
            banner.style.color = '#065f46';
            banner.style.borderColor = '#a7f3d0';
        } else if (type === 'info') {
            banner.style.background = '#eff6ff';
            banner.style.color = '#1e40af';
            banner.style.borderColor = '#bfdbfe';
        } else {
            banner.style.background = '#fef2f2';
            banner.style.color = '#991b1b';
            banner.style.borderColor = '#fecaca';
        }

        banner.style.display = 'flex';
        setTimeout(() => {
            banner.style.display = 'none';
        }, 4000);
    }

    function hideEmailToast() {
        document.getElementById('email-toast-banner').style.display = 'none';
    }

    function formatBytes(bytes, decimals = 1) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
</script>

<style>
    @keyframes modalScaleIn {
        from { opacity: 0; transform: scale(0.96); }
        to { opacity: 1; transform: scale(1); }
    }
    .email-folder-btn:hover {
        background: #f1f5f9 !important;
    }
    .email-feed-card:hover {
        background: #f8fafc !important;
    }
    .tag-filter-btn:hover {
        background: #f1f5f9 !important;
    }
</style>
@endsection
