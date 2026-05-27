<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditArchive extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'record_id',
        'old_data',
        'new_data',
        'description'
    ];
}
