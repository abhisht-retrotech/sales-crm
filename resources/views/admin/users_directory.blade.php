@extends('layouts.app')

@section('title', 'Users Directory — DealSync Admin')

@section('content')

<!-- USER DETAIL VIEW (Rendered when selectedUserForDetail is set, exactly matching React UserDetailPage.tsx) -->
<div id="userDetailContainer" class="animate-fade-in" style="display: none; padding-bottom: 3.5rem;">
    <!-- 1. TOP NAV & BREADCRUMB -->
    <div style="display: flex; justify-content: space-between; align-items: center; marginBottom: 1rem; flexWrap: wrap; gap: 0.75rem;">
        <button
            type="button"
            onclick="closeUserDetailView()"
            style="display: inline-flex; align-items: center; gap: 0.45rem; background: var(--zinc-100); border: 1px solid var(--zinc-200); color: var(--zinc-700); padding: 0.4rem 0.85rem; border-radius: var(--radius-md); font-size: 0.8125rem; font-weight: 600; cursor: pointer; transition: all 0.15s ease;"
            onmouseenter="this.style.background='var(--zinc-200)'"
            onmouseleave="this.style.background='var(--zinc-100)'"
        >
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to User Directory</span>
        </button>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
            <button
                type="button"
                id="detailEditBtn"
                onclick="detailTriggerEdit()"
                class="btn btn-secondary btn-sm"
                style="padding: 0.45rem 0.9rem; display: inline-flex; align-items: center; gap: 0.35rem; font-weight: 600;"
            >
                <i data-lucide="edit-2" style="width: 13px; height: 13px;"></i>
                <span>Edit Profile</span>
            </button>

            <button
                type="button"
                id="detailLoginBtn"
                onclick="detailTriggerLogin()"
                class="btn btn-sm"
                style="padding: 0.45rem 0.95rem; font-size: 0.78rem; display: inline-flex; align-items: center; gap: 0.4rem; background: #e0f2fe; border: 1px solid #7dd3fc; color: #0369a1; font-weight: 700; cursor: pointer;"
            >
                <i data-lucide="log-in" style="width: 13px; height: 13px;"></i>
                <span id="detailLoginBtnText">Login as User</span>
            </button>
        </div>
    </div>

    <!-- 2. EXECUTIVE PROFILE HERO CARD & KPI RIBBON -->
    <div style="background: #ffffff; border: 1px solid var(--border-card); border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-card); margin-bottom: 1.5rem;">
        <!-- Top Profile Summary Row -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.25rem;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <!-- Avatar badge -->
                <div style="position: relative;">
                    <div id="detailAvatarBox" style="width: 74px; height: 74px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary) 0%, #312e81 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.5rem; box-shadow: 0 4px 14px rgba(79, 70, 229, 0.25); letter-spacing: 0.05em;">
                        AM
                    </div>
                    <div
                        id="detailStatusDot"
                        style="position: absolute; bottom: 2px; right: 2px; width: 16px; height: 16px; border-radius: 50%; background: #10b981; border: 2.5px solid #ffffff; box-shadow: 0 0 8px rgba(16, 185, 129, 0.6);"
                    ></div>
                </div>

                <div>
                    <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                        <h1 id="detailFullName" style="font-size: 1.5rem; font-weight: 800; color: var(--zinc-950); letter-spacing: -0.025em; margin: 0;">
                            Alex Morgan
                        </h1>
                        <span id="detailStatusPill" class="badge badge-green" style="font-size: 0.75rem; font-weight: 700;">
                            ● Active Employee
                        </span>
                        <span id="detailAdminPill" class="badge badge-purple" style="font-size: 0.72rem; font-weight: 700;">
                            <i data-lucide="shield-check" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i>
                            Full Admin Rights
                        </span>
                    </div>

                    <!-- Roles & Profile Badges -->
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.45rem; flex-wrap: wrap;">
                        <span class="badge badge-purple" id="detailRoleBadge" style="font-size: 0.78rem; padding: 0.2rem 0.65rem; font-weight: 600;">
                            <i data-lucide="shield" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i>
                            Executive / CEO
                        </span>
                        <span class="badge badge-blue" id="detailProfileBadge" style="font-size: 0.78rem; padding: 0.2rem 0.65rem; font-weight: 600;">
                            <i data-lucide="lock" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i>
                            System Administrator
                        </span>
                        <span id="detailReportsToBadge" style="font-size: 0.75rem; color: var(--zinc-600); background: var(--zinc-100); padding: 0.2rem 0.6rem; border-radius: var(--radius-md); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="building-2" style="width: 12px; height: 12px; color: var(--zinc-500);"></i>
                            <span>Reports to: <strong>Board of Directors</strong></span>
                        </span>
                    </div>

                    <!-- Meta dossier pills -->
                    <div style="display: flex; align-items: center; gap: 1.25rem; margin-top: 0.65rem; font-size: 0.78rem; color: var(--zinc-500); flex-wrap: wrap;">
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="mail" style="width: 13px; height: 13px; color: var(--zinc-400);"></i>
                            <a id="detailEmailLink" href="mailto:alex.morgan@acmecorp.com" style="color: var(--zinc-800); text-decoration: none; font-weight: 500;">alex.morgan@acmecorp.com</a>
                        </span>
                        <span id="detailPhoneSpan" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="phone" style="width: 13px; height: 13px; color: var(--zinc-400);"></i>
                            <a id="detailPhoneLink" href="tel:+14158901200" style="color: var(--zinc-800); text-decoration: none; font-weight: 500;">+1 (415) 890-1200</a>
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <i data-lucide="calendar" style="width: 13px; height: 13px; color: var(--zinc-400);"></i>
                            <span id="detailJoinedDate">Joined Oct 12, 2024</span>
                        </span>
                        <span id="detailUserIdBadge" style="font-family: monospace; color: var(--zinc-500); background: var(--zinc-100); padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.72rem;">
                            ID: usr-101
                        </span>
                    </div>
                </div>
            </div>

            <!-- Quick Security Badge Pill -->
            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.4rem; padding: 0.75rem 1.1rem; background: var(--zinc-50); border-radius: var(--radius-lg); border: 1px solid var(--zinc-200); min-width: 180px;">
                <div style="font-size: 0.68rem; font-weight: 700; color: var(--zinc-400); text-transform: uppercase; letter-spacing: 0.05em;">
                    Governance Status
                </div>
                <div style="font-size: 0.85rem; font-weight: 700; color: var(--zinc-900); display: flex; align-items: center; gap: 0.4rem;">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Compliant &amp; Active</span>
                </div>
                <div style="font-size: 0.72rem; color: var(--zinc-500);">
                    2FA: Standard • Auth OK
                </div>
            </div>
        </div>

        <!-- Business Impact KPI Ribbon -->
        <div style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid var(--zinc-100); display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem;">
            <div style="background: var(--zinc-50); border-radius: var(--radius-lg); padding: 0.75rem 1rem; border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.72rem; color: var(--zinc-500); font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="dollar-sign" style="width: 13px; height: 13px; color: var(--color-primary);"></i>
                    <span>Pipeline Owned</span>
                </div>
                <div id="detailKpiPipeline" style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-950); margin-top: 0.2rem;">
                    $1,450,000
                </div>
                <div id="detailKpiDeals" style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.15rem;">
                    Across 12 active deals
                </div>
            </div>

            <div style="background: var(--zinc-50); border-radius: var(--radius-lg); padding: 0.75rem 1rem; border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.72rem; color: var(--zinc-500); font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="building" style="width: 13px; height: 13px; color: #0ea5e9;"></i>
                    <span>Assigned Accounts</span>
                </div>
                <div id="detailKpiAccounts" style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-950); margin-top: 0.2rem;">
                    18 Accounts
                </div>
                <div style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.15rem;">
                    Enterprise &amp; SMB Accounts
                </div>
            </div>

            <div style="background: var(--zinc-50); border-radius: var(--radius-lg); padding: 0.75rem 1rem; border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.72rem; color: var(--zinc-500); font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="target" style="width: 13px; height: 13px; color: #6366f1;"></i>
                    <span>Assigned Leads</span>
                </div>
                <div id="detailKpiLeads" style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-950); margin-top: 0.2rem;">
                    34 Leads
                </div>
                <div style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.15rem;">
                    Inbound &amp; outbound pipeline
                </div>
            </div>

            <div style="background: var(--zinc-50); border-radius: var(--radius-lg); padding: 0.75rem 1rem; border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.72rem; color: var(--zinc-500); font-weight: 600; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="check-square" style="width: 13px; height: 13px; color: #f59e0b;"></i>
                    <span>Open Tasks</span>
                </div>
                <div id="detailKpiTasks" style="font-size: 1.15rem; font-weight: 800; color: var(--zinc-950); margin-top: 0.2rem;">
                    8 Tasks
                </div>
                <div style="font-size: 0.7rem; color: var(--zinc-500); margin-top: 0.15rem;">
                    Pending operational items
                </div>
            </div>
        </div>
    </div>

    <!-- 3. ENTERPRISE TAB NAVIGATION BAR -->
    <div style="display: flex; align-items: center; gap: 0.5rem; border-bottom: 2px solid var(--zinc-200); margin-bottom: 1.5rem; overflow-x: auto;">
        <button
            type="button"
            id="detailSubtabBtnOverview"
            onclick="switchDetailSubtab('overview')"
            style="padding: 0.65rem 1.1rem; background: none; border: none; border-bottom: 2.5px solid var(--color-primary); color: var(--color-primary); font-weight: 700; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: -2px; white-space: nowrap; transition: all 0.15s ease;"
        >
            <i data-lucide="user" style="width: 15px; height: 15px;"></i>
            <span>Overview &amp; Details</span>
        </button>

        <button
            type="button"
            id="detailSubtabBtnPermissions"
            onclick="switchDetailSubtab('permissions')"
            style="padding: 0.65rem 1.1rem; background: none; border: none; border-bottom: 2.5px solid transparent; color: var(--zinc-600); font-weight: 500; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: -2px; white-space: nowrap; transition: all 0.15s ease;"
        >
            <i data-lucide="layers" style="width: 15px; height: 15px;"></i>
            <span>Permissions Matrix</span>
        </button>

        <button
            type="button"
            id="detailSubtabBtnSecurity"
            onclick="switchDetailSubtab('security')"
            style="padding: 0.65rem 1.1rem; background: none; border: none; border-bottom: 2.5px solid transparent; color: var(--zinc-600); font-weight: 500; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: -2px; white-space: nowrap; transition: all 0.15s ease;"
        >
            <i data-lucide="shield-check" style="width: 15px; height: 15px;"></i>
            <span>Security &amp; Sessions</span>
        </button>

        <button
            type="button"
            id="detailSubtabBtnAudit"
            onclick="switchDetailSubtab('audit')"
            style="padding: 0.65rem 1.1rem; background: none; border: none; border-bottom: 2.5px solid transparent; color: var(--zinc-600); font-weight: 500; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 0.45rem; margin-bottom: -2px; white-space: nowrap; transition: all 0.15s ease;"
        >
            <i data-lucide="file-text" style="width: 15px; height: 15px;"></i>
            <span>Audit &amp; Activity Log</span>
        </button>
    </div>

    <!-- TAB 1: OVERVIEW & DETAILS -->
    <div id="detailTabOverview" class="card" style="padding: 1.5rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Employee Dossier Information</h3>
            <span class="badge badge-purple" style="font-size: 0.72rem;">Standard Layout</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Full Legal Name</div>
                <div id="ovFullName" style="font-size: 0.875rem; font-weight: 600; color: var(--zinc-900);">Alex Morgan</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Corporate Email</div>
                <div id="ovEmail" style="font-size: 0.875rem; font-weight: 600; color: var(--color-primary);">alex.morgan@acmecorp.com</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Direct Phone</div>
                <div id="ovPhone" style="font-size: 0.875rem; color: var(--zinc-800);">+1 (415) 890-1200</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Role in Organization Hierarchy</div>
                <div id="ovRole" style="font-size: 0.875rem; font-weight: 600; color: var(--zinc-900);">Executive / CEO</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Assigned Security Profile</div>
                <div id="ovProfile" style="font-size: 0.875rem; font-weight: 600; color: var(--zinc-900);">System Administrator</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Account Access Status</div>
                <div><span id="ovStatus" class="badge badge-green">Active</span></div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Ambient Themes &amp; Glow Access</div>
                <div id="ovThemes" style="font-size: 0.875rem; color: var(--zinc-800);">Inherit from Profile (✨ Permitted)</div>
            </div>

            <div>
                <div style="font-size: 0.725rem; font-weight: 600; color: var(--zinc-500); text-transform: uppercase; margin-bottom: 0.25rem;">Tenant Identifier</div>
                <div style="font-size: 0.875rem; font-family: monospace; color: var(--zinc-600);">tenant-acme-corp</div>
            </div>
        </div>
    </div>

    <!-- TAB 2: PERMISSIONS MATRIX -->
    <div id="detailTabPermissions" class="card" style="padding: 1.5rem; display: none;">
        <div style="margin-bottom: 1rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Record-Level CRUD Permissions Matrix</h3>
            <p style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.2rem;">
                Derived from assigned security profile (<span id="permProfileName">System Administrator</span>).
            </p>
        </div>

        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 0.65rem 0.85rem;">Module Name</th>
                        <th style="padding: 0.65rem 0.85rem; text-align: center;">Read</th>
                        <th style="padding: 0.65rem 0.85rem; text-align: center;">Create</th>
                        <th style="padding: 0.65rem 0.85rem; text-align: center;">Edit</th>
                        <th style="padding: 0.65rem 0.85rem; text-align: center;">Delete</th>
                        <th style="padding: 0.65rem 0.85rem; text-align: center;">Transfer</th>
                        <th style="padding: 0.65rem 0.85rem;">Scope</th>
                    </tr>
                </thead>
                <tbody id="permMatrixTbody">
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: SECURITY & SESSIONS -->
    <div id="detailTabSecurity" class="card" style="padding: 1.5rem; display: none;">
        <div style="margin-bottom: 1.25rem;">
            <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Security Policies &amp; Active Sessions</h3>
            <p style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.2rem;">
                Monitor active browser sessions, IP addresses, and enforcement compliance.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem; font-weight: 600; font-size: 0.825rem; color: var(--zinc-900);">
                    <i data-lucide="key" style="width: 15px; height: 15px; color: var(--color-primary);"></i>
                    <span>Password Requirements</span>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--zinc-600); line-height: 1.5;">
                    • Minimum 8 characters<br>
                    • Must include uppercase, number, &amp; special symbol<br>
                    • Expiration: 90 days rotation
                </div>
            </div>

            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem; font-weight: 600; font-size: 0.825rem; color: var(--zinc-900);">
                    <i data-lucide="map-pin" style="width: 15px; height: 15px; color: #0ea5e9;"></i>
                    <span>IP Range Whitelist</span>
                </div>
                <div style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--zinc-600); line-height: 1.5;">
                    • Global Access Permitted<br>
                    • Strict SSL session token hashing<br>
                    • Failed attempts limit: 5 attempts
                </div>
            </div>
        </div>

        <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem;">Active Login Sessions</h4>
        <div style="overflow-x: auto;">
            <table class="data-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="padding: 0.65rem 0.85rem;">Device &amp; Browser</th>
                        <th style="padding: 0.65rem 0.85rem;">IP Address</th>
                        <th style="padding: 0.65rem 0.85rem;">Location</th>
                        <th style="padding: 0.65rem 0.85rem;">Started</th>
                        <th style="padding: 0.65rem 0.85rem;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 0.65rem 0.85rem; font-weight: 600; color: var(--zinc-900);">
                            <i data-lucide="laptop" style="width: 13px; height: 13px; display: inline-block; vertical-align: middle; margin-right: 4px;"></i>
                            Chrome 122 (Windows 11)
                        </td>
                        <td style="padding: 0.65rem 0.85rem; font-family: monospace; font-size: 0.75rem;">127.0.0.1 (Localhost)</td>
                        <td style="padding: 0.65rem 0.85rem; font-size: 0.8rem;">San Francisco, USA</td>
                        <td style="padding: 0.65rem 0.85rem; font-size: 0.75rem; color: var(--zinc-500);">Today, 10:14 AM</td>
                        <td style="padding: 0.65rem 0.85rem;"><span class="badge badge-green">Active Session</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 4: AUDIT & ACTIVITY LOG -->
    <div id="detailTabAudit" class="card" style="padding: 1.5rem; display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-900); margin: 0;">Audit Trail &amp; Activity History</h3>
                <p style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.2rem;">
                    Tamper-evident system actions executed by or performed on this user.
                </p>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-md);">
                <div style="background: #e0e7ff; color: #4338ca; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="shield-check" style="width: 14px; height: 14px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-900);">User Profile Verified</div>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">Security policies &amp; roles synchronized with organization tenant.</div>
                </div>
                <div style="font-size: 0.72rem; color: var(--zinc-400);">10 mins ago</div>
            </div>

            <div style="display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem; background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-md);">
                <div style="background: #ecfdf5; color: #047857; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <i data-lucide="log-in" style="width: 14px; height: 14px;"></i>
                </div>
                <div style="flex: 1;">
                    <div style="font-size: 0.8125rem; font-weight: 600; color: var(--zinc-900);">Authenticated Session Initiated</div>
                    <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.15rem;">Successful credential verification via browser client.</div>
                </div>
                <div style="font-size: 0.72rem; color: var(--zinc-400);">2 hours ago</div>
            </div>
        </div>
    </div>
</div>

<!-- MAIN DIRECTORY VIEW (When selectedUserForDetail is null, exactly matching React SettingsView.tsx lines 1770-2258) -->
<div id="mainDirectoryView">
    <!-- SEAT LICENSE CAPACITY & QUOTA METERING GAUGE -->
    <div
        class="card"
        style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid var(--zinc-200); padding: 1.25rem 1.5rem; margin-bottom: 1.25rem;"
    >
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--zinc-900); margin: 0; letter-spacing: -0.01em;">
                        Seat License &amp; Quota Governance
                    </h3>
                    <span
                        style="text-transform: uppercase; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.04em; padding: 0.2rem 0.6rem; border-radius: 999px; background: #e0e7ff; color: #4338ca; border: 1px solid #c7d2fe;"
                    >
                        Enterprise Tier
                    </span>
                </div>
                <div style="font-size: 0.8rem; color: var(--zinc-500); margin-top: 0.25rem;">
                    Metered capacity, cryptographic team invitations (7-day tokens), and automated user offboarding.
                </div>
            </div>

            <!-- Subscription Add Seats CTA -->
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button
                    type="button"
                    onclick="openAddSeatsModal()"
                    class="btn btn-primary btn-sm"
                    style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.78rem; padding: 0.35rem 0.85rem; font-weight: 600;"
                >
                    <i data-lucide="credit-card" style="width: 14px; height: 14px;"></i>
                    <span>+ Add Seats ($39/mo)</span>
                </button>
            </div>
        </div>

        <!-- Capacity Progress Bar -->
        <div>
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; font-weight: 600; color: var(--zinc-600); margin-bottom: 0.35rem;">
                <span id="quotaAllocatedText">6 of 10 Seats Allocated (60%)</span>
                <span id="quotaAvailableText" style="color: #16a34a;">
                    4 Seats Available
                </span>
            </div>
            <div style="height: 8px; width: 100%; background: #e2e8f0; border-radius: 999px; overflow: hidden;">
                <div
                    id="quotaProgressBar"
                    style="height: 100%; width: 60%; background: #10b981; border-radius: 999px; transition: width 0.4s ease, background 0.4s ease;"
                ></div>
            </div>
        </div>

        <!-- Metrics Breakdown -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem; margin-top: 1rem;">
            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Active Users</div>
                <div id="metricActiveUsers" style="font-size: 1.15rem; font-weight: 700; color: #047857;">4</div>
            </div>
            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Pending Invites</div>
                <div id="metricPendingInvites" style="font-size: 1.15rem; font-weight: 700; color: #d97706;">2</div>
            </div>
            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Available Seats</div>
                <div id="metricAvailableSeats" style="font-size: 1.15rem; font-weight: 700; color: #4f46e5;">4</div>
            </div>
            <div style="background: #ffffff; padding: 0.65rem 0.85rem; border-radius: var(--radius-md); border: 1px solid var(--zinc-200);">
                <div style="font-size: 0.7rem; color: var(--zinc-500); font-weight: 500;">Inactive Accounts</div>
                <div id="metricInactiveAccounts" style="font-size: 1.15rem; font-weight: 700; color: var(--zinc-400);">0</div>
            </div>
        </div>
    </div>

    <!-- Sub-Tabs & Action Buttons -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
        <div style="display: inline-flex; background: var(--zinc-100); padding: 0.25rem; border-radius: var(--radius-lg);">
            <button
                type="button"
                id="subtabBtnUsers"
                onclick="setUserDirectoryTab('users')"
                style="padding: 0.4rem 0.9rem; font-size: 0.8125rem; font-weight: 600; border: none; border-radius: var(--radius-md); cursor: pointer; background: #ffffff; color: var(--zinc-900); box-shadow: 0 1px 2px rgba(0,0,0,0.06); transition: all 0.15s ease;"
            >
                Active User Directory (<span id="userCountBadge">4</span>)
            </button>
            <button
                type="button"
                id="subtabBtnInvitations"
                onclick="setUserDirectoryTab('invitations')"
                style="padding: 0.4rem 0.9rem; font-size: 0.8125rem; font-weight: 600; border: none; border-radius: var(--radius-md); cursor: pointer; background: transparent; color: var(--zinc-600); box-shadow: none; display: inline-flex; align-items: center; gap: 0.35rem; transition: all 0.15s ease;"
            >
                <span>Pending Invitations</span>
                <span id="pendingCountBadge" style="background: #f59e0b; color: #ffffff; font-size: 0.7rem; padding: 0.1rem 0.4rem; border-radius: 999px; font-weight: 700;">2</span>
            </button>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button
                type="button"
                onclick="openInviteModal()"
                class="btn btn-secondary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;"
            >
                <i data-lucide="send" style="width: 13px; height: 13px;"></i>
                <span>Invite Team Member</span>
            </button>
            <button
                type="button"
                onclick="openCreateUserModal()"
                class="btn btn-primary btn-sm"
                style="display: inline-flex; align-items: center; gap: 0.4rem; font-weight: 600;"
            >
                <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- DIRECTORY VIEW: ACTIVE USERS -->
    <div id="usersTableCard" class="card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table class="data-table" style="width: 100%; min-width: 940px;">
            <thead>
                <tr>
                    <th style="padding: 0.75rem 0.85rem;">User Identity</th>
                    <th style="padding: 0.75rem 0.85rem;">Email</th>
                    <th style="padding: 0.75rem 0.85rem;">Role</th>
                    <th style="padding: 0.75rem 0.85rem;">Security Profile</th>
                    <th style="padding: 0.75rem 0.85rem;">Ambient Themes</th>
                    <th style="padding: 0.75rem 0.85rem;">Status</th>
                    <th class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 240px; min-width: 240px;">Actions</th>
                </tr>
            </thead>
            <tbody id="activeUsersTbody">
            </tbody>
        </table>
    </div>

    <!-- PENDING INVITATIONS DIRECTORY -->
    <div id="invitationsTableCard" class="card" style="padding: 0; overflow-x: auto; -webkit-overflow-scrolling: touch; display: none;">
        <div id="invitationsEmptyState" style="padding: 3.5rem 1rem; text-align: center; display: none;">
            <div style="font-size: 2rem; margin-bottom: 0.5rem;">✉️</div>
            <h4 style="font-size: 1rem; font-weight: 600; color: var(--zinc-800); margin: 0;">No Pending Invitations</h4>
            <p style="font-size: 0.8rem; color: var(--zinc-500); margin-top: 0.35rem;">
                Invite team members with 7-day cryptographic tokens and role-based assignments.
            </p>
            <button
                type="button"
                onclick="openInviteModal()"
                class="btn btn-primary btn-sm"
                style="margin-top: 1rem;"
            >
                Invite Team Member
            </button>
        </div>

        <table id="invitationsTable" class="data-table" style="width: 100%; min-width: 960px;">
            <thead>
                <tr>
                    <th style="padding: 0.75rem 0.85rem;">Invited Identity</th>
                    <th style="padding: 0.75rem 0.85rem;">Role</th>
                    <th style="padding: 0.75rem 0.85rem;">Security Profile</th>
                    <th style="padding: 0.75rem 0.85rem;">Invited By</th>
                    <th style="padding: 0.75rem 0.85rem;">Sent Date</th>
                    <th style="padding: 0.75rem 0.85rem;">Token Expiry (7 Days)</th>
                    <th style="padding: 0.75rem 0.85rem;">Status</th>
                    <th class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 260px; min-width: 260px;">Actions</th>
                </tr>
            </thead>
            <tbody id="invitationsTbody">
            </tbody>
        </table>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 1: CREATE / EDIT USER MODAL (SettingsView.tsx line 4567) -->
<!-- ===================================================================== -->
<div id="userModal" class="modal-overlay" style="z-index: 10002; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 500px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="users" style="width: 18px; height: 18px; color: #4f46e5;"></i>
                <h3 id="userModalTitle" style="font-size: 1rem; font-weight: 600; color: var(--zinc-950); margin: 0;">
                    Create New CRM User
                </h3>
            </div>
            <button
                type="button"
                onclick="closeUserModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.2rem; color: var(--zinc-400);"
            >
                ✕
            </button>
        </div>
        <form id="userModalForm" onsubmit="saveUserModal(event)">
            <input type="hidden" id="modalUserId" value="">
            <div class="modal-body" style="display: flex; flex-direction: column; gap: 0.9rem;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Full Name *</label>
                    <input
                        type="text"
                        required
                        class="form-input"
                        id="modalFullName"
                        placeholder="e.g. Sarah Connor"
                    >
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Email Address *</label>
                    <input
                        type="email"
                        required
                        class="form-input"
                        id="modalEmail"
                        placeholder="sconnor@acme.com"
                    >
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Assign Role</label>
                        <select class="form-select" id="modalRoleId">
                            <option value="role-ceo">Executive / CEO</option>
                            <option value="role-sales-vp">VP of Sales</option>
                            <option value="role-sales-rep" selected>Sales Executive</option>
                            <option value="role-procurement-mgr">Procurement Lead</option>
                            <option value="role-finance-lead">Finance &amp; Billing Lead</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Assign Security Profile</label>
                        <select class="form-select" id="modalProfileId">
                            <option value="prof-admin">System Administrator</option>
                            <option value="prof-sales" selected>Standard Sales Representative</option>
                            <option value="prof-procurement">Procurement Specialist</option>
                            <option value="prof-auditor">Read-Only Compliance Auditor</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">User Status</label>
                        <select class="form-select" id="modalStatus">
                            <option value="Active" selected>Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Phone (Optional)</label>
                        <input
                            type="text"
                            class="form-input"
                            id="modalPhone"
                            placeholder="+1 (555) 019-2834"
                        >
                    </div>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Ambient Themes &amp; Glow Access</label>
                    <select class="form-select" id="modalSurpriseMe">
                        <option value="inherit" selected>Inherit from Profile (Default)</option>
                        <option value="1">Permitted (✨ On)</option>
                        <option value="0">Locked (Disabled)</option>
                    </select>
                    <div style="font-size: 0.725rem; color: var(--zinc-500); margin-top: 0.2rem;">
                        Control whether this user can choose and customize ambient workspace themes.
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid var(--zinc-100); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" onclick="closeUserModal()" class="btn btn-secondary">
                    Cancel
                </button>
                <button type="submit" id="modalSubmitBtn" class="btn btn-primary">
                    Create User
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 2: TEAM MEMBER INVITATION MODAL (InviteUserModal.tsx line 85) -->
<!-- ===================================================================== -->
<div id="inviteModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 520px;">
        <div class="modal-header" style="border-bottom: 1px solid var(--zinc-200); padding-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--zinc-950); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <i data-lucide="mail" style="width: 20px; height: 20px; color: var(--color-primary);"></i>
                    <span>Invite New Team Member</span>
                </h3>
                <p style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.2rem; margin-bottom: 0;">
                    Send a personalized onboarding invitation with a secure 7-day activation link.
                </p>
            </div>
            <button
                type="button"
                onclick="closeInviteModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--zinc-400);"
            >
                ✕
            </button>
        </div>

        <!-- Form View -->
        <form id="inviteForm" onsubmit="submitInviteForm(event)">
            <div class="modal-body" style="padding: 1.25rem 0; display: flex; flex-direction: column; gap: 1rem;">
                <!-- Seat Quota Status Pill -->
                <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.65rem 0.85rem; font-size: 0.775rem; display: flex; justify-content: space-between; align-items: center;">
                    <span style="color: var(--zinc-600); font-weight: 500;">
                        ⚡ Organization License Capacity:
                    </span>
                    <span id="inviteModalRemainingSeats" style="font-weight: 700; color: #047857;">
                        4 Seats Remaining
                    </span>
                </div>

                <div class="form-group">
                    <label class="form-label">Recipient Work Email *</label>
                    <input
                        type="email"
                        required
                        placeholder="name@company.com"
                        class="form-input"
                        id="inviteEmail"
                    >
                </div>

                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input
                        type="text"
                        required
                        placeholder="e.g. Samantha Vance"
                        class="form-input"
                        id="inviteFullName"
                    >
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Role Assignment</label>
                        <select class="form-select" id="inviteRole">
                            <option value="role-sales-rep" selected>Sales Executive</option>
                            <option value="role-sales-vp">VP of Sales</option>
                            <option value="role-procurement-mgr">Procurement Lead</option>
                            <option value="role-finance-lead">Finance &amp; Billing Lead</option>
                            <option value="role-ceo">Executive / CEO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Security Profile</label>
                        <select class="form-select" id="inviteProfile">
                            <option value="prof-sales" selected>Standard Sales Representative</option>
                            <option value="prof-admin">System Administrator</option>
                            <option value="prof-procurement">Procurement Specialist</option>
                            <option value="prof-auditor">Read-Only Compliance Auditor</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Custom Welcome Note (Optional)</label>
                    <textarea
                        rows="3"
                        class="form-input"
                        id="inviteWelcomeNote"
                        placeholder="Welcome to our sales team! Please click the link below to set your password and get started."
                    ></textarea>
                </div>
            </div>

            <div class="modal-footer" style="border-top: 1px solid var(--zinc-200); padding-top: 1rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button
                    type="button"
                    class="btn btn-secondary"
                    onclick="closeInviteModal()"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="btn btn-primary"
                    style="display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 600;"
                >
                    <i data-lucide="send" style="width: 14px; height: 14px;"></i>
                    <span>Send Invitation Email</span>
                </button>
            </div>
        </form>

        <!-- Success View -->
        <div id="inviteSuccessScreen" style="display: none;" class="modal-body">
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-lg); padding: 1.25rem; text-align: center;">
                <i data-lucide="check-circle-2" style="width: 36px; height: 36px; color: #10b981; margin: 0 auto 0.5rem; display: block;"></i>
                <div style="font-size: 1rem; font-weight: 700; color: #047857;">
                    Invitation Dispatched Successfully!
                </div>
                <p style="font-size: 0.8rem; color: #065f46; margin-top: 0.35rem;">
                    A branded email has been dispatched via your SMTP gateway to <strong id="successInvitedEmail"></strong>.
                </p>
            </div>

            <div style="margin-top: 1.25rem;">
                <label class="form-label" style="font-size: 0.775rem; margin-bottom: 0.35rem; display: block;">
                    One-Click Activation URL (Valid for 7 Days)
                </label>
                <div style="display: flex; gap: 0.5rem;">
                    <input
                        type="text"
                        readonly
                        class="form-input"
                        id="successActivationUrl"
                        style="font-family: monospace; font-size: 0.75rem; background: var(--zinc-50);"
                    >
                    <button
                        type="button"
                        onclick="copyActivationUrl()"
                        class="btn btn-secondary"
                        style="display: inline-flex; align-items: center; gap: 0.35rem; white-space: nowrap; font-size: 0.8rem;"
                    >
                        <i data-lucide="copy" style="width: 14px; height: 14px;"></i>
                        <span id="copyUrlBtnText">Copy Link</span>
                    </button>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; padding-top: 1rem;">
                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="closeInviteModal()"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 3: USER OFFBOARDING & REASSIGNMENT WIZARD (UserOffboardingWizardModal.tsx) -->
<!-- ===================================================================== -->
<div id="offboardModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 580px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="border-bottom: 1px solid var(--zinc-200); padding-bottom: 1rem;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--zinc-950); display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                    <i data-lucide="user-x" style="width: 20px; height: 20px; color: #ef4444;"></i>
                    <span>Offboard User &amp; Reassign Records</span>
                </h3>
                <p style="font-size: 0.775rem; color: var(--zinc-500); margin-top: 0.2rem; margin-bottom: 0;">
                    Deactivate <strong id="offboardTargetName">Jordan Lee</strong> and safely reassign their active CRM records.
                </p>
            </div>
            <button
                type="button"
                onclick="closeOffboardModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--zinc-400);"
            >
                ✕
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem 0; display: flex; flex-direction: column; gap: 1.25rem;">
            <!-- Step 1: Record Inventory Scan -->
            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-lg); padding: 1.25rem;">
                <div style="font-size: 0.825rem; font-weight: 700; color: var(--zinc-900); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.45rem;">
                    <span>Step 1: Record Ownership Inventory</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin-bottom: 0.75rem;">
                    <div style="background: #ffffff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--zinc-500); font-size: 0.725rem;">
                            <i data-lucide="users" style="width: 14px; height: 14px; color: #6366f1;"></i>
                            <span>Assigned Leads</span>
                        </div>
                        <div id="offboardLeadsCount" style="font-size: 1.2rem; font-weight: 700; color: var(--zinc-900); margin-top: 0.2rem;">
                            14
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--zinc-500); font-size: 0.725rem;">
                            <i data-lucide="building" style="width: 14px; height: 14px; color: #0ea5e9;"></i>
                            <span>Assigned Accounts</span>
                        </div>
                        <div id="offboardAccountsCount" style="font-size: 1.2rem; font-weight: 700; color: var(--zinc-900); margin-top: 0.2rem;">
                            8
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--zinc-500); font-size: 0.725rem;">
                            <i data-lucide="target" style="width: 14px; height: 14px; color: #10b981;"></i>
                            <span>Pipeline Opportunities</span>
                        </div>
                        <div id="offboardOppsCount" style="font-size: 1.2rem; font-weight: 700; color: var(--zinc-900); margin-top: 0.2rem;">
                            6 ($380,000)
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.4rem; color: var(--zinc-500); font-size: 0.725rem;">
                            <i data-lucide="check-square" style="width: 14px; height: 14px; color: #f59e0b;"></i>
                            <span>Open Follow-Up Tasks</span>
                        </div>
                        <div id="offboardTasksCount" style="font-size: 1.2rem; font-weight: 700; color: var(--zinc-900); margin-top: 0.2rem;">
                            5
                        </div>
                    </div>
                </div>

                <div style="font-size: 0.775rem; color: var(--zinc-600); background: #ffffff; border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.6rem 0.85rem; display: flex; justify-content: space-between;">
                    <span>Total CRM Records at Risk:</span>
                    <strong style="color: #b91c1c;" id="offboardTotalCount">33 records</strong>
                </div>
            </div>

            <!-- Step 2: Destination User Picker -->
            <div>
                <label class="form-label" style="font-weight: 700; font-size: 0.825rem; margin-bottom: 0.4rem; display: block;">
                    Step 2: Successor Team Member Assignment
                </label>
                <p style="font-size: 0.75rem; color: var(--zinc-500); margin-bottom: 0.6rem;">
                    Select an active colleague to inherit all leads, accounts, pipeline deals, and pending tasks.
                </p>
                <select id="offboardDestinationUser" class="form-select" style="font-size: 0.85rem; font-weight: 500;">
                </select>
            </div>

            <!-- Step 3: Deactivation Alert Banner -->
            <div style="background: #fffbeb; border: 1px solid #fef3c7; border-radius: var(--radius-lg); padding: 1rem; font-size: 0.775rem; color: #92400e; line-height: 1.45;">
                <div style="font-weight: 700; margin-bottom: 0.25rem; display: flex; align-items: center; gap: 0.35rem;">
                    <i data-lucide="shield-alert" style="width: 14px; height: 14px; color: #d97706;"></i>
                    <span>Deactivation &amp; License Reclaim Action:</span>
                </div>
                <div>
                    Executing offboarding will change the user's status to <strong>Inactive</strong>, terminate all active browser sessions, and <strong>instantly reclaim 1 seat license</strong> back to your organization subscription pool.
                </div>
            </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid var(--zinc-200); padding-top: 1rem; display: flex; justify-content: flex-end; gap: 0.75rem;">
            <button
                type="button"
                class="btn btn-secondary"
                onclick="closeOffboardModal()"
            >
                Cancel
            </button>
            <button
                type="button"
                onclick="executeOffboarding()"
                class="btn btn-primary"
                style="background: #ef4444; border-color: #dc2626; display: inline-flex; align-items: center; gap: 0.45rem; font-weight: 600;"
            >
                <i data-lucide="user-x" style="width: 14px; height: 14px;"></i>
                <span>Confirm Offboarding &amp; Reclaim Seat</span>
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 4: PUBLIC SELF-ACTIVATION PREVIEW MODAL (ActivateInviteModal.tsx line 85) -->
<!-- ===================================================================== -->
<div id="previewInviteModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 500px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%); color: #ffffff;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="sparkles" style="width: 18px; height: 18px; color: #a5b4fc;"></i>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #ffffff; margin: 0;">
                        DealSync CRM — Candidate Activation
                    </h3>
                </div>
                <div style="font-size: 0.75rem; color: #c7d2fe; margin-top: 0.2rem;">
                    Previewing candidate invitation experience (7-day token verification)
                </div>
            </div>
            <button
                type="button"
                onclick="closePreviewInviteModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: #c7d2fe;"
            >
                ✕
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 0.75rem 1rem; display: flex; align-items: center; gap: 0.6rem;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #16a34a;"></i>
                <div>
                    <div style="font-size: 0.825rem; font-weight: 700; color: #166534;">Valid Cryptographic Token</div>
                    <div style="font-size: 0.725rem; color: #15803d;">Acme Enterprise Tech organization invitation verified.</div>
                </div>
            </div>

            <div>
                <div style="font-size: 0.75rem; color: var(--zinc-500);">Invited Candidate:</div>
                <div id="previewCandidateName" style="font-size: 1rem; font-weight: 700; color: var(--zinc-900);">Samantha Vance</div>
                <div id="previewCandidateEmail" style="font-size: 0.8125rem; color: var(--zinc-600);">samantha.vance@acmecorp.com</div>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">Create Enterprise Password</label>
                <input type="password" class="form-input" value="••••••••••••" readonly>
            </div>

            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem; font-size: 0.75rem; color: var(--zinc-600);">
                <div style="font-weight: 600; color: var(--zinc-800); margin-bottom: 0.35rem;">Password Policy Requirements:</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.25rem;">
                    <span style="color: #16a34a;">✓ Minimum 8 chars</span>
                    <span style="color: #16a34a;">✓ 1+ Uppercase letter</span>
                    <span style="color: #16a34a;">✓ 1+ Numeric digit</span>
                    <span style="color: #16a34a;">✓ 1+ Special character</span>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid var(--zinc-200); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" onclick="closePreviewInviteModal()" class="btn btn-secondary">
                Close Preview
            </button>
            <button type="button" onclick="showFeedback('Candidate self-activation simulated!')" class="btn btn-primary">
                Activate Account (Simulation)
            </button>
        </div>
    </div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 5: SUBSCRIPTION ADD SEATS PURCHASE MODAL (AddSeatsModal.tsx) -->
<!-- ===================================================================== -->
<div id="addSeatsModal" class="modal-overlay" style="z-index: 1200; display: none;">
    <div class="modal-content animate-fade-in" style="max-width: 480px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="credit-card" style="width: 18px; height: 18px; color: var(--color-primary);"></i>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--zinc-950); margin: 0;">
                    Add Enterprise Seat Licenses
                </h3>
            </div>
            <button
                type="button"
                onclick="closeAddSeatsModal()"
                style="background: none; border: none; cursor: pointer; font-size: 1.25rem; color: var(--zinc-400);"
            >
                ✕
            </button>
        </div>

        <div class="modal-body" style="padding: 1.25rem; display: flex; flex-direction: column; gap: 1rem;">
            <div style="background: var(--zinc-50); border: 1px solid var(--zinc-200); border-radius: var(--radius-md); padding: 0.75rem 1rem;">
                <div style="font-size: 0.75rem; color: var(--zinc-500);">Current Organization Plan:</div>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--zinc-900);">Enterprise Tier ($39 / seat / mo)</div>
                <div style="font-size: 0.75rem; color: var(--zinc-500); margin-top: 0.2rem;">Currently provisioned: <strong id="currentSeatsLabel">10 seats</strong></div>
            </div>

            <div class="form-group" style="margin: 0;">
                <label class="form-label" style="font-weight: 600; font-size: 0.8125rem;">New Total Seat Allocation</label>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <button type="button" class="btn btn-secondary" onclick="adjustSeatCount(-1)" style="width: 38px; height: 38px; padding: 0;">-</button>
                    <input type="number" id="newSeatsCountInput" value="12" min="6" max="100" class="form-input" style="text-align: center; font-size: 1.1rem; font-weight: 700; height: 38px;" oninput="updateAddSeatsCost()">
                    <button type="button" class="btn btn-secondary" onclick="adjustSeatCount(1)" style="width: 38px; height: 38px; padding: 0;">+</button>
                </div>
            </div>

            <div style="background: #eef2ff; border: 1px solid #c7d2fe; border-radius: var(--radius-md); padding: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <div style="font-size: 0.75rem; color: #4338ca; font-weight: 600;">Monthly Billing Increase:</div>
                    <div style="font-size: 0.72rem; color: #6366f1;">Prorated on next billing cycle</div>
                </div>
                <div id="seatsCostIncrease" style="font-size: 1.25rem; font-weight: 800; color: #3730a3;">
                    +$78.00 / mo
                </div>
            </div>
        </div>

        <div class="modal-footer" style="border-top: 1px solid var(--zinc-200); padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" onclick="closeAddSeatsModal()" class="btn btn-secondary">
                Cancel
            </button>
            <button type="button" onclick="confirmAddSeats()" class="btn btn-primary" style="font-weight: 600;">
                Confirm &amp; Provision Seats
            </button>
        </div>
    </div>
</div>

<!-- FLOATING FEEDBACK BANNER (SettingsView.tsx line 1740) -->
<div id="feedbackBanner" style="display: none; position: fixed; bottom: 1.5rem; right: 1.5rem; background: var(--zinc-900); color: #ffffff; padding: 0.75rem 1.25rem; border-radius: var(--radius-lg); box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2); align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 500; z-index: 10100;">
    <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #10b981;"></i>
    <span id="feedbackBannerText">Operation successful!</span>
</div>

<script>
// ============================================================================
// REACT PARITY STATE ENGINE
// ============================================================================
const STORAGE_USERS_KEY = 'dealsync_users_directory_data';
const STORAGE_INVITES_KEY = 'dealsync_user_invitations_data';
const STORAGE_QUOTA_KEY = 'dealsync_tenant_seats_quota';
const STORAGE_IMPERSONATED_KEY = 'dealsync_impersonated_user_id';

const DEFAULT_USERS = [
    {
        id: 'usr-101',
        full_name: 'Alex Morgan',
        email: 'alex.morgan@acmecorp.com',
        role_id: 'role-ceo',
        role_name: 'Executive / CEO',
        profile_id: 'prof-admin',
        profile_name: 'System Administrator',
        status: 'Active',
        phone: '+1 (415) 890-1200',
        surprise_me_enabled: null,
        created_at: '2024-10-12T00:00:00Z',
        pipeline_owned: 1450000,
        opps_count: 12,
        accounts_count: 18,
        leads_count: 34,
        tasks_count: 8
    },
    {
        id: 'usr-102',
        full_name: 'Jordan Lee',
        email: 'jordan.lee@acmecorp.com',
        role_id: 'role-sales-rep',
        role_name: 'Sales Executive',
        profile_id: 'prof-sales',
        profile_name: 'Standard Sales Representative',
        status: 'Active',
        phone: '+1 (617) 555-0199',
        surprise_me_enabled: 1,
        created_at: '2024-10-20T00:00:00Z',
        pipeline_owned: 380000,
        opps_count: 6,
        accounts_count: 8,
        leads_count: 14,
        tasks_count: 5
    },
    {
        id: 'usr-103',
        full_name: 'Marcus Brody',
        email: 'marcus.brody@acmecorp.com',
        role_id: 'role-procurement-mgr',
        role_name: 'Procurement Lead',
        profile_id: 'prof-procurement',
        profile_name: 'Procurement Specialist',
        status: 'Active',
        phone: '+1 (408) 555-4321',
        surprise_me_enabled: 0,
        created_at: '2024-11-05T00:00:00Z',
        pipeline_owned: 210000,
        opps_count: 4,
        accounts_count: 5,
        leads_count: 6,
        tasks_count: 3
    },
    {
        id: 'usr-104',
        full_name: 'Sarah Chen',
        email: 'sarah.finance@acmecorp.com',
        role_id: 'role-finance-lead',
        role_name: 'Finance & Billing Lead',
        profile_id: 'prof-admin',
        profile_name: 'System Administrator',
        status: 'Active',
        phone: '+1 (312) 555-7890',
        surprise_me_enabled: null,
        created_at: '2024-11-18T00:00:00Z',
        pipeline_owned: 940000,
        opps_count: 8,
        accounts_count: 11,
        leads_count: 19,
        tasks_count: 4
    }
];

const DEFAULT_INVITATIONS = [
    {
        id: 'inv-201',
        full_name: 'Samantha Vance',
        email: 'samantha.vance@acmecorp.com',
        role_id: 'role-sales-rep',
        role_name: 'Sales Executive',
        profile_id: 'prof-sales',
        profile_name: 'Standard Sales Representative',
        inviter_name: 'Alex Morgan',
        token: 'tok-sam-77291a84f',
        created_at: new Date(Date.now() - 24 * 60 * 60 * 1000).toISOString(),
        expires_at: new Date(Date.now() + 6 * 24 * 60 * 60 * 1000).toISOString(),
        status: 'pending'
    },
    {
        id: 'inv-202',
        full_name: 'David Kim',
        email: 'david.kim@acmecorp.com',
        role_id: 'role-procurement-mgr',
        role_name: 'Procurement Lead',
        profile_id: 'prof-procurement',
        profile_name: 'Procurement Specialist',
        inviter_name: 'Alex Morgan',
        token: 'tok-dav-88310be2c',
        created_at: new Date(Date.now() - 3 * 24 * 60 * 60 * 1000).toISOString(),
        expires_at: new Date(Date.now() + 4 * 24 * 60 * 60 * 1000).toISOString(),
        status: 'pending'
    }
];

let usersList = [];
let invitationsList = [];
let maxSeats = 10;
let userDirectoryTab = 'users'; // 'users' | 'invitations'
let selectedUserForDetail = null;
let offboardTargetUser = null;

const ROLE_NAMES = {
    'role-ceo': 'Executive / CEO',
    'role-sales-vp': 'VP of Sales',
    'role-sales-rep': 'Sales Executive',
    'role-procurement-mgr': 'Procurement Lead',
    'role-finance-lead': 'Finance & Billing Lead'
};

const PROFILE_NAMES = {
    'prof-admin': 'System Administrator',
    'prof-sales': 'Standard Sales Representative',
    'prof-procurement': 'Procurement Specialist',
    'prof-auditor': 'Read-Only Compliance Auditor'
};

function initUsersDirectory() {
    try {
        const u = localStorage.getItem(STORAGE_USERS_KEY);
        usersList = u ? JSON.parse(u) : [...DEFAULT_USERS];

        const inv = localStorage.getItem(STORAGE_INVITES_KEY);
        invitationsList = inv ? JSON.parse(inv) : [...DEFAULT_INVITATIONS];

        const q = localStorage.getItem(STORAGE_QUOTA_KEY);
        maxSeats = q ? parseInt(q, 10) : 10;
    } catch (e) {
        usersList = [...DEFAULT_USERS];
        invitationsList = [...DEFAULT_INVITATIONS];
        maxSeats = 10;
    }

    renderDirectory();
    updateQuotaGauge();

    if (window.lucide) {
        lucide.createIcons();
    }
}

function persistUsers() {
    localStorage.setItem(STORAGE_USERS_KEY, JSON.stringify(usersList));
}
function persistInvitations() {
    localStorage.setItem(STORAGE_INVITES_KEY, JSON.stringify(invitationsList));
}
function persistQuota() {
    localStorage.setItem(STORAGE_QUOTA_KEY, String(maxSeats));
}

function updateQuotaGauge() {
    const activeCount = usersList.filter(u => u.status === 'Active').length;
    const inactiveCount = usersList.filter(u => u.status === 'Inactive').length;
    const pendingCount = invitationsList.filter(i => i.status === 'pending').length;
    const totalAllocated = activeCount + pendingCount;
    const availableSeats = Math.max(0, maxSeats - totalAllocated);
    const pct = Math.min(100, Math.round((totalAllocated / maxSeats) * 100));

    document.getElementById('quotaAllocatedText').textContent = `${totalAllocated} of ${maxSeats} Seats Allocated (${pct}%)`;
    const availEl = document.getElementById('quotaAvailableText');
    availEl.textContent = `${availableSeats} Seats Available`;
    availEl.style.color = availableSeats <= 0 ? '#ef4444' : '#16a34a';

    const barEl = document.getElementById('quotaProgressBar');
    barEl.style.width = `${pct}%`;
    barEl.style.background = pct >= 100 ? '#ef4444' : (pct >= 80 ? '#f59e0b' : '#10b981');

    document.getElementById('metricActiveUsers').textContent = activeCount;
    document.getElementById('metricPendingInvites').textContent = pendingCount;
    const availCardEl = document.getElementById('metricAvailableSeats');
    availCardEl.textContent = availableSeats;
    availCardEl.style.color = availableSeats > 0 ? '#4f46e5' : '#ef4444';
    document.getElementById('metricInactiveAccounts').textContent = inactiveCount;

    document.getElementById('userCountBadge').textContent = usersList.length;
    const pendingBadge = document.getElementById('pendingCountBadge');
    pendingBadge.textContent = pendingCount;
    pendingBadge.style.display = pendingCount > 0 ? 'inline-block' : 'none';

    const invRem = document.getElementById('inviteModalRemainingSeats');
    if (invRem) {
        invRem.textContent = `${availableSeats} Seat${availableSeats === 1 ? '' : 's'} Remaining`;
        invRem.style.color = availableSeats <= 0 ? '#b91c1c' : '#047857';
    }
}

function setUserDirectoryTab(tab) {
    userDirectoryTab = tab;
    const usersBtn = document.getElementById('subtabBtnUsers');
    const invBtn = document.getElementById('subtabBtnInvitations');
    const usersCard = document.getElementById('usersTableCard');
    const invCard = document.getElementById('invitationsTableCard');

    if (tab === 'users') {
        usersBtn.style.background = '#ffffff';
        usersBtn.style.color = 'var(--zinc-900)';
        usersBtn.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';

        invBtn.style.background = 'transparent';
        invBtn.style.color = 'var(--zinc-600)';
        invBtn.style.boxShadow = 'none';

        usersCard.style.display = 'block';
        invCard.style.display = 'none';
    } else {
        invBtn.style.background = '#ffffff';
        invBtn.style.color = 'var(--zinc-900)';
        invBtn.style.boxShadow = '0 1px 2px rgba(0,0,0,0.06)';

        usersBtn.style.background = 'transparent';
        usersBtn.style.color = 'var(--zinc-600)';
        usersBtn.style.boxShadow = 'none';

        usersCard.style.display = 'none';
        invCard.style.display = 'block';
    }

    renderDirectory();
    if (window.lucide) lucide.createIcons();
}

function renderDirectory() {
    const impersonatedId = localStorage.getItem(STORAGE_IMPERSONATED_KEY);

    // 1. Render Active Users (exact line-by-line React td elements)
    const usersTbody = document.getElementById('activeUsersTbody');
    usersTbody.innerHTML = '';

    usersList.forEach(u => {
        const isCurrentActive = impersonatedId === u.id || (!impersonatedId && (u.id === 'usr-101' || u.email === 'alex.morgan@acmecorp.com'));
        const profileDefault = true;
        const currentVal = u.surprise_me_enabled === null || u.surprise_me_enabled === undefined
            ? 'inherit'
            : (u.surprise_me_enabled === 1 || u.surprise_me_enabled === true ? '1' : '0');

        const tr = document.createElement('tr');
        tr.style.cursor = 'pointer';
        tr.style.transition = 'background-color 0.15s ease';
        tr.title = 'Click to open full user profile page';
        tr.onmouseenter = () => tr.style.backgroundColor = 'var(--zinc-50)';
        tr.onmouseleave = () => tr.style.backgroundColor = 'transparent';
        tr.onclick = () => openUserDetailView(u);

        tr.innerHTML = `
            <td style="padding: 0.75rem 0.85rem;">
                <div style="font-weight: 600; color: var(--color-primary); display: inline-flex; align-items: center; gap: 0.35rem;">
                    <span>${escapeHtml(u.full_name)}</span>
                </div>
                <div style="font-size: 0.7rem; color: var(--zinc-400); font-family: monospace; margin-top: 0.1rem;">ID: ${escapeHtml(u.id)}</div>
            </td>
            <td style="padding: 0.75rem 0.85rem;">
                <span style="color: var(--zinc-700); font-size: 0.8125rem;">${escapeHtml(u.email)}</span>
            </td>
            <td style="padding: 0.75rem 0.85rem;">
                <span class="badge badge-purple">${escapeHtml(u.role_name || ROLE_NAMES[u.role_id] || 'No Role Assigned')}</span>
            </td>
            <td style="padding: 0.75rem 0.85rem;">
                <span class="badge badge-blue">${escapeHtml(u.profile_name || PROFILE_NAMES[u.profile_id] || 'No Profile Assigned')}</span>
            </td>
            <td style="padding: 0.75rem 0.85rem;">
                <div style="display: flex; align-items: center; gap: 0.45rem;">
                    <select
                        onclick="event.stopPropagation()"
                        onchange="handleUpdateUserSurpriseMe('${u.id}', this.value)"
                        class="form-select"
                        style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: var(--radius-md); border-color: ${currentVal === '1' ? '#a78bfa' : (currentVal === '0' ? '#fca5a5' : 'var(--zinc-200)')}; background: ${currentVal === '1' ? '#f5f3ff' : (currentVal === '0' ? '#fef2f2' : 'var(--zinc-50)')}; color: ${currentVal === '1' ? '#6d28d9' : (currentVal === '0' ? '#b91c1c' : 'var(--zinc-800)')}; font-weight: 500;"
                        title="Configure Ambient Themes &amp; Glow Customization permission for this user"
                    >
                        <option value="inherit" ${currentVal === 'inherit' ? 'selected' : ''}>Inherit (${profileDefault ? '✨ Permitted' : 'Locked'})</option>
                        <option value="1" ${currentVal === '1' ? 'selected' : ''}>Permitted (✨ On)</option>
                        <option value="0" ${currentVal === '0' ? 'selected' : ''}>Locked (Disabled)</option>
                    </select>
                </div>
            </td>
            <td style="padding: 0.75rem 0.85rem;">
                <span class="badge ${u.status === 'Active' ? 'badge-green' : 'badge-gray'}">${escapeHtml(u.status)}</span>
            </td>
            <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 240px; min-width: 240px;">
                <div style="display: inline-flex; align-items: center; gap: 0.4rem; justify-content: flex-end;" onclick="event.stopPropagation()">
                    <button
                        type="button"
                        onclick="handleOpenEditUser('${u.id}')"
                        class="btn btn-secondary btn-sm"
                        style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; font-weight: 600;"
                        title="Edit ${escapeHtml(u.full_name)}"
                    >
                        <i data-lucide="edit-2" style="width: 12px; height: 12px;"></i>
                        <span>Edit</span>
                    </button>

                    ${u.status === 'Active' && !isCurrentActive ? `
                    <button
                        type="button"
                        onclick="handleOpenOffboardModal('${u.id}')"
                        class="btn btn-secondary btn-sm"
                        style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; color: #dc2626; border-color: #fca5a5; background: #fff1f2; font-weight: 600;"
                        title="Offboard ${escapeHtml(u.full_name)} and reassign owned records"
                    >
                        <i data-lucide="user-x" style="width: 12px; height: 12px;"></i>
                        <span>Offboard</span>
                    </button>
                    ` : ''}

                    ${isCurrentActive ? `
                    <span
                        style="font-size: 0.725rem; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 0.28rem 0.6rem; border-radius: var(--radius-md); font-weight: 700; display: inline-flex; align-items: center; gap: 0.3rem;"
                        title="Currently active session identity"
                    >
                        <i data-lucide="user-check" style="width: 12px; height: 12px;"></i>
                        <span>Current</span>
                    </span>
                    ` : `
                    <button
                        type="button"
                        onclick="handleLoginAsUser('${u.id}')"
                        ${u.status === 'Inactive' ? 'disabled' : ''}
                        class="btn btn-sm"
                        style="padding: 0.3rem 0.55rem; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem; background: ${u.status === 'Inactive' ? 'var(--zinc-200)' : '#cffafe'}; border-color: ${u.status === 'Inactive' ? 'var(--zinc-300)' : '#67e8f9'}; color: ${u.status === 'Inactive' ? 'var(--zinc-400)' : '#0e7490'}; cursor: ${u.status === 'Inactive' ? 'not-allowed' : 'pointer'}; font-weight: 600; box-shadow: ${u.status === 'Inactive' ? 'none' : '0 1px 2px rgba(14, 116, 144, 0.15)'};"
                        title="Log in as ${escapeHtml(u.full_name)} (Impersonation Mode)"
                    >
                        <i data-lucide="log-in" style="width: 12px; height: 12px;"></i>
                        <span>Login</span>
                    </button>
                    `}
                </div>
            </td>
        `;
        usersTbody.appendChild(tr);
    });

    // 2. Render Pending Invitations Table
    const invitesTbody = document.getElementById('invitationsTbody');
    const emptyStateEl = document.getElementById('invitationsEmptyState');
    const tableEl = document.getElementById('invitationsTable');
    invitesTbody.innerHTML = '';

    if (invitationsList.length === 0) {
        emptyStateEl.style.display = 'block';
        tableEl.style.display = 'none';
    } else {
        emptyStateEl.style.display = 'none';
        tableEl.style.display = 'table';

        invitationsList.forEach(inv => {
            const isExpired = new Date(inv.expires_at) < new Date();
            const daysLeft = Math.max(0, Math.ceil((new Date(inv.expires_at).getTime() - Date.now()) / (1000 * 60 * 60 * 24)));

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="padding: 0.75rem 0.85rem;">
                    <div style="font-weight: 600; color: var(--zinc-900);">${escapeHtml(inv.full_name)}</div>
                    <div style="font-size: 0.75rem; color: var(--zinc-500);">${escapeHtml(inv.email)}</div>
                </td>
                <td style="padding: 0.75rem 0.85rem;">
                    <span class="badge badge-purple">${escapeHtml(inv.role_name || ROLE_NAMES[inv.role_id] || 'Standard')}</span>
                </td>
                <td style="padding: 0.75rem 0.85rem;">
                    <span class="badge badge-blue">${escapeHtml(inv.profile_name || PROFILE_NAMES[inv.profile_id] || 'Standard')}</span>
                </td>
                <td style="padding: 0.75rem 0.85rem; font-size: 0.8rem; color: var(--zinc-600);">
                    ${escapeHtml(inv.inviter_name || 'System Admin')}
                </td>
                <td style="padding: 0.75rem 0.85rem; font-size: 0.75rem; color: var(--zinc-500);">
                    ${new Date(inv.created_at).toLocaleDateString()}
                </td>
                <td style="padding: 0.75rem 0.85rem;">
                    ${isExpired ? `
                    <span class="badge badge-red">Expired</span>
                    ` : `
                    <span class="badge badge-amber" title="Expires ${new Date(inv.expires_at).toLocaleString()}">
                        ${daysLeft} days left
                    </span>
                    `}
                </td>
                <td style="padding: 0.75rem 0.85rem;">
                    <span class="badge ${inv.status === 'pending' ? 'badge-amber' : (inv.status === 'accepted' ? 'badge-green' : 'badge-gray')}">
                        ${escapeHtml(inv.status)}
                    </span>
                </td>
                <td class="sticky-actions-col" style="padding: 0.75rem 1rem; width: 260px; min-width: 260px;">
                    <div style="display: inline-flex; align-items: center; gap: 0.35rem; justify-content: flex-end;">
                        <button
                            type="button"
                            onclick="handleCopyInviteLink('${inv.token}', this)"
                            class="btn btn-secondary btn-sm"
                            style="padding: 0.25rem 0.5rem; font-size: 0.725rem; font-weight: 600;"
                            title="Copy direct self-activation URL"
                        >
                            <i data-lucide="copy" style="width: 12px; height: 12px;"></i>
                            <span>Link</span>
                        </button>

                        <button
                            type="button"
                            onclick="previewInviteToken('${inv.token}')"
                            class="btn btn-secondary btn-sm"
                            style="padding: 0.25rem 0.5rem; font-size: 0.725rem; font-weight: 600;"
                            title="Preview public activation wizard"
                        >
                            <span>Preview</span>
                        </button>

                        <button
                            type="button"
                            onclick="handleResendInvite('${inv.id}', this)"
                            class="btn btn-secondary btn-sm"
                            style="padding: 0.25rem 0.5rem; font-size: 0.725rem; font-weight: 600;"
                            title="Resend invitation email &amp; refresh 7-day token"
                        >
                            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                            <span>Resend</span>
                        </button>

                        <button
                            type="button"
                            onclick="handleRevokeInvite('${inv.id}')"
                            class="btn btn-secondary btn-sm"
                            style="padding: 0.25rem 0.5rem; font-size: 0.725rem; color: #dc2626; border-color: #fca5a5; font-weight: 600;"
                            title="Revoke invitation and release seat license"
                        >
                            <span>Revoke</span>
                        </button>
                    </div>
                </td>
            `;
            invitesTbody.appendChild(tr);
        });
    }

    if (window.lucide) {
        lucide.createIcons();
    }
}

// ============================================================================
// USER DETAIL PAGE VIEW
// ============================================================================
function openUserDetailView(user) {
    selectedUserForDetail = user;
    document.getElementById('mainDirectoryView').style.display = 'none';
    document.getElementById('userDetailContainer').style.display = 'block';

    const impersonatedId = localStorage.getItem(STORAGE_IMPERSONATED_KEY);
    const isCurrentActive = impersonatedId === user.id || (!impersonatedId && (user.id === 'usr-101' || user.email === 'alex.morgan@acmecorp.com'));

    const initials = user.full_name ? user.full_name.split(' ').map(n => n[0]).join('').slice(0, 2).toUpperCase() : 'U';
    document.getElementById('detailAvatarBox').textContent = initials;
    document.getElementById('detailFullName').textContent = user.full_name;

    const statusPill = document.getElementById('detailStatusPill');
    statusPill.textContent = user.status === 'Active' ? '● Active Employee' : '○ Suspended';
    statusPill.className = `badge ${user.status === 'Active' ? 'badge-green' : 'badge-gray'}`;

    const dot = document.getElementById('detailStatusDot');
    dot.style.background = user.status === 'Active' ? '#10b981' : '#9ca3af';

    const adminPill = document.getElementById('detailAdminPill');
    adminPill.style.display = (user.profile_id === 'prof-admin' || user.role_id === 'role-ceo') ? 'inline-flex' : 'none';

    document.getElementById('detailRoleBadge').innerHTML = `<i data-lucide="shield" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i> ${escapeHtml(user.role_name || ROLE_NAMES[user.role_id] || 'Standard User')}`;
    document.getElementById('detailProfileBadge').innerHTML = `<i data-lucide="lock" style="width: 12px; height: 12px; display: inline-block; vertical-align: middle; margin-right: 2px;"></i> ${escapeHtml(user.profile_name || PROFILE_NAMES[user.profile_id] || 'Standard Profile')}`;

    const reportsToEl = document.getElementById('detailReportsToBadge');
    if (user.role_id === 'role-ceo') {
        reportsToEl.innerHTML = `<i data-lucide="building-2" style="width: 12px; height: 12px; color: var(--zinc-500);"></i> <span>Reports to: <strong>Board of Directors</strong></span>`;
    } else {
        reportsToEl.innerHTML = `<i data-lucide="building-2" style="width: 12px; height: 12px; color: var(--zinc-500);"></i> <span>Reports to: <strong>Alex Morgan (CEO)</strong></span>`;
    }

    const emailLink = document.getElementById('detailEmailLink');
    emailLink.href = `mailto:${user.email}`;
    emailLink.textContent = user.email;

    const phoneLink = document.getElementById('detailPhoneLink');
    if (user.phone) {
        phoneLink.href = `tel:${user.phone}`;
        phoneLink.textContent = user.phone;
        document.getElementById('detailPhoneSpan').style.display = 'inline-flex';
    } else {
        document.getElementById('detailPhoneSpan').style.display = 'none';
    }

    document.getElementById('detailJoinedDate').textContent = `Joined ${new Date(user.created_at || '2024-10-12').toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}`;
    document.getElementById('detailUserIdBadge').textContent = `ID: ${user.id}`;

    // KPI Ribbons
    document.getElementById('detailKpiPipeline').textContent = `$${(user.pipeline_owned || 380000).toLocaleString()}`;
    document.getElementById('detailKpiDeals').textContent = `Across ${user.opps_count || 6} active deals`;
    document.getElementById('detailKpiAccounts').textContent = `${user.accounts_count || 8} Accounts`;
    document.getElementById('detailKpiLeads').textContent = `${user.leads_count || 14} Leads`;
    document.getElementById('detailKpiTasks').textContent = `${user.tasks_count || 5} Tasks`;

    // Overview Tab Details
    document.getElementById('ovFullName').textContent = user.full_name;
    document.getElementById('ovEmail').textContent = user.email;
    document.getElementById('ovPhone').textContent = user.phone || 'None provided';
    document.getElementById('ovRole').textContent = user.role_name || ROLE_NAMES[user.role_id] || 'Standard Role';
    document.getElementById('ovProfile').textContent = user.profile_name || PROFILE_NAMES[user.profile_id] || 'Standard Profile';
    document.getElementById('ovStatus').textContent = user.status;
    document.getElementById('ovStatus').className = `badge ${user.status === 'Active' ? 'badge-green' : 'badge-gray'}`;

    const themesLabel = user.surprise_me_enabled === null || user.surprise_me_enabled === undefined
        ? 'Inherit from Profile (✨ Permitted)'
        : (user.surprise_me_enabled === 1 || user.surprise_me_enabled === true ? 'Permitted (✨ On)' : 'Locked (Disabled)');
    document.getElementById('ovThemes').textContent = themesLabel;

    // Permissions Tab Matrix
    document.getElementById('permProfileName').textContent = user.profile_name || PROFILE_NAMES[user.profile_id] || 'System Administrator';
    renderPermissionsMatrix(user.profile_id || 'prof-admin');

    // Login CTA State
    const loginBtn = document.getElementById('detailLoginBtn');
    const loginBtnText = document.getElementById('detailLoginBtnText');
    if (isCurrentActive) {
        loginBtn.disabled = true;
        loginBtn.style.background = '#ecfdf5';
        loginBtn.style.borderColor = '#a7f3d0';
        loginBtn.style.color = '#047857';
        loginBtnText.textContent = 'Current Active User';
    } else {
        loginBtn.disabled = user.status === 'Inactive';
        loginBtn.style.background = user.status === 'Inactive' ? 'var(--zinc-200)' : '#e0f2fe';
        loginBtn.style.borderColor = user.status === 'Inactive' ? 'var(--zinc-300)' : '#7dd3fc';
        loginBtn.style.color = user.status === 'Inactive' ? 'var(--zinc-400)' : '#0369a1';
        loginBtnText.textContent = 'Login as User';
    }

    switchDetailSubtab('overview');
    if (window.lucide) lucide.createIcons();
}

function closeUserDetailView() {
    selectedUserForDetail = null;
    document.getElementById('userDetailContainer').style.display = 'none';
    document.getElementById('mainDirectoryView').style.display = 'block';
    renderDirectory();
    if (window.lucide) lucide.createIcons();
}

function switchDetailSubtab(tab) {
    const tabs = ['overview', 'permissions', 'security', 'audit'];
    tabs.forEach(t => {
        const btn = document.getElementById(`detailSubtabBtn${capitalize(t)}`);
        const pane = document.getElementById(`detailTab${capitalize(t)}`);
        if (t === tab) {
            btn.style.borderBottomColor = 'var(--color-primary)';
            btn.style.color = 'var(--color-primary)';
            btn.style.fontWeight = '700';
            pane.style.display = 'block';
        } else {
            btn.style.borderBottomColor = 'transparent';
            btn.style.color = 'var(--zinc-600)';
            btn.style.fontWeight = '500';
            pane.style.display = 'none';
        }
    });
    if (window.lucide) lucide.createIcons();
}

function renderPermissionsMatrix(profileId) {
    const tbody = document.getElementById('permMatrixTbody');
    const modules = [
        { mod: 'Leads', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
        { mod: 'Accounts', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
        { mod: 'Contacts', r: 1, c: 1, u: 1, d: 1, t: 1, s: 'All' },
        { mod: 'Opportunities', r: 1, c: 1, u: 1, d: 1, t: 0, s: 'All' },
        { mod: 'Purchase Deals', r: 1, c: profileId === 'prof-procurement' || profileId === 'prof-admin' ? 1 : 0, u: 1, d: 0, t: 0, s: 'All' },
        { mod: 'Shipping', r: 1, c: profileId === 'prof-procurement' || profileId === 'prof-admin' ? 1 : 0, u: 1, d: 0, t: 0, s: 'All' },
        { mod: 'Payments', r: 1, c: profileId === 'prof-admin' ? 1 : 0, u: profileId === 'prof-admin' ? 1 : 0, d: 0, t: 0, s: 'All' },
        { mod: 'Wires', r: profileId === 'prof-admin' ? 1 : 0, c: profileId === 'prof-admin' ? 1 : 0, u: profileId === 'prof-admin' ? 1 : 0, d: 0, t: 0, s: 'All' }
    ];

    tbody.innerHTML = modules.map(m => `
        <tr>
            <td style="padding: 0.55rem 0.85rem; font-weight: 600; color: var(--zinc-900);">${m.mod}</td>
            <td style="padding: 0.55rem 0.85rem; text-align: center;">${m.r ? '<span style="color: #16a34a; font-weight: 700;">✓</span>' : '<span style="color: var(--zinc-300);">—</span>'}</td>
            <td style="padding: 0.55rem 0.85rem; text-align: center;">${m.c ? '<span style="color: #16a34a; font-weight: 700;">✓</span>' : '<span style="color: var(--zinc-300);">—</span>'}</td>
            <td style="padding: 0.55rem 0.85rem; text-align: center;">${m.u ? '<span style="color: #16a34a; font-weight: 700;">✓</span>' : '<span style="color: var(--zinc-300);">—</span>'}</td>
            <td style="padding: 0.55rem 0.85rem; text-align: center;">${m.d ? '<span style="color: #16a34a; font-weight: 700;">✓</span>' : '<span style="color: var(--zinc-300);">—</span>'}</td>
            <td style="padding: 0.55rem 0.85rem; text-align: center;">${m.t ? '<span style="color: #16a34a; font-weight: 700;">✓</span>' : '<span style="color: var(--zinc-300);">—</span>'}</td>
            <td style="padding: 0.55rem 0.85rem;"><span class="badge badge-purple" style="font-size: 0.72rem;">${m.s}</span></td>
        </tr>
    `).join('');
}

function detailTriggerLogin() {
    if (selectedUserForDetail) {
        handleLoginAsUser(selectedUserForDetail.id);
    }
}

function detailTriggerEdit() {
    if (selectedUserForDetail) {
        handleOpenEditUser(selectedUserForDetail.id);
    }
}

// ============================================================================
// IMPERSONATION (handleLoginAsUser)
// ============================================================================
function handleLoginAsUser(userId) {
    const user = usersList.find(u => u.id === userId);
    if (!user) return;

    if (user.status === 'Inactive') {
        alert(`Cannot log in as "${user.full_name}" because this user account is Inactive.`);
        return;
    }

    localStorage.setItem(STORAGE_IMPERSONATED_KEY, user.id);
    showFeedback(`Switched active session identity to "${user.full_name}"!`);
    setTimeout(() => {
        renderDirectory();
        if (selectedUserForDetail && selectedUserForDetail.id === user.id) {
            openUserDetailView(user);
        }
    }, 600);
}

// ============================================================================
// AMBIENT THEMES IMMEDIATE UPDATE
// ============================================================================
function handleUpdateUserSurpriseMe(userId, val) {
    const u = usersList.find(item => item.id === userId);
    if (!u) return;

    u.surprise_me_enabled = val === 'inherit' ? null : (val === '1' ? 1 : 0);
    persistUsers();
    showFeedback(`Ambient themes permission for "${u.full_name}" updated!`);
    renderDirectory();
}

// ============================================================================
// CREATE / EDIT USER MODAL
// ============================================================================
function openCreateUserModal() {
    document.getElementById('modalUserId').value = '';
    document.getElementById('userModalTitle').textContent = 'Create New CRM User';
    document.getElementById('modalSubmitBtn').textContent = 'Create User';

    document.getElementById('modalFullName').value = '';
    document.getElementById('modalEmail').value = '';
    document.getElementById('modalRoleId').value = 'role-sales-rep';
    document.getElementById('modalProfileId').value = 'prof-sales';
    document.getElementById('modalStatus').value = 'Active';
    document.getElementById('modalPhone').value = '';
    document.getElementById('modalSurpriseMe').value = 'inherit';

    document.getElementById('userModal').style.display = 'flex';
}

function handleOpenEditUser(userId) {
    const u = usersList.find(item => item.id === userId);
    if (!u) return;

    document.getElementById('modalUserId').value = u.id;
    document.getElementById('userModalTitle').textContent = `Edit User: ${u.full_name}`;
    document.getElementById('modalSubmitBtn').textContent = 'Save Changes';

    document.getElementById('modalFullName').value = u.full_name || '';
    document.getElementById('modalEmail').value = u.email || '';
    document.getElementById('modalRoleId').value = u.role_id || 'role-sales-rep';
    document.getElementById('modalProfileId').value = u.profile_id || 'prof-sales';
    document.getElementById('modalStatus').value = u.status || 'Active';
    document.getElementById('modalPhone').value = u.phone || '';

    const themesVal = u.surprise_me_enabled === null || u.surprise_me_enabled === undefined
        ? 'inherit'
        : (u.surprise_me_enabled === 1 || u.surprise_me_enabled === true ? '1' : '0');
    document.getElementById('modalSurpriseMe').value = themesVal;

    document.getElementById('userModal').style.display = 'flex';
}

function closeUserModal() {
    document.getElementById('userModal').style.display = 'none';
}

function saveUserModal(e) {
    e.preventDefault();
    const id = document.getElementById('modalUserId').value;
    const fullName = document.getElementById('modalFullName').value.trim();
    const email = document.getElementById('modalEmail').value.trim();
    const roleId = document.getElementById('modalRoleId').value;
    const profileId = document.getElementById('modalProfileId').value;
    const status = document.getElementById('modalStatus').value;
    const phone = document.getElementById('modalPhone').value.trim();
    const surpriseMe = document.getElementById('modalSurpriseMe').value;
    const surpriseMeVal = surpriseMe === 'inherit' ? null : (surpriseMe === '1' ? 1 : 0);

    if (id) {
        const u = usersList.find(item => item.id === id);
        if (u) {
            u.full_name = fullName;
            u.email = email;
            u.role_id = roleId;
            u.role_name = ROLE_NAMES[roleId] || 'Role';
            u.profile_id = profileId;
            u.profile_name = PROFILE_NAMES[profileId] || 'Profile';
            u.status = status;
            u.phone = phone;
            u.surprise_me_enabled = surpriseMeVal;
            persistUsers();
            showFeedback(`User "${fullName}" updated successfully!`);
            if (selectedUserForDetail && selectedUserForDetail.id === id) {
                openUserDetailView(u);
            }
        }
    } else {
        const newId = `usr-${Date.now().toString().slice(-4)}`;
        const newUser = {
            id: newId,
            full_name: fullName,
            email: email,
            role_id: roleId,
            role_name: ROLE_NAMES[roleId] || 'Role',
            profile_id: profileId,
            profile_name: PROFILE_NAMES[profileId] || 'Profile',
            status: status,
            phone: phone,
            surprise_me_enabled: surpriseMeVal,
            created_at: new Date().toISOString(),
            pipeline_owned: 0,
            opps_count: 0,
            accounts_count: 0,
            leads_count: 0,
            tasks_count: 0
        };
        usersList.push(newUser);
        persistUsers();
        showFeedback(`User "${fullName}" created successfully!`);
    }

    closeUserModal();
    updateQuotaGauge();
    renderDirectory();
}

// ============================================================================
// INVITE TEAM MEMBER MODAL
// ============================================================================
function openInviteModal() {
    const activeCount = usersList.filter(u => u.status === 'Active').length;
    const pendingCount = invitationsList.filter(i => i.status === 'pending').length;
    const available = Math.max(0, maxSeats - (activeCount + pendingCount));

    if (available <= 0) {
        alert('Your organization license seat limit has been reached! Please add more seats first.');
        openAddSeatsModal();
        return;
    }

    document.getElementById('inviteForm').style.display = 'block';
    document.getElementById('inviteSuccessScreen').style.display = 'none';

    document.getElementById('inviteEmail').value = '';
    document.getElementById('inviteFullName').value = '';
    document.getElementById('inviteWelcomeNote').value = '';
    document.getElementById('inviteModalRemainingSeats').textContent = `${available} Seats Remaining`;

    document.getElementById('inviteModal').style.display = 'flex';
}

function closeInviteModal() {
    document.getElementById('inviteModal').style.display = 'none';
    renderDirectory();
    updateQuotaGauge();
}

function submitInviteForm(e) {
    e.preventDefault();
    const email = document.getElementById('inviteEmail').value.trim();
    const fullName = document.getElementById('inviteFullName').value.trim();
    const roleId = document.getElementById('inviteRole').value;
    const profileId = document.getElementById('inviteProfile').value;

    const token = 'tok-' + Math.random().toString(36).substring(2, 11) + Date.now().toString(36);
    const newInvite = {
        id: `inv-${Date.now().toString().slice(-4)}`,
        full_name: fullName,
        email: email,
        role_id: roleId,
        role_name: ROLE_NAMES[roleId] || 'Role',
        profile_id: profileId,
        profile_name: PROFILE_NAMES[profileId] || 'Profile',
        inviter_name: 'Alex Morgan',
        token: token,
        created_at: new Date().toISOString(),
        expires_at: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
        status: 'pending'
    };

    invitationsList.push(newInvite);
    persistInvitations();
    updateQuotaGauge();

    const activationUrl = `${window.location.origin}/admin/users-directory?token=${token}`;
    document.getElementById('successInvitedEmail').textContent = email;
    document.getElementById('successActivationUrl').value = activationUrl;

    document.getElementById('inviteForm').style.display = 'none';
    document.getElementById('inviteSuccessScreen').style.display = 'block';
    showFeedback(`Invitation sent to ${email}!`);
    if (window.lucide) lucide.createIcons();
}

function copyActivationUrl() {
    const url = document.getElementById('successActivationUrl').value;
    navigator.clipboard.writeText(url).then(() => {
        document.getElementById('copyUrlBtnText').textContent = 'Copied!';
        setTimeout(() => {
            const btn = document.getElementById('copyUrlBtnText');
            if (btn) btn.textContent = 'Copy Link';
        }, 2500);
    });
}

function handleCopyInviteLink(token, btn) {
    const url = `${window.location.origin}/admin/users-directory?token=${token}`;
    navigator.clipboard.writeText(url).then(() => {
        const span = btn.querySelector('span');
        if (span) span.textContent = 'Copied';
        btn.style.color = '#16a34a';
        setTimeout(() => {
            if (span) span.textContent = 'Link';
            btn.style.color = '';
        }, 2500);
    });
}

function previewInviteToken(token) {
    const inv = invitationsList.find(i => i.token === token);
    if (!inv) return;

    document.getElementById('previewCandidateName').textContent = inv.full_name;
    document.getElementById('previewCandidateEmail').textContent = inv.email;
    document.getElementById('previewInviteModal').style.display = 'flex';
}

function closePreviewInviteModal() {
    document.getElementById('previewInviteModal').style.display = 'none';
}

function handleResendInvite(invId, btn) {
    const inv = invitationsList.find(i => i.id === invId);
    if (!inv) return;

    inv.expires_at = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString();
    inv.token = 'tok-' + Math.random().toString(36).substring(2, 11);
    persistInvitations();

    const span = btn.querySelector('span');
    if (span) span.textContent = 'Sent!';
    showFeedback(`Invitation email resent to ${inv.email}!`);
    setTimeout(() => {
        if (span) span.textContent = 'Resend';
        renderDirectory();
    }, 1500);
}

function handleRevokeInvite(invId) {
    const inv = invitationsList.find(i => i.id === invId);
    if (!inv) return;

    if (!confirm(`Are you sure you want to revoke the invitation for ${inv.email}? This will immediately release their allocated seat.`)) {
        return;
    }

    invitationsList = invitationsList.filter(i => i.id !== invId);
    persistInvitations();
    updateQuotaGauge();
    renderDirectory();
    showFeedback(`Invitation for ${inv.email} revoked and seat reclaimed.`);
}

// ============================================================================
// USER OFFBOARDING WIZARD
// ============================================================================
function handleOpenOffboardModal(userId) {
    const user = usersList.find(u => u.id === userId);
    if (!user) return;

    offboardTargetUser = user;
    document.getElementById('offboardTargetName').textContent = `${user.full_name} (${user.email})`;

    const leads = user.leads_count || 14;
    const accounts = user.accounts_count || 8;
    const opps = user.opps_count || 6;
    const oppsVal = user.pipeline_owned || 380000;
    const tasks = user.tasks_count || 5;
    const total = leads + accounts + opps + tasks;

    document.getElementById('offboardLeadsCount').textContent = leads;
    document.getElementById('offboardAccountsCount').textContent = accounts;
    document.getElementById('offboardOppsCount').textContent = `${opps} ($${oppsVal.toLocaleString()})`;
    document.getElementById('offboardTasksCount').textContent = tasks;
    document.getElementById('offboardTotalCount').textContent = `${total} records`;

    const eligible = usersList.filter(u => u.id !== user.id && u.status === 'Active');
    const selectEl = document.getElementById('offboardDestinationUser');
    selectEl.innerHTML = eligible.map(u => `
        <option value="${u.id}">${escapeHtml(u.full_name)} (${escapeHtml(u.email)}) — ${escapeHtml(u.role_name || 'Active Member')}</option>
    `).join('') + '<option value="">Do not reassign (Keep unassigned / historical)</option>';

    document.getElementById('offboardModal').style.display = 'flex';
    if (window.lucide) lucide.createIcons();
}

function closeOffboardModal() {
    offboardTargetUser = null;
    document.getElementById('offboardModal').style.display = 'none';
}

function executeOffboarding() {
    if (!offboardTargetUser) return;

    const destId = document.getElementById('offboardDestinationUser').value;
    const destUser = usersList.find(u => u.id === destId);

    if (destUser) {
        destUser.leads_count = (destUser.leads_count || 0) + (offboardTargetUser.leads_count || 0);
        destUser.accounts_count = (destUser.accounts_count || 0) + (offboardTargetUser.accounts_count || 0);
        destUser.opps_count = (destUser.opps_count || 0) + (offboardTargetUser.opps_count || 0);
        destUser.pipeline_owned = (destUser.pipeline_owned || 0) + (offboardTargetUser.pipeline_owned || 0);
        destUser.tasks_count = (destUser.tasks_count || 0) + (offboardTargetUser.tasks_count || 0);
    }

    offboardTargetUser.status = 'Inactive';
    offboardTargetUser.leads_count = 0;
    offboardTargetUser.accounts_count = 0;
    offboardTargetUser.opps_count = 0;
    offboardTargetUser.pipeline_owned = 0;
    offboardTargetUser.tasks_count = 0;

    persistUsers();
    closeOffboardModal();
    updateQuotaGauge();
    renderDirectory();

    const reassignedMsg = destUser ? ` and records reassigned to ${destUser.full_name}` : '';
    showFeedback(`User ${offboardTargetUser.full_name} offboarded successfully${reassignedMsg}. 1 seat reclaimed!`);
}

// ============================================================================
// ADD SEATS MODAL
// ============================================================================
function openAddSeatsModal() {
    document.getElementById('currentSeatsLabel').textContent = `${maxSeats} seats`;
    document.getElementById('newSeatsCountInput').value = maxSeats + 2;
    updateAddSeatsCost();
    document.getElementById('addSeatsModal').style.display = 'flex';
}

function closeAddSeatsModal() {
    document.getElementById('addSeatsModal').style.display = 'none';
}

function adjustSeatCount(delta) {
    const input = document.getElementById('newSeatsCountInput');
    const cur = parseInt(input.value, 10) || maxSeats;
    const next = Math.max(maxSeats + 1, cur + delta);
    input.value = next;
    updateAddSeatsCost();
}

function updateAddSeatsCost() {
    const input = document.getElementById('newSeatsCountInput');
    const val = parseInt(input.value, 10) || maxSeats;
    const diff = Math.max(0, val - maxSeats);
    const cost = diff * 39;
    document.getElementById('seatsCostIncrease').textContent = `+$${cost.toFixed(2)} / mo`;
}

function confirmAddSeats() {
    const input = document.getElementById('newSeatsCountInput');
    const newTotal = parseInt(input.value, 10);
    if (isNaN(newTotal) || newTotal <= maxSeats) {
        alert('Please choose a seat count higher than your current allocation.');
        return;
    }

    maxSeats = newTotal;
    persistQuota();
    closeAddSeatsModal();
    updateQuotaGauge();
    showFeedback(`Tenant seat license limit updated to ${maxSeats} seats successfully!`);
}

// ============================================================================
// FEEDBACK BANNER
// ============================================================================
let feedbackTimer = null;
function showFeedback(msg) {
    const banner = document.getElementById('feedbackBanner');
    const textEl = document.getElementById('feedbackBannerText');
    if (!banner || !textEl) return;

    textEl.textContent = msg;
    banner.style.display = 'flex';

    if (feedbackTimer) clearTimeout(feedbackTimer);
    feedbackTimer = setTimeout(() => {
        banner.style.display = 'none';
    }, 4000);

    if (window.lucide) lucide.createIcons();
}

function capitalize(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', initUsersDirectory);
</script>
@endsection
