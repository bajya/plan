<?php

namespace App\Http\Controllers\Backend;

use App\Library\Helper;
use App\Library\Notify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ImportFile;
use App\CustomLog;
use App\State;
use App\Brand;
use App\Dispensary;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use Illuminate\Support\Arr;

class CronController extends Controller
{
    public function importLocation()
    {
        ini_set('max_execution_time', 0);
        $locationsFile = ImportFile::where("status", 0)->where("type","location")->first();
        if (!empty($locationsFile)) {
            $file = public_path("pendingfile/" . $locationsFile->filename);
            $file = fopen($file, "r");
            $i=1;
            ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
            while (!feof($file)) {
                $data=fgetcsv($file);
                if (isset(@$data[0]) && !empty(@$data[0]) && isset(@$data[1]) && isset($data[3]) && isset($data[5]) && isset($data[6]) && isset($data[8]) && isset($data[8]) && isset($data[13])) {
                    $stateId = 0;
                    $brandId = 0;
                    $stateData = State::where('name', $data[5])->where('status', '!=', 'delete')->first();
                    if (empty($stateData)) {
                        $stateDataInsert = new State;
                        $stateDataInsert->name = isset($data[5]) && !empty($data[5]) ? $data[5] : 'N/A';
                        $stateDataInsert->image = 'noimage.jpg';
                        $stateDataInsert->description = '';
                        $stateDataInsert->status = 'active';
                        $stateDataInsert->created_at = date('Y-m-d H:i:s');
                        $stateDataInsert->save();
                        $stateId = $stateDataInsert->id;
                    } else {
                        $stateId = $stateData->id;
                    }
                    $brd = Brand::where('name', $data[1])->where('status', '!=', 'delete')->first();
                    if (empty($brd)) {
                        $brd = new Brand;
                        $brd->name = isset($data[1]) && !empty($data[1]) ? $data[1] : 'N/A';
                        $brd->state_id = $stateId;
                        $brd->image = 'noimage.jpg';
                        $brd->description = '';
                        $brd->status = 'active';
                        $brd->created_at = date('Y-m-d H:i:s');
                        $brd->save();
                        $brandId = $brd->id;
                    } else {
                        $brandId = $brd->id;
                    }
                    $prod = Dispensary::where('name', $data[3])->where('location_id', $data[0])->where('brand_id', $brandId)->first();
                    if (!isset($prod->id)) {
                        $prod = new Dispensary;
                        $prod->brand_id = $brandId;
                        $prod->location_id = $data[0];
                        $prod->location_name_website = isset($data[2]) && !empty($data[2]) ? $data[2] : 'N/A';
                        $prod->name = isset($data[3]) && !empty($data[3]) ? $data[3] : 'N/A';
                        $prod->phone_code = '';
                        $prod->location_phone_website = isset($data[9]) && !empty($data[9]) ? $data[9] : 'N/A';
                        $prod->phone_number = isset($data[10]) && !empty($data[10]) ? $data[10] : 'N/A';
                        $prod->location_email = isset($data[11]) && !empty($data[11]) ? $data[11] : 'N/A';
                        $prod->image = $brd->image;
                        $prod->description = 'N/A';
                        $prod->country = '';
                        $prod->location_state_website = isset($data[4]) && !empty($data[4]) ? $data[4] : 'N/A';
                        $prod->state = isset($data[5]) && !empty($data[5]) ? $data[5] : 'N/A';
                        $prod->city = 'N/A';
                        $latlng = explode(",", $data[6]);
                        $prod->location_address_website = isset($data[7]) && !empty($data[7]) ? $data[7] : 'N/A';
                        $prod->address = isset($data[8]) && !empty($data[8]) ? $data[8] : 'N/A';
                        $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                        $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';
                        $prod->location_url = isset($data[14]) && !empty($data[14]) ? $data[14] : 'N/A';
                        if (isset($data[12]) && !empty($data[12])) {
                            $prod->location_times_website = json_encode(explode(",", $data[12]));
                        }
                        $prod->location_times = json_encode(explode(",", $data[13]));
                        $prod->status = 'active';
                        $prod->save();
                    }else{
                        $prod->brand_id = $brandId;
                        $prod->location_id = $data[0];
                        $prod->location_name_website = isset($data[2]) && !empty($data[2]) ? $data[2] : 'N/A';
                        $prod->name = isset($data[3]) && !empty($data[3]) ? $data[3] : 'N/A';
                        $prod->phone_code = '';
                        $prod->location_phone_website = isset($data[9]) && !empty($data[9]) ? $data[9] : 'N/A';
                        $prod->phone_number = isset($data[10]) && !empty($data[10]) ? $data[10] : 'N/A';
                        $prod->location_email = isset($data[11]) && !empty($data[11]) ? $data[11] : 'N/A';
                        $prod->image = $brd->image;
                        $prod->description = 'N/A';
                        $prod->country = '';
                        $prod->location_state_website = isset($data[4]) && !empty($data[4]) ? $data[4] : 'N/A';
                        $prod->state = isset($data[5]) && !empty($data[5]) ? $data[5] : 'N/A';
                        $prod->city = 'N/A';
                        $latlng = explode(",", $data[6]);
                        $prod->location_address_website = isset($data[7]) && !empty($data[7]) ? $data[7] : 'N/A';
                        $prod->address = isset($data[8]) && !empty($data[8]) ? $data[8] : 'N/A';
                        $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                        $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';
                        $prod->location_url = isset($data[14]) && !empty($data[14]) ? $data[14] : 'N/A';
                        if (isset($data[12]) && !empty($data[12])) {
                            $prod->location_times_website = json_encode(explode(",", $data[12]));
                        }
                        $prod->location_times = json_encode(explode(",", $data[13]));
                        $prod->status = 'active';
                        $prod->save();
                    }
                } else {
                    if(!empty($data[0]))
                    {
                        $CustomLog = new CustomLog;
                        $CustomLog->sno = $i;
                        $CustomLog->title = 'Please upload same sample csv file';
                        $CustomLog->description = json_encode($file, JSON_PRETTY_PRINT);
                        $CustomLog->type = 'location';
                        $CustomLog->status = 'active';
                        $CustomLog->created_at = date('Y-m-d H:i:s');
                        $CustomLog->save();
                    }else{
                        $CustomLog = new CustomLog;
                        $CustomLog->sno = $i;
                        $CustomLog->title = 'Required field blank location id' . @$data[0];
                        $CustomLog->description = json_encode($file, JSON_PRETTY_PRINT);
                        $CustomLog->type = 'location';
                        $CustomLog->status = 'active';
                        $CustomLog->created_at = date('Y-m-d H:i:s');
                        $CustomLog->save();
                    }
                   
                }
                $i++;
            }
            fclose($file);
            ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);
            if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                unlink(public_path('pendingfile/' . $locationsFile->filename));
                ImportFile::where("id",$locationsFile->id)->delete();
            }
            //exit;
        }else{
            $productFile = ImportFile::where("status", 0)->where("type","product")->first();
            if (!empty($productFile)) {
                $file = public_path("pendingfile/" . $productFile->filename);
                ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

                Excel::import(new ProductImport, $file);
                ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                    unlink(public_path('pendingfile/' . $productFile->filename));
                    ImportFile::where("id",$productFile->id)->delete();
                }
            }
        }
        
        
    }
    public function importProduct()
    {
        $productFile = ImportFile::where("status", 0)->where("type","product")->first();
        if (!empty($productFile)) {
            $file = public_path("pendingfile/" . $productFile->filename);
            ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

            Excel::import(new ProductImport, $file);
            ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);
            if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                unlink(public_path('pendingfile/' . $productFile->filename));
                ImportFile::where("id",$productFile->id)->delete();
            }
        }
        
    }
}
