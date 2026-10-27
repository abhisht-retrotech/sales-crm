<div id="detailEditModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    @php
        $modalSections = $layoutSections ?? [];
        $has3ColSection = collect($modalSections)->contains(function($s) {
            return (int)($s['columns'] ?? 2) >= 3;
        });
    @endphp
    <div class="relative w-full {{ !empty($has3ColSection) ? 'max-w-5xl' : 'max-w-3xl' }} bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8 transition-all duration-200">
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
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <form id="detailEditForm" class="p-6 space-y-5 max-h-[78vh] overflow-y-auto">
            <input type="hidden" id="edit-lead-id" value="{{ $lead['id'] }}">

            @php
                $modalSections = $layoutSections ?? [];
                $customFieldsMap = collect($customFields ?? [])->keyBy('field_name');
                $standardOverridesMap = collect($standardOverrides ?? [])->keyBy('field_name');
                $renderedFieldNames = [];
            @endphp

            @if(!empty($modalSections))
                @foreach($modalSections as $secIndex => $sec)
                    @php
                        $secId = $sec['id'] ?? 'sec-' . $secIndex;
                        $secTitle = $sec['title'] ?? 'Section ' . ($secIndex + 1);
                        $secColumns = (int)($sec['columns'] ?? 2);
                        $secFields = $sec['fields'] ?? [];
                        $isSystemSection = ($secId === 'sec-system-info') || (stripos($secTitle, 'System Information') !== false);
                        if ($isSystemSection) continue;

                        $validFields = array_filter($secFields, function($f) use ($fieldPermissions) {
                            $fName = is_array($f) ? ($f['name'] ?? '') : $f;
                            if (empty($fName) || in_array($fName, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) return false;
                            return ($fieldPermissions[$fName] ?? 'Read/Write') !== 'Hidden';
                        });
                        if (empty($validFields)) continue;
                    @endphp

                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800">
                            {{ $secTitle }}
                        </h4>
                        @php
                            $gridColsClass = 'grid-cols-1 sm:grid-cols-2';
                            if ($secColumns === 1) {
                                $gridColsClass = 'grid-cols-1';
                            } elseif ($secColumns === 3) {
                                $gridColsClass = 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3';
                            }
                        @endphp
                        <div class="grid {{ $gridColsClass }} gap-4">
                            @foreach($validFields as $f)
                                @php
                                    $fName = is_array($f) ? ($f['name'] ?? '') : $f;
                                    $renderedFieldNames[] = $fName;
                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    $isRo = ($perm === 'Read-Only') || !empty($f['readOnly']);
                                    $override = $standardOverridesMap[$fName] ?? null;
                                    $cf = $customFieldsMap[$fName] ?? null;
                                    $isReq = !empty($f['required']) || (!empty($override->is_required));
                                    $label = $override->field_label ?? ($f['label'] ?? ($cf->field_label ?? ucwords(str_replace('_', ' ', $fName))));
                                    $isFullSpan = ($secColumns === 1) || in_array($fName, ['notes', 'description', 'email_opt_out']) || ($cf && in_array($cf->data_type, ['textarea', 'rich_text'])) || (!empty($f['col_span']) && ($f['col_span'] === 'full' || (int)$f['col_span'] === 3));
                                    $colSpanClass = (!$isFullSpan && !empty($f['col_span']) && (int)$f['col_span'] === 2 && $secColumns === 3) ? 'sm:col-span-2 lg:col-span-2' : '';
                                    $inputCommonClass = $isRo ? 'w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800/80 cursor-not-allowed opacity-80' : 'w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none';
                                @endphp

                                <div class="space-y-1 {{ $isFullSpan ? 'col-span-full' : $colSpanClass }}">
                                    @if($fName !== 'email_opt_out' && !($cf && strtolower($cf->data_type) === 'boolean'))
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                            {{ $label }}
                                            @if($isReq)<span class="text-rose-500">*</span>@endif
                                        </label>
                                        @if($isRo)<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">🔒 Read-Only</span>@endif
                                    </div>
                                    @endif

                                    @if($fName === 'first_name')
                                        <input type="text" name="first_name" value="{{ $lead['first_name'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'last_name')
                                        <input type="text" name="last_name" value="{{ $lead['last_name'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'company')
                                        <input type="text" name="company" value="{{ $lead['company'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'title')
                                        <input type="text" name="title" value="{{ $lead['title'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'email')
                                        <input type="email" name="email" value="{{ $lead['email'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'phone')
                                        <input type="text" name="phone" value="{{ $lead['phone'] ?? '' }}" {{ $isRo ? 'disabled readonly' : ($isReq ? 'required' : '') }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'status')
                                        <select name="status" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            @foreach($stages ?? ['New', 'Contacted', 'Working', 'Qualified', 'Lost'] as $st)
                                                <option value="{{ $st }}" {{ ($lead['status'] ?? '') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($fName === 'source')
                                        <select name="source" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            @foreach($sources ?? [] as $src)
                                                <option value="{{ $src }}" {{ ($lead['source'] ?? '') === $src ? 'selected' : '' }}>{{ $src }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($fName === 'score')
                                        <input type="number" name="score" min="0" max="100" value="{{ $lead['score'] ?? 50 }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                    @elseif($fName === 'rating')
                                        <select name="rating" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            <option value="Hot" {{ strtolower($lead['rating'] ?? '') === 'hot' ? 'selected' : '' }}>Hot</option>
                                            <option value="Warm" {{ strtolower($lead['rating'] ?? '') === 'warm' ? 'selected' : '' }}>Warm</option>
                                            <option value="Cold" {{ strtolower($lead['rating'] ?? '') === 'cold' ? 'selected' : '' }}>Cold</option>
                                        </select>
                                    @elseif($fName === 'assigned_to')
                                        <select name="assigned_to" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            <option value="">Select Owner...</option>
                                            @foreach($users ?? [] as $u)
                                                <option value="{{ $u->id }}" {{ (string)($lead['assigned_to'] ?? '') === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    @elseif($fName === 'email_opt_out')
                                        <label class="flex items-center gap-2 pt-1 {{ $isRo ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                            <input type="checkbox" name="email_opt_out" value="1" {{ $isRo ? 'disabled' : '' }} {{ !empty($lead['email_opt_out']) && $lead['email_opt_out'] !== '0' && $lead['email_opt_out'] !== false ? 'checked' : '' }} class="rounded border-zinc-300 text-rose-600 focus:ring-rose-500 {{ $isRo ? 'cursor-not-allowed opacity-80' : 'cursor-pointer' }}">
                                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                            @if($isRo)<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒 Read-Only</span>@endif
                                        </label>
                                    @elseif($fName === 'notes')
                                        <textarea name="notes" rows="2" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">{{ $lead['notes'] ?? '' }}</textarea>
                                    @else
                                        {{-- Dynamic Custom Field in Section --}}
                                        @php
                                            $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? '');
                                            $mapping = $cf ? app(\App\Services\DynamicFields\DynamicFieldMappingService::class)->resolveTargetMapping($cf, 'leads') : null;
                                        @endphp

                                        @if($mapping && $mapping['target_module'] === 'users' && !empty($users))
                                            <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                                <option value="">Select User / Approver...</option>
                                                @foreach($users as $u)
                                                    <option value="{{ $u->id }}" {{ (string)$val === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($cf && (strtolower($cf->data_type) === 'dropdown' || strtolower($cf->data_type) === 'select'))
                                            @php $opts = $cf->options ? array_map('trim', explode(',', $cf->options)) : []; @endphp
                                            <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                                <option value="">Select...</option>
                                                @foreach($opts as $opt)
                                                    <option value="{{ $opt }}" {{ (string)$val === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        @elseif($cf && strtolower($cf->data_type) === 'rating')
                                            <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                                <option value="">None</option>
                                                @for($star = 1; $star <= 5; $star++)
                                                    <option value="{{ $star }}" {{ (int)$val === $star ? 'selected' : '' }}>{{ str_repeat('★', $star) }} ({{ $star }} Stars)</option>
                                                @endfor
                                            </select>
                                        @elseif($cf && strtolower($cf->data_type) === 'boolean')
                                            <label class="flex items-center gap-2 pt-1 {{ $isRo ? 'cursor-not-allowed' : 'cursor-pointer' }}">
                                                <input type="checkbox" name="cf_{{ $fName }}" value="1" data-custom-field="{{ $fName }}" {{ !empty($val) && $val != '0' ? 'checked' : '' }} {{ $isRo ? 'disabled' : '' }} class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $label }}</span>
                                                @if($isRo)<span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold ml-1">🔒 Read-Only</span>@endif
                                            </label>
                                        @elseif($cf && (strtolower($cf->data_type) === 'number' || strtolower($cf->data_type) === 'currency'))
                                            <input type="number" step="any" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                        @elseif($cf && strtolower($cf->data_type) === 'date')
                                            <input type="date" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                        @elseif($cf && strtolower($cf->data_type) === 'textarea')
                                            <textarea rows="2" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">{{ $val }}</textarea>
                                        @elseif($cf && strtolower($cf->data_type) === 'maskedtext')
                                            <input type="password" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                        @else
                                            <input type="text" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isRo ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Remaining Custom Fields Not Placed in Layout Sections --}}
                @php
                    $remainingCustomFields = collect($customFields ?? [])->filter(function($cf) use ($fieldPermissions, $renderedFieldNames) {
                        $fName = $cf->field_name;
                        $p = $fieldPermissions[$fName] ?? 'Read/Write';
                        return $p !== 'Hidden' && !in_array($fName, $renderedFieldNames);
                    });
                @endphp
                @if($remainingCustomFields->isNotEmpty())
                    <div>
                        <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                            <span>Additional Custom Fields</span>
                        </h4>
                        <div class="grid {{ !empty($has3ColSection) ? 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' : 'grid-cols-1 sm:grid-cols-2' }} gap-4">
                            @foreach($remainingCustomFields as $cf)
                                @php
                                    $fName = $cf->field_name;
                                    $perm = $fieldPermissions[$fName] ?? 'Read/Write';
                                    $isReadOnly = ($perm === 'Read-Only');
                                    $val = $lead[$fName] ?? ($lead['custom_data'][$fName] ?? '');
                                    $inputCommonClass = $isReadOnly ? 'w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 cursor-not-allowed opacity-75' : 'w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none';
                                @endphp
                                <div class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">{{ $cf->field_label }}</label>
                                        @if($isReadOnly)<span class="text-[10px] text-zinc-400 font-medium">Read-Only</span>@endif
                                    </div>
                                    @php $mapping = app(\App\Services\DynamicFields\DynamicFieldMappingService::class)->resolveTargetMapping($cf, 'leads'); @endphp
                                    @if($mapping && $mapping['target_module'] === 'users' && !empty($users))
                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            <option value="">Select User / Approver...</option>
                                            @foreach($users as $u)
                                                <option value="{{ $u->id }}" {{ (string)$val === (string)$u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(strtolower($cf->data_type) === 'dropdown' || strtolower($cf->data_type) === 'select')
                                        @php $opts = $cf->options ? array_map('trim', explode(',', $cf->options)) : []; @endphp
                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            <option value="">Select...</option>
                                            @foreach($opts as $opt)
                                                <option value="{{ $opt }}" {{ (string)$val === (string)$opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @elseif(strtolower($cf->data_type) === 'rating')
                                        <select name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                            <option value="">None</option>
                                            @for($star = 1; $star <= 5; $star++)
                                                <option value="{{ $star }}" {{ (int)$val === $star ? 'selected' : '' }}>{{ str_repeat('★', $star) }} ({{ $star }} Stars)</option>
                                            @endfor
                                        </select>
                                    @elseif(strtolower($cf->data_type) === 'boolean')
                                        <label class="flex items-center gap-2 pt-1 cursor-pointer">
                                            <input type="checkbox" name="cf_{{ $fName }}" value="1" data-custom-field="{{ $fName }}" {{ !empty($val) && $val != '0' ? 'checked' : '' }} {{ $isReadOnly ? 'disabled' : '' }} class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500">
                                            <span class="text-xs text-zinc-700 dark:text-zinc-300">Yes / Active</span>
                                        </label>
                                    @elseif(strtolower($cf->data_type) === 'number' || strtolower($cf->data_type) === 'currency')
                                        <input type="number" step="any" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                    @elseif(strtolower($cf->data_type) === 'date')
                                        <input type="date" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                    @elseif(strtolower($cf->data_type) === 'textarea')
                                        <textarea rows="2" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">{{ $val }}</textarea>
                                    @elseif(strtolower($cf->data_type) === 'maskedtext')
                                        <input type="password" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                    @else
                                        <input type="text" name="cf_{{ $fName }}" data-custom-field="{{ $fName }}" value="{{ $val }}" {{ $isReadOnly ? 'disabled readonly' : '' }} class="{{ $inputCommonClass }}">
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                {{-- Fallback: Core Identification & Qualification --}}
                <div>
                    <h4 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 uppercase tracking-wider mb-3 pb-1 border-b border-zinc-200 dark:border-zinc-800">
                        Core Identification
                    </h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">First Name</label>
                            <input type="text" name="first_name" value="{{ $lead['first_name'] ?? '' }}" required class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Last Name</label>
                            <input type="text" name="last_name" value="{{ $lead['last_name'] ?? '' }}" required class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Company Name</label>
                            <input type="text" name="company" value="{{ $lead['company'] ?? '' }}" required class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Job Title</label>
                            <input type="text" name="title" value="{{ $lead['title'] ?? '' }}" class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                            <input type="email" name="email" value="{{ $lead['email'] ?? '' }}" class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Phone Number</label>
                            <input type="text" name="phone" value="{{ $lead['phone'] ?? '' }}" class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100">
                        </div>
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
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Convert Lead: {{ $lead['name'] }}</h3>
            </div>
            <button
                type="button"
                onclick="window.closeDetailConvertModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
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
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                        >
                    </div>
                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-zinc-600 dark:text-zinc-400">Target Amount ($ USD)</label>
                        <input
                            type="number"
                            id="detail-input-opp-amount"
                            value="50000"
                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
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
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Change Lead Owner</h3>
            </div>
            <button
                type="button"
                onclick="window.closeDetailTransferModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
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
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-blue-500 focus:outline-none"
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

<!-- Clone Lead Modal -->
<div id="cloneLeadModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/40">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-zinc-100 dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 flex items-center justify-center">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Clone Lead: {{ $lead['name'] }}</h3>
            </div>
            <button
                type="button"
                onclick="window.closeCloneModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <form id="cloneLeadForm" class="p-6 space-y-4">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                This will create a new Lead duplicating the core parameters from <strong>{{ $lead['name'] }}</strong>.
            </p>

            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">First Name</label>
                    <input
                        type="text"
                        name="first_name"
                        value="{{ ($lead['first_name'] ?? '') . ' (Copy)' }}"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    >
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Last Name</label>
                    <input
                        type="text"
                        name="last_name"
                        value="{{ $lead['last_name'] ?? '' }}"
                        required
                        class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    >
                </div>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Company</label>
                <input
                    type="text"
                    name="company"
                    value="{{ $lead['company'] ?? '' }}"
                    required
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Email Address</label>
                <input
                    type="email"
                    name="email"
                    value="{{ $lead['email'] ?? '' }}"
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="window.closeCloneModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer inline-flex items-center gap-1.5"
                >
                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                    <span>Create Cloned Lead</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Direct Email Modal -->
<div id="quickEmailModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4 bg-zinc-950/60 backdrop-blur-xs overflow-y-auto">
    <div class="relative w-full max-w-lg bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-800 shadow-2xl overflow-hidden my-8">
        <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-200 dark:border-zinc-800 bg-indigo-50/40 dark:bg-indigo-950/30">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                    <i data-lucide="mail" class="w-4 h-4"></i>
                </div>
                <h3 class="text-sm font-bold text-zinc-950 dark:text-zinc-100">Direct Outreach: {{ $lead['name'] }}</h3>
            </div>
            <button
                type="button"
                onclick="window.closeQuickEmailModal()"
                class="p-1 rounded-lg text-zinc-400 hover:text-zinc-700 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors cursor-pointer"
            >
                ✕
            </button>
        </div>

        <form id="quickEmailForm" class="p-6 space-y-4">
            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Recipient Email</label>
                <input
                    type="email"
                    id="quick-email-to"
                    value="{{ $lead['email'] ?? '' }}"
                    readonly
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-400 font-medium"
                >
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Subject</label>
                <input
                    type="text"
                    id="quick-email-subject"
                    required
                    value="Following up from {{ config('app.name', 'DealSync CRM') }}"
                    class="w-full px-3 py-1.5 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                >
            </div>

            <div class="space-y-1">
                <label class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">Message</label>
                <textarea
                    id="quick-email-body"
                    rows="5"
                    required
                    class="w-full px-3 py-2 text-xs rounded-lg border border-zinc-300 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                    placeholder="Hi {{ $lead['first_name'] ?? $lead['name'] }},&#10;&#10;I wanted to follow up regarding your inquiry..."
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-zinc-200 dark:border-zinc-800">
                <button
                    type="button"
                    onclick="window.closeQuickEmailModal()"
                    class="px-3.5 py-1.5 text-xs font-semibold text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition-colors cursor-pointer"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-4 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition-colors cursor-pointer inline-flex items-center gap-1.5"
                >
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Send Email</span>
                </button>
            </div>
        </form>
    </div>
</div>
