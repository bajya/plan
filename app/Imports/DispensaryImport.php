<?php

namespace App\Imports;

use Session;

use Request;
use App\Category;
use App\Library\Helper;
use App\Product;
use App\Dispensary;
use App\Brand;
use App\State;
use App\CustomLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DispensaryImport implements ToCollection,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection  $rows)
    {
     
        ini_set('max_execution_time', 0);
       // ini_set('memory_limit', '1G');
        try {
            if ($rows->count()) {
                $i = 2;
                $j = 0;
                $arr = [];
                $count = 0;
                $error = 0;
                $temp=0;
                if(isset($rows[0])){
                    $errorInsert=array();
                    foreach ($rows as $value) {
                        if (!empty($value)) {
                            $temp++;
                            $stateId = 0;
                            $brandId = 0;
                            $value['location_id']=isset($value['location_id']) && !empty($value['location_id']) ? $value['location_id'] : 'N/A';
                            $value['company']=isset($value['company']) && !empty($value['company']) ? $value['company'] : 'N/A';
                            $value['location_name_website']=isset($value['location_name_website']) && !empty($value['location_name_website']) ? $value['location_name_website'] : 'N/A';
                            $value['location_name']=isset($value['location_name']) && !empty($value['location_name']) ? $value['location_name'] : 'N/A';
                            $value['location_state_website']=isset($value['location_state_website']) && !empty($value['location_state_website']) ? $value['location_state_website'] : 'N/A';
                            $value['location_state']=isset($value['location_state']) && !empty($value['location_state']) ? $value['location_state'] : 'N/A';
                            $value['location_coordinates']=isset($value['location_coordinates']) && !empty($value['location_coordinates']) ? $value['location_coordinates'] : '-7.0157404,110.4171283';
                            $value['location_address_website']=isset($value['location_address_website']) && !empty($value['location_address_website']) ? $value['location_address_website'] : 'N/A';
                            $value['location_address']=isset($value['location_address']) && !empty($value['location_address']) ? $value['location_address'] : 'N/A';
                            $value['location_phone_website']=isset($value['location_phone_website']) && !empty($value['location_phone_website']) ? $value['location_phone_website'] : 'N/A';
                            $value['location_phone']=isset($value['location_phone']) && !empty($value['location_phone']) ? $value['location_phone'] : 'N/A';
                            $value['location_email']=isset($value['location_email']) && !empty($value['location_email']) ? $value['location_email'] : 'N/A';
                            $value['location_times_website']=isset($value['location_times_website']) && !empty($value['location_times_website']) ? $value['location_times_website'] : 'N/A';
                            $value['location_times']=isset($value['location_times']) && !empty($value['location_times']) ? $value['location_times'] : 'N/A';
                            $value['location_url']=isset($value['location_url']) && !empty($value['location_url']) ? $value['location_url'] : 'N/A';
                                
                            $stateData = State::where('name', $value['location_state'])->where('status', '!=', 'delete')->first();
                            if (empty($stateData)) {
                                $stateDataInsert = new State;
                                $stateDataInsert->name = $value['location_state'];
                                $stateDataInsert->image = 'noimage.jpg';
                                $stateDataInsert->description = '';
                                $stateDataInsert->status = 'active';
                                $stateDataInsert->created_at = date('Y-m-d H:i:s');
                                $stateDataInsert->save();
                                $stateId = $stateDataInsert->id;
                            } else {
                                $stateId = $stateData->id;
                            }
                            $brd = Brand::where('name', $value['company'])->where('status', '!=', 'delete')->first();
                            if (empty($brd)) {
                                $brd = new Brand;
                                $brd->name = $value['company'];
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
                            $prod = Dispensary::where('name', $value['location_name'])->where('location_id', $value['location_id'])->where('brand_id', $brandId)->first();
                            if (!isset($prod->id)) {
                                $prod = new Dispensary;
                                $prod->brand_id = $brandId;
                                $prod->location_id = $value['location_id'];
                                $prod->location_name_website = $value['location_name_website'];
                                $prod->name = $value['location_name'];
                                $prod->phone_code = '';
                                $prod->location_phone_website = $value['location_phone_website'];
                                $prod->phone_number = $value['location_phone'];
                                $prod->location_email = $value['location_email'];
                                $prod->image = $brd->image;
                                $prod->description = 'N/A';
                                $prod->country = '';
                                $prod->location_state_website = $value['location_state_website'];
                                $prod->state = $value['location_state'];
                                $prod->city = 'N/A';
                                $latlng = explode(",", $value['location_coordinates']);
                                $prod->location_address_website = $value['location_address_website'];
                                $prod->address = $value['location_address'];
                                $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                                $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';
                                $prod->location_url = $value['location_url'];
                                $prod->location_times_website = json_encode(explode(",", $value['location_times_website']));
                                $prod->location_times = json_encode(explode(",", $value['location_times']));
                                $prod->status = 'active';
                                $prod->save();
                                echo  $temp." in ".$prod->id."<br>";
                            }else{
                                $prod->brand_id = $brandId;
                                $prod->location_id = $value['location_id'];
                                $prod->location_name_website = $value['location_name_website'];
                                $prod->name = $value['location_name'];
                                $prod->phone_code = '';
                                $prod->location_phone_website = $value['location_phone_website'];
                                $prod->phone_number = $value['location_phone'];
                                $prod->location_email = $value['location_email'];
                                $prod->image = $brd->image;
                                $prod->description = 'N/A';
                                $prod->country = '';
                                $prod->location_state_website = $value['location_state_website'];
                                $prod->state = $value['location_state'];
                                $prod->city = 'N/A';
                                $latlng = explode(",", $value['location_coordinates']);
                                $prod->location_address_website = $value['location_address_website'];
                                $prod->address = $value['location_address'];
                                $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                                $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';
                                $prod->location_url = $value['location_url'];
                                $prod->location_times_website = json_encode(explode(",", $value['location_times_website']));
                                $prod->location_times = json_encode(explode(",", $value['location_times']));
                                $prod->status = 'active';
                                $prod->save();
                                echo  $temp." in ".$prod->id."<br>";
                            }
                             
                            $i++;
                        }else{
                            echo  $temp." else<br>";
                            $errorInsert[]=array("sno"=>$i,"title"=>'Data issue',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                            CustomLog::insert($errorInsert);
                        }
                    }
                }else{
                    $value = 'Csv file are empty data';
                    $CustomLog = new CustomLog;
                    $CustomLog->sno = 0;
                    $CustomLog->title = 'Csv file are empty data';
                    $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                    $CustomLog->type = 'product';
                    $CustomLog->status = 'active';
                    $CustomLog->created_at = date('Y-m-d H:i:s');
                    $CustomLog->save();
                    Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
                    return redirect()->back();
                }
            }
        } catch (\Exception $ex) {

        }
       
    }


    public function collectionOld(Collection  $rows)
    {
        if ($rows->count()) {
            $i = 2;
            $arr = [];
            $count = 0;
            $error = 0;
            if(isset($rows[0])){
                foreach ($rows->chunk(100) as $row) {
                    foreach ($row as $key => $value) {
                        if ((isset($value['company']) && !empty($value['company'])) && (isset($value['location_name']) && !empty($value['location_name'])) && (isset($value['location_times']) && !empty($value['location_times'])) && (isset($value['location_email']) && !empty($value['location_email'])) && (isset($value['location_phone']) && !empty($value['location_phone'])) && (isset($value['location_address']) && !empty($value['location_address'])) && (isset($value['location_state']) && !empty($value['location_state'])) && (isset($value['location_id']) && !empty($value['location_id']))) {
                            
                           
                            if ($value['location_email'] == null) {
                                    Request::session()->flash('error', 'Email is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['location_times'] == null) {
                                    Request::session()->flash('error', 'Time is blank at line ' . $i);
                                    return redirect()->back();
                                
                            }
                            if ($value['location_url'] == null) {
                                    Request::session()->flash('error', 'Url is blank at line ' . $i);
                                    return redirect()->back();
                                
                            }
                            if ($value['location_coordinates'] == null) {
                                    Request::session()->flash('error', 'Coordinates is blank at line ' . $i);
                                    return redirect()->back();
                                
                            }
                            if ($value['location_name'] == null) {
                                Request::session()->flash('error', 'Location Name is blank at line ' . $i);
                                return redirect()->back();
                            }
                            if ($value['location_id'] == null) {
                                Request::session()->flash('error', 'Location is blank at line ' . $i);
                                return redirect()->back();
                            }
                            $stateData = State::where('name', $value['location_state'])->where('status', '!=', 'delete')->first();
                            if (empty($stateData)) {
                                $stateData = new State;
                                $stateData->name = $value['location_state'];
                                $stateData->image = 'noimage.jpg';
                                $stateData->description = '';
                                $stateData->status = 'active';
                                $stateData->created_at = date('Y-m-d H:i:s');
                                $stateData->save();
                            }
                            if ($value['company'] != null) {
                                $brd = Brand::where('name', $value['company'])->where('status', '!=', 'delete')->first();
                                if (empty($brd)) {
                                    $stateData = State::where('name', $value['location_state'])->where('status', '!=', 'delete')->first();
                                    if (!isset($stateData->id)) {
                                        $stateData = new State;
                                        $stateData->name = $value['location_state'];
                                        $stateData->image = 'noimage.jpg';
                                        $stateData->description = '';
                                        $stateData->status = 'active';
                                        $stateData->created_at = date('Y-m-d H:i:s');
                                        if ($stateData->save()) {
                                            $brand = new Brand;
                                            $brand->name = $value['company'];
                                            $brand->state_id = $stateData->id;
                                            $brand->image = 'noimage.jpg';
                                            $brand->description = '';
                                            $brand->status = 'active';
                                            $brand->created_at = date('Y-m-d H:i:s');
                                            $brand->save();
                                        }
                                    }else{
                                        $brand = new Brand;
                                        $brand->name = $value['company'];
                                        $brand->state_id = $stateData->id;
                                        $brand->image = 'noimage.jpg';
                                        $brand->description = '';
                                        $brand->status = 'active';
                                        $brand->created_at = date('Y-m-d H:i:s');
                                        $brand->save();
                                    }
                                    
                                }
                            } else {
                                if ($value['company'] == null) {
                                    Request::session()->flash('error', 'Company is blank at line ' . $i);
                                    return redirect()->back();
                                }
                            }
                            if ($value['location_name'] != null) {
                                if ($value['location_id'] != null) {
                                    $brand = Brand::where('name',$value['company'])->where('status', '!=', 'delete')->first();
                                    if (!$brand) {
                                        Request::session()->flash('error', 'Compay doesnot exists at line ' . $i);
                                        return redirect()->back();
                                    }
                                    $latlng = explode(",", $value['location_coordinates']);
                                    $prod = Dispensary::where('name', $value['location_name'])->where('location_id', $value['location_id'])->where('brand_id', $brand->id)->first();
                                    if (!isset($prod->id)) {
                                        $prod = new Dispensary;
                                        $prod->brand_id = $brand->id;
                                        $prod->location_id = $value['location_id'];
                                        $prod->location_name_website = isset($value['location_name_website']) && !empty($value['location_name_website']) ? $value['location_name_website'] : '';
                                        $prod->name = $value['location_name'];
                                        $prod->phone_code = '+91';
                                        $prod->location_phone_website = isset($value['location_phone_website']) && !empty($value['location_phone_website']) ? $value['location_phone_website'] : '';;
                                        $prod->phone_number = $value['location_phone'];
                                        $prod->location_email = $value['location_email'];
                                        $prod->image = 'noimage.jpg';
                                        $prod->description = '';
                                        $prod->country = '';
                                        $prod->location_state_website = isset($value['location_state_website']) && !empty($value['location_state_website']) ? $value['location_state_website'] : '';;
                                        $prod->state = $value['location_state'];
                                        $prod->city = '';
                                        $prod->location_address_website = isset($value['location_address_website']) && !empty($value['location_address_website']) ? $value['location_address_website'] : '';;
                                        $prod->address = $value['location_address'];
                                        $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                                        $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';;
                                        $prod->location_url = $value['location_url'];
                                        if (isset($value['location_times_website']) && !empty($value['location_times_website'])) {
                                            $prod->location_times_website = json_encode(explode(",", $value['location_times_website']));
                                        }
                                        $prod->location_times = json_encode(explode(",", $value['location_times']));
                                        $prod->status = 'active';
                                        $prod->save();
                                    }else{
                                        $prod->brand_id = $brand->id;
                                        $prod->location_id = $value['location_id'];
                                        $prod->location_name_website = isset($value['location_name_website']) && !empty($value['location_name_website']) ? $value['location_name_website'] : '';
                                        $prod->name = $value['location_name'];
                                        $prod->phone_code = '+91';
                                        $prod->location_phone_website = isset($value['location_phone_website']) && !empty($value['location_phone_website']) ? $value['location_phone_website'] : '';;
                                        $prod->phone_number = $value['location_phone'];
                                        $prod->location_email = $value['location_email'];
                                        $prod->image = 'noimage.jpg';
                                        $prod->description = '';
                                        $prod->country = '';
                                        $prod->location_state_website = isset($value['location_state_website']) && !empty($value['location_state_website']) ? $value['location_state_website'] : '';;
                                        $prod->state = $value['location_state'];
                                        $prod->city = '';
                                        $prod->location_address_website = isset($value['location_address_website']) && !empty($value['location_address_website']) ? $value['location_address_website'] : '';;
                                        $prod->address = $value['location_address'];
                                        $prod->lat = isset($latlng[0]) && !empty($latlng[0]) ? $latlng[0] : '';
                                        $prod->lng = isset($latlng[1]) && !empty($latlng[1]) ? $latlng[1] : '';;
                                        $prod->location_url = $value['location_url'];
                                        if (isset($value['location_times_website']) && !empty($value['location_times_website'])) {
                                            $prod->location_times_website = json_encode(explode(",", $value['location_times_website']));
                                        }
                                        
                                        $prod->location_times = json_encode(explode(",", $value['location_times']));
                                        $prod->save();
                                    }
                                }
                            }
                        }else{
                            $CustomLog = new CustomLog;
                            $CustomLog->sno = $i;
                            $CustomLog->title = 'Required field blank';
                            $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                            $CustomLog->type = 'location';
                            $CustomLog->status = 'active';
                            $CustomLog->created_at = date('Y-m-d H:i:s');
                            $CustomLog->save();
                        }
                        
                        $i++;
                    }
                    Request::session()->flash('success', 'Data imported successfully');
                    return redirect()->back();
                }
                Request::session()->flash('success', 'Data imported successfully');
                return redirect()->back();
            }else{
                $value = 'Please upload right csv file';
                $CustomLog = new CustomLog;
                $CustomLog->sno = $i;
                $CustomLog->title = 'Please upload right csv file';
                $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                $CustomLog->type = 'location';
                $CustomLog->status = 'active';
                $CustomLog->created_at = date('Y-m-d H:i:s');
                $CustomLog->save();
                Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
                return redirect()->back();
            }
        }
    }
}