<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyProfile extends Model
{
    protected $connection = 'tenant';
    protected $table = 'company_profile';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'allow_self_password_change' => 'boolean',
            'smtp_secure'                => 'boolean',
            'enforce_legal_footer'       => 'boolean',
            'smtp_port'                  => 'integer',
            'fiscal_year_start_month'    => 'integer',
            'max_seats'                  => 'integer',
            'max_storage_mb'             => 'integer',
            'max_records'                => 'integer',
            'session_timeout_minutes'    => 'integer',
            'password_min_length'        => 'integer',
            'password_require_special'   => 'boolean',
            'password_require_numbers'   => 'boolean',
            'password_require_uppercase' => 'boolean',
            'password_expiry_days'       => 'integer',
            'max_failed_login_attempts'  => 'integer',
            'lockout_duration_minutes'   => 'integer',
            'smtp_last_tested_at'        => 'datetime',
        ];
    }
}
