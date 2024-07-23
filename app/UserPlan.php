<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;
    protected $table = 'user_plans';

    public function user_plan_service() {
        return $this->hasMany('App\Models\UserPlanService', 'user_plan_id', 'id');
    }
	public function user() {
		return $this->belongsTo(User::class, 'user_id');
	}
    public function getTitleAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getPlanExpireTimeAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getAmountAttribute($details)
    {
        $res = 0;
        if (!empty($details)) {
            $res = $details;
        }
        return (string)$res;
    }    
}
