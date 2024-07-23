<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Library\Helper;
class Product extends Model {
	use HasFactory;
	protected $table = 'products';
	public function category() {
		return $this->belongsTo(Category::class, 'parent_id');
	}
	public function subcategory() {
		return $this->belongsTo(Category::class, 'sub_parent_id')->where('categories.parent_id', '!=', null);
	}
	public function type() {
		//return $this->belongsTo(ProductType::class, 'type_id');
        return $this->belongsTo(Category::class, 'type_id');
	}
	public function strain() {
		//return $this->belongsTo(Strain::class, 'strain_id')->where('strains.parent_id', null);
        return $this->belongsTo(Strain::class, 'strain_id');
	}
	public function substrain() {
		return $this->belongsTo(Strain::class, 'sub_strain_id')->where('strains.parent_id', '!=', null);
	}
	public function favourite() {
		return $this->belongsTo(ProductFavourite::class, "id", "product_id");
	}
	public function get_products_name($id) {
		return Product::where(['id' => $id, 'status' => 'active'])->pluck('name');
	}
	public function dispensary() {
		return $this->belongsTo(Dispensary::class, 'dispensary_id');
	}
	public function brand() {
		return $this->belongsTo(Brand::class, 'brand_id');
	}
	public function fetchProducts($request, $columns) {
		$query = Product::with(['category', 'subcategory'])->where('status', '!=', 'delete');
		
		if (isset($request->from_date)) {
			$query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
		}
		if (isset($request->end_date)) {
			$query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
		}
		if (isset($request->search)) {
			$query->where(function ($q) use ($request) {
				$q->where('name', 'like', '%' . $request->search . '%');
				$q->orWhere('product_code', 'like', '%' . $request->search . '%');
				$q->orWhere('description', 'like', '%' . $request->search . '%');
			});
		}
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
                $q->orWhere('product_code', 'like', '%' . $request->name . '%');
                $q->orWhere('description', 'like', '%' . $request->name . '%');
                $q->orWhere('product_sku', 'like', '%' . $request->name . '%');
                $q->orWhere('price', 'like', '%' . $request->name . '%');
            });
        }
		if (isset($request->status)) {
			$query->where('status', $request->status);
		}
		if (isset($request->manage_stock)) {
			$query->where('manage_stock', $request->manage_stock);
		}
		if (isset($request->is_featured)) {
			$query->where('is_featured', $request->is_featured);
		}
        if (isset($request->brand_id)) {
            $query->where('brand_id', $request->brand_id);
        }
        if (isset($request->parent_id)) {
            $query->where('parent_id', $request->parent_id);
        }
        if (isset($request->type_id)) {
            $query->where('type_id', $request->type_id);
        } 
        if (isset($request->strain_id)) {
            $query->where('strain_id', $request->strain_id);
        }
        if (isset($request->dispensary_id)) {
            $query->where('dispensary_id', $request->dispensary_id);
        }
		if (isset($request->categoryFilter)) {
			$query->whereHas('category', function ($q) use ($request) {
				$q->where('id', explode('-', $request->categoryFilter)[0]);
			});
			if (isset(explode('-', $request->categoryFilter)[1])) {

				$query->orWhereHas('subcategory', function ($q) use ($request) {
					$q->where('id', explode('-', $request->categoryFilter)[1]);
				});
			}
		}
        if (isset($request->price) && !empty($request->price)) {
            $plans = $query->orderByRaw('CONVERT(price, SIGNED) '.$request->price);
        }else{
            if (isset($request->order_column)) {
                $plans = $query->orderBy($columns[$request->order_column], $request->order_dir);
            } else {
                $plans = $query->orderBy('created_at', 'desc');
            }
        }
		
		return $plans;
	}
    public function getProductCodeAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getBrandIdAttribute($details)
    {
        $res = 0;
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getProductSkuAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
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
            //$res = '<p align="center">'.$details.'</p>';
            $res = $details;
        }
        return $res;
    }
    public function getQtyAttribute($details)
    {
        $res = 0;
        if (!empty($details)) {
            $res = $details;
        }
        return (int)$res;
    }
    public function getPriceAttribute($details)
    {
        $res = 0.00;
        if (!empty($details)) {
            
            $res = number_format($details, 2);
            
        }
        if ( strpos( $res, "." ) !== false ) {
            
            return $res;
        }
        return $res.'.00';
        //return (float)$res;
        //return round($res,2);
    }
    public function getDiscountPriceAttribute($details)
    {
        $res = 0.00;
        if (!empty($details)) {
            
            $res = number_format($details, 2);
            
        }
        if ( strpos( $res, "." ) !== false ) {
            
            return (String)$res;
        }
        return (String)$res;
        
    }
    public function getAmountAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }  
    public function getThcAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    } 
    public function getCbdAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    } 
    public function getPriceColorCodeAttribute($details)
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
    public function getImageUrlAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getProductUrlAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getCreatedDateAttribute($details)
    {

        $res = '';
        if (!empty($details)) {
            $res = Helper::get_time_ago($details);
        }
        return $res;
    }
}
