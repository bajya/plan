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

class CategoryController extends Controller {
	public $category;
	public $columns;
	public $restrict;

	public function __construct() {
		$this->category = new Category;
		$this->columns = [
			"select", "s_no", "parent_id", "name", "image", "description", "status", "activate", "action"
		];
		$this->middleware('permission:category-list|category-create|category-edit|category-delete', ['only' => ['index','store']]);
        $this->middleware('permission:category-create', ['only' => ['create','store']]);
        $this->middleware('permission:category-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:category-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = Category::where('status', '!=', 'delete')->count();
		return view('backend.categories.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function categoryAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->category->fetchCategoriesAjax($request, $this->columns);
		$count = $records->get();
		if (isset($request->start)) {
			$list = $records->offset($request->start)->limit($request->length)->get();
		} else {
			$list = $records->offset($request->start)->limit(count($count))->get();
		}
		// echo $total;
		$result = [];
		$categories = [];
		$i = 1;

		foreach ($list as $key => &$value) {
			$level = 1;
			$value->sno = $i . ". ";
			$categories[$value->id] = $value;
			$root = $value;
			$categories = $this->setListView($root, $categories, $i, $request, $level);
			$i++;

		}
		$total = count($categories);
		// die();
		$i = 1;
		foreach ($categories as $cat) {
			$data = [];
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['parent_id'] = ($cat->parent != null) ? $cat->parent : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/categories/' . $cat->image).'" width="70" />' : '-';
			$data['is_defalt'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox"' . ($cat->is_defalt == 'true' ? ' checked' : '') . ' data-id="' . $cat->id . '" name="is_defalt" class="statusCategoryDefalt"><i class="input-helper"></i></label></div>';
			$data['order_no'] = '<div class="form-group  col-md-2 m-t-20"><label class=""><input type="number" data-id="' . $cat->id . '" name="order_no" value="' . $cat->order_no . '" min="1" class="statusCategoryOrder"><i class="input-helper"></i></label></div>';


			$data['description'] = ($cat->description != null) ? $cat->description : '-';
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusCategory"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editCategory'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editCategory', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewCategory', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteCategory'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteCategory" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
	public function setListView($root, $categories, $i, $request, $level) {
		/*$child = $root->childCat;
		$j = 1;
		foreach ($child as &$ch) {
			if ($ch->status != 'delete') {
				$k = $i . "." . $j;
				$ch->sno = $k . '. ';
				$ch->parent = $root->name;
				$categories[$ch->id] = $ch;
				$categories = $this->setListView($ch, $categories, $k, $request, ++$level);
				$level = 2;
				$j++;
			}

		}
		$root = $child;*/
		return $categories;
	}
	public function setList($root, $categories, $i, $level, $id = null)
    {
    	if (isset($root->childCat) && !empty($root->childCat)) {
    		/*$child = $root->childCat;
		        $j = 1;
		        foreach ($child as $ch) {
		            if ($ch->status != 'delete' && ($id == null || ($id != null && $id != $ch->id))) {
		                $k = "&nbsp;&nbsp;" . $i . "." . $j;
		                $categories[$ch->id] = $k . '. ' . $ch->name;
		                $categories = $this->setList($ch, $categories, $k, ++$level, $id);
		                $j++;
		            }
		        }
		        $root = $child;*/
    	}
       
        
        return $categories;
    }
	public function setListOrder($root, $categories, $level) {
		$child = $root->childCat;
			foreach ($child as $ch) {
				if ($ch->status != 'delete') {
					if (!array_key_exists($root->id, $categories)) {
						$categories[$root->id] = array();
					}
					$categories[$root->id][$ch->id] = $ch->name;

					$categories = $this->setListOrder($ch, $categories, ++$level);
					$level = 2;
				}
			}
			$root = $child;
		
		return $categories;
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() { 
		$type = 'add';
		$url = route('addCategory');
		$category = new Category;
		$list = $this->category->fetchCategories()->get(); 

		$categories = [];
		$i = 1;
		$level = 1;
		foreach ($list as $key => $value) {
			$categories[$value->id] = $i . ". " . $value->name;
			$root = $value;
			$categories = $this->setList($root, $categories, $i, $level);
			$i++;
		}
		return view('backend.categories.create', compact('type', 'url', 'category', 'categories'));
	}

	/**
	 * check for unique category
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkCategory(Request $request, $id = null) {
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
			//'description' => 'required',
			//'cat_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'cat_name' => 'Category Name',
			//'description' => 'Description',
			//'cat_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createCategory')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$category = new Category;

				$imageName = '';
				if ($request->file('cat_image') != null) {
					$image = $request->file('cat_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/categories'), $imageName);
					//Helper::compress_image(public_path('uploads/categories/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}

				

				
				$category->name = $request->post('cat_name');
				$category->image = str_replace('.jpeg', '.jpg', $imageName);
				$category->description = $request->post('description');

				if (isset($request->parent_id)) {
					$category->parent_id = $request->post('parent_id');
				}
				$category->type = 'category';
				if ($res = Category::select('order_no')->where('status', '!=', 'delete')->where('type', 'category')->orderBy('order_no', 'desc')->first()) {
					$category->order_no = $res->order_no + 1;
				}else{
					$category->order_no = 1;
				}
				
				$category->status = trim($request->post('status'));
				$category->created_at = date('Y-m-d H:i:s');

				if ($category->save()) {
					$request->session()->flash('success', 'Category added successfully');
					return redirect()->route('categories');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('categories');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('categories');
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
			$category = Category::with(['childCat' => function ($q) {
				$q->where('status', '!=', 'delete');
			}])->where('id', $id)->first();
			if (isset($category->id)) {
				//dd($category);


				$categories = [];
				$i = 1;

				$categories = $this->setListOrder($category, $categories, 1);
				
				return view('backend.categories.view', compact('category', 'categories', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('categories');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('categories');
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
            $category = Category::where('id', $id)->first();
            if (isset($category->id)) {
                $type = 'edit';
                $url = route('updateCategory', ['id' => $category->id]);

                $list = $this->category->fetchCategories($id)->get();

                $categories = [];
                $i = 1;
                foreach ($list as $key => $value) {
                    if ($value->id != $category->id) {

                        $categories[$value->id] = $i . ". " . $value->name;
                        $root = $value;
                        $categories = $this->setList($root, $categories, $i, 1, $id);
                        $i++;
                    }
                }
                $sub = $category;

                return view('backend.categories.create', compact('category', 'type', 'url', 'categories'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('categories');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('categories');
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
			$category = Category::where('id', $id)->first();
			if (isset($category->id)) {

				$validate = Validator($request->all(), [
					'cat_name' => 'required',
					//'description' => 'required',
				]);

				$attr = [
					'cat_name' => 'Category Name',
					//'description' => 'Description',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createCategory')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('cat_image') != null) {
							$image = $request->file('cat_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($category->image != null && file_exists(public_path('uploads/categories/' . $category->image))) {
								if ($category->image != 'noimage.jpg') {
									unlink(public_path('uploads/categories/' . $category->image));
								}
							}

							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/categories'), $imageName);
							//Helper::compress_image(public_path('uploads/categories/' . $imageName), 100);
							$category->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						
						$category->name = $request->post('cat_name');
						$category->description = $request->post('description');
						if (isset($request->parent_id)) {
							$category->parent_id = $request->post('parent_id');
						}
						$category->status = trim($request->post('status'));

						if ($category->save()) {
							$request->session()->flash('success', 'Category updated successfully');
							return redirect()->route('categories');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('categories');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('categories');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('categories');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('categories');
		}

	}

	// activate/deactivate category
	public function updateStatus(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Category::find($request->statusid);

			if (isset($category->id)) {
				$category->status = $request->status;
				if ($category->save()) {
					if ($request->status == 'inactive') {
						DB::transaction(function () use($category) {
		                	Product::where('status', '!=', 'delete')->where('parent_id', $category->id)->update(array('status' => 'inactive'));
			            }, 5);
						$types = Category::where('parent_id', $category->id)->where('type', 'type')->where('status', 'active')->get();
						if (!empty($types)) {
							foreach ($types as $k => $v) {
								$v->status = $request->status;
								$v->save();
								




								/*$starins = Category::where('parent_id', $v->id)->where('type', 'strain')->where('status', 'active')->get();
								if (!empty($starins)) {
									foreach ($starins as $key => $val) {
										$val->status = $request->status;
										$val->save();
									}
								}*/
							}
						}
					}
					$request->session()->flash('success', 'Category updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update category. Please try again later.');
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

	// activate/deactivate category
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Category::find($request->statusid);

			if (isset($category->id)) {
				$category->status = $request->status;
				if ($category->save()) {
					if ($request->status == 'inactive') {
						DB::transaction(function () use($category) {
		                	Product::where('status', '!=', 'delete')->where('parent_id', $category->id)->update(array('status' => 'inactive'));
			            }, 5);
						$types = Category::where('parent_id', $category->id)->where('type', 'type')->where('status', 'active')->get();
						if (!empty($types)) {
							foreach ($types as $k => $v) {
								$v->status = $request->status;
								$v->save();
								/*$starins = Category::where('parent_id', $v->id)->where('type', 'strain')->where('status', 'active')->get();
								if (!empty($starins)) {
									foreach ($starins as $key => $val) {
										$val->status = $request->status;
										$val->save();
									}
								}*/
							}
						}
					}
					echo json_encode(['status' => 1, 'message' => 'Category updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update category. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
		}

	}
	// true/false category
	public function updateStatusDefaltAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Category::find($request->statusid);

			if (isset($category->id)) {
				DB::transaction(function () {
                	Category::where('status', '!=', 'delete')->where('type', 'category')->where('is_defalt', 'true')->update(array('is_defalt' => 'false'));
	            }, 5);
				$category->is_defalt = $request->is_defalt;
				if ($category->save()) {
					if ($request->is_defalt == 'true') {
						echo json_encode(['status' => 1, 'message' => 'Category set successfully default.']);
					}else{
						echo json_encode(['status' => 1, 'message' => 'Category unset successfully default.']);
					}
					
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update category default. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
		}

	}
	// Order category
	public function updateStatusOrderAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$category = Category::find($request->statusid);

			if (isset($category->id)) {
					if ($res = Category::where('status', '!=', 'delete')->where('type', 'category')->where('order_no', $request->order_no)->first()) {
						$res->order_no = $category->order_no;
						$res->save();
					}
				$category->order_no = $request->order_no;
				if ($category->save()) {
					echo json_encode(['status' => 1, 'message' => 'Category order successfully update.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update category order. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
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
			$category = Category::find($request->deleteid);

			if (isset($category->id)) {
				$category->status = 'delete';
				if ($category->save()) {

					$this->deleteItems($category, 1);

					echo json_encode(['status' => 1, 'message' => 'Category deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete category. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Category']);
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
				$category = Category::find($id);

				if (isset($category->id)) {
					$category->status = 'delete';
					if ($category->save()) {
						$this->deleteItems($category, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Category deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all category were deleted. Please try again later.']);
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
				$category = Category::find($id);

				if (isset($category->id)) {
					if ($category->status == 'active') {
						$category->status = 'inactive';
					} elseif ($category->status == 'inactive') {
						$category->status = 'active';
					}

					if ($category->save()) {
						if ($category->status == 'inactive') {
							DB::transaction(function () use($category) {
			                	Product::where('status', '!=', 'delete')->where('parent_id', $category->id)->update(array('status' => 'inactive'));
				            }, 5);
							$types = Category::where('parent_id', $category->id)->where('type', 'type')->where('status', 'active')->get();
							if (!empty($types)) {
								foreach ($types as $k => $v) {
									$v->status = $category->status;
									$v->save();
									/*$starins = Category::where('parent_id', $v->id)->where('type', 'strain')->where('status', 'active')->get();
									if (!empty($starins)) {
										foreach ($starins as $key => $val) {
											$val->status = $category->status;
											$val->save();
										}
									}*/
								}
							}
						}
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Category updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all categories were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}

}
