<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'datetime', 'total'
    ];

    protected $hidden = ['deleted_at'];

    protected $dates = [
        'deleted_at'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function detail_transactions() {
        return $this->hasMany(DetailTransaction::class);
    }
}
