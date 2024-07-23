<?php
    
namespace App\Http\Controllers\Backend;
    
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Transaction;
use App\BusRuleRef;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;
    
class TransactionController extends Controller
{
    public $transaction;
    public $columns;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->transaction = new Transaction;
        $this->columns = [
            "select", "name", "email", "created_at", "status", "activate", "action",
        ];
        $this->middleware('permission:transaction-list', ['only' => ['index','store']]);
    }

    public function index(Request $request) {
        $users = User::all()->where('status', 'active')->pluck('name', 'id');
        return view('backend.transactions.index', compact('users'));
    }

    public function transactionsAjax(Request $request) {
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->transaction->fetchTransactions($request, $this->columns);
        $total = $records->get();
        if (isset($request->start)) {
            $transactions = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $transactions = $records->offset($request->start)->limit(count($total))->get();
        }
        $result = [];
        $i = 1;
        foreach ($transactions as $transaction) {
            $data = [];
            $data['sno'] = $i++;

            $userData = '';
            $infoData = '';
            
            if(isset($transaction->user->id) && !empty($transaction->user->id)){
              
              $userData .='<div class="user_number">
                <span>Name :</span>  <a  href="'.route('viewUsers',$transaction->user->id).'">'.
                ucfirst($transaction->user->name).'</a> 
              </div>
              <div class="user_number">
                <span>Number :</span> '.$transaction->user->phone_code.' '.$transaction->user->mobile.'
              </div>';
            }
            $paymentData = '<div class="method">
                    <span>Method -</span> '.ucfirst($transaction->payment_method).' 
                </div>
                <div class="txn_id">
                    <span>Txn -</span> '.$transaction->txn_id.'
                </div>
                <div class="amount">
                    <span>Amount -</span> '.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$transaction->amount.'
                </div>';
            
            $item = DB::table('user_plans')->where('plan_id', $transaction->item_id)->where('user_id', $transaction->user_id)->first();
            if($item){
                $infoData .='<div class="user_plan">
                  <span>Plan :</span> '.$item->title.'('.BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$item->amount.')
                </div>';
            }
            $data['user'] = $userData;
            $data['payment_method'] = $paymentData;
            $data['info'] = $infoData; 
            $data['message'] = ucfirst($transaction->message); 
            $data['created_at'] = date('Y, M d', strtotime($transaction->created_at));  
            $action = '';
            $action .= '<a href="' . route('viewTransactions', ['id' => $transaction->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
            $data['action'] = $action;
            $result[] = $data;
        }
        $data = json_encode([
            'data' => $result,
            'recordsTotal' => count($total),
            'recordsFiltered' => count($total),
        ]);
        echo $data;

    }
    public function create() {

    }
    public function store(Request $request) {
        
    }
    public function show(Request $request, $id = null) {
        $type = 'View';
        if (isset($id) && $id != null) {
            $transaction = Transaction::where('id', $id)->first();
            if (isset($transaction->id)) {
                return view('backend.transactions.view', compact('transaction', 'type'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('transactions');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('transactions');
        }
    }
    public function edit(Request $request, $id = null) {
        
    }
    public function update(Request $request, $id = null) {
        

    }
}