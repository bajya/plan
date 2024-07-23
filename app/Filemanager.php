<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filemanager extends Model
{
    use HasFactory;
        protected $table = 'filemanagers';

	public function fetchFilemanagers($request, $columns) {
		$query = Filemanager::where('status', '!=', 'delete');

		$Filemanagers = $query->orderBy('created_at', 'desc');
        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        if (isset($request->type)) {
            $query->where('type', $request->type);
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->order_column)) {
            $Filemanagers = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $Filemanagers = $query->orderBy('created_at', 'desc');
        }
		return $Filemanagers;
	}
    public function getNameAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getImageAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
}
