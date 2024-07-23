<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Doctor;
use App\Brand;
use App\Filemanager;
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
use App\Imports\DoctorImport;
use Illuminate\Support\Str;
use App\Events\BulkImageCrop;
use Symfony\Component\Finder\SplFileInfo; 

class DoctorController extends Controller {
	public $doctor;
	public $columns;
	public $logs;

	public function __construct() {
		$this->doctor = new Doctor;
		$this->logs = new CustomLog;
		$this->columns = [
			"select", "name", "image", "address", "zipcode", "status", "activate", "action"
		];
		$this->middleware('permission:doctor-list|doctor-create|doctor-edit|doctor-delete', ['only' => ['index','store']]);
        $this->middleware('permission:doctor-create', ['only' => ['create','store']]);
        $this->middleware('permission:doctor-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:doctor-delete', ['only' => ['destroy']]);
	}
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request) {
		$count = Doctor::where('status', '!=', 'delete')->count();
		$brands = Brand::select('id','name')->where('status', 'active')->get();
		return view('backend.doctors.index', compact('count', 'brands'));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function doctorAjax(Request $request) {
		if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
		if (isset($request->order[0]['column'])) {
			$request->order_column = $request->order[0]['column'];
			$request->order_dir = $request->order[0]['dir'];
		}
		$records = $this->doctor->fetchDoctors($request, $this->columns);
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
			//$data['brand_id'] = ($cat->brand->name != null) ? $cat->brand->name : '-';
			$data['name'] = $cat->name;
			$data['image'] = ($cat->image != null) ? '<img src="'.URL::asset('/uploads/doctors/' . $cat->image).'" width="70" />' : '-';
			$data['address'] = ($cat->address != null) ? $cat->address : '-';
			$data['zipcode'] = ($cat->zipcode != null) ? $cat->zipcode : '-';
			$data['status'] = ucfirst(config('constants.STATUS.' . $cat->status));

			$data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($cat->status == 'active' ? ' checked' : '') . ' data-id="' . $cat->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusDoctor"></div></div>';
			

			$action = '';
			
			if (Helper::checkAccess(route('editDoctor'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editDoctor', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
			}
			$action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewDoctor', ['id' => $cat->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
			if (Helper::checkAccess(route('deleteDoctor'))) {
				$action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteDoctor" data-toggle="tooltip" data-placement="bottom" data-id="' . $cat->id . '" title="Delete"><i class="fa fa-times"></i></a>';
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
		$url = route('addDoctor');
		$doctor = new Doctor;
		$brands = Brand::select('id','name')->where('status', 'active')->get();
		return view('backend.doctors.create', compact('type', 'url', 'doctor', 'brands'));
	}

	/**
	 * check for unique doctor
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function checkDoctor(Request $request, $id = null) {
		if (isset($request->doc_name)) {
			$check = Doctor::where('name', $request->doc_name);
			if (isset($id) && $id != null) {
				$check = $check->where('id', '!=', $id);
			}
			if (isset($request->brand_id) && $request->brand_id != null) {
				$check = $check->where('brand_id', $request->brand_id);
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
			'doc_name' => 'required',
			//'brand_id' => 'required',
			'address' => 'required',
			'phone_number' => 'required|min:8|numeric',
			'doc_email' => 'required|email',
			'zipcode' => 'required',
			'doc_image' => 'mimes:jpeg,png,jpg,gif,svg',

		]);

		$attr = [
			'doc_name' => 'Doctor Name',
			//'brand_id' => 'Brand',
			'address' => 'Address',
			'phone_number' => 'Phone Number',
			'doc_email' => 'Location Email',
			'zipcode' => 'Zip Code',
			'doc_image' => 'Image',
		];

		$validate->setAttributeNames($attr);

		if ($validate->fails()) {
			return redirect()->route('createDoctor')->withInput($request->all())->withErrors($validate);
		} else {
			try {
				$imageName = '';
				if ($request->file('doc_image') != null) {
					$image = $request->file('doc_image');
					$imageName = time() . $image->getClientOriginalName();
					$imageName = str_replace(' ', '', $imageName);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
					$image->move(public_path('uploads/doctors'), $imageName);
					//Helper::compress_image(public_path('uploads/dispensaries/' . $imageName), 100);
					$imageName = str_replace('.jpeg', '.jpg', $imageName);
				}
				$doctor = new Doctor;
				$doctor->name = $request->post('doc_name');
				$doctor->phone_number = $request->post('phone_number');
				$doctor->address = $request->post('address');
				$doctor->brand_id = $request->post('brand_id');
				$doctor->lat = $request->post('lat');
				$doctor->lng = $request->post('lng');
				$doctor->country = $request->post('country');
				$doctor->state = $request->post('state');
				$doctor->city = $request->post('city');
				$doctor->email = $request->post('doc_email');
				$doctor->zipcode = $request->post('zipcode');
				$doctor->status = trim($request->post('status'));
				$doctor->image = str_replace('.jpeg', '.jpg', $imageName);
				$doctor->created_at = date('Y-m-d H:i:s');

				if ($doctor->save()) {
					$request->session()->flash('success', 'Doctor added successfully');
					return redirect()->route('doctors');
				} else {
					$request->session()->flash('error', 'Something went wrong. Please try again later.');
					return redirect()->route('doctors');
				}
			} catch (Exception $e) {
				$request->session()->flash('error', 'Something went wrong. Please try again later.');
				return redirect()->route('doctors');
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
			$doctor = Doctor::where('id', $id)->first();
			if (isset($doctor->id)) {
				return view('backend.doctors.view', compact('doctor', 'type'));
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('doctors');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('doctors');
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
            $doctor = Doctor::where('id', $id)->first();
            $brands = Brand::select('id','name')->where('status', 'active')->get();
            if (isset($doctor->id)) {
                $type = 'edit';
                $url = route('updateDoctor', ['id' => $doctor->id]);
                return view('backend.doctors.create', compact('doctor', 'type', 'url', 'brands'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('doctors');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('doctors');
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
			$doctor = Doctor::where('id', $id)->first();
			if (isset($doctor->id)) {

				$validate = Validator($request->all(), [
					'doc_name' => 'required',
					//'brand_id' => 'required',
					'address' => 'required',
					'phone_number' => 'required|min:8|numeric',
					'doc_email' => 'required|email',
					'zipcode' => 'required',
				]);

				$attr = [
					'doc_name' => 'Doctor Name',
					//'brand_id' => 'Brand',
					'address' => 'Address',
					'phone_number' => 'Phone Number',
					'doc_email' => 'Email',
					'zipcode' => 'Zip Code',
				];

				$validate->setAttributeNames($attr);

				if ($validate->fails()) {
					return redirect()->route('createDoctor')->withInput($request->all())->withErrors($validate);
				} else {
					try {
						$imageName = '';
						if ($request->file('doc_image') != null) {
							$image = $request->file('doc_image');
							$imageName = time() . $image->getClientOriginalName();
							if ($doctor->image != null && file_exists(public_path('uploads/doctors/' . $doctor->image))) {
								if ($doctor->image != 'noimage.jpg') {
									unlink(public_path('uploads/doctors/' . $doctor->image));
								}
							}
							$imageName = str_replace(' ', '', $imageName);
							$imageName = str_replace('.jpeg', '.jpg', $imageName);
							$image->move(public_path('uploads/doctors'), $imageName);
							//Helper::compress_image(public_path('uploads/doctors/' . $imageName), 100);
							$doctor->image = str_replace('.jpeg', '.jpg', $imageName);
						}
						$doctor->name = $request->post('doc_name');
						$doctor->phone_number = $request->post('phone_number');
						$doctor->address = $request->post('address');
						$doctor->brand_id = $request->post('brand_id');
						$doctor->lat = $request->post('lat');
						$doctor->lng = $request->post('lng');
						$doctor->country = $request->post('country');
						$doctor->state = $request->post('state');
						$doctor->city = $request->post('city');
						$doctor->email = $request->post('doc_email');
						$doctor->zipcode = $request->post('zipcode');
						$doctor->status = trim($request->post('status'));

						if ($doctor->save()) {
							$request->session()->flash('success', 'Doctor updated successfully');
							return redirect()->route('doctors');
						} else {
							$request->session()->flash('error', 'Something went wrong. Please try again later.');
							return redirect()->route('doctors');
						}
					} catch (Exception $e) {
						$request->session()->flash('error', 'Something went wrong. Please try again later.');
						return redirect()->route('doctors');
					}

				}
			} else {
				$request->session()->flash('error', 'Invalid Data');
				return redirect()->route('doctors');
			}
		} else {
			$request->session()->flash('error', 'Invalid Data');
			return redirect()->route('doctors');
		}

	}

	// activate/deactivate Doctor
	public function updateStatus(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$doctor = Doctor::find($request->statusid);

			if (isset($doctor->id)) {
				$doctor->status = $request->status;
				if ($doctor->save()) {
					$request->session()->flash('success', 'Doctor updated successfully.');
					return redirect()->back();
				} else {
					$request->session()->flash('error', 'Unable to update Doctor. Please try again later.');
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

	// activate/deactivate Doctor
	public function updateStatusAjax(Request $request) {

		if (isset($request->statusid) && $request->statusid != null) {
			$doctor = Doctor::find($request->statusid);

			if (isset($doctor->id)) {
				$doctor->status = $request->status;
				if ($doctor->save()) {
					echo json_encode(['status' => 1, 'message' => 'Doctor updated successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to update doctor. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Doctor']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Doctor']);
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
			$doctor = Doctor::find($request->deleteid);

			if (isset($doctor->id)) {
				$doctor->status = 'delete';
				if ($doctor->save()) {

					//$this->deleteItems($doctor, 1);

					echo json_encode(['status' => 1, 'message' => 'Doctor deleted successfully.']);
				} else {
					echo json_encode(['status' => 0, 'message' => 'Unable to delete Doctor. Please try again later.']);
				}
			} else {
				echo json_encode(['status' => 0, 'message' => 'Invalid Doctor']);
			}
		} else {
			echo json_encode(['status' => 0, 'message' => 'Invalid Doctor']);
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
				$doctor = Doctor::find($id);

				if (isset($doctor->id)) {
					$doctor->status = 'delete';
					if ($doctor->save()) {
						//$this->deleteItems($doctor, 1);
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Doctor deleted successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all Doctor were deleted. Please try again later.']);
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
				$doctor = Doctor::find($id);

				if (isset($doctor->id)) {
					if ($doctor->status == 'active') {
						$doctor->status = 'inactive';
					} elseif ($doctor->status == 'inactive') {
						$doctor->status = 'active';
					}

					if ($doctor->save()) {
						$count++;
					}
				}
			}
			if ($count == $ids) {
				echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Doctor updated successfully.']);
			} else {
				echo json_encode(["status" => 0, 'message' => 'Not all doctors were updated. Please try again later.']);
			}
		} else {
			echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
		}
	}
    /**
     * Show the form for importing doctors sheet
     *
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request) {
        try {
        	$url = route('importDoctor');
	        if (strtolower($request->method()) == 'post') {
	            $validate = Validator($request->all(), [
	                'doctor_import' => 'required|mimes:csv,zip,txt',
	                //'doctor_import' => 'required',
	            ]);
	            $attr = [
	                'doctor_import' => 'File Csv and Zip',
	            ];
	            $validate->setAttributeNames($attr);
	            if ($validate->fails()) {
	            	$request->session()->flash('error', 'Something went wrong. Please try again later.');
	                return redirect()->route('importDoctor')->withInput($request->all())->withErrors($validate);
	            } else {
	            	//dd(request()->file('doctor_import')->extension());
	            	if (request()->file('doctor_import')->extension() == 'csv' || request()->file('doctor_import')->extension() == 'txt') {
	                    Excel::import(new DoctorImport,request()->file('doctor_import'));
	                }else{
	                	\File::deleteDirectory('uploads/doctors/zip-csv');
	                    $zip = Zip::open($request->doctor_import);
	                    $zip->extract('uploads/doctors/zip-csv');
	                    $files = \File::allFiles('uploads/doctors/zip-csv');
	                    if (!empty($files)) {
	                        foreach ($files as $key => $file) {
	                            if ($file != null) {
	                            	try {
	                                	Excel::import(new DoctorImport,$file);
	                                } catch (NoTypeDetectedException $e) {
	                                    //$request->session()->flash('error', 'Sorry you are using a wrong format to upload files.');
	                                    //return redirect()->back();
	                                }
	                            }
	                        }
	                       // \Artisan::call('cache:clear');
	                    }
	                }
	                $request->session()->flash('success', 'Data imported successfully');
                    return redirect()->route('importDoctor');
	                
	            }
	                
	        }

        	return view('backend.doctors.import', compact('url'));
        } catch (Exception $e) {
            $request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->back();
        }
    }
   public function imageUpload()
    {
        return view('backend.doctors.image-upload');
    }
    
    public function bulkImageUpload(Request $request){
    	try {
        $validate = Validator($request->all(), [
            'image_upload' => 'required|mimes:zip',

        ]);

        $attr = [
            'image_upload' => 'Zip File',
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
        	$request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->route('imageUploadDoctor')->withInput($request->all())->withErrors($validate);
        } else {
            
                $type = $request->type;
                \File::deleteDirectory('uploads/doctors/zip-images');
                $zip = Zip::open($request->image_upload);
                $zip->extract('uploads/doctors/zip-images');

                $files = \File::allFiles('uploads/doctors/zip-images');
                if (!empty($files)) {
                    foreach ($files as $key => $file) {
                        if ($file != null) {
                        	if ($file->getExtension() == 'png' || $file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg' || $file->getExtension() == 'gif' || $file->getExtension() == 'svg') {
                        		$filemanager = new Filemanager;
	                            $imageName = '';
	                            $image = $file;
	                            $imageName = time() . $image->getFilename();
	                            $imageName = str_replace(' ', '', $imageName);
	                            $imageName = str_replace('.jpeg', '.jpg', $imageName);
	                           
	                            $destination = public_path('uploads/doctors');
	                            \File::move($file, $destination .'/'.$imageName);
	                            
	                            
	                            //Helper::compress_image(public_path('uploads/products/' . $imageName), 100);
	                            $imageName = str_replace('.jpeg', '.jpg', $imageName);
	                             $filemanager->type = 'doctor';
	                            $filemanager->name = str_replace('.jpeg', '.jpg', $imageName);
	                            $filemanager->image = str_replace('.jpeg', '.jpg', $imageName);
	                            $filemanager->created_at = date('Y-m-d H:i:s');
	                            $filemanager->save();
                        	}   
                            
                        }
                    }
                   // \Artisan::call('cache:clear');
                }
                //event(New BulkImageCrop(Doctor::first())); 
                
                session()->flash('success','Zip Images uploaded successfully');
                return redirect()->route('filemanagers');
            

        }
        } catch (Exception $e) {
            $request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->back();
        }
    }
    public function bulkImageUploadOLD(Request $request){
    	try {
	        $validate = Validator($request->all(), [
	            'image_upload' => 'required|mimes:zip',

	        ]);

	        $attr = [
	            'image_upload' => 'Zip File',
	        ];

	        $validate->setAttributeNames($attr);

	        if ($validate->fails()) {
	            return redirect()->route('imageUploadDoctor')->withInput($request->all())->withErrors($validate);
	        } else {
                $type = $request->type;
                \File::deleteDirectory('uploads/doctors/zip-images');
                $zip = Zip::open($request->image_upload);
                $zip->extract('uploads/doctors/zip-images');

                $files = \File::allFiles('uploads/doctors/zip-images');
                if (!empty($files)) {
                    foreach ($files as $key => $file) {
                        if ($file != null) {
                            $filemanager = new Filemanager;
                            $imageName = '';
                            $image = $file;
                            $imageName = time() . $image->getFilename();
                            $imageName = str_replace(' ', '', $imageName);
                            $imageName = str_replace('.jpeg', '.jpg', $imageName);
                           
                            $destination = public_path('uploads\doctors');
                            \File::move($file, $destination .'/'.$imageName);
                            
                            
                            //Helper::compress_image(public_path('uploads/products/' . $imageName), 100);
                            $imageName = str_replace('.jpeg', '.jpg', $imageName);
                             $filemanager->type = 'doctor';
                            $filemanager->name = str_replace('.jpeg', '.jpg', $imageName);
                            $filemanager->image = str_replace('.jpeg', '.jpg', $imageName);
                            $filemanager->created_at = date('Y-m-d H:i:s');
                            $filemanager->save();
                        }
                    }
                  //  \Artisan::call('cache:clear');
                }
                //event(New BulkImageCrop(Doctor::first())); 
                
                session()->flash('success','Zip Images uploaded successfully');
                return redirect()->back();
	        }
        } catch (Exception $e) {
            $request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->back();
        }
    }
   	public function doctorLogsAjax(Request $request) {
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->logs->fetchDoctorLog($request, $this->columns);
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
                    <span>Sno :</span> '.$list->sno.'
                  </div>';
            $res = json_decode($list->description);
            if(!empty($res)){
              foreach ($res as $k => $v) {
                   $logData .='<div class="log_data">
                    <span>'.$k.' :</span> '.$v.'
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
   	public function clears()
    {
        ini_set('max_execution_time', 0);
        \Artisan::call('config:clear');
        return view('backend.clear_record');
    }
    
    public function clearRecord(Request $request){
    	try {
    		ini_set('max_execution_time', 0);
            \Artisan::call('config:clear');
	        $validate = Validator($request->all(), [
	            'type' => 'required',
	        ]);
	        $attr = [
	            'type' => 'Type',
	        ];
	        $validate->setAttributeNames($attr);

	        if ($validate->fails()) {
	        	$request->session()->flash('error', 'Something went wrong. Please try again later.');
	            return redirect()->route('clears')->withInput($request->all())->withErrors($validate);
	        } else {
               // \Artisan::call('cache:clear');
                $path = public_path('uploads/products');
	        	$files = \File::allFiles($path);
	        	if (!empty($files)) {
                	foreach ($files as $key => $value) {
                		if ($value->getFilename() != null && file_exists(public_path('uploads/products/' . $value->getFilename()))) {
                			if ($value->getFilename() != 'noimage.jpg') {
                                unlink(public_path('uploads/products/' . $value->getFilename()));
                            }
                        }
                	}
                }
                
                $path_p = public_path('pendingfile');
	        	$files_p = \File::allFiles($path_p);
	        	if (!empty($files_p)) {
                	foreach ($files_p as $key => $value) {
                		if ($value->getFilename() != null && file_exists(public_path('pendingfile/' . $value->getFilename()))) {
                			if ($value->getFilename() != 'noimage.jpg') {
                                unlink(public_path('pendingfile/' . $value->getFilename()));
                            }
                        }
                	}
                }
                if ($request->type == 'all') {
                	DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                	DB::table('products')->truncate();
                	DB::table('product_favourites')->truncate();
                	DB::table('companies')->truncate();
                	
                	DB::table('dispensaries')->truncate();
                	DB::table('doctors')->truncate();
                	DB::table('feedbacks')->truncate();
                	DB::table('filemanagers')->truncate();
                	DB::table('file_imports')->truncate();
                	DB::table('messages')->truncate();
                	DB::table('notification_users')->truncate();
                	DB::table('otp')->truncate();
                	
                	DB::table('product_types')->truncate();
                	DB::table('categories')->truncate();
                	DB::table('pushs')->truncate();
                	DB::table('push_user')->truncate();
                	DB::table('states')->truncate();
                	DB::table('strains')->truncate();
                	DB::table('supports')->truncate();
                	DB::table('transactions')->truncate();
                	DB::table('user_devices')->truncate();
                	DB::table('user_plans')->truncate();
                	DB::table('custom_logs')->truncate();
                	DB::table('import_files')->truncate();
                	DB::table('limitations')->truncate();
                	DB::table('users')->where('id', '!=', 1)->delete();
                	DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 
	                session()->flash('success','All record successfully clear');
	                return redirect()->route('clears');
                }else{
                	DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                	DB::table('products')->truncate();
                	DB::table('product_favourites')->truncate();
                	DB::table('categories')->truncate();
                	DB::table('product_types')->truncate();
                	DB::table('strains')->truncate();
                	DB::table('custom_logs')->truncate();
                	DB::table('limitations')->truncate();
                	DB::table('import_files')->truncate();
                	DB::statement('SET FOREIGN_KEY_CHECKS=1;'); 
	                session()->flash('success','Product related record successfully clear');
	                return redirect()->route('clears');
                }
	        }
        } catch (Exception $e) {
            $request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->back();
        }
    }

}
