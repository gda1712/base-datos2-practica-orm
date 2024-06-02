<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'quantity',
        'last_movement_id'
    ];

    public function movements()
    {
        return $this->hasMany(Movement::class, 'last_movement_id');
    }
}
