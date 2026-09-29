<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'is_active',
        'sort_order',
    ];
}
