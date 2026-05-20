<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherLog extends Model
{
    protected $table = 'weather_logs';
    protected $fillable = [
        'user_id', 'city', 'temperature', 'weather_condition'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}