<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ProductType; 
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL; 
use Illuminate\Support\Arr;

class ProductTypeController extends Controller {
	public $type;
	public $columns;

	public function __construct() {
		$this->type = new ProductType;
		$this->columns = [
			"select", "s_no", "name", "image", "description", "status", "activate", "action"
		];
		$this->middleware('permission:type-list|type-create|type-edit|type-delete', ['only' => ['index','store']]);
        $this->middleware('permission:type-create', ['only' => ['create','store']]);
        $this->middleware('permission:type-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:type-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = ProductType::where('status', '!=', 'delete')->count();
		return view('backend.types.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function typeAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->type->fetchProductTypes();
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
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="type_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['name'] = $cat->name;
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusType"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editType'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editType', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewType', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteType'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteType" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
		$typepage = 'add';
		$url = route('addType');
		$type = new ProductType;
		return view('backend.types.create', compact('typepage', 'url', 'type'));
	}

	/**
	 * check for unique type
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkType(Request $request, $id = null) {
		if (isset($request->dis_name)) {
			$check = ProductType::where('name', $request->dis_name);
			if (isset($id) && $id != null) {
				$check = $check->where('id', '!=', $id);
			}
			if (isset($request->parent) && $request->parent != null) {
				$check = $check->where('parent_id', $request->parent);
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
			'type_name' => 'required',

		]);

		$attr = [
			'type_name' => 'Type Name',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createType')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$type = new ProductType;

				

				

				
				$type->name = $request->post('type_name');

				$type->status = trim($request->post('status'));
				$type->created_at = date('Y-m-d H:i:s');

				if ($type->save()) {
					$request->session()->flash('success', 'Type added successfully');
					return redirect()->route('types');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('types');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('types');
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
			$typepage = 'Show';
			$type = ProductType::where('id', $id)->first();
			if (isset($type->id)) {
				return view('backend.types.view', compact('type', 'typepage'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('types');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('types');
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
            $type = ProductType::where('id', $id)->first();
            if (isset($type->id)) {
                $typepage = 'edit';
                $url = route('updateType', ['id' => $type->id]);
                return view('backend.types.create', compact('type', 'typepage', 'url'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('types');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('types');
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
			$type = ProductType::where('id', $id)->first();
			if (isset($type->id)) {

				$validate = Validator($request->all(), [
					'type_name' => 'required',
				]);

				$attr = [
					'type_name' => 'Type Name',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createType')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$type->name = $request->post('type_name');
						$type->status = trim($request->post('status'));

						if ($type->save()) {
							$request->session()->flash('success', 'Type updated successfully');
							return redirect()->route('types');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('types');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('types');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('types');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('types');
		}

	}

	// activate/deactivate type
	public function updateStatus(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$type = ProductType::find($request->statusid);

			if (isset($type->id)) {
				$type->status = $request->status;
				if ($type->save()) {
					$request->session()->flash('success', 'Type updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update Type. Please try again later.');
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

	// activate/deactivate type
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$type = ProductType::find($request->statusid);

			if (isset($type->id)) {
				$type->status = $request->status;
				if ($type->save()) {
					echo json_encode(['status' => 1, 'message' => 'Type updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update Type. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
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
			$type = ProductType::find($request->deleteid);

			if (isset($type->id)) {
				$type->status = 'delete';
				if ($type->save()) {

					$this->deleteItems($type, 1);

					echo json_encode(['status' => 1, 'message' => 'Type deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete type. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
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
				$type = ProductType::find($id);

				if (isset($type->id)) {
					$type->status = 'delete';
					if ($type->save()) {
						$this->deleteItems($type, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Type deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all type were deleted. Please try again later.']);
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
				$type = ProductType::find($id);

				if (isset($type->id)) {
					if ($type->status == 'active') {
						$type->status = 'inactive';
					} elseif ($type->status == 'inactive') {
						$type->status = 'active';
					}

					if ($type->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Type updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all types were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

}
