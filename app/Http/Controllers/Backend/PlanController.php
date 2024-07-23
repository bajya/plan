<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Plan; 
use App\BusRuleRef; 
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;

class PlanController extends Controller {
	public $plan;
	public $columns;

	public function __construct() {
		$this->plan = new Plan;
		$this->columns = [
			"sno", "name", "action",
		];

		$this->middleware('permission:plan-list|plan-edit', ['only' => ['index','update']]);
        $this->middleware('permission:plan-edit', ['only' => ['edit','update']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		return view('backend.plan.index');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function planAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->plan->fetchPlan($request, $this->columns);
		$total = $records->get();
		if (isset($request->start)) {
			$plan = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$plan = $records->offset($request->start)->limit(count($total))->get();
		}
		// echo $total;
		$result = [];
		$i = 1;
		foreach ($plan as $list) {
			$data = [];
			$data['sno'] = $i++;
			$data['title'] = ucfirst($list->title);
			$data['amount'] = BusRuleRef::where("rule_name", 'currency')->first()->rule_value.' '.$list->amount;
			$data['duration_text'] = $list->duration_text;
			$data['created_at'] = date('d M Y', strtotime($list->created_at)).', at'.  date('h:i A', strtotime($list->created_at));
			$action = '';

			if (Helper::checkAccess(route('editPlan'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editPlan', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			if (Helper::checkAccess(route('viewPlan'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewPlan', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			}
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

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id = null) {
		if (isset($id) && $id != null) {
			$type = 'Show';
			$plan = Plan::where('id', $id)->first();
			if (isset($plan->id)) {
				return view('backend.plan.view', compact('plan', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('plan');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('plan');
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id = null) {
		if (isset($id) && $id != null) {
			$plan = Plan::where('id', $id)->first();
			if (isset($plan->id)) {
				
				$type = 'Edit';
				return view('backend.plan.create', compact('plan', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('plan');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('plan');
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id = null) {
		$validate = Validator($request->all(), [
            'title' => 'required',
            'amount' => 'required',
            'duration_month' => 'required',
        ]);
        $attr = [
            'title' => 'Title',
            'amount' => 'Amount',
            'duration_month' => 'Duration',
        ];
        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->back()->withInput($request->all())->withErrors($validate);
        } else {
            try {
				if (isset($id) && $id != null) {
					$plan = Plan::where('id', $id)->first();
					if (isset($plan->id)) {
						try {
							$input = $request->all();
					        $data = array();
					        $data['title']= ucfirst($request->title); 
					        $data['amount']= $request->amount; 
					        $data['duration_month']= $request->duration_month; 
					        $data['status']= 'active'; 
					        if ($request->duration_month == 30) {
					            $data['duration_text']= 'Monthly'; 
					        }else if ($request->duration_month == 90) {
					            $data['duration_text']= 'Quarterly'; 
					        }else if ($request->duration_month == 180) {
					            $data['duration_text']= 'Semi Annually'; 
					        }else if ($request->duration_month == 1) {
					            $data['duration_text']= 'Daily'; 
					        }else{
					            $data['duration_text']= 'Annual'; 
					        }
					        $plan->update($data);
							$request->session()->flash('success', 'Plan updated successfully');
							return redirect()->route('plan');
						} catch (Exception $e) {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('plan');
						}
					} else {
						$request->session()->flash('error', 'Invalid Data');
						return redirect()->route('plan');
					}
				} else {
					$request->session()->flash('error', 'Invalid Data');
					return redirect()->route('plan');
				}
		 	} catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('pushs');
            }

        }

	}
	
}
