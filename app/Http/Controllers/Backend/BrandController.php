<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Brand;
use App\State;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL; 
use Illuminate\Support\Arr;

class BrandController extends Controller {
	public $brand;
	public $columns;

	public function __construct() {
		$this->brand = new Brand;
		$this->columns = [
			"select", "s_no", "name", "image", "description", "status", "activate", "action"
		];
		$this->middleware('permission:brand-list|brand-create|brand-edit|brand-delete', ['only' => ['index','store']]);
        $this->middleware('permission:brand-create', ['only' => ['create','store']]);
        $this->middleware('permission:brand-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:brand-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = Brand::where('status', '!=', 'delete')->count();
		return view('backend.brands.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function brandAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->brand->fetchBrands($request, $this->columns);
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
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="brand_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/brands/' . $cat->image).'" width="70" />' : '-';


			$data['description'] = ($cat->description != null) ? $cat->description : '-';
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusBrand"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editBrand'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editBrand', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewBrand', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteBrand'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteBrand" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
		$url = route('addBrand');
		$brand = new Brand;
		$states = State::select('id','name')->where('status', 'active')->where('type','state')->get();
		return view('backend.brands.create', compact('type', 'url', 'brand', 'states'));
	}

	/**
	 * check for unique brand
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkBrand(Request $request, $id = null) {
		if (isset($request->dis_name)) {
			$check = Brand::where('name', $request->dis_name);
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
			'brand_name' => 'required',
			'state_id' => 'required',
			'description' => 'required',
			'brand_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'brand_name' => 'Company Name',
			'state_id' => 'State Name',
			'description' => 'Description',
			'brand_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createBrand')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$brand = new Brand;

				$imageName = '';
				if ($request->file('brand_image') != null) {
					$image = $request->file('brand_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/brands'), $imageName);
					//Helper::compress_image(public_path('uploads/brands/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}
				$brand->name = $request->post('brand_name');
				$brand->state_id = implode(",",$request->post('state_id'));
				$brand->image = str_replace('.jpeg', '.jpg', $imageName);
				$brand->description = $request->post('description');

				$brand->status = trim($request->post('status'));
				$brand->created_at = date('Y-m-d H:i:s');

				if ($brand->save()) {
					$request->session()->flash('success', 'Company added successfully');
					return redirect()->route('brands');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('brands');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('brands');
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
			$brand = Brand::where('id', $id)->first();
			if (isset($brand->id)) {
				return view('backend.brands.view', compact('brand', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('brands');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('brands');
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
            $brand = Brand::where('id', $id)->first();
            if (isset($brand->id)) {
                $type = 'edit';
                $url = route('updateBrand', ['id' => $brand->id]);
                $states = State::select('id','name')->where('status', 'active')->where('type','state')->get();
                return view('backend.brands.create', compact('brand', 'type', 'url', 'states'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('brands');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('brands');
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
			$brand = Brand::where('id', $id)->first();
			if (isset($brand->id)) {

				$validate = Validator($request->all(), [
					'brand_name' => 'required',
					'state_id' => 'required',
					'description' => 'required',
				]);

				$attr = [
					'brand_name' => 'Company Name',
					'state_id' => 'State',
					'description' => 'Description',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createBrand')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('brand_image') != null) {
							$image = $request->file('brand_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($brand->image != null && file_exists(public_path('uploads/brands/' . $brand->image))) {
								if ($brand->image != 'noimage.jpg') {
									//unlink(public_path('uploads/brands/' . $brand->image));
								}
							}
							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/brands'), $imageName);
							//Helper::compress_image(public_path('uploads/brands/' . $imageName), 100);
							$brand->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						
						$brand->name = $request->post('brand_name');
						$brand->state_id = implode(",",$request->post('state_id'));
						$brand->description = $request->post('description');
						$brand->status = trim($request->post('status'));

						if ($brand->save()) {
							$request->session()->flash('success', 'Company updated successfully');
							return redirect()->route('brands');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('brands');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('brands');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('brands');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('brands');
		}

	}

	// activate/deactivate Brand
	public function updateStatus(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$brand = Brand::find($request->statusid);

			if (isset($brand->id)) {
				$brand->status = $request->status;
				if ($brand->save()) {
					$request->session()->flash('success', 'Company updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update Company. Please try again later.');
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

	// activate/deactivate Brand
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$brand = Brand::find($request->statusid);

			if (isset($brand->id)) {
				$brand->status = $request->status;
				if ($brand->save()) {
					echo json_encode(['status' => 1, 'message' => 'Company updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update Company. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Company']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Company']);
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
			$brand = Brand::find($request->deleteid);

			if (isset($brand->id)) {
				$brand->status = 'delete';
				if ($brand->save()) {

					//$this->deleteItems($brand, 1);

					echo json_encode(['status' => 1, 'message' => 'Company deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete Company. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Company']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Company']);
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
				$brand = Brand::find($id);

				if (isset($brand->id)) {
					$brand->status = 'delete';
					if ($brand->save()) {
						//$this->deleteItems($brand, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Company deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Company were deleted. Please try again later.']);
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
				$brand = Brand::find($id);

				if (isset($brand->id)) {
					if ($brand->status == 'active') {
						$brand->status = 'inactive';
					} elseif ($brand->status == 'inactive') {
						$brand->status = 'active';
					}

					if ($brand->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Company updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Companys were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

}
