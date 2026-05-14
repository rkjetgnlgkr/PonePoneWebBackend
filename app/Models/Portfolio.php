<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    protected $table = 'portfolios';

    protected $fillable = ['user_id', 'name', 'description', 'url'];

    public function images()
    {
        return $this->hasMany(PortfolioImage::class, 'portfolio_id');
    }
}
