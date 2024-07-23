<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strain extends Model
{
    use HasFactory;
    protected $table = 'strains';
	
	public function dispensary() {
		return $this->belongsTo(Dispensary::class, 'dispensary_id');
	}
	public function brand() {
		return $this->belongsTo(Brand::class, 'brand_id');
	}	
	public function fetchStrains() {
		$query = Strain::where('status', '!=', 'delete');
		
			$Strains = $query->orderBy('name', 'asc');
		

		return $Strains;
	}
	public function fetchStrains1($request, $columns) {
		$query = Strain::where('status', '!=', 'delete')->groupBy('name');
		if (isset($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }
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
        if (isset($request->dispensary_id)) {
            $query->where('dispensary_id', $request->dispensary_id);
        }
        if (isset($request->order_column)) {
			$Strains = $query->orderBy($columns[$request->order_column], $request->order_dir);
		} else {
			$Strains = $query->orderBy('order_no', 'asc');
		}

		return $Strains;
	}
}
