<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $table = 'exercises';

    protected $fillable = [
        'api_exercise_id',
        'name',
        'body_part',
        'muscle_group',
        'equipment',
        'instructions'
    ];
}
