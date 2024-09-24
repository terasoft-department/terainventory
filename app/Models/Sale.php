<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $primaryKey = 'sale_id';

    protected $fillable = [
        'item_id',
        'amount_distributed',
        'total_amount',
        'payment_method',
        'customername',
        'phone_number',
        'sold_at',
    ];

  public function item()
{
    return $this->belongsTo(Item::class, 'item_id', 'item_id');
}

    // If you are using date attributes
    protected $dates = ['sold_at'];
}
