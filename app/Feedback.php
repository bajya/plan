<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    public $table = 'feedbacks';
    public function user() {
        return $this->belongsTo(User::class, "user_id", "id");
    }
    public function fetchFeedback($request, $columns) {
        $query = Feedback::where('status', '!=', 'delete');
        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->orWhere('smiley', 'like', '%' . $request->search . '%');
                $q->orWhere('category', 'like', '%' . $request->search . '%');
                $q->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        if (isset($request->order_column)) {
            $newss = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $newss = $query->orderBy('created_at', 'desc');
        }
        return $newss;
    }
}
