<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Library\Helper;
class ProductFavourite extends Model
{
    use HasFactory;
    protected $table = 'product_favourites';
    protected $with = 'product';

	public function product() {
		return $this->belongsTo(Product::class, "product_id", "id");
	}

	public function user() {
		return $this->belongsTo(User::class, "user_id", "id");
	}
    public function getCreatedDateAttribute($details)
    {

        $res = '';
        if (!empty($details)) {
            $res = Helper::get_time_ago($details);
        }
        return $res;
    }

    public function getPauseExpireTimeAttribute($details)
    {
        $date1 = date('Y-m-d');
        $date1_ts = strtotime($date1);
        $date2_ts = strtotime($details);
        if ($date2_ts > $date1_ts) {
            $diff = $date2_ts - $date1_ts;
            return round($diff / 86400);
        }else if($date2_ts == $date1_ts){
            return 1;
        }else{
            return 0;
        }
        
    
    }
}
