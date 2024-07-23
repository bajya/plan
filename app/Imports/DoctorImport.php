<?php

namespace App\Imports;

use Session;

use Request;
use App\Category;
use App\Library\Helper;
use App\Product;
use App\Doctor;
use App\Brand;
use App\CustomLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DoctorImport implements ToCollection,WithHeadingRow
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
                        if ((isset($value['doctor_name']) && !empty($value['doctor_name'])) && (isset($value['state']) && !empty($value['state'])) && (isset($value['city']) && !empty($value['city'])) && (isset($value['image_filename']) && !empty($value['image_filename'])) && (isset($value['address']) && !empty($value['address'])) && (isset($value['phone']) && !empty($value['phone'])) && (isset($value['email']) && !empty($value['email'])) && (isset($value['zip']) && !empty($value['zip']))) {
                            
                            if ($value['state'] == null) {
                                    Request::session()->flash('error', 'State is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['city'] == null) {
                                    Request::session()->flash('error', 'City is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['image_filename'] == null) {
                                Request::session()->flash('error', 'Image is blank at line ' . $i);
                                return redirect()->back();
                            }
                            if ($value['address'] == null) {
                                    Request::session()->flash('error', 'Address is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['phone'] == null) {
                                    Request::session()->flash('error', 'Phone is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['email'] == null) {
                                    Request::session()->flash('error', 'Email is blank at line ' . $i);
                                    return redirect()->back();
                                
                            } 
                            if ($value['doctor_name'] == null) {
                                Request::session()->flash('error', 'Doctor Name is blank at line ' . $i);
                                return redirect()->back();
                            }
                            if ($value['zip'] == null) {
                                Request::session()->flash('error', 'Zip is blank at line ' . $i);
                                return redirect()->back();
                            }
                            

                            if($value != 'doctor_name' && $value != 'email' && $value != 'phone' && $value != 'address' && $value != 'state' && $value != 'city'){
                                
                            }else{
                                Request::session()->flash('error', 'Please add right file' . $i);
                                return redirect()->back();
                            }
                            if ($value['doctor_name'] != null) {
                                $lat = 0;
                                $lng = 0;

                                $data_location = "https://maps.google.com/maps/api/geocode/json?key=".env('MAP_API_KEY')."&address=".str_replace(" ", "+", $value['address'])."&sensor=false";
                                $data = file_get_contents($data_location);
                                usleep(200000);
                                $data = json_decode($data);
                                if ($data->status=="OK") {
                                    if (isset($data->results[0]->geometry->location->lat) && isset($data->results[0]->geometry->location->lng)) {
                                        $lat = $data->results[0]->geometry->location->lat;
                                        $lng = $data->results[0]->geometry->location->lng;
                                    }
                                    
                                }
                                $prod = Doctor::where('name', $value['doctor_name'])->where('phone_number', $value['phone'])->where('email', $value['email'])->first();
                                if (!isset($prod->id)) {
                                    $prod = new Doctor;
                                    $prod->brand_id = 0;
                                    $prod->name = $value['doctor_name'];
                                    $prod->phone_code = '+91';
                                    $prod->phone_number = $value['phone'];
                                    $prod->email = $value['email'];
                                    $prod->country = '';
                                    $prod->image = $value['image_filename'];
                                    $prod->state = $value['state'];
                                    $prod->city = $value['city'];
                                    $prod->address = $value['address'];
                                    $prod->zipcode = $value['zip'];
                                    $prod->lat = $lat;
                                    $prod->lng = $lng;
                                    $prod->status = 'active';
                                    $prod->save();
                                }else{
                                    $prod->brand_id = 0;
                                    $prod->name = $value['doctor_name'];
                                    $prod->phone_code = '+91';
                                    $prod->phone_number = $value['phone'];
                                    $prod->email = $value['email'];
                                    $prod->country = '';
                                    $prod->image = $value['image_filename'];
                                    $prod->state = $value['state'];
                                    $prod->city = $value['city'];
                                    $prod->address = $value['address'];
                                    $prod->zipcode = $value['zip'];
                                    $prod->lat = $lat;
                                    $prod->lng = $lng;
                                    $prod->save();
                                }
                            }
                        }else{
                            $CustomLog = new CustomLog;
                            $CustomLog->sno = $i;
                            $CustomLog->title = 'Required field blank';
                            $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                            $CustomLog->type = 'doctor';
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
                $CustomLog->type = 'doctor';
                $CustomLog->status = 'active';
                $CustomLog->created_at = date('Y-m-d H:i:s');
                $CustomLog->save();
                Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
                return redirect()->back();
            }
        }
    }
}