<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificationuser extends Model
{
    use HasFactory;
    protected $table = 'notification_users';
    protected $fillable=['sender_id','receiver_id','title','description','notification_type','is_read','status','created_at','updated_at'];
}
