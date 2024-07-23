<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Product;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL;
use Illuminate\Support\Arr;

class ProductTypeController extends Controller {
	public $type;
	public $columns;
	public $restrict;

	public function __construct() {
		$this->type = new Category;
		$this->columns = [
			"select", "s_no", "parent_id", "name", "image", "description", "status", "activate", "action"
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
		$count = Category::where('status', '!=', 'delete')->count();
		$categorys = Category::select('id','name')->where('status', '!=','delete')->whereNull('parent_id')->orderBy('name', 'asc')->get();
		return view('backend.types.index', compact('count', 'categorys'));
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
		$records = $this->type->fetchTypesAjax($request, $this->columns);
		$count = $records->get();
		if (isset($request->start)) {
			$types = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$types = $records->offset($request->start)->limit(count($count))->get();
		}
		// echo $total;
		$result = [];
		//$types = [];
		$i = 1;

		/*foreach ($list as $key => &$value) {
			$level = 1;
			$value->sno = $i . ". ";
			$types[$value->id] = $value;
			$root = $value;
			$types = $this->setListView($root, $types, $i, $request, $level);
			$i++;

		}*/
		$total = count($types);
		// die();
		$i = 1;
		foreach ($types as $cat) {
			$data = [];
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['parent_id'] = ($cat->parentCat->name != null) ? $cat->parentCat->name : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/types/' . $cat->image).'" width="70" />' : '-';

			$data['order_no'] = '<div class="form-group  col-md-2 m-t-20"><label class=""><input type="number" min="1" data-id="' . $cat->id . '" name="order_no" value="' . $cat->order_no . '" class="statusCategoryOrder"><i class="input-helper"></i></label></div>';
			$data['description'] = ($cat->description != null) ? $cat->description : '-';
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
	public function setListView($root, $types, $i, $request, $level) {
		/*$child = $root->childCat;
		$j = 1;
		foreach ($child as &$ch) {
			if ($ch->status != 'delete') {
				$k = $i . "." . $j;
				$ch->sno = $k . '. ';
				$ch->parent = $root->name;
				$types[$ch->id] = $ch;
				$types = $this->setListView($ch, $types, $k, $request, ++$level);
				$level = 2;
				$j++;
			}

		}
		$root = $child;*/
		return $types;
	}
	public function setList($root, $types, $i, $level, $id = null)
    {
    	if (isset($root->childCat) && !empty($root->childCat)) {
    		/*$child = $root->childCat;
		        $j = 1;
		        foreach ($child as $ch) {
		            if ($ch->status != 'delete' && ($id == null || ($id != null && $id != $ch->id))) {
		                $k = "&nbsp;&nbsp;" . $i . "." . $j;
		                $types[$ch->id] = $k . '. ' . $ch->name;
		                $types = $this->setList($ch, $types, $k, ++$level, $id);
		                $j++;
		            }
		        }
		        $root = $child;*/
    	}
       
        
        return $types;
    }
	public function setListOrder($root, $types, $level) {
		$child = $root->childCat;
			foreach ($child as $ch) {
				if ($ch->status != 'delete') {
					if (!array_key_exists($root->id, $types)) {
						$types[$root->id] = array();
					}
					$types[$root->id][$ch->id] = $ch->name;

					$types = $this->setListOrder($ch, $types, ++$level);
					$level = 2;
				}
			}
			$root = $child;
		
		return $types;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() { 
		$typepage = 'add';
		$url = route('addType');
		$type = new Category;
		$list = $this->type->fetchTypes()->get(); 

		$types = [];
		$i = 1;
		$level = 1;
		foreach ($list as $key => $value) {
			$types[$value->id] = $i . ". " . $value->name;
			$root = $value;
			$types = $this->setList($root, $types, $i, $level);
			$i++;
		}
		return view('backend.types.create', compact('typepage', 'url', 'type', 'types'));
	}

	/**
	 * check for unique type
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkType(Request $request, $id = null) {
		if (isset($request->cat_name)) {
			$check = Category::where('name', $request->cat_name);
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
			'cat_name' => 'required',
			'description' => 'required',
			'parent_id' => 'required',
			//'cat_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'cat_name' => 'Name',
			'parent_id' => 'Category Name',
			'description' => 'Description',
			//'cat_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createType')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$type = new Category;

				$imageName = '';
				if ($request->file('cat_image') != null) {
					$image = $request->file('cat_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/types'), $imageName);
					//Helper::compress_image(public_path('uploads/types/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}

				$type->name = $request->post('cat_name');
				$type->image = str_replace('.jpeg', '.jpg', $imageName);
				$type->description = $request->post('description');
				$type->parent_id = $request->post('parent_id');
				if ($res = Category::select('order_no')->where('status', '!=', 'delete')->where('type', 'type')->orderBy('order_no', 'desc')->first()) {
					$type->order_no = $res->order_no + 1;
				}else{
					$type->order_no = 1;
				}
				$type->type = 'type';
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
			$type = Category::with(['childCat' => function ($q) {
				$q->where('status', '!=', 'delete');
			}])->where('id', $id)->first();
			if (isset($type->id)) {
				//dd($type);


				$types = [];
				$i = 1;

				$types = $this->setListOrder($type, $types, 1);
				
				return view('backend.types.view', compact('type', 'types', 'typepage'));
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
            $type = Category::where('id', $id)->first();
            if (isset($type->id)) {
                $typepage = 'edit';
                $url = route('updateType', ['id' => $type->id]);

                $list = $this->type->fetchTypes($id)->get();

                $types = [];
                $i = 1;
                foreach ($list as $key => $value) {
                    if ($value->id != $type->id) {

                        $types[$value->id] = $i . ". " . $value->name;
                        $root = $value;
                        $types = $this->setList($root, $types, $i, 1, $id);
                        $i++;
                    }
                } 
                $sub = $type;

                return view('backend.types.create', compact('type', 'typepage', 'url', 'types'));
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
			$type = Category::where('id', $id)->first();
			if (isset($type->id)) {

				$validate = Validator($request->all(), [
					'cat_name' => 'required',
					'description' => 'required',
					'parent_id' => 'required',
				]);

				$attr = [
					'cat_name' => 'Name',
					'description' => 'Description',
					'parent_id' => 'Category Name',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createType')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('cat_image') != null) {
							$image = $request->file('cat_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($type->image != null && file_exists(public_path('uploads/types/' . $type->image))) {
								unlink(public_path('uploads/types/' . $type->image));
							}

							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/types'), $imageName);
							//Helper::compress_image(public_path('uploads/types/' . $imageName), 100);
							$type->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						
						$type->name = $request->post('cat_name');
						$type->description = $request->post('description');
						$type->parent_id = $request->post('parent_id');
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
			$type = Category::find($request->statusid);

			if (isset($type->id)) {
				if ($request->status == 'active') {
					$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'active')->first();
					if (!empty($maincats)) {
						$type->status = $request->status;
					}else{
						$request->session()->flash('error', 'Unable to update type. Beacuase category not active yet.');
						return redirect()->back();
					}
				}else{
					$type->status = $request->status;
				}
				
				
				if ($type->save()) {
					if ($request->status == 'inactive') {
						DB::transaction(function () use($type) {
		                	Product::where('status', '!=', 'delete')->where('type_id', $type->id)->update(array('status' => 'inactive'));
			            }, 5);
						/*$starins = Category::where('parent_id', $type->id)->where('type', 'strain')->where('status', 'active')->get();
						if (!empty($starins)) {
							foreach ($starins as $key => $val) {
								$val->status = $request->status;
								$val->save();
							}
						}*/
					}
					/*if ($request->status == 'active') {
						$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
						if (!empty($maincats)) {
							$maincats->status = $request->status;
							$maincats->save();
						}
					}*/
					$request->session()->flash('success', 'Type updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update type. Please try again later.');
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
			$type = Category::find($request->statusid);
			$checkData = 0;
			if (isset($type->id)) {
				if ($request->status == 'active') {
					$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'active')->first();
					if (!empty($maincats)) {
						$type->status = $request->status;
					}else{
						$checkData = 1;
						echo json_encode(['status' => 0, 'message' => 'Unable to update type. Beacuase category not active yet.']);
					}
				}else{
					$type->status = $request->status;
				}
				if ($checkData == 0) {
					if ($type->save()) {
						if ($request->status == 'inactive') {
							DB::transaction(function () use($type) {
			                	Product::where('status', '!=', 'delete')->where('type_id', $type->id)->update(array('status' => 'inactive'));
				            }, 5);
							/*$starins = Category::where('parent_id', $type->id)->where('type', 'strain')->where('status', 'active')->get();
							if (!empty($starins)) {
								foreach ($starins as $key => $val) {
									$val->status = $request->status;
									$val->save();
								}
							}*/
						}
						/*if ($request->status == 'active') {
							$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
							if (!empty($maincats)) {
								$maincats->status = $request->status;
								$maincats->save();
							}
						}*/
						echo json_encode(['status' => 1, 'message' => 'Type updated successfully.']);
					} else {
						echo json_encode(['status' => 0, 'message' => 'Unable to update type. Please try again later.']);
					}
				}
				
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid type']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid type']);
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
			$type = Category::find($request->deleteid);

			if (isset($type->id)) {
				$type->status = 'delete';
				if ($type->save()) {

					$this->deleteItems($type, 1);

					echo json_encode(['status' => 1, 'message' => 'Type deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete type. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid type']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid type']);
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
				$type = Category::find($id);

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
				$type = Category::find($id);

				if (isset($type->id)) {
					if ($type->status == 'active') {
						$type->status = 'inactive';
					} elseif ($type->status == 'inactive') {
						$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'active')->first();
						if (!empty($maincats)) {
							$type->status = 'active';
						}else{
							$type->status = 'inactive';
							$count--;
						}
						
					}

					if ($type->save()) {
						if ($type->status == 'inactive') {
							DB::transaction(function () use($type) {
			                	Product::where('status', '!=', 'delete')->where('type_id', $type->id)->update(array('status' => 'inactive'));
				            }, 5);
							/*$starins = Category::where('parent_id', $type->id)->where('type', 'strain')->where('status', 'active')->get();
							if (!empty($starins)) {
								foreach ($starins as $key => $val) {
									$val->status = $type->status;
									$val->save();
								}
							}*/
						}
						/*if ($type->status == 'active') {
							$maincats = Category::where('id', $type->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
							if (!empty($maincats)) {
								$maincats->status = $type->status;
								$maincats->save();
							}
						}*/
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Type updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all types were updated. Beacuase some category not active yet.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}
	// Order TYPE
	public function updateStatusOrderAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Category::find($request->statusid);

			if (isset($category->id)) {
					if ($res = Category::where('status', '!=', 'delete')->where('type', 'type')->where('order_no', $request->order_no)->first()) {
						$res->order_no = $category->order_no;
						$res->save();
					}
				$category->order_no = $request->order_no;
				if ($category->save()) {
					echo json_encode(['status' => 1, 'message' => 'Type order successfully update.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update Type order. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Type']);
		}

	}


}
