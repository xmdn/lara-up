<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Experience extends Model
{
    protected $guarded = [];
    protected $hidden = ['id', 'user_id'];

    protected $casts = [
        'user_id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public $timestamps = false;
}
