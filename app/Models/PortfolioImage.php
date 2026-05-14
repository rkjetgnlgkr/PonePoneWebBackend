<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PortfolioImage extends Model
{
    protected $table = 'portfolio_images';

    public $timestamps = false;

    protected $fillable = ['portfolio_id', 'image_path'];
}
