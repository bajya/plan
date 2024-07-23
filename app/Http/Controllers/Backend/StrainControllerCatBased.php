<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL;
use Illuminate\Support\Arr;

class StrainController extends Controller {
	public $strain;
	public $columns;
	public $restrict;

	public function __construct() {
		$this->strain = new Category;
		$this->columns = [
			"select", "s_no", "parent_id", "name", "image", "description", "status", "activate", "action"
		];
		$this->middleware('permission:strain-list|strain-create|strain-edit|strain-delete', ['only' => ['index','store']]);
        $this->middleware('permission:strain-create', ['only' => ['create','store']]);
        $this->middleware('permission:strain-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:strain-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = Category::where('status', '!=', 'delete')->count();
		return view('backend.strains.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function strainAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->strain->fetchStrainsAjax($request, $this->columns);
		$count = $records->get();
		if (isset($request->start)) {
			$list = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$list = $records->offset($request->start)->limit(count($count))->get();
		}
		// echo $total;
		$result = [];
		//$strains = [];
		$strains = $list;
		$i = 1;

		/*foreach ($list as $key => &$value) {
			$level = 1;
			$value->sno = $i . ". ";
			$strains[$value->id] = $value;
			$root = $value;
			$strains = $this->setListView($root, $strains, $i, $request, $level);
			$i++;

		}*/
		$total = count($strains);
		// die();
		$i = 1;
		foreach ($strains as $cat) {
			$data = [];
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$mainP = DB::table('categories')->where('id', $cat->parentCat->id)->first();
			$mainPf = DB::table('categories')->where('id', $mainP->parent_id)->first();
			$data['category_id'] = ($mainPf->name != null) ? $mainPf->name : '-';
			$data['parent_id'] = ($cat->parentCat->name != null) ? $cat->parentCat->name : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/strains/' . $cat->image).'" width="70" />' : '-';


			$data['description'] = ($cat->description != null) ? $cat->description : '-';
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusStrain"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editStrain'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editStrain', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewStrain', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteStrain'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteStrain" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
	public function setListView($root, $strains, $i, $request, $level) {
		$child = $root->childCat;
		$j = 1;
		foreach ($child as &$ch) {
			if ($ch->status != 'delete') {
				$k = $i . "." . $j;
				$ch->sno = $k . '. ';
				$ch->parent = $root->name;
				$strains[$ch->id] = $ch;
				//$strains = $this->setListView($ch, $strains, $k, $request, ++$level);
				$level = 2;
				$j++;
			}

		}
		$root = $child;
		return $strains;
	}
	public function setList($root, $strains, $i, $level, $id = null)
    {
    	if (isset($root->childCat) && !empty($root->childCat)) {
    		$child = $root->childCat;
	        $j = 1;
	        foreach ($child as $ch) {
	            if ($ch->status != 'delete' && $ch->type != 'strain' && ($id == null || ($id != null && $id != $ch->id))) {
	                $k = "&nbsp;&nbsp;" . $i . "." . $j;
	                $strains[$ch->id] = $k . '. ' . $ch->name;
	                $strains = $this->setList($ch, $strains, $k, ++$level, $id);
	                $j++;
	            }
	        }
	        $root = $child;
    	}
        return $strains;
    }
	public function setListOrder($root, $strains, $level) {
		$child = $root->childCat;
			foreach ($child as $ch) {
				if ($ch->status != 'delete') {
					if (!array_key_exists($root->id, $strains)) {
						$strains[$root->id] = array();
					}
					$strains[$root->id][$ch->id] = $ch->name;

					$strains = $this->setListOrder($ch, $strains, ++$level);
					$level = 2;
				}
			}
			$root = $child;
		
		return $strains;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() { 
		$type = 'add';
		$url = route('addStrain');
		$strain = new Category;
		$list = $this->strain->fetchStrains()->get(); 
		$strains = [];
		$i = 1;
		$level = 1;
		foreach ($list as $key => $value) {
			$strains[$value->id] = $i . ". " . $value->name;
			$root = $value;
			$strains = $this->setList($root, $strains, $i, $level);
			$i++;
		}
		$categories = Category::select('id','name')->where('status', 'active')->whereNull('parent_id')->get();
        $producttypes = array();
		return view('backend.strains.create', compact('type', 'url', 'strain', 'strains', 'categories', 'producttypes'));
	}

	/**
	 * check for unique Strain
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkStrain(Request $request, $id = null) {
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
			'parent_id' => 'required',
			'description' => 'required',
			//'cat_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'cat_name' => 'Name',
			'parent_id' => 'Type Name',
			'description' => 'Description',
			//'cat_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createStrain')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$strain = new Category;

				$imageName = '';
				if ($request->file('cat_image') != null) {
					$image = $request->file('cat_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/strains'), $imageName);
					//Helper::compress_image(public_path('uploads/strains/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}

				

				
				$strain->name = $request->post('cat_name');
				$strain->image = str_replace('.jpeg', '.jpg', $imageName);
				$strain->description = $request->post('description');
				$strain->parent_id = $request->post('parent_id');
				$strain->type = 'strain';
				$strain->status = trim($request->post('status'));
				$strain->created_at = date('Y-m-d H:i:s');

				if ($strain->save()) {
					$request->session()->flash('success', 'Strain added successfully');
					return redirect()->route('strains');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('strains');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('strains');
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
			$strain = Category::with(['childCat' => function ($q) {
				$q->where('status', '!=', 'delete');
			}])->where('id', $id)->first();
			if (isset($strain->id)) {
				//dd($strain);


				$strains = [];
				$i = 1;

				$strains = $this->setListOrder($strain, $strains, 1);
				
				return view('backend.strains.view', compact('strain', 'strains', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('strains');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('strains');
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
            $strain = Category::where('id', $id)->first();
            if (isset($strain->id)) {
                $type = 'edit';
                $url = route('updateStrain', ['id' => $strain->id]);

                $list = $this->strain->fetchStrains($id)->get();

                $strains = [];
                $i = 1;
                foreach ($list as $key => $value) {
                    if ($value->id != $strain->id) {

                        $strains[$value->id] = $i . ". " . $value->name;
                        $root = $value;
                        $strains = $this->setList($root, $strains, $i, 1, $id);
                        $i++;
                    }
                }
                $sub = $strain;
                $categories = Category::select('id','name')->where('status', 'active')->whereNull('parent_id')->get();
                $ptype = Category::select('id','name', 'parent_id')->where('status', 'active')->where('id', $strain->parent_id)->first();
               // dd($ptype);
                if ($ptype) {
                	 $producttypes = Category::select('id','name')->where('status', 'active')->where('parent_id', $ptype->parent_id)->get();
                }else{
                	$producttypes = array();
                }
               
                return view('backend.strains.create', compact('strain', 'type', 'url', 'strains', 'categories', 'producttypes'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('strains');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('strains');
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
			$strain = Category::where('id', $id)->first();
			if (isset($strain->id)) {

				$validate = Validator($request->all(), [
					'cat_name' => 'required',
					'parent_id' => 'required',
					//'description' => 'required',
				]);

				$attr = [
					'cat_name' => 'Name',
					'parent_id' => 'Type Name',
					//'description' => 'Description',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createStrain')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('cat_image') != null) {
							$image = $request->file('cat_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($strain->image != null && file_exists(public_path('uploads/strains/' . $strain->image))) {
								unlink(public_path('uploads/strains/' . $strain->image));
							}

							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/strains'), $imageName);
							//Helper::compress_image(public_path('uploads/strains/' . $imageName), 100);
							$strain->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						
						$strain->name = $request->post('cat_name');
						$strain->description = $request->post('description');
						$strain->parent_id = $request->post('parent_id');
						$strain->status = trim($request->post('status'));

						if ($strain->save()) {
							$request->session()->flash('success', 'Strain updated successfully');
							return redirect()->route('strains');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('strains');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('strains');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('strains');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('strains');
		}

	}

	// activate/deactivate Strain
	public function updateStatus(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$strain = Category::find($request->statusid);

			if (isset($strain->id)) {
				if ($request->status == 'active') {
					$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->where('status', 'active')->first();
					if (!empty($maintypes)) {
						$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'active')->first();
						if (!empty($maincats)) {
							$strain->status = $request->status;
						}else{
							$request->session()->flash('error', 'Unable to update type. Beacuase category not active yet.');
							return redirect()->back();
						}
					}else{
						$request->session()->flash('error', 'Unable to update starin. Beacuase type not active yet.');
						return redirect()->back();
					}
				}else{
					$strain->status = $request->status;
				}
				if ($strain->save()) {
					/*if ($request->status == 'active') {
						$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->whereIn('status', ['active', 'inactive'])->first();
						if (!empty($maintypes)) {
							$maintypes->status = $request->status;
							$maintypes->save();
							$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
							if (!empty($maincats)) {
								$maincats->status = $request->status;
								$maincats->save();
							}
						}
					}*/
					$request->session()->flash('success', 'Strain updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update Strain. Please try again later.');
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

	// activate/deactivate Strain
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$strain = Category::find($request->statusid);
			$checkData = 0;
			if (isset($strain->id)) {
				if ($request->status == 'active') {
					$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->where('status', 'active')->first();
					if (!empty($maintypes)) {
						$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'active')->first();
						if (!empty($maincats)) {
							$strain->status = $request->status;
						}else{
							$checkData = 1;
							echo json_encode(['status' => 0, 'message' => 'Unable to update type. Beacuase category not active yet.']);
						}
					}else{
						$checkData = 1;
						echo json_encode(['status' => 0, 'message' => 'Unable to update starin. Beacuase type not active yet.']);
					}
				}else{
					$strain->status = $request->status;
				}
				if ($checkData == 0) {
					if ($strain->save()) {
						/*if ($request->status == 'active') {
							$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->whereIn('status', ['active', 'inactive'])->first();
							if (!empty($maintypes)) {
								$maintypes->status = $request->status;
								$maintypes->save();
								$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
								if (!empty($maincats)) {
									$maincats->status = $request->status;
									$maincats->save();
								}
							}
						}*/
						echo json_encode(['status' => 1, 'message' => 'Strain updated successfully.']);
					} else {
						echo json_encode(['status' => 0, 'message' => 'Unable to update strain. Please try again later.']);
					}
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
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
			$strain = Category::find($request->deleteid);

			if (isset($strain->id)) {
				$strain->status = 'delete';
				if ($strain->save()) {

					$this->deleteItems($strain, 1);

					echo json_encode(['status' => 1, 'message' => 'Strain deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete strain. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
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
				$strain = Category::find($id);

				if (isset($strain->id)) {
					$strain->status = 'delete';
					if ($strain->save()) {
						$this->deleteItems($strain, 1);
						$count++;
					}
				}
			} 
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Strain deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Strain were deleted. Please try again later.']);
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
				$strain = Category::find($id);

				if (isset($strain->id)) {
					if ($strain->status == 'active') {
						$strain->status = 'inactive';
					} elseif ($strain->status == 'inactive') {
						$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->where('status', 'active')->first();
						if (!empty($maintypes)) {
							$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'active')->first();
							if (!empty($maincats)) {
								$strain->status = 'active';
							}else{
								$strain->status = 'inactive';
								$count--;
							}
						}else{
							$strain->status = 'inactive';
							$count--;
						}
						
					}

					if ($strain->save()) {
						/*if ($strain->status == 'active') {
							$maintypes = Category::where('id', $strain->parent_id)->where('type', 'type')->whereIn('status', ['active', 'inactive'])->first();
							if (!empty($maintypes)) {
								$maintypes->status = $strain->status;
								$maintypes->save();
								$maincats = Category::where('id', $maintypes->parent_id)->where('type', 'category')->where('status', 'inactive')->first();
								if (!empty($maincats)) {
									$maincats->status = $strain->status;
									$maincats->save();
								}
							}
						}*/
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Strain updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all types were updated. Beacuase some category and type not active yet.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

}
