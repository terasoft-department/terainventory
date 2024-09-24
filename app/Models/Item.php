<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'item_name',
        'itemcategory_id',
         'quantity',
          'price',
           'status',
           'item_img',
           'distribution',
           'amount_distributed',
           'total_amount',
           'payment_method',
           'customername',
          'phone_number',
            'status',
            'sold_at'
    ];

    public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'itemcategory_id', 'itemcategory_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'item_id', 'item_id');
    }

     public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'store_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}


