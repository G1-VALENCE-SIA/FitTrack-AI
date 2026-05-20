<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    
    protected $fillable = [
        'user_id', 'workout_id', 'date', 'duration', 'calories_burned'
    ];
}