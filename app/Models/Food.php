<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    protected $table = 'foods';

    protected $fillable = [
        'api_food_id', 'name', 'calories', 'protein', 'carbs', 'fats'
    ];
}