<!-- Edit Lead Detail Modal -->
<div id="detailEditModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-2xl bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Edit Lead: {{ $lead['name'] }}</h3>
            </div>
            <button
                type="button"
                onclick="window.closeDetailEditModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                ✕
            </button>
        </div>

        <form id="detailEditForm" class="p-6 space-y-5 max-h-[78vh] overflow-y-auto">
            <input type="hidden" id="edit-lead-id" value="{{ $lead['id'] }}">

            <div>
                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800">
                    Core Identification
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">First Name</label>
                        <input
                            type="text"
                            name="first_name"
                            value="{{ $lead['first_name'] ?? '' }}"
                            required
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Last Name</label>
                        <input
                            type="text"
                            name="last_name"
                            value="{{ $lead['last_name'] ?? '' }}"
                            required
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Company Name</label>
                        <input
                            type="text"
                            name="company"
                            value="{{ $lead['company'] ?? '' }}"
                            required
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Job Title</label>
                        <input
                            type="text"
                            name="title"
                            value="{{ $lead['title'] ?? '' }}"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ $lead['email'] ?? '' }}"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Phone Number</label>
                        <input
                            type="text"
                            name="phone"
                            value="{{ $lead['phone'] ?? '' }}"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800">
                    Qualification &amp; Assignment
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Status Stage</label>
                        <select
                            name="status"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                            <option value="New" {{ ($lead['status'] ?? '') === 'New' ? 'selected' : '' }}>New</option>
                            <option value="Contacted" {{ ($lead['status'] ?? '') === 'Contacted' ? 'selected' : '' }}>Contacted</option>
                            <option value="Qualified" {{ ($lead['status'] ?? '') === 'Qualified' ? 'selected' : '' }}>Qualified</option>
                            <option value="Lost" {{ ($lead['status'] ?? '') === 'Lost' ? 'selected' : '' }}>Lost</option>
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Lead Source</label>
                        <select
                            name="source"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                            @foreach($sources ?? [] as $src)
                                <option value="{{ $src }}" {{ ($lead['source'] ?? '') === $src ? 'selected' : '' }}>{{ $src }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Propensity Score (0-100)</label>
                        <input
                            type="number"
                            name="score"
                            min="0"
                            max="100"
                            value="{{ $lead['score'] ?? 50 }}"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>

                    <div class="space-y-1">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Rating Priority</label>
                        <select
                            name="rating"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                            <option value="Hot" {{ ($lead['rating'] ?? '') === 'Hot' ? 'selected' : '' }}>Hot</option>
                            <option value="Warm" {{ ($lead['rating'] ?? '') === 'Warm' ? 'selected' : '' }}>Warm</option>
                            <option value="Cold" {{ ($lead['rating'] ?? '') === 'Cold' ? 'selected' : '' }}>Cold</option>
                        </select>
                    </div>

                    <div class="space-y-1 col-span-full">
                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Qualification Notes</label>
                        <textarea
                            name="notes"
                            rows="2"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >{{ $lead['notes'] ?? '' }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Dynamic Custom Fields Form Section -->
            @php
                $editableCustomFields = collect($customFields ?? [])->filter(function($cf) use ($fieldPermissions) {
                    $p = $fieldPermissions[$cf->field_name] ?? 'Read/Write';
                    return $p !== 'Hidden';
                });
            @endphp
            @if($editableCustomFields->isNotEmpty())
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                        <span>Dynamic Custom Fields</span>
                        <span class="text-[10px] text-zinc-400 font-normal">Permissions Enforced</span>
                    </h4>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($editableCustomFields as $cf)
                            @php
                                $fName = $cf->field_name;
                                $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                $isReadOnly = ($perm === 'Read-Only');
                                $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? '');
                                $readOnlyAttrs = $isReadOnly ? 'disabled readonly class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 cursor-not-allowed opacity-75"' : 'class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"';
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $cf->field_label }}</label>
                                    @if($isReadOnly)
                                        <span class="text-[10px] text-zinc-400 font-medium">Read-Only</span>
                                    @endif
                                </div>
                                @if(strtolower($cf->data_type) === 'dropdown' || strtolower($cf->data_type) === 'select')
                                    @php $opts = $cf->options ? array_map('trim', explode(',', $cf->options)) : []; @endphp
                                    <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {!! $readOnlyAttrs !!}>
                                        <option value="">Select...</option>
                                        @foreach($opts as $opt)
                                            <option value="{{ $opt }}" {{ (string)$val === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                @elseif(strtolower($cf->data_type) === 'rating')
                                    <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {!! $readOnlyAttrs !!}>
                                        <option value="">None</option>
                                        @for($star = 1; $star <= 5; $star++)
                                            <option value="{{ $star }}" {{ (int)$val === $star ? 'selected' : '' }}>{{ str_repeat('★', $star) }} ({{ $star }} Stars)</option>
                                        @endfor
                                    </select>
                                @else
                                    <input type="text" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {!! $readOnlyAttrs !!}>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="window.closeDetailEditModal()"
                    class="px-4 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Convert Lead Detail Modal -->
<div id="detailConvertModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-emerald-50/50 dark:bg-emerald-950/30">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                    <i data-lucide="arrow-right-left" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Convert Lead: {{ $lead['name'] }}</h3>
            </div>
            <button
                type="button"
                onclick="window.closeDetailConvertModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                ✕
            </button>
        </div>

        <form id="detailConvertForm" class="p-6 space-y-4">
            <div class="p-3.5 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700/60 text-xs space-y-2">
                <p class="font-semibold text-zinc-800 dark:text-zinc-200">
                    Converting this lead generates the following core CRM records:
                </p>
                <ul class="space-y-1 pl-4 list-disc text-zinc-600 dark:text-zinc-400">
                    <li><strong class="text-zinc-900 dark:text-zinc-100">New Account:</strong> {{ $lead['company'] ?? 'Customer Account' }}</li>
                    <li><strong class="text-zinc-900 dark:text-zinc-100">New Contact:</strong> {{ $lead['name'] }} ({{ $lead['email'] ?? 'No email' }})</li>
                </ul>
            </div>

            <div class="p-3 rounded-xl border border-zinc-200 dark:border-zinc-700/60 bg-zinc-50/50 dark:bg-zinc-800/30 space-y-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" id="detail-chk-create-opp" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                    <span class="text-xs font-semibold text-zinc-800 dark:text-zinc-200">Also create new Opportunity for this Account</span>
                </label>

                <div id="detail-opp-fields" class="space-y-2 pt-2 border-t border-zinc-200 dark:border-zinc-700/40">
                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Opportunity Deal Name</label>
                        <input
                            type="text"
                            id="detail-input-opp-name"
                            value="{{ ($lead['company'] ?? $lead['name']) }} — Deal"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Target Amount ($ USD)</label>
                        <input
                            type="number"
                            id="detail-input-opp-amount"
                            value="50000"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="window.closeDetailConvertModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors cursor-pointer inline-flex items-center gap-1.5"
                >
                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                    <span>Confirm Conversion</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Transfer Lead Detail Modal -->
<div id="detailTransferModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-md bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-blue-100 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Transfer Ownership</h3>
            </div>
            <button
                type="button"
                onclick="window.closeDetailTransferModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors"
            >
                ✕
            </button>
        </div>

        <form id="detailTransferForm" class="p-6 space-y-4">
            <div class="text-xs text-zinc-600 dark:text-zinc-400">
                Current Owner: <strong class="text-zinc-900 dark:text-zinc-100">{{ $lead['assigned_to_name'] ?? 'Unassigned' }}</strong>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Select New Owner</label>
                <select
                    id="detailTransferUserSelect"
                    required
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                >
                    @foreach($users ?? [] as $u)
                        <option value="{{ $u->id }}" {{ ($lead['assigned_to'] ?? '') == $u->id ? 'selected' : '' }}>
                            {{ $u->name }} ({{ $u->email }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="window.closeDetailTransferModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors cursor-pointer"
                >
                    Confirm Transfer
                </button>
            </div>
        </form>
    </div>
</div>
