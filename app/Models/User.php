<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username', 'nickname', 'password', 'phone', 'email',
        'title', 'bio', 'avatar', 'location', 'google_id',
    ];

    protected $hidden = ['password', 'remember_token', 'google_id'];

    public function portfolios()
    {
        return $this->hasMany(Portfolio::class, 'user_id');
    }

    public function skills()
    {
        return $this->hasMany(Skill::class, 'user_id')->orderBy('sort_order');
    }

    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class, 'user_id')->orderBy('sort_order');
    }

    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class, 'user_id')->orderBy('sort_order');
    }
}
