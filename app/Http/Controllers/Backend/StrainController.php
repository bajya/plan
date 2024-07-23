<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Strain;
use App\Brand;
use App\Dispensary;
use App\Product;
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
		$this->strain = new Strain;
		$this->columns = [
			"select", "s_no", "brand_id", "dispensary_id", "name", "image", "description", "status", "activate", "action"
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
		$brands = Brand::select('id','name')->where('status', '!=','delete')->get();
        $dispensarys = array();
		$count = Strain::where('status', '!=', 'delete')->count();
		return view('backend.strains.index', compact('count', 'brands', 'dispensarys'));
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
		$records = $this->strain->fetchStrains1($request, $this->columns);
		$total = $records->count();
        if (isset($request->start)) {
            $list = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $list = $records->offset($request->start)->limit($total)->get();
        }
		$result = [];
		// die();
		$i = 1;
		foreach ($list as $cat) {
			$data = [];
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="strain_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['brand_id'] = isset($cat->brand->name) && !empty($cat->brand->name) ? ucfirst($cat->brand->name) : '-';
            $data['dispensary_id'] = isset($cat->dispensary->name) && !empty($cat->dispensary->name) ? $cat->dispensary->name : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/strains/' . $cat->image).'" width="70" />' : '-';

			$data['order_no'] = '<div class="form-group  col-md-2 m-t-20"><label class=""><input type="number" data-id="' . $cat->id . '" name="order_no" value="' . $cat->order_no . '" min="1" class="statusCategoryOrder"><i class="input-helper"></i></label></div>';
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
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() { 
		$type = 'add';
		$url = route('addStrain');
		$strain = new Strain;
		

        $brands = Brand::select('id','name')->where('status', '!=', 'delete')->get();
        $dispensarys = array();
		return view('backend.strains.create', compact('type', 'url', 'strain', 'brands'));
	}

	/**
	 * check for unique strain
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */ 
	public function checkStrain(Request $request, $id = null) {
		if (isset($request->strain_name)) {
			$check = Strain::where('name', $request->strain_name);
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
			'strain_name' => 'required',
			//'dispensary_id' => 'required',
			//'brand_id' => 'required',
			//'description' => 'required',
			//'strain_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'strain_name' => 'Strain Name',
			//'dispensary_id' => 'Location',
			//'brand_id' => 'Company',
			//'description' => 'Description',
			//'strain_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createStrain')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$strain = new Strain;

				$imageName = '';
				if ($request->file('strain_image') != null) {
					$image = $request->file('strain_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/strains'), $imageName);
					//Helper::compress_image(public_path('uploads/strains/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}

				

				
				$strain->name = $request->post('strain_name');
				$strain->image = str_replace('.jpeg', '.jpg', $imageName);
				$strain->description = $request->post('description');
				if ($res = Strain::select('order_no')->where('status', '!=', 'delete')->orderBy('order_no', 'desc')->first()) {
					$strain->order_no = $res->order_no + 1;
				}else{
					$strain->order_no = 1;
				}
				//$strain->brand_id = $request->post('brand_id');
				//$strain->dispensary_id = $request->post('dispensary_id');
				
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
			$strain = Strain::where('id', $id)->first();
			if (isset($strain->id)) {
				//dd($strain);
				
				return view('backend.strains.view', compact('strain', 'type'));
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
            $strain = Strain::where('id', $id)->first();
            if (isset($strain->id)) {
                $type = 'edit';
                $url = route('updateStrain', ['id' => $strain->id]);

                $brands = Brand::select('id','name')->where('status', '!=', 'delete')->get();
                $dispensarys = Dispensary::select('id','name')->where('status', '!=', 'delete')->where('brand_id', $strain->brand_id)->get();
                return view('backend.strains.create', compact('strain', 'type', 'url', 'brands', 'dispensarys'));
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
			$strain = Strain::where('id', $id)->first();
			if (isset($strain->id)) {

				$validate = Validator($request->all(), [
					'strain_name' => 'required',
					//'dispensary_id' => 'required',
					//'brand_id' => 'required',
					//'description' => 'required',
				]);

				$attr = [
					'strain_name' => 'Strain Name',
					//'dispensary_id' => 'Location',
					//'brand_id' => 'Company',
					//'description' => 'Description',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createStrain')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('strain_image') != null) {
							$image = $request->file('strain_image');
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
						
						$strain->name = $request->post('strain_name');
						$strain->description = $request->post('description');
						//$strain->brand_id = $request->post('brand_id');
						//$strain->dispensary_id = $request->post('dispensary_id');
						
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
			$strain = Strain::find($request->statusid);

			if (isset($strain->id)) {
				$strain->status = $request->status;
				if ($strain->save()) {
					$request->session()->flash('success', 'Strain updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update strain. Please try again later.');
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

	// activate/deactivate strain
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$strain = Strain::find($request->statusid);

			if (isset($strain->id)) {
				$strain->status = $request->status;
				if ($strain->save()) {
					if ($request->status == 'inactive') {
						DB::transaction(function () use($strain) {
		                	Product::where('status', '!=', 'delete')->where('strain_id', $strain->id)->update(array('status' => 'inactive'));
			            }, 5);
					}
					echo json_encode(['status' => 1, 'message' => 'Strain updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update strain. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
		}

	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function destroy(Request $request) {
		if (isset($request->deleteid) && $request->deleteid != null) {
			$strain = Strain::find($request->deleteid);

			if (isset($strain->id)) {
				$strain->status = 'delete';
				if ($strain->save()) {
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
				$strain = Strain::find($id);

				if (isset($strain->id)) {
					$strain->status = 'delete';
					if ($strain->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Strain deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all strain were deleted. Please try again later.']);
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
				$strain = Strain::find($id);

				if (isset($strain->id)) {
					if ($strain->status == 'active') {
						$strain->status = 'inactive';
					} elseif ($strain->status == 'inactive') {
						$strain->status = 'active';
					}

					if ($strain->save()) {
						if ($strain->status == 'inactive') {
							DB::transaction(function () use($strain) {
			                	Product::where('status', '!=', 'delete')->where('strain_id', $strain->id)->update(array('status' => 'inactive'));
				            }, 5);
						}
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Strain updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all strains were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}
	// Order Strain
	public function updateStatusOrderAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Strain::find($request->statusid);

			if (isset($category->id)) {
					if ($res = Strain::where('status', '!=', 'delete')->where('order_no', $request->order_no)->first()) {
						$res->order_no = $category->order_no;
						$res->save();
					}
				$category->order_no = $request->order_no;
				if ($category->save()) {
					echo json_encode(['status' => 1, 'message' => 'Strain order successfully update.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update Strain order. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Strain']);
		}

	}
}
