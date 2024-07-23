<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
	//protected $with = ['childCat'];

	public function parentCat() {
		return $this->belongsTo('App\Category', "parent_id", "id");
	}

	public function childCat() {
		return $this->hasMany('App\Category', "parent_id", "id");
	}
	public function childCatProduct() {
        return $this->hasMany('App\Product', "type_id", "id");
    }
	public function fetchCategories() {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete');
		}])->where('status', '!=', 'delete')->whereNull('parent_id');

		$categories = $query->orderBy('created_at', 'desc');

		return $categories;
	}
	public function fetchCategoriesAjax($request, $columns) {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete');
		}])->where('status', '!=', 'delete')->where('type', 'category');
		if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        if (isset($request->order_column)) {
            $categories = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
			$categories = $query->orderBy('order_no', 'asc');
		}

		return $categories;
	}
	public function fetchTypes() {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete');
		}])->where('status', '!=', 'delete')->whereNull('parent_id');

		$categories = $query->orderBy('name', 'asc');

		return $categories;
	}
	public function fetchTypesAjax($request, $columns) {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete');
		}])->where('status', '!=', 'delete')->where('type', 'type');
		if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->parent_id)) {
            $query->where('parent_id', $request->parent_id);
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }

        if (isset($request->order_column)) {
            $categories = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
			$categories = $query->orderBy('order_no', 'asc');
		}

		return $categories;
	}
	public function fetchStrains() {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete')->where('type', '!=', 'strain');
		}])->where('status', '!=', 'delete')->whereNull('parent_id');

		$categories = $query->orderBy('created_at', 'desc');

		return $categories;
	}
	public function fetchStrainsAjax($request, $columns) {
		$query = Category::with(['childCat' => function ($q) {
			$q->where('status', '!=', 'delete');
		}])->where('status', '!=', 'delete')->where('type', 'strain');

		if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        if (isset($request->order_column)) {
            $categories = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
			$categories = $query->orderBy('created_at', 'desc');
		}

		return $categories;
	}
}
