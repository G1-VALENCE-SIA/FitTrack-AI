<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BmiRecord extends Model
{
    protected $table = 'bmi_records';
    
    protected $fillable = [
        'user_id', 'bmi_value', 'category'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}