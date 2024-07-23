<?php

namespace App\Http\Controllers\Backend;

use App\Library\Helper;
use App\Library\Notify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Dispensary;
use App\Brand;
use App\CustomLog;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL;
use Zip;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use App\Imports\DispensaryImport;
use Illuminate\Support\Str;
use App\Events\BulkImageCrop;
use App\ImportFile;
use Symfony\Component\Finder\SplFileInfo;

class DispensaryController extends Controller
{
	public $dispensary;
	public $columns;
	public $logs;

	public function __construct()
	{
		$this->dispensary = new Dispensary;
		$this->logs = new CustomLog;
		$this->columns = [
			"select", "brand_id", "location_id", "name", "image", "description", "status", "activate", "action"
		];
		$this->middleware('permission:dispensary-list|dispensary-create|dispensary-edit|dispensary-delete', ['only' => ['index', 'store']]);
		$this->middleware('permission:dispensary-create', ['only' => ['create', 'store']]);
		$this->middleware('permission:dispensary-edit', ['only' => ['edit', 'update']]);
		$this->middleware('permission:dispensary-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$count = Dispensary::where('status', '!=', 'delete')->count();
		$brands = Brand::select('id', 'name')->where('status', '!=','delete')->orderBy('name', 'asc')->get();
		return view('backend.dispensaries.index', compact('count', 'brands'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function dispensaryAjax(Request $request)
	{
		if (isset($request->search['value'])) {
			$request->search = $request->search['value'];
		} else {
			$request->search = '';
		}
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->dispensary->fetchDispensaries($request, $this->columns);
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
			$data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $cat->id . '"><i class="input-helper"></i></label></div>';
			//$data['sno'] = $i++;
			$data['brand_id'] = ($cat->brand->name != null) ? $cat->brand->name : '-';
			$data['location_id'] = ($cat->location_id != null) ? $cat->location_id : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="' . URL::asset('/uploads/brands/' . $cat->image) . '" width="70" />' : '-';
			$data['description'] = ($cat->description != null) ? $cat->description : '-';
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));
			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusDispensary"></div></div>';
			$action = '';

			if (Helper::checkAccess(route('editDispensary'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editDispensary', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewDispensary', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteDispensary'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteDispensary" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
	public function create()
	{
		$type = 'add';
		$url = route('addDispensary');
		$dispensary = new Dispensary;
		$brands = Brand::select('id', 'name')->where('status', '!=','delete')->orderBy('name', 'asc')->get();
		return view('backend.dispensaries.create', compact('type', 'url', 'dispensary', 'brands'));
	}

	/**
	 * check for unique dispensary
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkDispensary(Request $request, $id = null)
	{
		if (isset($request->dis_name)) {
			$check = Dispensary::where('name', $request->dis_name);
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
	public function store(Request $request)
	{

		$validate = Validator($request->all(), [
			'dis_name' => 'required',
			'location_id' => 'required',
			'description' => 'required',
			'brand_id' => 'required',
			'address' => 'required',
			'phone_number' => 'required|min:8|numeric',
			'location_url' => 'required',
			'location_email' => 'required|email',
			'dis_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'dis_name' => 'Location Name',
			'location_id' => 'Location Id',
			'description' => 'Description',
			'brand_id' => 'Brand',
			'address' => 'Address',
			'phone_number' => 'Phone Number',
			'location_url' => 'Location Url',
			'location_email' => 'Location Email',
			'dis_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createDispensary')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$dispensary = new Dispensary;
				$imageName = '';
				if ($request->file('dis_image') != null) {
					$image = $request->file('dis_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/brands'), $imageName);
					//Helper::compress_image(public_path('uploads/brands/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}
				$dispensary->name = $request->post('dis_name');
				$dispensary->location_id = $request->post('location_id');
				$dispensary->phone_number = $request->post('phone_number');
				$dispensary->address = $request->post('address');
				$dispensary->brand_id = $request->post('brand_id');
				$dispensary->lat = $request->post('lat');
				$dispensary->lng = $request->post('lng');
				$dispensary->country = $request->post('country');
				$dispensary->state = $request->post('state');
				$dispensary->city = $request->post('city');
				$dispensary->image = str_replace('.jpeg', '.jpg', $imageName);
				$dispensary->description = $request->post('description');
				$dispensary->location_email = $request->post('location_email');
				$dispensary->location_url = $request->post('location_url');

				$dispensary->status = trim($request->post('status'));
				$dispensary->created_at = date('Y-m-d H:i:s');

				if ($dispensary->save()) {
					$request->session()->flash('success', 'Location added successfully');
					return redirect()->route('dispensaries');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('dispensaries');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('dispensaries');
			}
		}
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id = null)
	{
		if (isset($id) && $id != null) {
			$type = 'Show';
			$dispensary = Dispensary::where('id', $id)->first();
			if (isset($dispensary->id)) {
				return view('backend.dispensaries.view', compact('dispensary', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('dispensaries');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('dispensaries');
		}
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id = null)
	{
		if (isset($id) && $id != null) {
			$dispensary = Dispensary::where('id', $id)->first();
			$brands = Brand::select('id', 'name')->where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
			if (isset($dispensary->id)) {
				$type = 'edit';
				$url = route('updateDispensary', ['id' => $dispensary->id]);
				return view('backend.dispensaries.create', compact('dispensary', 'type', 'url', 'brands'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('dispensaries');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('dispensaries');
		}
	}



	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id = null)
	{
		if (isset($id) && $id != null) {
			$dispensary = Dispensary::where('id', $id)->first();
			if (isset($dispensary->id)) {
				$validate = Validator($request->all(), [
					'dis_name' => 'required',
					'location_id' => 'required',
					'description' => 'required',
					'brand_id' => 'required',
					'address' => 'required',
					'phone_number' => 'required|min:8|numeric',
					'location_url' => 'required',
					'location_email' => 'required|email',
				]);

				$attr = [
					'dis_name' => 'Location Name',
					'location_id' => 'Location Id',
					'description' => 'Description',
					'brand_id' => 'Brand',
					'address' => 'Address',
					'phone_number' => 'Phone Number',
					'location_url' => 'Location Url',
					'location_email' => 'Location Email',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createDispensary')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('dis_image') != null) {
							$image = $request->file('dis_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($dispensary->image != null && file_exists(public_path('uploads/brands/' . $dispensary->image))) {
								if ($dispensary->image != 'noimage.jpg') {
									//unlink(public_path('uploads/brands/' . $dispensary->image));
								}
							}
							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/brands'), $imageName);
							//Helper::compress_image(public_path('uploads/brands/' . $imageName), 100);
							$dispensary->image = str_replace('.jpeg', '.jpg', $imageName);
						}

						$dispensary->name = $request->post('dis_name');
						$dispensary->location_id = $request->post('location_id');
						$dispensary->phone_number = $request->post('phone_number');
						$dispensary->address = $request->post('address');
						$dispensary->brand_id = $request->post('brand_id');
						$dispensary->lat = $request->post('lat');
						$dispensary->lng = $request->post('lng');
						$dispensary->country = $request->post('country');
						$dispensary->state = $request->post('state');
						$dispensary->city = $request->post('city');
						$dispensary->description = $request->post('description');
						$dispensary->location_email = $request->post('location_email');
						$dispensary->location_url = $request->post('location_url');
						$dispensary->status = trim($request->post('status'));

						if ($dispensary->save()) {
							$request->session()->flash('success', 'Location updated successfully');
							return redirect()->route('dispensaries');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('dispensaries');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('dispensaries');
					}
				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('dispensaries');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('dispensaries');
		}
	}

	// activate/deactivate Dispensary
	public function updateStatus(Request $request)
	{

		if (isset($request->statusid) && $request->statusid != null) {
			$dispensary = Dispensary::find($request->statusid);

			if (isset($dispensary->id)) {
				$dispensary->status = $request->status;
				if ($dispensary->save()) {
					$request->session()->flash('success', 'Location updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update Location. Please try again later.');
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

	// activate/deactivate Dispensary
	public function updateStatusAjax(Request $request)
	{
		if (isset($request->statusid) && $request->statusid != null) {
			$dispensary = Dispensary::find($request->statusid);
			if (isset($dispensary->id)) {
				$dispensary->status = $request->status;
				if ($dispensary->save()) {
					echo json_encode(['status' => 1, 'message' => 'Location updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update Location. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Location']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Location']);
		}
	}

	public function deleteItems($root, $level)
	{
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
	public function destroy(Request $request)
	{
		if (isset($request->deleteid) && $request->deleteid != null) {
			$dispensary = Dispensary::find($request->deleteid);

			if (isset($dispensary->id)) {
				$dispensary->status = 'delete';
				if ($dispensary->save()) {

					//$this->deleteItems($dispensary, 1);

					echo json_encode(['status' => 1, 'message' => 'Location deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete Location. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Location']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Location']);
		}
	}
	/**
	 * Remove multiple resource from storage.
	 *
	 * @param  \Illuminate\Http\Request
	 * @return \Illuminate\Http\Response
	 */
	public function bulkdelete(Request $request)
	{

		if (isset($request->deleteid) && $request->deleteid != null) {
			$deleteid = explode(',', $request->deleteid);
			$ids = count($deleteid);
			$count = 0;
			foreach ($deleteid as $id) {
				$dispensary = Dispensary::find($id);

				if (isset($dispensary->id)) {
					$dispensary->status = 'delete';
					if ($dispensary->save()) {
						//$this->deleteItems($dispensary, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Location deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Location were deleted. Please try again later.']);
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
	public function bulkchangeStatus(Request $request)
	{

		if (isset($request->ids) && $request->ids != null) {
			$ids = count($request->ids);
			$count = 0;
			foreach ($request->ids as $id) {
				$dispensary = Dispensary::find($id);

				if (isset($dispensary->id)) {
					if ($dispensary->status == 'active') {
						$dispensary->status = 'inactive';
					} elseif ($dispensary->status == 'inactive') {
						$dispensary->status = 'active';
					}

					if ($dispensary->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Location updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all dispensaries were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}
	/**
	 * Show the form for importing dispensaries sheet
	 *
	 * @return \Illuminate\Http\Response
	 */

	public function import(Request $request)
	{
		$url = route('importDispensary');
		if (strtolower($request->method()) == 'post') {
			$validate = Validator($request->all(), [
				'location_import' => 'required|mimes:csv,zip',
			]);
			$attr = [
				'location_import' => 'File Csv and Zip',
			];
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('importDispensary')->withInput($request->all())->withErrors($validate);
			} else {
				$uniqueId = 0;
				if (request()->file('location_import')->extension() == 'csv') {
					$filenameArray = explode("_",substr(request()->file('location_import')->getClientOriginalName(), 0, strrpos(request()->file('location_import')->getClientOriginalName(), ".")));

                    if( in_array("locations" ,$filenameArray))
                    {
                        $file_name = 'locations';
                    }else{
                        $file_name = '';
                    } 
					if ($file_name == 'locations') {
                        $file = fopen($request->location_import->getRealPath(), "r");
						$completeSheetData = array();
						while (!feof($file)) {
							$completeSheetData[] = fgetcsv($file);
						}

						fclose($file);

						$heading = $completeSheetData[0];

						$data = array_slice($completeSheetData, 1);

						$Allparts = (array_chunk($data, 500));



						$insertData = array();
						foreach ($Allparts as $key => $parts) {

							$uniqueId = Helper::generateNumber('import_files', 'id');
							$orgName = time() . $key . $uniqueId . ".csv";
							$fileName = public_path("pendingfile/" . $orgName);
							$file = fopen($fileName, "w");
							fputcsv($file, $heading);

							foreach ($parts as $key1 => $val) {
								if (!empty($val)) {
									fputcsv($file, $val);
								}						
							}
							$insertData[$key]['filename'] = $orgName;
							$insertData[$key]['status'] = 0;
							$insertData[$key]['type'] = "location";
							fclose($file);
						}
						ImportFile::insert($insertData);
						$locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();
				        if (!empty($locationsFiles)) {
				        	foreach ($locationsFiles as $key => $locationsFile) {
					            if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
					                $file = public_path("pendingfile/" . $locationsFile->filename);
					                ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

					                Excel::import(new DispensaryImport, $file);
					                ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

					                if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
					                    unlink(public_path('pendingfile/' . $locationsFile->filename));
					                    
					                }
					                ImportFile::where("id",$locationsFile->id)->delete();
					            }
				            }

				        }

						// Excel::import(new DispensaryImport,request()->file('location_import'));
                    }else{
                        $request->session()->flash('error', 'Please put file name ex. name_locations.');
                        return redirect()->route('importDispensary');
                    }
					
				} else {
					\File::deleteDirectory('uploads/dispensaries/zip-csv');
					$zip = Zip::open($request->location_import);
					$zip->extract('uploads/dispensaries/zip-csv');
					$files = \File::allFiles('uploads/dispensaries/zip-csv');
					if (!empty($files)) {
						foreach ($files as $key => $file) {
							if ($file != null) {
								$filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
			                    if( in_array("locations" ,$filenameArray))
			                    {
			                        $file_name = 'locations';
			                    }else{
			                        $file_name = '';
			                    }
								if ($file_name == 'locations') {
			                        $tempFile = fopen($file->getRealPath(), "r");
									$completeSheetData = array();
									while (!feof($tempFile)) {
										$completeSheetData[] = fgetcsv($tempFile);
									}
									fclose($tempFile);
									$heading = $completeSheetData[0];
									$data = array_slice($completeSheetData, 1);
									$Allparts = (array_chunk($data, 500));
									$insertData = array();
									foreach ($Allparts as $key => $parts) {
										$uniqueId = Helper::generateNumber('import_files', 'id');
										$orgName = time() . $key . $uniqueId . ".csv";
										$fileName = public_path("pendingfile/" . $orgName);
										$file = fopen($fileName, "w");
										fputcsv($file, $heading);

										foreach ($parts as $key1 => $val) {
											if (!empty($val)) {
												fputcsv($file, $val);
											}						
										}
										$insertData[$key]['filename'] = $orgName;
										$insertData[$key]['status'] = 0;
										$insertData[$key]['type'] = "location";
										fclose($file);
									}
									ImportFile::insert($insertData);
									$locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();
							        if (!empty($locationsFiles)) {
							        	foreach ($locationsFiles as $key => $locationsFile) {
								            if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
								                $file = public_path("pendingfile/" . $locationsFile->filename);
								                ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

								                Excel::import(new DispensaryImport, $file);
								                ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

								                if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
								                    unlink(public_path('pendingfile/' . $locationsFile->filename));
								                    
								                }
								                ImportFile::where("id",$locationsFile->id)->delete();
								            }
							            }

							        }
			                    }else{
			                        $request->session()->flash('error', 'Please put file name ex. name_locations.');
			                        return redirect()->route('importDispensary');
			                    }
								
							}
						}
					//	\Artisan::call('cache:clear');
					}
				}
				$request->session()->flash('success', 'Data imported successfully Please wait 40 to 60 second, your data will appear');
				return redirect()->route('importDispensary');
			}
		}
		return view('backend.dispensaries.import', compact('url'));
	}
	public function importCron(Request $request)
	{
		$url = route('importDispensary');
		if (strtolower($request->method()) == 'post') {
			$validate = Validator($request->all(), [
				'location_import' => 'required|mimes:csv,zip',
			]);
			$attr = [
				'location_import' => 'File Csv and Zip',
			];
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('importDispensary')->withInput($request->all())->withErrors($validate);
			} else {
				$uniqueId = 0;
				if (request()->file('location_import')->extension() == 'csv') {
					$filenameArray = explode("_",substr(request()->file('location_import')->getClientOriginalName(), 0, strrpos(request()->file('location_import')->getClientOriginalName(), ".")));

                    if( in_array("locations" ,$filenameArray))
                    {
                        $file_name = 'locations';
                    }else{
                        $file_name = '';
                    } 
					if ($file_name == 'locations') {
                        $file = fopen($request->location_import->getRealPath(), "r");
						$completeSheetData = array();
						while (!feof($file)) {
							$completeSheetData[] = fgetcsv($file);
						}

						fclose($file);

						$heading = $completeSheetData[0];

						$data = array_slice($completeSheetData, 1);

						$Allparts = (array_chunk($data, 500));



						$insertData = array();
						foreach ($Allparts as $key => $parts) {

							$uniqueId = Helper::generateNumber('import_files', 'id');
							$orgName = time() . $key . $uniqueId . ".csv";
							$fileName = public_path("pendingfile/" . $orgName);
							$file = fopen($fileName, "w");
							fputcsv($file, $heading);

							foreach ($parts as $key1 => $val) {
								if (!empty($val)) {
									fputcsv($file, $val);
								}						
							}
							$insertData[$key]['filename'] = $orgName;
							$insertData[$key]['status'] = 0;
							$insertData[$key]['type'] = "location";
							fclose($file);
						}
						ImportFile::insert($insertData);

						// Excel::import(new DispensaryImport,request()->file('location_import'));
                    }else{
                        $request->session()->flash('error', 'Please put file name ex. name_locations.');
                        return redirect()->route('importDispensary');
                    }
					
				} else {
					\File::deleteDirectory('uploads/dispensaries/zip-csv');
					$zip = Zip::open($request->location_import);
					$zip->extract('uploads/dispensaries/zip-csv');
					$files = \File::allFiles('uploads/dispensaries/zip-csv');
					if (!empty($files)) {
						foreach ($files as $key => $file) {
							if ($file != null) {
								$filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
			                    if( in_array("locations" ,$filenameArray))
			                    {
			                        $file_name = 'locations';
			                    }else{
			                        $file_name = '';
			                    }
								if ($file_name == 'locations') {
			                        $tempFile = fopen($file->getRealPath(), "r");
									$completeSheetData = array();
									while (!feof($tempFile)) {
										$completeSheetData[] = fgetcsv($tempFile);
									}
									fclose($tempFile);
									$heading = $completeSheetData[0];
									$data = array_slice($completeSheetData, 1);
									$Allparts = (array_chunk($data, 500));
									$insertData = array();
									foreach ($Allparts as $key => $parts) {
										$uniqueId = Helper::generateNumber('import_files', 'id');
										$orgName = time() . $key . $uniqueId . ".csv";
										$fileName = public_path("pendingfile/" . $orgName);
										$file = fopen($fileName, "w");
										fputcsv($file, $heading);

										foreach ($parts as $key1 => $val) {
											if (!empty($val)) {
												fputcsv($file, $val);
											}						
										}
										$insertData[$key]['filename'] = $orgName;
										$insertData[$key]['status'] = 0;
										$insertData[$key]['type'] = "location";
										fclose($file);
									}
									ImportFile::insert($insertData);
			                    }else{
			                        $request->session()->flash('error', 'Please put file name ex. name_locations.');
			                        return redirect()->route('importDispensary');
			                    }
								
							}
						}
					//	\Artisan::call('cache:clear');
					}
				}
				$request->session()->flash('success', 'Data imported successfully Please wait 40 to 60 second, your data will appear');
				return redirect()->route('importDispensary');
			}
		}
		return view('backend.dispensaries.import', compact('url'));
	}

	public function importNumber(Request $request)
	{
		$url = route('importDispensary');
		if (strtolower($request->method()) == 'post') {
			$validate = Validator($request->all(), [
				'location_import' => 'required|mimes:csv,zip',
			]);
			$attr = [
				'location_import' => 'File Csv and Zip',
			];
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('importDispensary')->withInput($request->all())->withErrors($validate);
			} else {
				if (request()->file('location_import')->extension() == 'csv') {
					$file = file($request->location_import->getRealPath());
					$heading = $file[0];
					$data = array_slice($file, 1);

					$parts = (array_chunk($data, 500));
					$insertData = array();
					foreach ($parts as $key => $val) {
						array_unshift($val, $heading);

						$orgName = time() . $key . ".csv";
						$fileName = public_path("pendingfile/" . $orgName);
						file_put_contents($fileName, $val);
						$insertData[$key]['filename'] = $orgName;
						$insertData[$key]['status'] = 0;
						$insertData[$key]['type'] = "location";
					}
					ImportFile::insert($insertData);

					// Excel::import(new DispensaryImport,request()->file('location_import'));
				} else {
					\File::deleteDirectory('uploads/dispensaries/zip-csv');
					$zip = Zip::open($request->location_import);
					$zip->extract('uploads/dispensaries/zip-csv');
					$files = \File::allFiles('uploads/dispensaries/zip-csv');
					if (!empty($files)) {
						foreach ($files as $key => $file) {
							if ($file != null) {

								$tempFile = file($file->getRealPath());
								$heading = $tempFile[0];
								$data = array_slice($tempFile, 1);
								$parts = (array_chunk($data, 500));
								$insertData = array();
								foreach ($parts as $key => $val) {
									array_unshift($val, $heading);
									$orgName = time() . $key . ".csv";
									$fileName = public_path("pendingfile/" . $orgName);
									file_put_contents($fileName, $val);
									$insertData[$key]['filename'] = $orgName;
									$insertData[$key]['status'] = 0;
									$insertData[$key]['type'] = "location";
								}
								ImportFile::insert($insertData);
							}
						}
					//	\Artisan::call('cache:clear');
					}
				}
				$request->session()->flash('success', 'Data imported successfully Please wait 5 to 6 minutes, your data will appear');
				return redirect()->route('importDispensary');
			}
		}
		return view('backend.dispensaries.import', compact('url'));
	}
	public function importold(Request $request)
	{
		$url = route('importDispensary');
		if (strtolower($request->method()) == 'post') {
			$validate = Validator($request->all(), [
				'location_import' => 'required|mimes:csv,zip',
			]);
			$attr = [
				'location_import' => 'File Csv and Zip',
			];
			$validate->setAttributeNames($attr);

			if ($validate->fails()) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('importDispensary')->withInput($request->all())->withErrors($validate);
			} else {
				if (request()->file('location_import')->extension() == 'csv') {
					Excel::import(new DispensaryImport, request()->file('location_import'));
				} else {
					\File::deleteDirectory('uploads/dispensaries/zip-csv');
					$zip = Zip::open($request->location_import);
					$zip->extract('uploads/dispensaries/zip-csv');
					$files = \File::allFiles('uploads/dispensaries/zip-csv');
					if (!empty($files)) {
						foreach ($files as $key => $file) {
							if ($file != null) {
								try {
									Excel::import(new DispensaryImport, $file);
								} catch (NoTypeDetectedException $e) {
									//$request->session()->flash('error', 'Sorry you are using a wrong format to upload files.');
									//return redirect()->back();
								}
							}
						}
					//	\Artisan::call('cache:clear');
					}
				}
			}
		}

		return view('backend.dispensaries.import', compact('url'));
	}
	public function locationLogsAjax(Request $request)
	{
		if (isset($request->search['value'])) {
			$request->search = $request->search['value'];
		} else {
			$request->search = '';
		}
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->logs->fetchLocationLog($request, $this->columns);
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
			$data['sno'] = $list->sno;
			$data['title'] = ucfirst($list->title);
			$logData = '';
			$logData .= '<div class="log_data">
                    <span>Sno :</span> ' . $list->sno . '
                  </div>';
			$res = json_decode($list->description);
			if (!empty($res)) {
				foreach ($res as $k => $v) {
					$logData .= '<div class="log_data">
                    <span>' . $k . ' :</span> ' . $v . '
                  </div>';
				}
			}

			$data['description'] = $logData;
			$result[] = $data;
		}
		$data = json_encode([
			'data' => $result,
			'recordsTotal' => count($total),
			'recordsFiltered' => count($total),
		]);
		echo $data;
	}
}
