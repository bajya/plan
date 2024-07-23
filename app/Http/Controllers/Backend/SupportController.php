<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;

class SupportController extends Controller {
	public $supports;
	public $columns;

	public function __construct() {
		$this->supports = new Support;
		$this->columns = [
			"sno", "name", "email", "subject", "action",
		];

		$this->middleware('permission:support-list', ['only' => ['index']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		return view('backend.supports.index');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function supportsAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->supports->fetchSupport($request, $this->columns);
		$total = $records->get();
		if (isset($request->start)) {
			$supports = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$supports = $records->offset($request->start)->limit(count($total))->get();
		}
		// echo $total;
		$result = [];
		$i = 1;
		foreach ($supports as $list) {
			$data = [];
			$data['sno'] = $i++;
			$data['name'] = $list->name;
			$data['email'] = $list->email;
			$data['description'] = $list->description;
			$action = '';

			if (Helper::checkAccess(route('viewSupport'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewSupport', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
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
			$supports = Support::where('id', $id)->first();
			if (isset($supports->id)) {
				return view('backend.supports.view', compact('supports'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('supports');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('supports');
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id = null) {
		
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id = null) {
		

	}
	/**
	 * Remove the specified FQA from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request) {
		
	}
}
