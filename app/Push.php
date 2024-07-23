<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Push extends Model
{
    use HasFactory;
    public $table = 'pushs';
    public $with = 'push_user';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'status',
        'sender_id',
        'title',
        'description',
        'is_send',
    ];


    public function push_user()
    {
        return $this->hasMany(PushNotificationUser::class, 'push_id', 'id');
    }
    public function fetchPushs($request, $columns) {
        $query = new Push;

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
                $q->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->order_column)) {
            $users = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $users = $query->orderBy('created_at', 'desc');
        }
        return $users;
    }
}
