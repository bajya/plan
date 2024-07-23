<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CMS extends Model
{
    use HasFactory;
    public $table = 'cms';
    	public function child() {
		return $this->hasMany(CMS::class, "parent_id", "id");
	}

	public function fetchCMS($request, $columns) {
		$query = CMS::whereNull('parent_id')->where('status', 'active');
		if (isset($request->search)) {
			$query->where(function ($q) use ($request) {
				$q->orWhere('name', 'like', '%' . $request->search . '%');
			});
		}

		if (isset($request->order_column)) {
			$cms = $query->orderBy($columns[$request->order_column], $request->order_dir);
		} else {
			$cms = $query->orderBy('name', 'asc');
		}
		return $cms;
	}
}
