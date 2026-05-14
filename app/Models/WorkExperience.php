<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperience extends Model
{
    protected $table = 'work_experiences';

    protected $fillable = [
        'user_id', 'company', 'position',
        'start_date', 'end_date', 'is_current',
        'description', 'sort_order',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];
}
