<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispensary extends Model
{
    use HasFactory;
    protected $table = 'dispensaries';
	public function products() {
		return $this->hasMany(Product::class, "dispensary_id", 'id');
	}
    public function brand() {
        return $this->belongsTo(Brand::class, "brand_id", "id");
    }
	public function fetchDispensaries($request, $columns) {
        $query = Dispensary::where('status', '!=', 'delete');

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
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        if (isset($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
                $q->orWhere('location_id', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->order_column)) {
            $dispensaries = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $dispensaries = $query->orderBy('created_at', 'desc');
        }
        return $dispensaries;
	}
    public function getNameAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getPhoneCodeAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getPhoneNumberAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getAddressAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getLatAttribute($details)
    {
        $res = '';
        if (!empty($details)) {

            $res = trim($details);
        }
        return $res;
    }
    public function getLngAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = trim($details);
        }
        return $res;
    }
    public function getCityAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getStateAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getCountryAttribute($details)
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
    public function getDistanceAttribute($details)
    {
        $res = 0.00;
        if (!empty($details)) {
            $res = round($details, 2);
        }
        return $res;
    } 
    public function getLocationTimesAttribute($details)
    {
        return json_decode($details, true);
    }
    public function getLocationTimesWebsiteAttribute($details)
    {
        return json_decode($details, true);
    }   		
}
