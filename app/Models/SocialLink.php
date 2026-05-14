<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    protected $table = 'social_links';

    public $timestamps = false;

    protected $fillable = ['user_id', 'platform', 'url', 'sort_order'];
}
