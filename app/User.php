<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;
use Laravel\Cashier\Billable;
use App\UserDevice;
class User extends Authenticatable
{
    use HasFactory, Notifiable,HasApiTokens, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['first_name', 'last_name', 
        'name', 'avatar', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function devices() {
        return $this->hasMany(UserDevice::class, 'user_id', 'id'); 
    }
    public function fetchAdmins($request, $columns) {
        $query = User::where('status', '!=', 'delete')->where('id', '!=', 1)->where('is_admin', 'Yes');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
                $q->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }
        if (isset($request->name)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->name . '%');
            });
        }
        if (isset($request->order_column)) {
            $users = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $users = $query->orderBy('created_at', 'desc');
        }
        return $users;
    }
    public function fetchUsers($request, $columns) {
        $query = User::where('status', '!=', 'delete')->where('id', '!=', 1)->where('is_admin', 'No');

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->search)) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
                //$q->orWhere('email', 'like', '%' . $request->search . '%');
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
            $users = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $users = $query->orderBy('created_at', 'desc');
        }
        return $users;
    }
    public function fetchUsersFavProd($request, $columns) {
        $dev = UserDevice::select('device_id')->where('user_id', $request->user_id)->orderBy('id', 'desc')->where('status', 'active')->first();
        $query = ProductFavourite::where('user_id', $request->user_id)->groupBy('product_id');
        if ($dev) {
            $query->orWhere('device_id', $dev->device_id);
        }

        if (isset($request->from_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") >= "' . date("Y-m-d", strtotime($request->from_date)) . '"');
        }
        if (isset($request->end_date)) {
            $query->whereRaw('DATE_FORMAT(created_at, "%Y-%m-%d") <= "' . date("Y-m-d", strtotime($request->end_date)) . '"');
        }
        if (isset($request->status)) {
            $query->where('status', $request->status);
        }else{
            $query->where('status', 'active');
        }
        
        if (isset($request->order_column)) {
            $users = $query->orderBy($columns[$request->order_column], $request->order_dir);
        } else {
            $users = $query->orderBy('created_at', 'desc');
        }
        return $users;
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
    public function getFirstNameAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getLastNameAttribute($details)
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
    public function getMobileAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
   public function getEmailAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    } 
    public function getPasswordAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
    public function getAvatarAttribute($details)
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
    public function getSubscriptionIdAttribute($details)
    {
        $res = '';
        if (!empty($details)) {
            $res = $details;
        }
        return $res;
    }
}
