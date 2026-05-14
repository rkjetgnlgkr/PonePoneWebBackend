<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LayoutConfig extends Model
{
    protected $table = 'layout_config';

    protected $fillable = ['user_id', 'theme_style'];
}
