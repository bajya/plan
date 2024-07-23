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
use App\Strain;
use App\CustomLog;
use App\UserNotificationLimitation;
use App\ProductFavourite;

use App\UserOTP;
use App\UserPlan;
use App\UserDevice;
use App\Notificationuser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Http\Controllers\Controller;
use Log;
use DB;



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
                $product_status = 'inactive';
                if(isset($rows[0])){
                    $errorInsert=array();
                    
                    foreach ($rows as $value) {
                        DB::transaction(function () use($value, $product_status, $temp, $error, $count, $arr, $j, $i, $rows) {
                            if (!empty($value)) {
                                $temp++;
                                $insertIdBrand = '';
                                $insertIdDes = '';
                                $insertIdCat = '';
                                $insertIdType = '';
                                $insertIdStrain = '';
                                $value['location_id']=isset($value['location_id']) && !empty($value['location_id']) ? $value['location_id'] : '';
                                $value['product_name']=isset($value['product_name']) && !empty($value['product_name']) ? $value['product_name'] : 'N/A';
                                $value['category']=isset($value['category']) && !empty($value['category']) ? $value['category'] : 'N/A';
                                $value['type']=isset($value['type']) && !empty($value['type']) ? $value['type'] : 'N/A';
                                $value['strain']=isset($value['strain']) && !empty($value['strain']) ? $value['strain'] : 'N/A';
                                $value['amount']=isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : 'N/A';
                                $value['thc']=isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : 'N/A';
                                $value['cbd']=isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : 'N/A';
                                $value['price_original']=isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0.00';
                                $value['price_discounted']=isset($value['price_discounted']) && !empty($value['price_discounted']) ? $value['price_discounted'] : '0.00';
                                $value['brand']=isset($value['brand']) && !empty($value['brand']) ? $value['brand'] : '';
                                $value['product_description']=isset($value['product_description']) && !empty($value['product_description']) ? $value['product_description'] : 'N/A';
                                $value['product_url']=isset($value['product_url']) && !empty($value['product_url']) ? $value['product_url'] : 'N/A';
                                $value['image_url']=isset($value['image_url']) && !empty($value['image_url']) ? $value['image_url'] : '';
                                $value['image_filename']=isset($value['image_filename']) && !empty($value['image_filename']) ? $value['image_filename'] : 'noimage.jpg';
                                $value['featured']=isset($value['featured']) && !empty($value['featured']) ? $value['featured'] : '0';
                                $value['stock']=isset($value['stock']) && !empty($value['stock']) ? $value['stock'] : '1';
                                $value['qty']=isset($value['qty']) && !empty($value['qty']) ? $value['qty'] : '500';
                                if (!empty($value['location_id']) && !empty($value['category']) && !empty($value['type'])  &&  !empty($value['strain']) &&  !empty($value['product_name'])) 
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
                                        if ($res = Category::select('order_no')->where('status', '!=', 'delete')->where('type', 'category')->orderBy('order_no', 'desc')->first()) {
                                            $order_no = $res->order_no + 1;
                                        }else{
                                            $order_no = 1;
                                        }
                                        $insertArray=array("name"=>$value['category'],"image"=>"noimage.jpg","type"=>"category","order_no"=>$order_no,'description'=>'',"status"=>"inactive");
                                        $insertIdCat=Category::insertGetId($insertArray);
                                        $product_status = 'inactive';
                                    }else{
                                        $insertIdCat = $cat->id;
                                        $product_status = 'active';
                                    }

                                    $type = Category::where('name', $value['type'])->where('parent_id', $insertIdCat)->where('status', '!=', 'delete')->first();
                                    if (empty($type)) {
                                        if ($res = Category::select('order_no')->where('status', '!=', 'delete')->where('type', 'type')->orderBy('order_no', 'desc')->first()) {
                                            $order_no = $res->order_no + 1;
                                        }else{
                                            $order_no = 1;
                                        }
                                        $insertArray=array("name"=>$value['type'],"parent_id"=>$insertIdCat,"image"=>"noimage.jpg","type"=>"type","order_no"=>$order_no,'description'=>'',"status"=>"inactive");
                                        $insertIdType=Category::insertGetId($insertArray);
                                        $product_status = 'inactive';
                                    }else{
                                        $insertIdType = $type->id;
                                        $product_status = 'active';
                                    }
                                    /*$strain = Strain::where('name', $value['strain'])->where('dispensary_id', $insertIdDes)->where('brand_id', $insertIdBrand)->where('status', '!=', 'delete')->first();*/
                                    $strain = Strain::where('name', $value['strain'])->where('status', '!=', 'delete')->first();
                                    if (empty($strain)) {
                                        $strain_save = new Strain;
                                        $strain_save->name = $value['strain'];
                                       // $strain_save->dispensary_id = $insertIdDes;
                                        //$strain_save->brand_id = $insertIdBrand;
                                        $strain_save->dispensary_id = 0;
                                        $strain_save->brand_id = 0;
                                        if ($res = Strain::select('order_no')->where('status', '!=', 'delete')->orderBy('order_no', 'desc')->first()) {
                                            $strain_save->order_no = $res->order_no + 1;
                                        }else{
                                            $strain_save->order_no = 1;
                                        }
                                        $strain_save->image = 'noimage.jpg';
                                        $strain_save->description = 'Test';
                                        $strain_save->status = 'inactive';
                                        $strain_save->created_at = date('Y-m-d H:i:s');
                                        $strain_save->save();
                                        $insertIdStrain = $strain_save->id;
                                    }else{
                                        $insertIdStrain = $strain->id;
                                    }
                                    if (!empty($insertIdDes) && !empty($insertIdBrand) && !empty($insertIdCat) && !empty($insertIdType) && !empty($insertIdStrain)) {
                                        $j++;

                                        if (isset($value['product_id_website'])) {
                                            $p_id = sprintf("%.0f ",$value['product_id_website']);
                                        }else{
                                           if (isset($value['product_id'])) {
                                                $p_id = sprintf("%.0f ",$value['product_id']);
                                            }else{
                                                $p_id = '';
                                            } 
                                        }
                                        $prod_check = Product::where('name', $value['product_name'])->where('dispensary_id', $insertIdDes)->where('brand_id', $insertIdBrand)->where('status', '!=', 'delete');
                                        if ($p_id != '') {
                                            $prod_check->where('product_sku', $p_id);
                                        }
                                        $prod = $prod_check->first();
                                        //echo  $temp." if".@$prod->id."<br>";
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
                                                $prod->product_sku = sprintf("%.0f ",$value['product_id_website']);
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = sprintf("%.0f ",$value['product_id']);
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
                                            $prod->description = isset($value['product_description']) && !empty($value['product_description']) ? $value['product_description'] : '';
                                            $prod->product_url = isset($value['product_url']) && !empty($value['product_url']) ? $value['product_url'] : '';
                                            if (isset($value['image_filename']) && !empty($value['image_filename'])) {
                                                if ($value['image_filename'] != null && file_exists(public_path('uploads/products/' . $value['image_filename']))) {
                                                    $prod->image_url = asset('uploads/products').'/'.$value['image_filename'];
                                                }else{
                                                    if (isset($value['image_url']) && !empty($value['image_url'])) {

                                                        $image_url=$value['image_url'];
                                                        $base_name = basename($value['image_url']);
                                                        if ($base_name != null && file_exists(public_path('uploads/products/' . $base_name))) {
                                                            $prod->image_url = asset('uploads/products').'/'.$base_name;
                                                        }else{
                                                            /*$image_download = file_get_contents($value['image_url']); 
                                                            file_put_contents(public_path('uploads/products/'.basename($value['image_url'])), $image_download);
                                                            $prod->image_url = asset('uploads/products').'/'.basename($value['image_url']);*/
                                                            $prod->image_url = asset('uploads/products/noimage.jpg');
                                                        }
                                                        
                                                    }else{
                                                        $prod->image_url = asset('uploads/products/noimage.jpg');
                                                    }
                                                }
                                            }else{
                                                if (isset($value['image_url']) && !empty($value['image_url'])) {
                                                    $image_url=$value['image_url'];
                                                    $base_name = basename($value['image_url']);
                                                    if ($base_name != null && file_exists(public_path('uploads/products/' . $base_name))) {
                                                        $prod->image_url = asset('uploads/products').'/'.$base_name;
                                                    }else{
                                                        /*$image_download = file_get_contents($value['image_url']); 
                                                        file_put_contents(public_path('uploads/products/'.basename($value['image_url'])), $image_download);
                                                        $prod->image_url = asset('uploads/products').'/'.basename($value['image_url']);*/
                                                        $prod->image_url = asset('uploads/products/noimage.jpg');
                                                    }
                                                    
                                                }else{
                                                    $prod->image_url = asset('uploads/products/noimage.jpg');
                                                }
                                            }
                                            //$prod->image_url = isset($value['image_url']) && !empty($value['image_url']) ? $value['image_url'] : '';
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] == 1) {
                                                    $prod->manage_stock = 1;
                                                }else{
                                                    $prod->manage_stock = 1;
                                                }
                                            }else{
                                                $prod->manage_stock = 1;
                                            }
                                            $prod->status = $product_status;
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }else{
                                                $prod->qty = 500;
                                            }
                                            $prod->product_out = 'No';
                                            $prod->created_at = date('Y-m-d H:i:s');
                                            $prod->updated_at = date('Y-m-d H:i:s');
                                            $prod->save();
                                            echo  $temp." in ".$prod->id."<br>";
                                            
                                        }else{
                                            $camount = isset($value['amount']) && !empty($value['amount']) ? $value['amount'] : '';
                                            $cthc = isset($value['thc']) && !empty($value['thc']) ? $value['thc'] : '';
                                            $ccbd = isset($value['cbd']) && !empty($value['cbd']) ? $value['cbd'] : '';
                                            $cprice = isset($value['price_original']) && !empty($value['price_original']) ? $value['price_original'] : '0';
                                            $cdescription = isset($value['product_description']) && !empty($value['product_description']) ? $value['product_description'] : '';
                                            $cdiscount_price = isset($value['price_discounted']) && !empty($value['price_discounted']) ? $value['price_discounted'] : 0;

                                            if (isset($value['product_id_website'])) {
                                                $cproduct_sku = sprintf("%.0f ",$value['product_id_website']);
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $cproduct_sku = sprintf("%.0f ",$value['product_id']);
                                                }else{
                                                    $cproduct_sku = '';
                                                } 
                                            }
                                            if (isset($value['image_filename'])) {
                                                $cimage = $value['image_filename'];
                                            }else{
                                                $cimage = 'noimage.jpg';
                                            }
                                            $cproduct_url = isset($value['product_url']) && !empty($value['product_url']) ? $value['product_url'] : '';
                                            $cimage_url = isset($value['image_url']) && !empty($value['image_url']) ? $value['image_url'] : '';
                                            if (isset($value['featured'])) {
                                                $cis_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $cis_featured = 1;
                                            }
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] != null) {
                                                    $cmanage_stock = 1;
                                                }else{
                                                    $cmanage_stock = $prod->manage_stock;
                                                }
                                            }else{
                                                $cmanage_stock = $prod->manage_stock;
                                            }
                                            if ($product_status == 'active') {
                                                $prod->status = $product_status;
                                            }
                                            
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $cqty = $value['qty'];
                                            }else{
                                                $cqty = $prod->qty;
                                            }
                                            $oldCheck = Product::where('brand_id', $insertIdBrand)->where('parent_id', $insertIdCat)->where('type_id', $insertIdType)->where('strain_id', $insertIdStrain)->where('dispensary_id', $insertIdDes)->where('name', $value['product_name'])->where('amount', $camount)->where('thc', $cthc)->where('cbd', $ccbd)->where('price', $cprice)->where('discount_price', $cdiscount_price)->where('product_sku', $cproduct_sku)->where('image', $cimage)->where('description', $cdescription)->where('product_url', $cproduct_url)->where('image_url', $cimage_url)->where('is_featured', $cis_featured)->where('manage_stock', $cmanage_stock)->where('qty', $cqty)->first(); 
                                            if (!$oldCheck) {
                                                $errorInsert[]=array("sno"=>$i,"title"=>'Replace Before Product',"description"=>json_encode($prod, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                            }
                                           

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
                                                $prod->product_sku = sprintf("%.0f ",$value['product_id_website']);
                                            }else{
                                               if (isset($value['product_id'])) {
                                                    $prod->product_sku = sprintf("%.0f ",$value['product_id']);
                                                }else{
                                                    $prod->product_sku = '';
                                                } 
                                            }
                                            if (isset($value['image_filename'])) {
                                                $prod->image = $value['image_filename'];
                                            }else{
                                                $prod->image = 'noimage.jpg';
                                            }
                                            $prod->description = isset($value['product_description']) && !empty($value['product_description']) ? $value['product_description'] : '';
                                            $prod->product_url = isset($value['product_url']) && !empty($value['product_url']) ? $value['product_url'] : '';
                                            if (isset($value['image_filename']) && !empty($value['image_filename'])) {
                                                if ($value['image_filename'] != null && file_exists(public_path('uploads/products/' . $value['image_filename']))) {
                                                    $prod->image_url = asset('uploads/products').'/'.$value['image_filename'];
                                                }else{
                                                    if (isset($value['image_url']) && !empty($value['image_url'])) {

                                                        $image_url=$value['image_url'];
                                                        $base_name = basename($value['image_url']);
                                                        if ($base_name != null && file_exists(public_path('uploads/products/' . $base_name))) {
                                                            $prod->image_url = asset('uploads/products').'/'.$base_name;
                                                        }else{
                                                            /*$image_download = file_get_contents($value['image_url']); 
                                                            file_put_contents(public_path('uploads/products/'.basename($value['image_url'])), $image_download);
                                                            $prod->image_url = asset('uploads/products').'/'.basename($value['image_url']);*/
                                                            $prod->image_url = asset('uploads/products/noimage.jpg');
                                                        }
                                                        
                                                    }else{
                                                        $prod->image_url = asset('uploads/products/noimage.jpg');
                                                    }
                                                }
                                            }else{
                                                if (isset($value['image_url']) && !empty($value['image_url'])) {
                                                    $image_url=$value['image_url'];
                                                    $base_name = basename($value['image_url']);
                                                    if ($base_name != null && file_exists(public_path('uploads/products/' . $base_name))) {
                                                        $prod->image_url = asset('uploads/products').'/'.$base_name;
                                                    }else{
                                                        /*$image_download = file_get_contents($value['image_url']); 
                                                        file_put_contents(public_path('uploads/products/'.basename($value['image_url'])), $image_download);
                                                        $prod->image_url = asset('uploads/products').'/'.basename($value['image_url']);*/
                                                        $prod->image_url = asset('uploads/products/noimage.jpg');
                                                    }
                                                    
                                                }else{
                                                    $prod->image_url = asset('uploads/products/noimage.jpg');
                                                }
                                            }
                                            //$prod->image_url = isset($value['image_url']) && !empty($value['image_url']) ? $value['image_url'] : '';
                                            if (isset($value['featured'])) {
                                                $prod->is_featured = $value['featured'] != null ? strval((int) $value['featured']) : '0';
                                            }else{
                                                $prod->is_featured = 1;
                                            }
                                            if (isset($value['stock'])) {
                                                if ($value['stock'] == 1) {
                                                    $prod->manage_stock = 1;
                                                    $prod->update_stock = 'No';
                                                }else{
                                                    $prod->manage_stock = 1;
                                                }
                                            }else{
                                                $prod->manage_stock = 1;
                                                $prod->update_stock = 'No';
                                            }
                                            //$prod->status = 'active';
                                            if (isset($value['qty']) && !empty($value['qty'])) {
                                                $prod->qty = $value['qty'];
                                            }
                                            $prod->product_out = 'No';
                                            $prod->updated_at = date('Y-m-d H:i:s');
                                            $prod->save();

                                            if (!$oldCheck) {

                                                $errorInsert[]=array("sno"=>$i,"title"=>'Replace After Product',"description"=>json_encode($prod, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                            }
                                           // if ($prod->manage_stock == '1') {
                                                $dispensary = Dispensary::select('name')->where('id',$insertIdDes)->first();
                                                $brand = Brand::select('name')->where('id',$insertIdBrand)->first();
                                                if (!empty($dispensary) && !empty($brand)) {
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->where('type', 'in')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                            { 
                                                                
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->type = 'in';
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at '. $brand->name.' in '.$dispensary->name;
                                                                    $sms = $otp_message;
                                                                   // $this->otpSend($mobiles,$sms);

                                                                    $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                                                    $this->pushNotificationSendActive($userData, $push);
                                                                }
                                                            }
                                                        }
                                                        
                                                    }
                                                }
                                           // }
                                        }
                                    }else{
                                        $errorInsert[]=array("sno"=>$i,"title"=>'Required location id field data not addeded admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                    }
                                }else{
                                    $errorInsert[]=array("sno"=>$i,"title"=>'Required field location id',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                                  
                                }  
                                $i++;
                            }else{
                                echo  $temp." else<br>";
                                $errorInsert[]=array("sno"=>$i,"title"=>'Data issue',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));
                            }
                        }, 5);
                        
                    }

                    //Product::whereDate('updated_at', '!=', date('Y-m-d'))->where('product_out', 'No')->update(array('product_out' => 'Yes'));
                    if(!empty($errorInsert))
                    {
                        /*$errorInsert[]=array("sno"=>$i,"title"=>'Required field data not addeded admin',"description"=>json_encode($value, JSON_PRETTY_PRINT),"type"=>"product","status"=>"active",'created_at'=> date('Y-m-d H:i:s'));*/
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
            Log::info(['product' => $ex]);
           // dd($ex);
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
                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $prod->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                                                    if (!empty($productFav)) {
                                                        foreach ($productFav as $fav => $fav_value) {
                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $prod->id)->where('type', 'in')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                            { 
                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                { 
                                                                    $limitation_data = new UserNotificationLimitation;
                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                    $limitation_data->product_id = $prod->id;
                                                                    $limitation_data->type = 'in';
                                                                    $limitation_data->save();
                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                    $otp_message = 'Laravel – '.$prod->name.' is in stock at '. $brand->name.' in '.$dispensary->name;
                                                                    $sms = urlencode($otp_message);
                                                                    //$this->otpSend($mobiles,$sms);

                                                                    $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                                                    $this->pushNotificationSendActive($userData, $push);
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

        $mobiles = urlencode($number);
        //$sms = urlencode($body);
        $sms = $body;

        $sendSms = ['message' => $sms];
        $token = env('TOKAN_FIRST');
        $headers = array();
        $headers[] = 'accept: application/json';
        $headers[] = 'authorization: Basic '.$token;
        $headers[] = 'content-type: application/json';
        $curl = curl_init();


        $smsurl = "https://us-1.dailystory.com/api/v1/textmessage/sendsingle?mobile=".$mobiles."&dsid=DailyStory%20unique%20id";
        //echo '<pre>'; print_r($smsurl); die;
        curl_setopt_array($curl, [
          CURLOPT_URL => $smsurl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => json_encode($sendSms),
          CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
         // echo "cURL Error #:" . $err;
        } else {
         // echo $response;
        }
        return json_decode($response,true);
        /*$ID = env('TWILIO_ACCOUNT_SID');
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
            '&From=' . rawurlencode('+1 469 798 7898') .
            '&Body=' . rawurlencode($body));

        $resp = curl_exec($ch);
        curl_close($ch);
        return json_decode($resp,true);*/


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
public function pushNotificationSendActive($user, $push) {
        try
        {
            $notification=new Notificationuser();
            $notification->sender_id = $push['sender_id'];
            $notification->receiver_id = $user->id;
            $notification->notification_type = $push['notification_type'];
            $notification->title = $push['title'];
            $notification->description = $push['description'];
            $notification->status = 'active';
            $notification->save();

            $sound = true;
            $alert = true;
            /*if ($user->sound == 'Yes') {
                $sound = 'true';
            }
            if ($user->alert == 'Yes') {
                $alert = 'true';
            }*/
            //dd($user->devices);
            $headtitle = ucfirst($push['title']);
            $extramessage = ucfirst($push['description']);
            if (isset($user->devices)) {
                foreach ($user->devices as $k => $v) {
                    $device_type = isset($v) && !empty($v->device_type) ? $v->device_type : 'android' ;
                    $apptoken = isset($v) && !empty($v->device_token) ? $v->device_token : '' ;
                    
                    if ($device_type == 'android') {
                        $this->androidPushNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
                    }
                    if ($device_type == 'ios') {
                        $this->androidPushNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
                        //$this->sendIosNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
                    }
                }
            }

            return [];
        } catch (\Exception $ex) {
            return [];
        }
    }
    public function androidPushNotification($token, $title, $extramessage, $sound, $alert)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'title' => $title,
            'sound' => $sound,
            'body' => $extramessage,
            'vibrate' => $alert,
        ];
        $extraNotificationData = ["message" => $notification, "moredata" => $extramessage, 'type' => ''];
        $fcmNotification = [
            //'registration_ids' => $tokenList, 
            'to'        => $token, 
            'notification' => $notification,
            'data' => $extraNotificationData
        ];
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
        $data = json_encode($fcmNotification);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
       // dd($result);
        if ($result === FALSE) {
           // die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
    public function sendIosNotification($token, $title, $extramessage, $sound, $alert)
    {
        $url = "https://fcm.googleapis.com/fcm/send";
        $registrationIds = $token;
        $serverKey =env('FCM_LEGACY_KEY');
        $body = $extramessage;
        $notification = array('title' =>$title , 'body' => $body, 'text' => $body, 'sound' => $sound);
        $arrayToSend = array('to' => $registrationIds, 'notification'=>$notification,'priority'=>'high');
        $json = json_encode($arrayToSend);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: key='.env('FCM_LEGACY_KEY');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
                    curl_setopt($ch,CURLOPT_POST, true );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        $result = curl_exec($ch);
        if ($result === FALSE) 
        {
          //  die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close( $ch );
        return $result;
    }
}