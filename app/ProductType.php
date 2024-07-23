<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductType extends Model
{
    use HasFactory;
    protected $table = 'product_types';

	public function fetchProductTypes() {
		$query = ProductType::where('status', '!=', 'delete');

		$ProductType = $query->orderBy('created_at', 'desc');

		return $ProductType;
	}
    public function getNameAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }

}
