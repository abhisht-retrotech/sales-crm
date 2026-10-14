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
        'db_password' => 'encrypted',
        'is_active'   => 'boolean',
        'max_seats'   => 'integer',
    ];

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
