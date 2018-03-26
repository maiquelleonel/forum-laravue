<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\DateFormat;
use App\User;
use App\Thread;

class Reply extends Model
{
    //use DateFormat;
    protected $fillable =[
        'highlighted',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function thread()
    {
        return $this->belongsTo(Thread::class, 'thread_id', 'id');
    }
}
