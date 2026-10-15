<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    protected $connection = 'mysql'; // Central database connection
    protected $table = 'tenants';

    protected $guarded = [];

    protected $casts = [
        'is_active'   => 'boolean',
        'max_seats'   => 'integer',
    ];

    public function getDbPasswordAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setDbPasswordAttribute($value)
    {
        $this->attributes['db_password'] = !empty($value) ? encrypt($value) : null;
    }

    /**
     * Connect application to this tenant's isolated database.
     */
    public function connect(): void
    {
        config([
            'database.connections.tenant.host'     => $this->db_host ?: env('DB_HOST', '127.0.0.1'),
            'database.connections.tenant.port'     => $this->db_port ?: env('DB_PORT', 3306),
            'database.connections.tenant.database' => $this->db_name,
            'database.connections.tenant.username' => $this->db_username ?: env('DB_USERNAME', 'root'),
            'database.connections.tenant.password' => $this->db_password ?? env('DB_PASSWORD', ''),
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        app()->instance('currentTenant', $this);
    }
}
