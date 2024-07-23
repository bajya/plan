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