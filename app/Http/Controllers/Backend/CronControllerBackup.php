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
        $locationsFile = ImportFile::where("status", 0)->where("type","location")->first();
        if (!empty($locationsFile)) {
            $file = public_path("pendingfile/" . $locationsFile->filename);
            $file = fopen($file, "r");
            $i=0;
            ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
            while (!feof($file)) {
                $data=fgetcsv($file);
                if (!empty(@$data[1]) &&  !empty(@$data[2]) && !empty($data[4]) && !empty($data[6]) && !empty($data[7]) && !empty($data[9]) && !empty($data[9]) && !empty($data[14])) {

                    $stateId = 0;
                    $brandId = 0;
                    $stateData = State::where('name', $data[6])->where('status', '!=', 'delete')->first();
                    if (empty($stateData)) {
                        $stateDataInsert = new State;
                        $stateDataInsert->name = $data[6];
                        $stateDataInsert->image = 'noimage.jpg';
                        $stateDataInsert->description = '';
                        $stateDataInsert->status = 'active';
                        $stateDataInsert->created_at = date('Y-m-d H:i:s');
                        $stateDataInsert->save();
                        $stateId = $stateDataInsert->id;
                    } else {
                        $stateId = $stateData->id;
                    }

                    $brd = Brand::where('name', $data[2])->where('status', '!=', 'delete')->first();
                    if (empty($brd)) {

                        $brand = new Brand;
                        $brand->name = $data[2];
                        $brand->state_id = $stateId;
                        $brand->image = 'noimage.jpg';
                        $brand->description = '';
                        $brand->status = 'active';
                        $brand->created_at = date('Y-m-d H:i:s');
                        $brand->save();
                        $brandId = $brand->id;
                    } else {
                        $brandId = $brd->id;
                    }
                    $prod = Dispensary::where('name', $data[4])->where('location_id', $data[1])->where('brand_id', $brandId)->first();
                    if (!isset($prod->id)) {
                        $prod = new Dispensary;
                        $prod->brand_id = $brandId;
                        $prod->location_id = $data[1];
                        $prod->location_name_website = isset($data[3]) && !empty($data[3]) ? $data[3] : '';
                        $prod->name = $data[4];
                        $prod->phone_code = '+91';
                        $prod->location_phone_website = isset($data[10]) && !empty($data[10]) ? $data[10] : '';
                        $prod->phone_number = $data[11];
                        $prod->location_email = $data[12];
                        $prod->image = 'noimage.jpg';
                        $prod->description = '';
                        $prod->country = '';
                        $prod->location_state_website = isset($data[5]) && !empty($data[5]) ? $data[5] : '';
                        $prod->state = $data[6];
                        $prod->city = '';
                        $latlng = explode(",", $data[7]);
                        $prod->location_address_website = isset($data[8]) && !empty($data[8]) ? $data[8] : '';
                        $prod->address = $data[9];
                        $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                        $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';
                        $prod->location_url = $data[15];
                        if (isset($data[13]) && !empty($data[13])) {
                            $prod->location_times_website = json_encode(explode(",", $data[13]));
                        }
                        $prod->location_times = json_encode(explode(",", $data[14]));
                        $prod->status = 'active';
                        $prod->save();
                    }
                } else {
                    if(!empty($data[1]))
                    {
                        $CustomLog = new CustomLog;
                        $CustomLog->sno = $i;
                        $CustomLog->title = 'Required field blank location id' . @$data[1];
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
            exit;
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
        }
        
    }
}
