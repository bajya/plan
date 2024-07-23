<?php

namespace App\Http\Controllers\Backend;

use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Dispensary;
use App\Category;
use App\Product;
use App\Support;
use App\Strain;
use App\Feedback;
use App\Transaction;
use Spatie\Permission\Models\Role;
use DB;
use Auth;
use Hash;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request) {
        ini_set('max_execution_time', 0);
        \Artisan::call('config:clear');
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))->where('status', '!=', 'delete')->where('is_admin', 'No')->where('id', '!=', 1)->where('created_at', '>', Carbon::today()->subDay(29))->groupBy('day_name','day')->orderBy('day')->get();
            $array[] = ['Name', 'Number'];
            foreach($data as $key => $value)
            {
                $array[++$key] = [$value->day_name, $value->count];
            }
        $users = json_encode($array);
        $total_amount = Transaction::sum('amount');
        $total_user = User::where('status', '!=', 'delete')->where('is_admin', 'No')->where('id', '!=', 1)->count();
        $total_Dispensary = Dispensary::where('status', '!=', 'delete')->count();
        $total_Category = Category::where('status', '!=', 'delete')->count();
        $total_Strain = Strain::where('status', '!=', 'delete')->count();
        $total_Product = Product::where('status', '!=', 'delete')->count();
        $total_Support = Support::where('status', '!=', 'delete')->count();
        $total_Feedback = Feedback::where('status', '!=', 'delete')->count();
        $total_Category = $total_Category + $total_Strain;
        return view('backend.dashboard', compact('users', 'total_user', 'total_Dispensary', 'total_Category', 'total_Product', 'total_Support', 'total_Feedback', 'total_amount')); 
    }
    public function changePassword(Request $request) {
        if ($request->isMethod('post')) {
            $user = Auth::user();
            if ($user) {
                if ($user->password == Hash::make($request->post('old-pass'))) {
                    if ($request->post('pass') == $request->post('confirm-pass')) {
                        $user->password = Hash::make($request->post('pass'));
                        if ($user->save()) {
                            $request->session()->flash('success', 'Password changed successfully. Please login again.');
                            return redirect('/admin');
                        } else {
                            $request->session()->flash('error', 'Password not changed! Try again later.');
                            return view('backend.changepassword');
                        }
                    } else {
                        $request->session()->flash('error', 'Passwords do not match.');
                        return view('backend.changepassword');
                    }
                } else {
                    $request->session()->flash('error', 'Old Passwords do not match.');
                    return view('backend.changepassword');
                }
            } else {
                $request->session()->flash('error', 'Access Denied');
                return view('backend.changepassword');
            }
        }

            $user = Auth::user();
            if ($user) {
                return view('backend.changepassword');
            } else {
                $request->session()->flash('error', 'Access Denied');
                return redirect('/login');
            }
        
    }































    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        $items = array();
        $items = Transaction::whereYear('created_at',$year)->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
        $result=[];
        //dd($items);
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                //$amount=$item->sum('amount');
                $amount=$item->amount;
                $m=intval($month);
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }

}
