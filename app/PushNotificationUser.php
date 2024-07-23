<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotificationUser extends Model
{
    use HasFactory;
    public $table = 'push_user';
    protected $fillable = [
        'user_id',
        'push_id',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
