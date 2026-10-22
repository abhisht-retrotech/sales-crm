<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CustomField;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\Tenant;
use App\Models\TenantUser;
use App\Models\WireReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WireController extends Controller
{
    const STAGES = ['Pending Verification', 'Matched/Reconciled', 'Discrepancy'];

    /**
     * Ensure active tenant database connection.
     */
    protected function ensureTenantConnection()
    {
        $tenant = app()->bound('currentTenant') ? app('currentTenant') : Tenant::on('mysql')->first();
        if ($tenant && config('database.connections.tenant.database') !== $tenant->db_name) {
            config(['database.connections.tenant.database' => $tenant->db_name ?: 'crm_retrotech_nna59']);
            DB::purge('tenant');
        }
        return $tenant;
    }

    /**
     * Ensure realistic demo wire receipts exist in tenant database.
     */
    protected function ensureDemoWires(): void
    {
        try {
            if (WireReceived::on('tenant')->count() > 0) {
                return;
            }

            $accounts = Account::on('tenant')->get()->keyBy('name');
            $user = TenantUser::on('tenant')->first();
            $userId = $user?->id;

            $skyline = $accounts->get('Skyline Cloud Systems LLC') ?? $accounts->first();
            $apex = $accounts->get('Apex Technologies') ?? $accounts->skip(1)->first() ?? $skyline;
            $globex = $accounts->get('Globex International') ?? $accounts->skip(2)->first() ?? $apex;

            $inv1 = Payment::on('tenant')->where('account_id', $skyline?->id)->first();

            $demoWires = [
                [
                    'utr_reference' => 'UTR9982410294',
                    'remitter_name' => 'Skyline Cloud Systems LLC',
                    'sending_bank' => 'JPMorgan Chase NY (CHASUS33)',
                    'receiving_bank_account' => 'CITI-US-MAIN-9482',
                    'amount' => 30000.00,
                    'currency' => 'USD',
                    'date_received' => date('Y-m-d', strtotime('-4 days')),
                    'status' => 'Matched/Reconciled',
                    'reconciled_account_id' => $skyline?->id,
                    'reconciled_payment_id' => $inv1?->id,
                    'reconciled_by' => $userId,
                    'reconciled_at' => now()->subDays(4),
                    'reconciliation_notes' => 'Automatic match with Q1 enterprise infrastructure billing INV-2026-849102.',
                ],
                [
                    'utr_reference' => 'UTR4491029381',
                    'remitter_name' => 'Skyline Cloud Systems LLC',
                    'sending_bank' => 'JPMorgan Chase NY (CHASUS33)',
                    'receiving_bank_account' => 'CITI-US-MAIN-9482',
                    'amount' => 38000.00,
                    'currency' => 'USD',
                    'date_received' => date('Y-m-d', strtotime('-1 day')),
                    'status' => 'Pending Verification',
                    'reconciled_account_id' => $skyline?->id,
                    'reconciled_payment_id' => null,
                    'reconciliation_notes' => 'Wire advice received from treasury. Open invoice match pending controller signoff.',
                ],
                [
                    'utr_reference' => 'UTR7710294822',
                    'remitter_name' => 'Apex Holdings Group',
                    'sending_bank' => 'Bank of America Charlotte (BOFAUS3N)',
                    'receiving_bank_account' => 'CITI-US-MAIN-9482',
                    'amount' => 15000.00,
                    'currency' => 'USD',
                    'date_received' => date('Y-m-d'),
                    'status' => 'Pending Verification',
                    'reconciled_account_id' => $apex?->id,
                    'reconciled_payment_id' => null,
                    'reconciliation_notes' => 'Direct ACH Wire deposit. Pending matching against open purchase order advance milestone.',
                ],
                [
                    'utr_reference' => 'UTR1102948571',
                    'remitter_name' => 'Stark Global Capital Partners',
                    'sending_bank' => 'Wells Fargo SF (WFBIUS6S)',
                    'receiving_bank_account' => 'CITI-US-MAIN-9482',
                    'amount' => 8500.00,
                    'currency' => 'USD',
                    'date_received' => date('Y-m-d', strtotime('-6 days')),
                    'status' => 'Discrepancy',
                    'reconciled_account_id' => null,
                    'reconciled_payment_id' => null,
                    'reconciliation_notes' => 'Unallocated remittance. Remitter name does not match current registered account tax ID. Awaiting client bank clarification.',
                ],
            ];

            foreach ($demoWires as $w) {
                WireReceived::on('tenant')->create($w);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Resolve permissions for current profile.
     */
    protected function getFieldPermissionsForProfile(?Profile $profile): array
    {
        if (!$profile) {
            return [];
        }

        try {
            return DB::connection('tenant')
                ->table('field_permissions')
                ->where('profile_id', $profile->id)
                ->where('module', 'wires')
                ->pluck('permission', 'field_name')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Format wire record for JSON & Blade hydration.
     */
    protected function formatWire(WireReceived $wire, array $fieldPermissions = []): array
    {
        $data = $wire->toArray();

        $data['account_name'] = $wire->account?->name ?? 'Unallocated / Unmatched';
        $data['account_type'] = $wire->account?->type ?? 'Organization';
        $data['invoice_number'] = $wire->payment?->invoice_number ?? null;
        $data['opportunity_name'] = $wire->opportunity?->name ?? null;
        $data['reconciled_user_name'] = $wire->reconciledUser?->name ?? null;

        $data['amount_formatted'] = '$' . number_format((float)$wire->amount, 2);
        $data['date_received_formatted'] = $wire->date_received ? date('M d, Y', strtotime($wire->date_received)) : '—';
        $data['reconciled_at_formatted'] = $wire->reconciled_at ? date('M d, Y H:i', strtotime($wire->reconciled_at)) : null;

        return $data;
    }

    /**
     * Main Blade View for Wire Received Workbench.
     */
    public function index()
    {
        $this->ensureTenantConnection();
        $this->ensureDemoWires();

        $profile = Profile::on('tenant')->first();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $rawWires = WireReceived::on('tenant')
            ->with(['account', 'payment', 'opportunity', 'reconciledUser'])
            ->orderBy('date_received', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $initialWires = $rawWires->map(fn($w) => $this->formatWire($w, $fieldPermissions))->toArray();

        // Calculate Top KPI summary cards
        $totalWiresReceived = 0;
        $reconciledFunds = 0;
        $pendingVerification = 0;
        $discrepancyFunds = 0;

        foreach ($rawWires as $w) {
            $amt = (float)$w->amount;
            $totalWiresReceived += $amt;

            if ($w->status === 'Matched/Reconciled' || $w->status === 'Reconciled') {
                $reconciledFunds += $amt;
            } elseif ($w->status === 'Discrepancy' || $w->status === 'Disputed') {
                $discrepancyFunds += $amt;
            } else {
                $pendingVerification += $amt;
            }
        }

        $kpis = [
            'totalWiresReceived' => $totalWiresReceived,
            'totalWiresReceivedFormatted' => '$' . number_format($totalWiresReceived, 2),
            'reconciledFunds' => $reconciledFunds,
            'reconciledFundsFormatted' => '$' . number_format($reconciledFunds, 2),
            'pendingVerification' => $pendingVerification,
            'pendingVerificationFormatted' => '$' . number_format($pendingVerification, 2),
            'discrepancyFunds' => $discrepancyFunds,
            'discrepancyFundsFormatted' => '$' . number_format($discrepancyFunds, 2),
            'totalCount' => $rawWires->count(),
        ];

        $accounts = Account::on('tenant')
            ->select('id', 'name', 'type')
            ->orderBy('name')
            ->get();

        $unpaidPayments = Payment::on('tenant')
            ->where('status', '!=', 'Paid')
            ->with('account')
            ->select('id', 'invoice_number', 'account_id', 'amount', 'paid_amount', 'currency', 'status')
            ->orderBy('due_date', 'asc')
            ->get();

        return view('wires', [
            'initialWires' => $initialWires,
            'kpis' => $kpis,
            'accounts' => $accounts,
            'unpaidPayments' => $unpaidPayments,
            'stages' => self::STAGES,
            'fieldPermissions' => $fieldPermissions,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * Single wire show.
     */
    public function show($id)
    {
        return $this->apiShow($id);
    }

    // ==========================================
    // API METHODS
    // ==========================================

    /**
     * API: List wires with search and status filtering.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $query = WireReceived::on('tenant')->with(['account', 'payment', 'opportunity', 'reconciledUser']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->filled('account_id')) {
            $query->where('reconciled_account_id', $request->account_id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($w) use ($q) {
                $w->where('utr_reference', 'like', "%{$q}%")
                  ->orWhere('remitter_name', 'like', "%{$q}%")
                  ->orWhere('sending_bank', 'like', "%{$q}%")
                  ->orWhere('reconciliation_notes', 'like', "%{$q}%")
                  ->orWhereHas('account', function ($aw) use ($q) {
                      $aw->where('name', 'like', "%{$q}%");
                  });
            });
        }

        $wires = $query->orderBy('date_received', 'desc')->get()->map(fn($w) => $this->formatWire($w));

        return response()->json([
            'success' => true,
            'data' => $wires,
        ]);
    }

    /**
     * API: Show single wire.
     */
    public function apiShow($id)
    {
        $this->ensureTenantConnection();
        $wire = WireReceived::on('tenant')
            ->with(['account', 'payment', 'opportunity', 'reconciledUser'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatWire($wire),
        ]);
    }

    /**
     * API: Store incoming bank wire intake.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();

        $validated = $request->validate([
            'utr_reference' => 'nullable|string|max:100',
            'remitter_name' => 'required|string|max:150',
            'sending_bank' => 'required|string|max:120',
            'receiving_bank_account' => 'nullable|string|max:80',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:10',
            'date_received' => 'required|date',
            'status' => 'required|in:Pending Verification,Matched/Reconciled,Discrepancy,Pending,Reconciled',
            'reconciled_account_id' => 'nullable|exists:tenant.accounts,id',
            'reconciled_payment_id' => 'nullable|exists:tenant.payments,id',
            'reconciliation_notes' => 'nullable|string',
        ]);

        if (empty($validated['utr_reference'])) {
            $validated['utr_reference'] = sprintf('UTR%s%06d', date('Ymd'), rand(1000, 9999));
        }
        $validated['receiving_bank_account'] = $validated['receiving_bank_account'] ?? 'CITI-US-MAIN-9482';
        $validated['currency'] = $validated['currency'] ?? 'USD';

        $user = TenantUser::on('tenant')->first();
        $validated['created_by'] = $user?->id;

        if ($validated['status'] === 'Matched/Reconciled' || $validated['status'] === 'Reconciled') {
            $validated['reconciled_by'] = $user?->id;
            $validated['reconciled_at'] = now();
        }

        $wire = WireReceived::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Wire intake logged successfully into workbench!',
            'data' => $this->formatWire($wire->fresh(['account', 'payment', 'opportunity', 'reconciledUser'])),
        ]);
    }

    /**
     * API: Update wire details.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $wire = WireReceived::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'utr_reference' => 'sometimes|required|string|max:100',
            'remitter_name' => 'sometimes|required|string|max:150',
            'sending_bank' => 'sometimes|required|string|max:120',
            'receiving_bank_account' => 'nullable|string|max:80',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'currency' => 'nullable|string|max:10',
            'date_received' => 'sometimes|required|date',
            'status' => 'sometimes|required|in:Pending Verification,Matched/Reconciled,Discrepancy,Pending,Reconciled',
            'reconciled_account_id' => 'nullable|exists:tenant.accounts,id',
            'reconciled_payment_id' => 'nullable|exists:tenant.payments,id',
            'reconciliation_notes' => 'nullable|string',
        ]);

        $wire->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Wire record updated successfully!',
            'data' => $this->formatWire($wire->fresh(['account', 'payment', 'opportunity', 'reconciledUser'])),
        ]);
    }

    /**
     * API: Update wire status directly (e.g. Kanban Drag & Drop).
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $wire = WireReceived::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Pending Verification,Matched/Reconciled,Discrepancy,Pending,Reconciled',
        ]);

        $wire->status = $validated['status'];
        if ($validated['status'] === 'Matched/Reconciled' || $validated['status'] === 'Reconciled') {
            $user = TenantUser::on('tenant')->first();
            $wire->reconciled_by = $user?->id;
            $wire->reconciled_at = now();
        }

        $wire->save();

        return response()->json([
            'success' => true,
            'message' => "Wire status updated to {$validated['status']}",
            'data' => $this->formatWire($wire->fresh(['account', 'payment', 'opportunity', 'reconciledUser'])),
        ]);
    }

    /**
     * API: Reconcile wire directly with an account and invoice.
     */
    public function apiReconcile(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $wire = WireReceived::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'accountId' => 'required|exists:tenant.accounts,id',
            'paymentId' => 'nullable|exists:tenant.payments,id',
            'notes' => 'nullable|string',
        ]);

        $user = TenantUser::on('tenant')->first();
        $wire->reconciled_account_id = $validated['accountId'];
        $wire->status = 'Matched/Reconciled';
        $wire->reconciled_by = $user?->id;
        $wire->reconciled_at = now();

        if (!empty($validated['notes'])) {
            $wire->reconciliation_notes = $validated['notes'];
        }

        // If invoice is attached, settle invoice balance
        if (!empty($validated['paymentId'])) {
            $wire->reconciled_payment_id = $validated['paymentId'];
            $payment = Payment::on('tenant')->find($validated['paymentId']);
            if ($payment) {
                $wireAmt = (float)$wire->amount;
                $newPaid = (float)$payment->paid_amount + $wireAmt;
                $payment->paid_amount = $newPaid;
                if ($newPaid >= (float)$payment->amount) {
                    $payment->status = 'Paid';
                } else {
                    $payment->status = 'Partially Paid';
                }
                $today = date('Y-m-d');
                $note = "Settled \${$wireAmt} via Bank Wire {$wire->utr_reference} on {$today}";
                $payment->notes = $payment->notes ? ($payment->notes . "\n" . $note) : $note;
                $payment->save();
            }
        }

        $wire->save();

        return response()->json([
            'success' => true,
            'message' => "Wire {$wire->utr_reference} successfully reconciled and allocated!",
            'data' => $this->formatWire($wire->fresh(['account', 'payment', 'opportunity', 'reconciledUser'])),
        ]);
    }

    /**
     * API: Delete incoming wire.
     */
    public function apiDestroy($id)
    {
        $this->ensureTenantConnection();
        $wire = WireReceived::on('tenant')->findOrFail($id);
        $wire->delete();

        return response()->json([
            'success' => true,
            'message' => 'Wire intake record removed from workbench.',
        ]);
    }
}
