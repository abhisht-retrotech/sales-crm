<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Profile;
use App\Models\ProfilePermission;
use App\Models\GlobalPicklist;
use App\Models\TenantCurrency;
use App\Models\TenantSetting;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed initial foundational data for a tenant's isolated database.
     */
    public function run(): void
    {
        // 1. Roles Hierarchy
        $ceoRole = Role::firstOrCreate(
            ['name' => 'CEO'],
            ['description' => 'Chief Executive Officer']
        );

        $vpSales = Role::firstOrCreate(
            ['name' => 'VP of Sales'],
            [
                'description'         => 'Head of global sales organization',
                'reports_to_role_id'  => $ceoRole->id,
            ]
        );

        $salesManager = Role::firstOrCreate(
            ['name' => 'Sales Manager'],
            [
                'description'         => 'Team sales lead and pipeline controller',
                'reports_to_role_id'  => $vpSales->id,
            ]
        );

        $salesRep = Role::firstOrCreate(
            ['name' => 'Sales Representative'],
            [
                'description'         => 'Direct customer and lead relationship executive',
                'reports_to_role_id'  => $salesManager->id,
            ]
        );

        $procurementLead = Role::firstOrCreate(
            ['name' => 'Procurement Director'],
            [
                'description'         => 'Oversees purchase deals and supplier contracts',
                'reports_to_role_id'  => $ceoRole->id,
            ]
        );

        // 2. Profiles
        $adminProfile = Profile::firstOrCreate(
            ['name' => 'System Administrator'],
            [
                'description'         => 'Full access to all CRM objects, security, and administrative settings',
                'surprise_me_enabled' => true,
            ]
        );

        $standardProfile = Profile::firstOrCreate(
            ['name' => 'Standard User'],
            [
                'description'         => 'Standard CRM operational permissions',
                'surprise_me_enabled' => true,
            ]
        );

        $readOnlyProfile = Profile::firstOrCreate(
            ['name' => 'Read Only User'],
            [
                'description'         => 'View-only access across CRM modules',
                'surprise_me_enabled' => false,
            ]
        );

        // 3. Profile Permissions across Core Modules
        $modules = [
            'leads', 'accounts', 'contacts', 'purchase_deals', 
            'opportunities', 'shipments', 'payments', 'wires_received', 'tasks'
        ];

        foreach ($modules as $module) {
            ProfilePermission::updateOrCreate(
                ['profile_id' => $adminProfile->id, 'module' => $module],
                [
                    'can_create'   => true,
                    'can_read'     => true,
                    'can_update'   => true,
                    'can_delete'   => true,
                    'can_transfer' => true,
                    'record_scope' => 'All',
                ]
            );

            ProfilePermission::updateOrCreate(
                ['profile_id' => $standardProfile->id, 'module' => $module],
                [
                    'can_create'   => true,
                    'can_read'     => true,
                    'can_update'   => true,
                    'can_delete'   => false,
                    'can_transfer' => false,
                    'record_scope' => 'Team',
                ]
            );

            ProfilePermission::updateOrCreate(
                ['profile_id' => $readOnlyProfile->id, 'module' => $module],
                [
                    'can_create'   => false,
                    'can_read'     => true,
                    'can_update'   => false,
                    'can_delete'   => false,
                    'can_transfer' => false,
                    'record_scope' => 'All',
                ]
            );
        }

        // 4. Global Picklists
        $picklists = [
            [
                'name'        => 'Lead Statuses',
                'api_name'    => 'lead_statuses',
                'description' => 'Lifecycle statuses for inbound leads',
                'values_json' => ['New', 'Contacted', 'Qualified', 'Unqualified', 'Converted'],
            ],
            [
                'name'        => 'Opportunity Stages',
                'api_name'    => 'opportunity_stages',
                'description' => 'Sales funnel pipeline stages',
                'values_json' => ['Prospecting', 'Qualification', 'Needs Analysis', 'Proposal/Quote', 'Negotiation/Review', 'Closed Won', 'Closed Lost'],
            ],
            [
                'name'        => 'Purchase Deal Stages',
                'api_name'    => 'purchase_deal_stages',
                'description' => 'Procurement stages for supplier orders',
                'values_json' => ['Draft', 'PO Issued', 'Supplier Confirmed', 'In Production', 'Quality Check', 'Shipped', 'Delivered', 'Closed'],
            ],
            [
                'name'        => 'Shipment Statuses',
                'api_name'    => 'shipment_statuses',
                'description' => 'Logistics tracking states',
                'values_json' => ['Booked', 'Dispatched', 'In Transit', 'Customs Clearance', 'Delivered', 'Exception'],
            ],
            [
                'name'        => 'Payment Statuses',
                'api_name'    => 'payment_statuses',
                'description' => 'Financial invoice lifecycle stages',
                'values_json' => ['Draft', 'Issued', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'],
            ],
            [
                'name'        => 'Wire Reconciliation Statuses',
                'api_name'    => 'wire_statuses',
                'description' => 'Bank transfer reconciliation tracking',
                'values_json' => ['Pending', 'Reconciled', 'Disputed', 'Unmatched'],
            ],
        ];

        foreach ($picklists as $pl) {
            GlobalPicklist::updateOrCreate(
                ['api_name' => $pl['api_name']],
                [
                    'name'        => $pl['name'],
                    'description' => $pl['description'],
                    'values_json' => $pl['values_json'],
                ]
            );
        }

        // 5. Tenant Currencies
        $currencies = [
            ['currency_code' => 'USD', 'currency_symbol' => '$', 'currency_name' => 'US Dollar', 'exchange_rate' => 1.0, 'is_base' => true],
            ['currency_code' => 'EUR', 'currency_symbol' => '€', 'currency_name' => 'Euro', 'exchange_rate' => 0.92, 'is_base' => false],
            ['currency_code' => 'GBP', 'currency_symbol' => '£', 'currency_name' => 'British Pound', 'exchange_rate' => 0.79, 'is_base' => false],
            ['currency_code' => 'INR', 'currency_symbol' => '₹', 'currency_name' => 'Indian Rupee', 'exchange_rate' => 83.50, 'is_base' => false],
            ['currency_code' => 'AED', 'currency_symbol' => 'AED', 'currency_name' => 'UAE Dirham', 'exchange_rate' => 3.67, 'is_base' => false],
        ];

        foreach ($currencies as $curr) {
            TenantCurrency::updateOrCreate(
                ['currency_code' => $curr['currency_code']],
                $curr
            );
        }

        // 6. Tenant Settings
        TenantSetting::updateOrCreate(
            ['key' => 'system_preferences'],
            ['value' => json_encode([
                'default_currency'           => 'USD',
                'fiscal_year_start_month'    => 1,
                'working_days'               => 'Mon,Tue,Wed,Thu,Fri',
                'working_hours_start'        => '09:00',
                'working_hours_end'          => '18:00',
                'allow_self_password_change' => true,
                'session_timeout_minutes'    => 60,
                'max_failed_login_attempts'  => 5,
            ])]
        );
    }
}
