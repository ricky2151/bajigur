<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'price', 'category_id', 'stock', 'points_earned'
    ];

    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    protected $dates = [
        'deleted_at'
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function detail_transactions() {
        return $this->hasMany(DetailTransaction::class);
    }
}
