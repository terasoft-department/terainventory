<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    use HasFactory;

    protected $primaryKey = 'itemcategory_id';

    protected $fillable = [
        'item_category',
        'description',
    ];


// Define the inverse relationship
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'itemcategory_id', 'itemcategory_id');
    }

      public function items()
    {
        return $this->hasMany(Item::class, 'itemcategory_id', 'itemcategory_id');
    }
}
