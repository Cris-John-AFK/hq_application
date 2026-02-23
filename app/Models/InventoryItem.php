<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = [
        'department',
        'location',
        'name',
        'type',
        'brand',
        'model_name',
        'color',
        'serial_number',
        'asset_tag',
        'quantity',
        'status',
        'last_audit_date',
    ];
}
