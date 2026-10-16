    <!-- =========================================================================
         TOP STUDIO HEADER (Matches React DashboardStudio.tsx line 1042)
         ========================================================================= -->
    <div style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 24px; padding: 1.5rem 1.75rem; margin-bottom: 0.5rem; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.04); display: flex; flex-direction: column; gap: 1.25rem;">
        <!-- ROW 1: Title & Right-aligned Badges -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <!-- Left: Icon, Title & 16-Column Canvas Pill -->
            <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                <i data-lucide="layout-dashboard" style="width: 22px; height: 22px; color: #4f46e5;"></i>
                <h1 style="font-size: 1.375rem; font-weight: 800; color: #09090b; letter-spacing: -0.025em; margin: 0;">
                    Dashboard Studio &amp; Layout Designer
                </h1>
                <span style="font-size: 0.75rem; font-weight: 700; color: #9333ea; background: #f3e8ff; padding: 3px 10px; border-radius: 9999px; line-height: 1;">
                    16-Column Canvas
                </span>
            </div>

            <!-- Right: 3 Stats & Feature Badges -->
            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span id="stat-widgets-count" style="font-size: 0.78125rem; font-weight: 600; color: #3f3f46; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                    📦 8 Widgets
                </span>
                <span id="stat-sections-count" style="font-size: 0.78125rem; font-weight: 600; color: #3f3f46; background: #f4f4f5; border: 1px solid #e4e4e7; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 4px;">
                    📑 3 Sections
                </span>
                <span style="font-size: 0.78125rem; font-weight: 600; color: #3b82f6; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 4px 10px; display: inline-flex; align-items: center; gap: 5px;">
                    <i data-lucide="arrow-left-right" style="width: 13px; height: 13px;"></i>
                    <span>Drag &amp; Drop Enabled</span>
                </span>
            </div>
        </div>

        <!-- ROW 2: Subtitle & Reset Defaults Link -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; margin-top: -0.35rem;">
            <p style="font-size: 0.85rem; color: #71717a; margin: 0; line-height: 1.4;">
                Design and rearrange role-specific dashboard layouts, drag widgets, and configure interactive drilldown panels.
            </p>

            <button
                type="button"
                onclick="handleResetProfileLayout()"
                style="background: transparent; border: none; color: #71717a; font-size: 0.8rem; font-weight: 500; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; padding: 2px 4px;"
                title="Reset current profile back to default preset"
            >
                <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
                <span>Reset Defaults</span>
            </button>
        </div>

        <!-- ROW 3: Target Profile Card + Segmented Tabs + Save Button -->
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-top: 0.25rem;">
            <!-- Target Profile Picker Box -->
            <div style="position: relative; display: inline-flex; align-items: center; gap: 0.75rem; background: #ffffff; border: 1px solid #e4e4e7; border-radius: 12px; padding: 0.55rem 1rem; min-width: 310px; cursor: pointer;">
                <i data-lucide="shield-check" style="width: 18px; height: 18px; color: #6366f1; flex-shrink: 0;"></i>
                <div style="display: flex; flexDirection: column; line-height: 1.15;">
                    <span style="font-size: 0.72rem; font-weight: 700; color: #52525b;">Target</span>
                    <span style="font-size: 0.72rem; font-weight: 700; color: #52525b;">Profile:</span>
                </div>
                <div id="selected-profile-display-name" style="font-size: 0.925rem; font-weight: 700; color: #09090b; margin-left: 0.35rem; flex: 1;">
                    System Administrator
                </div>
                <i data-lucide="chevron-down" style="width: 16px; height: 16px; color: #09090b; flex-shrink: 0;"></i>

                <!-- Native select for seamless switching -->
                <select
                    id="profile-switcher-select"
                    style="position: absolute; inset: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; font-size: 1rem;"
                    onchange="handleProfileChange(this.value)"
                    title="Select profile"
                >
                    @if(isset($profiles) && count($profiles) > 0)
                        @foreach($profiles as $prof)
                            @php
                                $pLower = strtolower($prof->name);
                                $pAlias = 'prof-admin';
                                if (str_contains($pLower, 'admin')) $pAlias = 'prof-admin';
                                elseif (str_contains($pLower, 'sales') || str_contains($pLower, 'standard')) $pAlias = 'prof-sales';
                                elseif (str_contains($pLower, 'procurement')) $pAlias = 'prof-procurement';
                                elseif (str_contains($pLower, 'read')) $pAlias = 'prof-readonly';
                                else $pAlias = (string)$prof->id;
                            @endphp
                            <option value="{{ $pAlias }}" {{ $loop->first ? 'selected' : '' }}>
                                {{ $prof->name }} {{ $prof->is_admin ? '(Admin)' : '' }}
                            </option>
                        @endforeach
                    @else
                        <option value="prof-admin" selected>System Administrator (Admin)</option>
                        <option value="prof-sales">Standard Sales Representative</option>
                        <option value="prof-procurement">Procurement Specialist</option>
                        <option value="prof-readonly">Read Only Profile</option>
                    @endif
                </select>
            </div>

            <!-- Right: Segmented Mode Switcher + Save & Publish -->
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <!-- Segmented Mode Switcher -->
                <div style="display: flex; background: #f4f4f5; padding: 4px; border-radius: 10px; border: 1px solid #e4e4e7; gap: 4px;">
                    <button
                        type="button"
                        id="tab-btn-editor"
                        onclick="switchStudioTab('editor')"
                        style="display: flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 700; color: #09090b; background: #ffffff; border: none; border-radius: 6px; cursor: pointer; box-shadow: 0 1px 3px rgba(0,0,0,0.1); transition: all 0.15s ease;"
                    >
                        <i data-lucide="sliders" style="width: 13px; height: 13px;"></i>
                        <span>Layout Designer</span>
                    </button>

                    <button
                        type="button"
                        id="tab-btn-preview"
                        onclick="switchStudioTab('preview')"
                        style="display: flex; align-items: center; gap: 6px; padding: 0.45rem 1rem; font-size: 0.8125rem; font-weight: 500; color: #71717a; background: transparent; border: none; border-radius: 6px; cursor: pointer; box-shadow: none; transition: all 0.15s ease;"
                    >
                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i>
                        <span>Live Preview</span>
                    </button>
                </div>

                <!-- Primary Save & Publish Button -->
                <button
                    type="button"
                    onclick="handleSaveAndPublish()"
                    style="background: #4f46e5; color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 0.55rem 1.35rem; border-radius: 10px; border: none; display: inline-flex; align-items: center; gap: 7px; cursor: pointer; box-shadow: 0 1px 2px rgba(79, 70, 229, 0.25); transition: background 0.15s ease;"
                    onmouseenter="this.style.background='#4338ca'"
                    onmouseleave="this.style.background='#4f46e5'"
                >
                    <i data-lucide="save" style="width: 15px; height: 15px;"></i>
                    <span>Save &amp; Publish</span>
                </button>
            </div>
        </div>

        <!-- ROW 4: Action Buttons Row -->
        <div style="display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap; margin-top: 0.15rem;">
            <button
                type="button"
                onclick="openWidgetLibraryModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Add Widget</span>
            </button>

            <button
                type="button"
                onclick="openInsertSectionModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="folder-plus" style="width: 14px; height: 14px; color: #6366f1;"></i>
                <span>Insert Section</span>
            </button>

            <button
                type="button"
                onclick="openMatrixModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="users" style="width: 14px; height: 14px; color: #52525b;"></i>
                <span>Profile Dashboards Matrix</span>
            </button>

            <button
                type="button"
                onclick="openSettingsModal()"
                style="background: #ffffff; border: 1px solid #e4e4e7; border-radius: 8px; padding: 0.45rem 0.95rem; font-size: 0.8125rem; font-weight: 600; color: #18181b; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.02);"
            >
                <i data-lucide="calendar" style="width: 14px; height: 14px; color: #52525b;"></i>
                <span>Date &amp; Manager Controls</span>
            </button>
        </div>
    </div>

    <!-- Feedback Banner -->
    <div id="studio-feedback-banner" style="display: none; padding: 0.75rem 1.25rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: var(--radius-lg); color: #047857; margin-bottom: 0.5rem; align-items: center; gap: 0.5rem; font-size: 0.8125rem; font-weight: 500; animation: fadeInScale 0.25s ease;">
        <i data-lucide="check-circle-2" style="width: 16px; height: 16px;"></i>
        <span id="studio-feedback-text">Operation completed successfully</span>
    </div>

