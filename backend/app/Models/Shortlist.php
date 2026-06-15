<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shortlist extends Model
{
    protected $fillable = ['video_id'];

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
