<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Feedback;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use URL;
use Illuminate\Support\Arr;

class FeedbackController extends Controller {
	public $feedbacks;
	public $columns;

	public function __construct() {
		$this->feedbacks = new Feedback;
		$this->columns = [
			"sno", "name", "email", "subject", "action",
		];

		$this->middleware('permission:feedback-list', ['only' => ['index']]);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		return view('backend.feedbacks.index');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function feedbacksAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->feedbacks->fetchFeedback($request, $this->columns);
		$total = $records->get();
		if (isset($request->start)) {
			$feedbacks = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$feedbacks = $records->offset($request->start)->limit(count($total))->get();
		}
		// echo $total;
		$result = [];
		$i = 1;
		foreach ($feedbacks as $list) {
			$data = [];
			$data['sno'] = $i++;
			$data['name'] = isset($list->user_id) && !empty($list->user_id) ? $list->user_id : '-';
			$img = $list->smiley.'.png';
			$data['smiley'] = ($list->smiley != null) ? '<img src="'.URL::asset('/uploads/smiley/' . $img).'" width="70" />' : '-';
			$data['category'] = ucfirst($list->category);
			$action = '';

			if (Helper::checkAccess(route('viewFeedback'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewFeedback', ['id' => $list->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
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
			$feedbacks = Feedback::where('id', $id)->first();
			if (isset($feedbacks->id)) {
				return view('backend.feedbacks.view', compact('feedbacks'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('feedbacks');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('feedbacks');
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
