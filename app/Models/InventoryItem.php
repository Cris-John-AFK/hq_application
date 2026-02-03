<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'name',
        'type',
        'quantity',
        'serial_number',
        'status',
        'last_audit_date',
        'description',
        'location'
    ];
}
