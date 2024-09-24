<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $primaryKey = 'purchase_id';

    protected $fillable = [
        'item_name','itemcategory_id', 'user_id', 'quantity_purchased','price', 'purchase_date', 'status',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

      public function itemCategory()
    {
        return $this->belongsTo(ItemCategory::class, 'itemcategory_id', 'itemcategory_id');
    }

    public function item()
{
    return $this->belongsTo(Item::class, 'item_id', 'item_id');
}

}
