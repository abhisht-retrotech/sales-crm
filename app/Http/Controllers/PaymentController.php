<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CustomField;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\Profile;
use App\Models\PurchaseDeal;
use App\Models\Tenant;
use App\Models\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    const STAGES = ['Draft', 'Issued', 'Partially Paid', 'Paid', 'Overdue'];
    const CATEGORIES = ['Accounts Receivable', 'Accounts Payable'];

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
     * Ensure default dynamic custom fields exist for payments/invoices.
     */
    protected function ensureCustomFields(): void
    {
        try {
            if (CustomField::on('tenant')->whereIn('module', ['payments', 'invoices'])->count() > 0) {
                return;
            }

            $defaults = [
                [
                    'module' => 'payments',
                    'field_name' => 'cf_payment_method',
                    'field_label' => 'Preferred Payment Method',
                    'data_type' => 'picklist',
                    'options' => json_encode(['Bank Wire Transfer', 'ACH Direct Debit', 'Corporate Credit Card', 'Bank Check', 'Cryptocurrency Escrow']),
                    'is_required' => false,
                ],
                [
                    'module' => 'payments',
                    'field_name' => 'cf_tax_invoice_id',
                    'field_label' => 'Fiscal Tax Invoice Ref #',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                ],
                [
                    'module' => 'payments',
                    'field_name' => 'cf_billing_contact_name',
                    'field_label' => 'Billing Officer / AP Contact',
                    'data_type' => 'text',
                    'options' => null,
                    'is_required' => false,
                ],
                [
                    'module' => 'payments',
                    'field_name' => 'cf_payment_terms_clause',
                    'field_label' => 'Agreed Payment Terms Clause',
                    'data_type' => 'picklist',
                    'options' => json_encode(['Due Upon Receipt', 'Net 15 Days', 'Net 30 Days', 'Net 45 Days', 'Net 60 Days']),
                    'is_required' => false,
                ],
            ];

            foreach ($defaults as $cf) {
                CustomField::on('tenant')->create($cf);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Ensure realistic demo invoices and settlement ledger entries.
     */
    protected function ensureDemoPayments(): void
    {
        try {
            if (Payment::on('tenant')->count() > 0) {
                return;
            }

            $accounts = Account::on('tenant')->get()->keyBy('name');
            $user = TenantUser::on('tenant')->first();
            $userId = $user?->id;

            $demoInvoices = [
                [
                    'invoice_number' => 'INV-2026-849102',
                    'account_name' => 'Apex Technologies',
                    'type' => 'Accounts Receivable',
                    'amount' => 45000.00,
                    'paid_amount' => 25000.00,
                    'currency' => 'USD',
                    'status' => 'Partially Paid',
                    'issue_date' => date('Y-m-d', strtotime('-18 days')),
                    'due_date' => date('Y-m-d', strtotime('+12 days')),
                    'notes' => 'Q1 Enterprise Cloud Platform Migration & Dedicated Hosting.',
                    'custom_data' => [
                        'cf_payment_method' => 'Bank Wire Transfer',
                        'cf_tax_invoice_id' => 'TAX-US-2026-9921',
                        'cf_billing_contact_name' => 'Elena Rostova',
                        'cf_payment_terms_clause' => 'Net 30 Days',
                    ],
                ],
                [
                    'invoice_number' => 'INV-2026-910482',
                    'account_name' => 'Globex International',
                    'type' => 'Accounts Receivable',
                    'amount' => 84000.00,
                    'paid_amount' => 84000.00,
                    'currency' => 'USD',
                    'status' => 'Paid',
                    'issue_date' => date('Y-m-d', strtotime('-35 days')),
                    'due_date' => date('Y-m-d', strtotime('-5 days')),
                    'notes' => 'Full annual software licensing. Settle via Silicon Valley Bank wire #WT-88192.',
                    'custom_data' => [
                        'cf_payment_method' => 'Bank Wire Transfer',
                        'cf_tax_invoice_id' => 'TAX-US-2026-4410',
                        'cf_billing_contact_name' => 'Marcus Vance',
                        'cf_payment_terms_clause' => 'Net 30 Days',
                    ],
                ],
                [
                    'invoice_number' => 'INV-2026-339104',
                    'account_name' => 'Stark Industries',
                    'type' => 'Accounts Receivable',
                    'amount' => 62500.00,
                    'paid_amount' => 0.00,
                    'currency' => 'USD',
                    'status' => 'Overdue',
                    'issue_date' => date('Y-m-d', strtotime('-45 days')),
                    'due_date' => date('Y-m-d', strtotime('-15 days')),
                    'notes' => 'Awaiting corporate AP disbursement clearance. Automated dunning notices triggered.',
                    'custom_data' => [
                        'cf_payment_method' => 'ACH Direct Debit',
                        'cf_tax_invoice_id' => 'TAX-US-2026-1188',
                        'cf_billing_contact_name' => 'Pepper Potts',
                        'cf_payment_terms_clause' => 'Net 30 Days',
                    ],
                ],
                [
                    'invoice_number' => 'VEND-2026-102948',
                    'account_name' => 'Initech Supplies',
                    'type' => 'Accounts Payable',
                    'amount' => 28000.00,
                    'paid_amount' => 14000.00,
                    'currency' => 'USD',
                    'status' => 'Partially Paid',
                    'issue_date' => date('Y-m-d', strtotime('-10 days')),
                    'due_date' => date('Y-m-d', strtotime('+35 days')),
                    'notes' => 'Hardware rack servers and optical fiber equipment delivery batch #B4.',
                    'custom_data' => [
                        'cf_payment_method' => 'Bank Wire Transfer',
                        'cf_tax_invoice_id' => 'VEND-TAX-9902',
                        'cf_billing_contact_name' => 'Peter Gibbons',
                        'cf_payment_terms_clause' => 'Net 45 Days',
                    ],
                ],
                [
                    'invoice_number' => 'VEND-2026-558291',
                    'account_name' => 'Cyberdyne Systems',
                    'type' => 'Accounts Payable',
                    'amount' => 32500.00,
                    'paid_amount' => 0.00,
                    'currency' => 'USD',
                    'status' => 'Issued',
                    'issue_date' => date('Y-m-d', strtotime('-4 days')),
                    'due_date' => date('Y-m-d', strtotime('+41 days')),
                    'notes' => 'Vendor invoice for AI accelerator coprocessor chipsets.',
                    'custom_data' => [
                        'cf_payment_method' => 'Bank Wire Transfer',
                        'cf_tax_invoice_id' => 'VEND-TAX-4419',
                        'cf_billing_contact_name' => 'Miles Dyson',
                        'cf_payment_terms_clause' => 'Net 45 Days',
                    ],
                ],
                [
                    'invoice_number' => 'INV-2026-771829',
                    'account_name' => 'Wayne Enterprises',
                    'type' => 'Accounts Receivable',
                    'amount' => 58000.00,
                    'paid_amount' => 0.00,
                    'currency' => 'USD',
                    'status' => 'Issued',
                    'issue_date' => date('Y-m-d', strtotime('-2 days')),
                    'due_date' => date('Y-m-d', strtotime('+28 days')),
                    'notes' => 'Multi-subsidiary enterprise SLA & Security Audit engagement.',
                    'custom_data' => [
                        'cf_payment_method' => 'Corporate Credit Card',
                        'cf_tax_invoice_id' => 'TAX-US-2026-7782',
                        'cf_billing_contact_name' => 'Lucius Fox',
                        'cf_payment_terms_clause' => 'Net 30 Days',
                    ],
                ],
            ];

            foreach ($demoInvoices as $inv) {
                $acc = $accounts->get($inv['account_name']) ?? Account::on('tenant')->first();
                if (!$acc) continue;

                Payment::on('tenant')->create([
                    'account_id' => $acc->id,
                    'invoice_number' => $inv['invoice_number'],
                    'type' => $inv['type'],
                    'amount' => $inv['amount'],
                    'paid_amount' => $inv['paid_amount'],
                    'currency' => $inv['currency'],
                    'status' => $inv['status'],
                    'issue_date' => $inv['issue_date'],
                    'due_date' => $inv['due_date'],
                    'notes' => $inv['notes'],
                    'custom_data' => $inv['custom_data'],
                    'created_by' => $userId,
                ]);
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
                ->where('module', 'payments')
                ->pluck('permission', 'field_name')
                ->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Format payment record for JSON & Blade hydration.
     */
    protected function formatPayment(Payment $pay, array $fieldPermissions = []): array
    {
        $data = $pay->toArray();
        $customData = is_array($pay->custom_data) ? $pay->custom_data : (json_decode($pay->custom_data ?? '[]', true) ?: []);

        $data['account_name'] = $pay->account?->name ?? 'Unlinked Account';
        $data['opportunity_name'] = $pay->opportunity?->name ?? null;
        $data['po_number'] = $pay->purchaseDeal?->po_number ?? null;
        $data['purchase_deal_title'] = $pay->purchaseDeal?->title ?? null;
        $data['balance_due'] = max(0, (float)$pay->amount - (float)$pay->paid_amount);

        $data['amount_formatted'] = '$' . number_format((float)$pay->amount, 2);
        $data['paid_amount_formatted'] = '$' . number_format((float)$pay->paid_amount, 2);
        $data['balance_due_formatted'] = '$' . number_format($data['balance_due'], 2);
        $data['issue_date_formatted'] = $pay->issue_date ? date('M d, Y', strtotime($pay->issue_date)) : '—';
        $data['due_date_formatted'] = $pay->due_date ? date('M d, Y', strtotime($pay->due_date)) : '—';

        // Apply field-level permissions
        foreach ($customData as $cfKey => $cfVal) {
            $perm = $fieldPermissions[$cfKey] ?? 'Read/Write';
            if ($perm !== 'Hidden') {
                $data[$cfKey] = $cfVal;
            } else {
                $data['_hidden_' . $cfKey] = true;
            }
        }
        $data['custom_data'] = $customData;

        return $data;
    }

    /**
     * Display the main Invoicing & Settlement Ledger view.
     */
    public function index(?Request $request = null)
    {
        $this->ensureTenantConnection();
        $this->ensureCustomFields();
        $this->ensureDemoPayments();

        $profile = Profile::on('tenant')->first();
        $fieldPermissions = $this->getFieldPermissionsForProfile($profile);

        $customFields = CustomField::on('tenant')
            ->whereIn('module', ['payments', 'invoices'])
            ->get();

        $rawPayments = Payment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal'])
            ->orderBy('due_date', 'asc')
            ->get();

        $initialPayments = $rawPayments->map(fn($p) => $this->formatPayment($p, $fieldPermissions))->toArray();

        // Calculate KPI summaries
        $totalInvoiced = 0;
        $totalCollected = 0;
        $outstandingAR = 0;
        $outstandingAP = 0;

        foreach ($rawPayments as $p) {
            $amt = (float)$p->amount;
            $paid = (float)$p->paid_amount;
            $bal = max(0, $amt - $paid);

            $totalInvoiced += $amt;
            $totalCollected += $paid;

            if ($p->type === 'Accounts Receivable' || $p->type === 'Receivable') {
                $outstandingAR += $bal;
            } else {
                $outstandingAP += $bal;
            }
        }

        $kpis = [
            'totalInvoiced' => $totalInvoiced,
            'totalInvoicedFormatted' => '$' . number_format($totalInvoiced, 2),
            'totalCollected' => $totalCollected,
            'totalCollectedFormatted' => '$' . number_format($totalCollected, 2),
            'outstandingAR' => $outstandingAR,
            'outstandingARFormatted' => '$' . number_format($outstandingAR, 2),
            'outstandingAP' => $outstandingAP,
            'outstandingAPFormatted' => '$' . number_format($outstandingAP, 2),
            'totalCount' => $rawPayments->count(),
        ];

        $accounts = Account::on('tenant')->select('id', 'name', 'type')->orderBy('name')->get();
        $opportunities = Opportunity::on('tenant')->select('id', 'name', 'amount', 'account_id')->orderBy('name')->get();
        $purchaseDeals = PurchaseDeal::on('tenant')->select('id', 'po_number', 'title', 'amount', 'account_id')->orderBy('po_number')->get();

        return view('payments', [
            'initialPayments' => $initialPayments,
            'kpis' => $kpis,
            'customFields' => $customFields,
            'fieldPermissions' => $fieldPermissions,
            'accounts' => $accounts,
            'opportunities' => $opportunities,
            'purchaseDeals' => $purchaseDeals,
            'stages' => self::STAGES,
            'categories' => self::CATEGORIES,
            'currentUserProfile' => $profile?->name ?? 'Administrator',
        ]);
    }

    /**
     * Dedicated Invoice detail view.
     */
    public function show($id)
    {
        $this->ensureTenantConnection();
        $pay = Payment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'wires'])
            ->findOrFail($id);

        return $this->apiShow($id);
    }

    // ==========================================
    // API METHODS
    // ==========================================

    /**
     * API: List payments with filtering.
     */
    public function apiList(Request $request)
    {
        $this->ensureTenantConnection();
        $query = Payment::on('tenant')->with(['account', 'opportunity', 'purchaseDeal']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($w) use ($q) {
                $w->where('invoice_number', 'like', "%{$q}%")
                  ->orWhere('notes', 'like', "%{$q}%")
                  ->orWhereHas('account', function ($aw) use ($q) {
                      $aw->where('name', 'like', "%{$q}%");
                  });
            });
        }

        $payments = $query->orderBy('due_date', 'asc')->get()->map(fn($p) => $this->formatPayment($p));

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * API: Show single payment invoice.
     */
    public function apiShow($id)
    {
        $this->ensureTenantConnection();
        $pay = Payment::on('tenant')
            ->with(['account', 'opportunity', 'purchaseDeal', 'wires'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatPayment($pay),
        ]);
    }

    /**
     * API: Store new payment invoice.
     */
    public function apiStore(Request $request)
    {
        $this->ensureTenantConnection();

        $validated = $request->validate([
            'account_id' => 'required|exists:tenant.accounts,id',
            'opportunity_id' => 'nullable|exists:tenant.opportunities,id',
            'purchase_deal_id' => 'nullable|exists:tenant.purchase_deals,id',
            'invoice_number' => 'nullable|string|max:80',
            'type' => 'required|in:Accounts Receivable,Accounts Payable,Receivable,Payable',
            'amount' => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'required|in:Draft,Issued,Partially Paid,Paid,Overdue,Cancelled',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'custom_data' => 'nullable|array',
        ]);

        if (empty($validated['invoice_number'])) {
            $prefix = ($validated['type'] === 'Accounts Payable' || $validated['type'] === 'Payable') ? 'VEND' : 'INV';
            $validated['invoice_number'] = sprintf('%s-%s-%06d', $prefix, date('Y'), rand(100000, 999999));
        }

        $user = TenantUser::on('tenant')->first();
        $validated['created_by'] = $user?->id;
        $validated['paid_amount'] = $validated['paid_amount'] ?? 0;
        $validated['currency'] = $validated['currency'] ?? 'USD';

        $payment = Payment::on('tenant')->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice record created successfully!',
            'data' => $this->formatPayment($payment->fresh(['account', 'opportunity', 'purchaseDeal'])),
        ]);
    }

    /**
     * API: Update payment invoice.
     */
    public function apiUpdate(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $payment = Payment::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'account_id' => 'sometimes|required|exists:tenant.accounts,id',
            'opportunity_id' => 'nullable|exists:tenant.opportunities,id',
            'purchase_deal_id' => 'nullable|exists:tenant.purchase_deals,id',
            'invoice_number' => 'sometimes|required|string|max:80',
            'type' => 'sometimes|required|in:Accounts Receivable,Accounts Payable,Receivable,Payable',
            'amount' => 'sometimes|required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:10',
            'status' => 'sometimes|required|in:Draft,Issued,Partially Paid,Paid,Overdue,Cancelled',
            'issue_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'custom_data' => 'nullable|array',
        ]);

        $payment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully!',
            'data' => $this->formatPayment($payment->fresh(['account', 'opportunity', 'purchaseDeal'])),
        ]);
    }

    /**
     * API: Update settlement status directly.
     */
    public function apiUpdateStatus(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $payment = Payment::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:Draft,Issued,Partially Paid,Paid,Overdue,Cancelled',
        ]);

        $payment->status = $validated['status'];
        if ($validated['status'] === 'Paid') {
            $payment->paid_amount = $payment->amount;
        }

        $payment->save();

        return response()->json([
            'success' => true,
            'message' => "Settlement status updated to {$validated['status']}",
            'data' => $this->formatPayment($payment->fresh(['account', 'opportunity', 'purchaseDeal'])),
        ]);
    }

    /**
     * API: Record a settlement payment on an invoice.
     */
    public function apiRecordPayment(Request $request, $id)
    {
        $this->ensureTenantConnection();
        $payment = Payment::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $addAmount = (float)$validated['paymentAmount'];
        $newPaid = (float)$payment->paid_amount + $addAmount;

        $payment->paid_amount = $newPaid;
        if ($newPaid >= (float)$payment->amount) {
            $payment->status = 'Paid';
        } else {
            $payment->status = 'Partially Paid';
        }

        if (!empty($validated['notes'])) {
            $today = date('Y-m-d');
            $recordNote = "Settled \${$addAmount} on {$today}: {$validated['notes']}";
            $payment->notes = $payment->notes ? ($payment->notes . "\n" . $recordNote) : $recordNote;
        }

        $payment->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment settlement recorded successfully!',
            'data' => $this->formatPayment($payment->fresh(['account', 'opportunity', 'purchaseDeal'])),
        ]);
    }

    /**
     * API: Delete payment invoice.
     */
    public function apiDestroy($id)
    {
        $this->ensureTenantConnection();
        $payment = Payment::on('tenant')->findOrFail($id);
        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice record deleted from ledger.',
        ]);
    }
}
