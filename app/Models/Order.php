<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    const  CREATED_AT =  'created_at';
    const  UPDATED_AT  =  'completed_at';

    protected $fillable = ['customer', 'warehouse_id', 'status'];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
