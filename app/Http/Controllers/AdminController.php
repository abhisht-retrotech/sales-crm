<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.roles_permissions');
    }

    public function tenantStudio()
    {
        return view('admin.tenant_studio');
    }

    public function companyProfile()
    {
        return view('admin.company_profile');
    }

    public function usersDirectory()
    {
        return view('admin.users_directory');
    }

    public function dashboardStudio()
    {
        return view('admin.dashboard_studio');
    }

    public function rolesPermissions()
    {
        return view('admin.roles_permissions');
    }

    public function recordRights()
    {
        return view('admin.record_rights');
    }

    public function fieldRights()
    {
        return view('admin.field_rights');
    }

    public function sharingRules()
    {
        return view('admin.sharing_rules');
    }

    public function securityGovernance()
    {
        return view('admin.security_governance');
    }

    public function approvalRules()
    {
        return view('admin.approval_rules');
    }

    public function duplicateRules()
    {
        return view('admin.duplicate_rules');
    }

    public function processStudio()
    {
        return view('admin.process_studio');
    }

    public function notificationRules()
    {
        return view('admin.notification_rules');
    }

    public function objectFields()
    {
        return view('admin.object_fields');
    }

    public function customObjects()
    {
        return view('admin.custom_objects');
    }

    public function pageLayouts()
    {
        return view('admin.page_layouts');
    }

    public function dataStudio()
    {
        return view('admin.data_studio');
    }

    public function auditLogs()
    {
        return view('admin.audit_logs');
    }
}
