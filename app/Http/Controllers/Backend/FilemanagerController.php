<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Filemanager;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL; 
use Illuminate\Support\Arr;

class FilemanagerController extends Controller {
	public $filemanager;
	public $columns;

	public function __construct() {
		$this->filemanager = new Filemanager;
		$this->columns = [
			"select", "s_no", "name", "image", "action"
		];
		$this->middleware('permission:filemanager-list|filemanager-create|filemanager-edit|filemanager-delete', ['only' => ['index','store']]);
        $this->middleware('permission:filemanager-create', ['only' => ['create','store']]);
        $this->middleware('permission:filemanager-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:filemanager-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = Filemanager::where('status', '!=', 'delete')->count();
		return view('backend.filemanagers.index', compact('count'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function filemanagerAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->filemanager->fetchFilemanagers($request, $this->columns);
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
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="filemanager_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			$data['sno'] = $i++;
			$data['name'] = $cat->name;
			$data['type'] = ucfirst($cat->type);
			if ($cat->type == 'product') {
				$data['url'] = URL::asset('/uploads/products/' . $cat->image);
				$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/products/' . $cat->image).'" width="70" />' : '-';
			}else{
				$data['url'] = URL::asset('/uploads/doctors/' . $cat->image);
				$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/doctors/' . $cat->image).'" width="70" />' : '-';
			}
			
			

			$action = '';
			
			if (Helper::checkAccess(route('editFilemanager'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editFilemanager', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewFilemanager', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteFilemanager'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteFilemanager" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
		$url = route('addFilemanager');
		$filemanager = new Filemanager;
		return view('backend.filemanagers.create', compact('type', 'url', 'filemanager'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {

		$validate = Validator($request->all(), [
			'filemanager_image' => 'required|mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'filemanager_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createFilemanager')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$filemanager = new Filemanager;

				$imageName = '';
				if ($request->file('filemanager_image') != null) {
					$image = $request->file('filemanager_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					if ($request->type == 'product') {
						$image->move(public_path('uploads/products'), $imageName);
					}else{
						$image->move(public_path('uploads/doctors'), $imageName);
					}
					
					//Helper::compress_image(public_path('uploads/products/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}

				

				$filemanager->type = $request->post('type');
				$filemanager->name = str_replace('.jpeg', '.jpg', $imageName);
				$filemanager->image = str_replace('.jpeg', '.jpg', $imageName);
				$filemanager->created_at = date('Y-m-d H:i:s');

				if ($filemanager->save()) {
					$request->session()->flash('success', 'Filemanager added successfully');
					return redirect()->route('filemanagers');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('filemanagers');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('filemanagers');
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
			$filemanager = Filemanager::where('id', $id)->first();
			if (isset($filemanager->id)) {
				return view('backend.filemanagers.view', compact('filemanager', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('filemanagers');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('filemanagers');
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
            $filemanager = Filemanager::where('id', $id)->first();
            if (isset($filemanager->id)) {
                $type = 'edit';
                $url = route('updateFilemanager', ['id' => $filemanager->id]);
                return view('backend.filemanagers.create', compact('filemanager', 'type', 'url'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('filemanagers');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('filemanagers');
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
			$filemanager = Filemanager::where('id', $id)->first();
			if (isset($filemanager->id)) {

				$validate = Validator($request->all(), [
					'filemanager_image' => 'required|mimes:jpeg,png,jpg,gif,svg',

				]);

				$attr = [
					'filemanager_image' => 'Image',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createFilemanager')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('filemanager_image') != null) {
							$image = $request->file('filemanager_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($filemanager->type == 'product') {
								if ($filemanager->image != null && file_exists(public_path('uploads/products/' . $filemanager->image))) {
									if ($filemanager->image != 'noimage.jpg') {
										//unlink(public_path('uploads/products/' . $filemanager->image));
									}
								}
							}else{
								if ($filemanager->image != null && file_exists(public_path('uploads/doctors/' . $filemanager->image))) {
									if ($filemanager->image != 'noimage.jpg') {
										//unlink(public_path('uploads/doctors/' . $filemanager->image));
									}
									
								}
							}
							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							if ($request->type == 'product') {
								$image->move(public_path('uploads/products'), $imageName);
							}else{
								$image->move(public_path('uploads/doctors'), $imageName);
							}
							//Helper::compress_image(public_path('uploads/products/' . $imageName), 100);
							$filemanager->name = str_replace('.jpeg', '.jpg', $imageName);
							$filemanager->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						$filemanager->type = $request->post('type');
						

						if ($filemanager->save()) {
							$request->session()->flash('success', 'Filemanager updated successfully');
							return redirect()->route('filemanagers');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('filemanagers');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('filemanagers');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('filemanagers');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('filemanagers');
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
			$filemanager = Filemanager::find($request->deleteid);

			if (isset($filemanager->id)) {
				$filemanager->status = 'delete';
				if ($filemanager->save()) {

					//$this->deleteItems($filemanager, 1);

					echo json_encode(['status' => 1, 'message' => 'Filemanager deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete filemanager. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Filemanager']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Filemanager']);
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
				$filemanager = Filemanager::find($id);

				if (isset($filemanager->id)) {
					$filemanager->status = 'delete';
					if ($filemanager->save()) {
						//$this->deleteItems($filemanager, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Filemanager deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Filemanager were deleted. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}


}
