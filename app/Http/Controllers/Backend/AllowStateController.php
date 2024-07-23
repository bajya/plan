<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\State;
use App\Product;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL; 
use Illuminate\Support\Arr;

class AllowStateController extends Controller {
	public $allowstate;
	public $columns;

	public function __construct() {
		$this->allowstate = new State;
		$this->columns = [
			"select", "s_no", "name", "status", "activate", "action"
		];
		$this->middleware('permission:state-list|state-create|state-edit|state-delete', ['only' => ['index','store']]);
        $this->middleware('permission:state-create', ['only' => ['create','store']]);
        $this->middleware('permission:state-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:state-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		//Brand::delete();
        //Product::where('status', 'active')->delete(); die;
       // Category::delete();
		$count = State::where('status', '!=', 'delete')->count();
		return view('backend.allowstates.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function allowstateAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->allowstate->fetchAllowStates($request, $this->columns);
		$count = $records->get();
		if (isset($request->start)) {
			$list = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$list = $records->offset($request->start)->limit(count($count))->get();
		}
		// echo $total;
		$result = [];
		
		$total = count($count);
		// die();
		$i = 1;
		foreach ($list as $cat) { 
			$data = [];
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="state_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['name'] = $cat->name;
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->is_allow == 'true' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusAllowState"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editAllowState'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editAllowState', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewAllowState', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteAllowState'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteAllowState" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
			}
			$data['action'] = $action;

			$result[] = $data;
		}
		$data = json_encode([
			'data' => $result,
			'recordsTotal' => $total,
			'recordsFiltered' => $total,
		]);
		echo $data;

	}




	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() { 
		$type = 'add';
		$url = route('addAllowState');
		$allowstate = new State;
		return view('backend.allowstates.create', compact('type', 'url', 'allowstate'));
	}

	/**
	 * check for unique allowstate
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkAllowState(Request $request, $id = null) {
		if (isset($request->dis_name)) {
			$check = State::where('name', $request->state_name)->where('type', 'allow');
			if (isset($id) && $id != null) {
				$check = $check->where('id', '!=', $id);
			}
			$check = $check->where('status', '!=', 'delete')->count();
			if ($check > 0) {
				return "false";
			} else {
				return "true";
			}

		} else {
			return "true";
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {

		$validate = Validator($request->all(), [
			'state_name' => 'required',

		]);

		$attr = [
			'state_name' => 'State Name',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createAllowState')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$allowstate = new State;

				$allowstate->name = $request->post('state_name');
				$allowstate->is_allow = 'true';
				$allowstate->type = 'allow';

				$allowstate->status = trim($request->post('status'));
				$allowstate->created_at = date('Y-m-d H:i:s');

				if ($allowstate->save()) {
					$request->session()->flash('success', 'State added successfully');
					return redirect()->route('allowstates');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('allowstates');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('allowstates');
			}

		}
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
			$allowstate = State::where('id', $id)->first();
			if (isset($allowstate->id)) {
				return view('backend.allowstates.view', compact('allowstate', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('allowstates');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('allowstates');
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
            $allowstate = State::where('id', $id)->first();
            if (isset($allowstate->id)) {
                $type = 'edit';
                $url = route('updateAllowState', ['id' => $allowstate->id]);
                return view('backend.allowstates.create', compact('allowstate', 'type', 'url'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('allowstates');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('allowstates');
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
		if (isset($id) && $id != null) {
			$allowstate = State::where('id', $id)->first();
			if (isset($allowstate->id)) {
				$validate = Validator($request->all(), [
					'state_name' => 'required',
				]);
				$attr = [
					'state_name' => 'State Name',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createAllowState')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$allowstate->name = $request->post('state_name');
						$allowstate->is_allow = 'true';
						$allowstate->status = trim($request->post('status'));
						if ($allowstate->save()) {
							$request->session()->flash('success', 'State updated successfully');
							return redirect()->route('allowstates');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('allowstates');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('allowstates');
					}
				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('allowstates');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('allowstates');
		}
	}

	// activate/deactivate AllowState
	public function updateStatus(Request $request) {
		if (isset($request->statusid) && $request->statusid != null) {
			$allowstate = State::find($request->statusid);
			if (isset($allowstate->id)) {
			    if($request->status == 'active'){
			        $allowstate->is_allow = 'true';
			    }else{
			        $allowstate->is_allow = 'false';
			    }
				
				if ($allowstate->save()) {
					$request->session()->flash('success', 'State updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update State. Please try again later.');
					return redirect()->back();
				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->back();
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->back();
		}
	}

	// activate/deactivate State
	public function updateStatusAjax(Request $request) {
		if (isset($request->statusid) && $request->statusid != null) {
			$allowstate = State::find($request->statusid);
			if (isset($allowstate->id)) {
				if($request->status == 'active'){
			        $allowstate->is_allow = 'true';
			    }else{
			        $allowstate->is_allow = 'false';
			    }
				if ($allowstate->save()) {
					echo json_encode(['status' => 1, 'message' => 'State updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update State. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid State']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid State']);
		}
	}

	public function deleteItems($root, $level) {
		$child = $root->childCat;
		foreach ($child as $ch) {
			$ch->status = 'delete';
			$ch->save();
			$this->deleteItems($ch, ++$level);
		}
		$root = $child;
		return true;
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request) {
		if (isset($request->deleteid) && $request->deleteid != null) {
			$allowstate = State::find($request->deleteid);

			if (isset($allowstate->id)) {
				$allowstate->status = 'delete';
				if ($allowstate->save()) {
					echo json_encode(['status' => 1, 'message' => 'State deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete State. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid State']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid State']);
		}
	}
	/**
	 * Remove multiple resource from storage.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function bulkdelete(Request $request) {

		if (isset($request->deleteid) && $request->deleteid != null) {
			$deleteid = explode(',', $request->deleteid);
			$ids = count($deleteid);
			$count = 0;
			foreach ($deleteid as $id) {
				$allowstate = State::find($id);

				if (isset($allowstate->id)) {
					$allowstate->status = 'delete';
					if ($allowstate->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'State deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all state were deleted. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}
	/**
	 * activate/deactivate multiple resource from storage.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function bulkchangeStatus(Request $request) {

		if (isset($request->ids) && $request->ids != null) {
			$ids = count($request->ids);
			$count = 0;
			foreach ($request->ids as $id) {
				$allowstate = State::find($id);
				if (isset($allowstate->id)) {
					if ($allowstate->is_allow == 'true') {
						$allowstate->is_allow = 'false';
					} elseif ($allowstate->is_allow == 'false') {
						$allowstate->is_allow = 'true';
					}

					if ($allowstate->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'State updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all states were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

}
