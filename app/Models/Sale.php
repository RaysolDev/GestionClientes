<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'sale_date',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
    ];

    /**
     * Get the client that owns the sale.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user that made the sale.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sale details for the sale.
     */
    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    /**
     * Get the inventory movements related to this sale.
     */
    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class, 'related_sale_id');
    }
}

