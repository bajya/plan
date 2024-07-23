<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;
    //protected $with = ['childCat'];
    protected $table = 'companies';
    public function childCat() {
        return $this->hasMany('App\Dispensary', "brand_id", "id");
    }
    public function fetchAdminBrands() {
        $query = Brand::with(['childCat' => function ($q) {
            $q->where('status', 'active');
        }])->where('status',  'active')->orderBy('name', 'asc');

        $categories = $query->orderBy('created_at', 'desc');

        return $categories;
    }
	public function fetchBrands($request, $columns) {
		$query = Brand::where('status', '!=', 'delete');
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
            $Brands = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
		  $Brands = $query->orderBy('created_at', 'desc');
        }

		return $Brands;
	}
    public function getNameAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getDescriptionAttribute($details)
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
    public function getStateIdAttribute($details)
    {
       
        $res = array();
        if (!empty($details)) {
            $res = explode(",",$details);
        }
        return $res;
    }
}
