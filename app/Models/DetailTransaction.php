<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id', 'product_id', 'qty'
    ];

    protected $hidden = ['deleted_at'];

    protected $dates = [
        'deleted_at'
    ];

    public function transaction() {
        return $this->belongsTo(Transaction::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
    


}
