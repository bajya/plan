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
use App\User;
use App\CustomLog;
use App\UserNotificationLimitation;
use App\ProductFavourite;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Controllers\Controller;



class ProductImport implements ToCollection,WithHeadingRow
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
                          
                                $insertIdBrand = '';
                                $insertIdDes = '';
                                $insertIdCat = '';
                                $insertIdType = '';
                                $insertIdStrain = '';
                                $value['strain']=$value['strain']?$value['strain']:"N/A";
                                if (!empty($value['location_id']) && !empty($value['category']) && !empty($value['type'])  &&  !empty($value['strain']) &&  !empty($value['price_original']) &&  !empty($value['product_name'])) 
                                {
                                   
                                    /*if ($value['brand'] == null) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'brand is blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }*/
                                    if ($value['location_id'] == null) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'location is blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }
                                    if ($value['category'] == null) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'category is blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }
                                    if ($value['type'] == null) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'type is blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }
                                    if ($value['strain'] == null) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'strain is blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }
                                    if (isset($value['brand']) && !empty($value['brand'])) {
                                        $brand = Brand::where('name',$value['brand'])->where('status', '!=','delete')->first();
                                        if (empty($brand)) {
                                            $brand = Dispensary::where('location_id',$value['location_id'])->first();
                                            if (empty($brand)) {

                                                
                                            }else{
                                                $insertIdBrand = $brand->brand_id;
                                            }
                                            /*$errorInsert[]=array("sno"=>$i,"title"=>'Brand name not added in admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));*/

                                            
                                        }else{
                                            $insertIdBrand = $brand->id;
                                        }
                                    }else{
                                        $brand = Dispensary::where('location_id',$value['location_id'])->first();
                                        if (empty($brand)) {

                                            
                                        }else{
                                            $insertIdBrand = $brand->brand_id;
                                        }
                                    }
                                    
                                    

                                    $dispensary = Dispensary::where('location_id',$value['location_id'])->where('brand_id',$insertIdBrand)->where('status', '!=','delete')->first();
                                    if (empty($dispensary)) {
                                        $errorInsert[]=array("sno"=>$i,"title"=>'Location not added in admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));

                                    }else{
                                        $insertIdDes = $dispensary->id;
                                    }
                                    $cat = Category::where('name', $value['category'])->where('status', '!=', 'delete')->whereNull('parent_id')->first();
                                    if (empty($cat)) {
                                        $insertArray=array("name"=>$value['category'],"image"=>"noimage.jpg","type"=>"category",'description'=>'',"status"=>"active");
                                        $insertIdCat=Category::insertGetId($insertArray);
                                    }else{
                                        $insertIdCat = $cat->id;
                                    }

                                    $type = Category::where('name', $value['type'])->where('parent_id', $insertIdCat)->where('status', '!=', 'delete')->first();
                                    if (empty($type)) {
                                        $insertArray=array("name"=>$value['type'],"parent_id"=>$insertIdCat,"image"=>"noimage.jpg","type"=>"type",'description'=>'',"status"=>"active");
                                        $insertIdType=Category::insertGetId($insertArray);
                                    }else{
                                        $insertIdType = $type->id;
                                    }
                                    $strain = Category::where('name', $value['strain'])->where('parent_id', $insertIdType)->where('status', '!=', 'delete')->first();
                                    if (empty($strain)) {
                                        $strain_save = new Category;
                                        $strain_save->name = $value['strain'];
                                        $strain_save->parent_id = $insertIdType;
                                        $strain_save->image = 'noimage.jpg';
                                        $strain_save->type = 'strain';
                                        $strain_save->description = 'Test';
                                        $strain_save->status = 'active';
                                        $strain_save->created_at = date('Y-m-d H:i:s');
                                        $strain_save->save();
                                        $insertIdStrain = $strain_save->id;
                                    }else{
                                        $insertIdStrain = $strain->id;
                                    }
                                    if (!empty($insertIdDes) && !empty($insertIdBrand) && !empty($insertIdCat) && !empty($insertIdType) && !empty($insertIdStrain)) {
                                        $j++;
                                        $prod = Product::where('name', $value['product_name'])->where('dispensary_id', $insertIdDes)->where('brand_id', $insertIdBrand)->first();
                                        echo  $temp." if".@$prod->id."<br>";
                                        if (!$prod) {
                                            $prod = new Product;
                                            $prod->brand_id = $insertIdBrand;
                                            $prod->parent_id = $insertIdCat;
                                            $prod->type_id = $insertIdType;
                                            $prod->strain_id = $insertIdStrain;
                                            $prod->dispensary_id = $insertIdDes;
                                            $prod->name = $value['product_name'];
                                            $prod->amount = isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : '';
                                            $prod->sub_amount = 0;
                                            $prod->thc = isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : '';
                                            $prod->cbd = isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : '';
                                            $prod->price = isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0';
                                            $prod->discount_price = $value['price_discounted'] != '' ? $value['price_discounted'] : 0;
                                            if (isset($value['product_id_website'])) {
                                                $prod->product_sku = $value['product_id_website'];
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = $value['product_id'];
                                                }else{
                                                    $prod->product_sku = '';
                                                } 
                                            }
                                            
                                            if (isset($value['image_filename'])) {
                                                $prod->image = $value['image_filename'];
                                            }else{
                                                $prod->image = 'noimage.jpg';
                                            }
                                            $prod->product_code = Helper::generateNumber('products', 'product_code');
                                            $prod->description = $value['product_description'];
                                            $prod->product_url = $value['product_url'];
                                            $prod->image_url = $value['image_url'];
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] != null) {
                                                    $prod->manage_stock = $value['stock'] != null ? strval((int) $value['stock']) : '1';
                                                }else{
                                                    $prod->manage_stock = '1';
                                                }
                                            }else{
                                                $prod->manage_stock = '1';
                                            }
                                            $prod->status = 'active';
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }else{
                                                $prod->qty = 500;
                                            }
                                            $prod->save();

                                            echo  $temp." in ".$prod->id."<br>";
                                            
                                        }else{
                                            $prod->brand_id = $insertIdBrand;
                                            $prod->parent_id = $insertIdCat;
                                            $prod->type_id = $insertIdType;
                                            $prod->strain_id = $insertIdStrain;
                                            $prod->dispensary_id = $insertIdDes;
                                            $prod->name = $value['product_name'];
                                            $prod->amount = isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : '';
                                            $prod->sub_amount = 0;
                                            $prod->thc = isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : '';
                                            $prod->cbd = isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : '';
                                            $prod->price = isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0';
                                            $prod->discount_price = $value['price_discounted'] != '' ? $value['price_discounted'] : 0;
                                            if (isset($value['product_id_website'])) {
                                                $prod->product_sku = $value['product_id_website'];
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = $value['product_id'];
                                                }else{
                                                    $prod->product_sku = '';
                                                } 
                                            }
                                            if (isset($value['image_filename'])) {
                                                $prod->image = $value['image_filename'];
                                            }else{
                                                $prod->image = 'noimage.jpg';
                                            }
                                            $prod->product_code = Helper::generateNumber('products', 'product_code');
                                            $prod->description = $value['product_description'];
                                            $prod->product_url = $value['product_url'];
                                            $prod->image_url = $value['image_url'];
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] != null) {
                                                    $prod->manage_stock = $value['stock'] != null ? strval((int) $value['stock']) : '1';
                                                }else{
                                                    $prod->manage_stock = '1';
                                                }
                                            }else{
                                                $prod->manage_stock = 1;
                                            }
                                            $prod->status = 'active';
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }else{
                                                $prod->qty = 500;
                                            }
                                            $prod->save();
                                            if ($prod->manage_stock == '1') {
                                                $dispensary = Dispensary::select('name')->where('id',$insertIdDes)->first();
                                                $brand = Brand::select('name')->where('id',$insertIdBrand)->first();
                                                if (!empty($dispensary) && !empty($brand)) {
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('is_user_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->whereDay('created_at', '=', date('d'))->first()) 
                                                            { 
                                                                
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->where('pause_expire_time', '<' ,date('Y-m-d'))->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at'. $brand->name.' in'.$dispensary->name;
                                                                    $sms = urlencode($otp_message);
                                                                    // $this->otpSend($mobiles,$sms);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            if ($prod->manage_stock == '1') {
                                                $dispensary = Dispensary::select('name')->where('id',$insertIdDes)->first();
                                                $brand = Brand::select('name')->where('id',$insertIdBrand)->first();
                                                if (!empty($dispensary) && !empty($brand)) {
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('is_user_status', 'active')->where('pause_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->whereDay('created_at', '=', date('d'))->first()) 
                                                            { 
                                                                
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at'. $brand->name.' in'.$dispensary->name;
                                                                    $sms = urlencode($otp_message);
                                                                   // $this->otpSend($mobiles,$sms);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                        }
                                    }else{
                                        $errorInsert[]=array("sno"=>$i,"title"=>'Required field data not addeded admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                     
                                    }
                                }else{
                                  
                                 
                                    $errorInsert[]=array("sno"=>$i,"title"=>'Required field blank',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                  
                                }  
                                $i++;
                              
                           
                        }else{
                            echo  $temp." else<br>";
                            $errorInsert[]=array("sno"=>$i,"title"=>'Data issue',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                        }
                    }
                    if(!empty($errorInsert))
                    {
                        $errorInsert[]=array("sno"=>$i,"title"=>'Required field data not addeded admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                        CustomLog::insert($errorInsert);
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
            dd($ex);
        }
       
    }
    public function collectionOld(Collection  $rows)
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
                if(isset($rows[0])){
                    foreach ($rows as $row) {
                        if (!empty($row)) {
                            foreach ($row as $key => $value) {
                                $insertIdBrand = '';
                                $insertIdDes = '';
                                $insertIdCat = '';
                                $insertIdType = '';
                                $insertIdStrain = '';
                                if ((isset($value['brand']) && !empty($value['brand'])) && (isset($value['location_id']) && !empty($value['location_id'])) && (isset($value['category']) && !empty($value['category'])) && (isset($value['type']) && !empty($value['type']))  && (isset($value['strain']) && !empty($value['strain'])) && (isset($value['price_original']) && !empty($value['price_original'])) && (isset($value['product_name']) && !empty($value['product_name']))) 
                                {
                                    if ($value['brand'] == null) {
                                        Request::session()->flash('error', 'Brand is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['location_id'] == null) {
                                        Request::session()->flash('error', 'Location is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['category'] == null) {
                                        Request::session()->flash('error', 'Category is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['type'] == null) {
                                        Request::session()->flash('error', 'Type is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['strain'] == null) {
                                        Request::session()->flash('error', 'Strain is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    $brand = Brand::where('name',$value['brand'])->where('status', '!=','delete')->first();
                                    if (empty($brand)) {
                                        /*$insertArray=array("name"=>$value['brand'],"image"=>"noimage.jpg",'description'=>'',"status"=>"active");
                                        $insertIdBrand=Brand::insertGetId($insertArray);
                                        if ($insertIdBrand) {
                                            $dispensary_save = new Dispensary;
                                            $dispensary_save->location_id = $value['location_id'];
                                            $dispensary_save->name = $value['location_id'];
                                            $dispensary_save->brand_id = $insertIdBrand;
                                            $dispensary_save->image = 'noimage.jpg';
                                            $dispensary_save->description = 'Test';
                                            $dispensary_save->status = 'active';
                                            $dispensary_save->created_at = date('Y-m-d H:i:s');
                                            $dispensary_save->save();
                                            $insertIdDes = $dispensary_save->id;
                                        }*/
                                    }else{
                                        $insertIdBrand = $brand->id;
                                    }

                                    $dispensary = Dispensary::where('location_id',$value['location_id'])->where('brand_id',$insertIdBrand)->where('status', '!=','delete')->first();
                                    if (empty($dispensary)) {
                                        /*$dispensary_save = new Dispensary;
                                        $dispensary_save->location_id = $value['location_id'];
                                        $dispensary_save->name = $value['location_id'];
                                        $dispensary_save->brand_id = $insertIdBrand;
                                        $dispensary_save->image = 'noimage.jpg';
                                        $dispensary_save->description = 'Test';
                                        $dispensary_save->status = 'active';
                                        $dispensary_save->created_at = date('Y-m-d H:i:s');
                                        $dispensary_save->save();
                                        $insertIdDes = $dispensary_save->id;*/
                                    }else{
                                        $insertIdDes = $dispensary->id;
                                    }
                                    $cat = Category::where('name', $value['category'])->where('status', '!=', 'delete')->whereNull('parent_id')->first();
                                    if (empty($cat)) {
                                        $insertArray=array("name"=>$value['category'],"image"=>"noimage.jpg","type"=>"category",'description'=>'',"status"=>"active");
                                        $insertIdCat=Category::insertGetId($insertArray);
                                    }else{
                                        $insertIdCat = $cat->id;
                                    }

                                    $type = Category::where('name', $value['type'])->where('parent_id', $insertIdCat)->where('status', '!=', 'delete')->first();
                                    if (empty($type)) {
                                        $insertArray=array("name"=>$value['type'],"parent_id"=>$insertIdCat,"image"=>"noimage.jpg","type"=>"type",'description'=>'',"status"=>"active");
                                        $insertIdType=Category::insertGetId($insertArray);
                                    }else{
                                        $insertIdType = $type->id;
                                    }
                                    $strain = Category::where('name', $value['strain'])->where('parent_id', $insertIdType)->where('status', '!=', 'delete')->first();
                                    if (empty($strain)) {
                                        $strain_save = new Category;
                                        $strain_save->name = $value['strain'];
                                        $strain_save->parent_id = $insertIdType;
                                        $strain_save->image = 'noimage.jpg';
                                        $strain_save->type = 'strain';
                                        $strain_save->description = 'Test';
                                        $strain_save->status = 'active';
                                        $strain_save->created_at = date('Y-m-d H:i:s');
                                        $strain_save->save();
                                        $insertIdStrain = $strain_save->id;
                                    }else{
                                        $insertIdStrain = $strain->id;
                                    }
                                    if (!empty($insertIdDes) && !empty($insertIdBrand) && !empty($insertIdCat) && !empty($insertIdType) && !empty($insertIdStrain)) {
                                        $j++;
                                        $prod = Product::where('name', $value['product_name'])->where('dispensary_id', $insertIdDes)->where('brand_id', $insertIdBrand)->first();
                                        if (!$prod) {
                                            $prod = new Product;
                                            $prod->brand_id = $insertIdBrand;
                                            $prod->parent_id = $insertIdCat;
                                            $prod->type_id = $insertIdType;
                                            $prod->strain_id = $insertIdStrain;
                                            $prod->dispensary_id = $insertIdDes;
                                            $prod->name = $value['product_name'];
                                            $prod->amount = isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : '';
                                            $prod->sub_amount = 0;
                                            $prod->thc = isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : '';
                                            $prod->cbd = isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : '';
                                            $prod->price = isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0';
                                            $prod->discount_price = $value['price_discounted'] != '' ? $value['price_discounted'] : 0;
                                            if (isset($value['product_id_website'])) {
                                                $prod->product_sku = $value['product_id_website'];
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = $value['product_id'];
                                                }else{
                                                    $prod->product_sku = '';
                                                } 
                                            }
                                            
                                            if (isset($value['image_filename'])) {
                                                $prod->image = $value['image_filename'];
                                            }else{
                                                $prod->image = 'noimage.jpg';
                                            }
                                            $prod->product_code = Helper::generateNumber('products', 'product_code');
                                            $prod->description = $value['product_description'];
                                            $prod->product_url = $value['product_url'];
                                            $prod->image_url = $value['image_url'];
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] != null) {
                                                    $prod->manage_stock = $value['stock'] != null ? strval((int) $value['stock']) : '1';
                                                }else{
                                                    $prod->manage_stock = '1';
                                                }
                                            }else{
                                                $prod->manage_stock = '1';
                                            }
                                            $prod->status = 'active';
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }else{
                                                $prod->qty = 500;
                                            }
                                            $prod->save();
                                            
                                        }else{
                                            $prod->brand_id = $insertIdBrand;
                                            $prod->parent_id = $insertIdCat;
                                            $prod->type_id = $insertIdType;
                                            $prod->strain_id = $insertIdStrain;
                                            $prod->dispensary_id = $insertIdDes;
                                            $prod->name = $value['product_name'];
                                            $prod->amount = isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : '';
                                            $prod->sub_amount = 0;
                                            $prod->thc = isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : '';
                                            $prod->cbd = isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : '';
                                            $prod->price = isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0';
                                            $prod->discount_price = $value['price_discounted'] != '' ? $value['price_discounted'] : 0;
                                            if (isset($value['product_id_website'])) {
                                                $prod->product_sku = $value['product_id_website'];
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = $value['product_id'];
                                                }else{
                                                    $prod->product_sku = '';
                                                } 
                                            }
                                            if (isset($value['image_filename'])) {
                                                $prod->image = $value['image_filename'];
                                            }else{
                                                $prod->image = 'noimage.jpg';
                                            }
                                            $prod->product_code = Helper::generateNumber('products', 'product_code');
                                            $prod->description = $value['product_description'];
                                            $prod->product_url = $value['product_url'];
                                            $prod->image_url = $value['image_url'];
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] != null) {
                                                    $prod->manage_stock = $value['stock'] != null ? strval((int) $value['stock']) : '1';
                                                }else{
                                                    $prod->manage_stock = '1';
                                                }
                                            }else{
                                                $prod->manage_stock = 1;
                                            }
                                            $prod->status = 'active';
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }else{
                                                $prod->qty = 500;
                                            }
                                            $prod->save();
                                            if ($prod->manage_stock == '1') {
                                                $dispensary = Dispensary::select('name')->where('id',$insertIdDes)->first();
                                                $brand = Brand::select('name')->where('id',$insertIdBrand)->first();
                                                if (!empty($dispensary) && !empty($brand)) {
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('is_user_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->whereDay('created_at', '=', date('d'))->first()) 
                                                            { 
                                                                
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->where('pause_expire_time', '<' ,date('Y-m-d'))->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at'. $brand->name.' in'.$dispensary->name;
                                                                    $sms = urlencode($otp_message);
                                                                    $this->otpSend($mobiles,$sms);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            if ($prod->manage_stock == '1') {
                                                $dispensary = Dispensary::select('name')->where('id',$insertIdDes)->first();
                                                $brand = Brand::select('name')->where('id',$insertIdBrand)->first();
                                                if (!empty($dispensary) && !empty($brand)) {
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('is_user_status', 'active')->where('pause_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->whereDay('created_at', '=', date('d'))->first()) 
                                                            { 
                                                                
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at'. $brand->name.' in'.$dispensary->name;
                                                                    $sms = urlencode($otp_message);
                                                                   // $this->otpSend($mobiles,$sms);
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }

                                        }
                                    }else{
                                        $CustomLog = new CustomLog;
                                        $CustomLog->sno = $i;
                                        $CustomLog->title = 'Required field data not addeded admin';
                                        $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                                        $CustomLog->type = 'product';
                                        $CustomLog->status = 'active';
                                        $CustomLog->created_at = date('Y-m-d H:i:s');
                                        $CustomLog->save();
                                    }
                                }else{
                                    $CustomLog = new CustomLog;
                                    $CustomLog->sno = $i;
                                    $CustomLog->title = 'Required field blank';
                                    $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                                    $CustomLog->type = 'product';
                                    $CustomLog->status = 'active';
                                    $CustomLog->created_at = date('Y-m-d H:i:s');
                                    $CustomLog->save();
                                }  
                                $i++;
                              
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
                        }
                    }
                    if ($j == 0) {
                        Request::session()->flash('error', 'Data not store please check log');
                        return redirect()->back();
                    }else{
                        Request::session()->flash('success', 'Data imported successfully');
                        return redirect()->back();
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
            dd($ex);
            Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
            return redirect()->back();
        }
    }
   public function otpSend($number,$body)
    {


        $ID = env('TWILIO_ACCOUNT_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $service = env('TWILIO_SMS_SERVICE_ID');
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . $ID . '/Messages.json';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);

        curl_setopt($ch, CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD,$ID . ':' . $token);

        curl_setopt($ch, CURLOPT_POST,true);
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            'To=' . rawurlencode('+' . $number) .
            '&MessagingServiceSid=' . $service .
            //'&From=' . rawurlencode('+1 469 798 7898') .
            '&Body=' . rawurlencode($body));

        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp,true);


       /*   $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        if ($output === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $output;*/
    }

}