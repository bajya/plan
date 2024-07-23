<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    public $table = 'transactions';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'created_at',
        'updated_at',
        'status',
        'user_id',
        'transaction_type',
        'txn_id',
        'before_wallet_amount',
        'after_wallet_amount',
        'amount',
        'title',
        'message',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fetchTransactions($request, $columns) {
        $query = Transaction::where('user_id', '!=', 1);

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
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
