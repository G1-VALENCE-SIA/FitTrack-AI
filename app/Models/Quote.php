<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $table = 'quotes';
    
    protected $fillable = [
        'quote', 'author', 'date'
    ];
}