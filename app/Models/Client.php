<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'last_purchase_date',
        'total_spent',
    ];

    protected $casts = [
        'last_purchase_date' => 'date',
        'total_spent' => 'decimal:2',
    ];

    /**
     * Get the sales for the client.
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}

