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
                if(isset($rows[0])){
                    foreach ($rows->chunk(100) as $row) {
                        if (!empty($row)) {
                            foreach ($row as $key => $value) {
                            /*if ((isset($value['brand']) && !empty($value['brand'])) && (isset($value['location_id']) && !empty($value['location_id'])) && (isset($value['category']) && !empty($value['category'])) && (isset($value['type']) && !empty($value['type'])) && (isset($value['strain']) && !empty($value['strain'])) && (isset($value['qty']) && !empty($value['qty'])) && (isset($value['price_original']) && !empty($value['price_original'])) && (isset($value['image_filename']) && !empty($value['image_filename'])) && (isset($value['product_name']) && !empty($value['product_name'])) && (isset($value['product_description']) && !empty($value['product_description'])) && (isset($value['amount']) && !empty($value['amount'])) && (isset($value['thc']) && !empty($value['thc'])) && (isset($value['cbd']) && !empty($value['cbd'])) && (isset($value['product_url']) && !empty($value['product_url'])) && (isset($value['image_url']) && !empty($value['image_url'])) && (isset($value['price_discounted']) && !empty($value['price_discounted']))) {*/

                             if ((isset($value['brand']) && !empty($value['brand'])) && (isset($value['location_id']) && !empty($value['location_id'])) && (isset($value['category']) && !empty($value['category'])) && (isset($value['type']) && !empty($value['type']))  && (isset($value['strain']) && !empty($value['strain'])) && (isset($value['price_original']) && !empty($value['price_original'])) && (isset($value['product_name']) && !empty($value['product_name']))) {
                                    if(isset($value['brand']) && isset($value['location_id']) && isset($value['category']) && isset($value['type']) && isset($value['strain'])  && isset($value['price_original']) && isset($value['product_name']) && isset($value['product_description']) && isset($value['amount']) && isset($value['thc']) && isset($value['cbd']) && isset($value['product_url']) && isset($value['image_url']) && isset($value['price_discounted'])){
                                    
                                    }else{  
                                        if($value != 'brand' && $value != 'location_id' && $value != 'category' && $value != 'type' && $value != 'name' && $value != 'strain' && $value != 'price_original' && $value != 'product_name' && $value != 'amount' && $value != 'thc' && $value != 'cbd' && $value != 'product_url' && $value != 'image_url' && $value != 'price_discounted'){
                                        
                                        }else{
                                            Request::session()->flash('error', 'Please add right file' . $i);
                                            return redirect()->back();
                                        }
                                    }
                                   //
                                    // validate fields properly
                                    
                                   /* if ($value['amount'] == null) {
                                        Request::session()->flash('error', 'Amount is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['thc'] == null) {
                                        Request::session()->flash('error', 'thc is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['cbd'] == null) {
                                        Request::session()->flash('error', 'cbd is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['product_url'] == null) {
                                        Request::session()->flash('error', 'Product Url is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['image_url'] == null) {
                                        Request::session()->flash('error', 'Image Url is blank at line ' . $i);
                                        return redirect()->back();
                                    }*/
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
                                    /*if ($value['qty'] <= 0) {
                                        Request::session()->flash('error', 'Quantity should be greather than 0 at line ' . $i);
                                        return redirect()->back();
                                    }   
                                    if ($value['price_original'] == null) {
                                        Request::session()->flash('error', 'Price is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if ($value['price_discounted'] == null) {
                                        Request::session()->flash('error', 'Price Discounted is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                                    if (isset($value['product_id_website'])) {
                                        if ($value['product_id_website'] == null) {
                                            Request::session()->flash('error', 'Product id sku is blank at line ' . $i);
                                            return redirect()->back();
                                        }
                                    }else{
                                        if (isset($value['product_id'])) {
                                            if ($value['product_id'] == null) {
                                                Request::session()->flash('error', 'Product id sku is blank at line ' . $i);
                                                return redirect()->back();
                                            }
                                        }
                                    }*/
                                    
                                    /*$brand = Brand::where('name',$value['brand'])->where('status', '!=','delete')->first();
                                    if (empty($brand)) {
                                        $insertArray=array("name"=>$value['brand'],"image"=>"noimage.jpg",'description'=>'',"status"=>"active");
                                        $insertIdBrand=Brand::insertGetId($insertArray);
                                        if ($insertIdBrand) {
                                            $dispensary = Dispensary::where('location_id',$value['location_id'])->where('brand_id',$insertIdBrand)->where('status', '!=','delete')->first();
                                            if (empty($dispensary)) {
                                                $dispensary_save = new Dispensary;
                                                $dispensary_save->location_id = $value['location_id'];
                                                $dispensary_save->name = $value['location_id'];
                                                $dispensary_save->brand_id = $insertIdBrand;
                                                $dispensary_save->image = 'noimage.jpg';
                                                $dispensary_save->description = 'Test';
                                                $dispensary_save->status = 'active';
                                                $dispensary_save->created_at = date('Y-m-d H:i:s');
                                                $dispensary_save->save();
                                            }
                                        }
                                    }else{

                                        $dispensary = Dispensary::where('location_id',$value['location_id'])->where('brand_id',$brand->id)->where('status', '!=','delete')->first();
                                        if (empty($dispensary)) {
                                            $dispensary_save = new Dispensary;
                                            $dispensary_save->location_id = $value['location_id'];
                                            $dispensary_save->name = $value['location_id'];
                                            $dispensary_save->brand_id = $brand->id;
                                            $dispensary_save->image = 'noimage.jpg';
                                            $dispensary_save->description = 'Test';
                                            $dispensary_save->status = 'active';
                                            $dispensary_save->created_at = date('Y-m-d H:i:s');
                                            $dispensary_save->save();
                                        }
                                    }*/
                                     
                                    
                                    if ($value['category'] != null) {
                                        $cat = Category::where('name', $value['category'])->where('status', '!=', 'delete')->whereNull('parent_id')->first();

                                        if (empty($cat)) {

                                            /*$category_save = new Category;
                                            $category_save->name = $value['category'];
                                            $category_save->image = 'noimage.jpg';
                                            $category_save->type = 'category';
                                            $category_save->description = 'Test';
                                            $category_save->status = 'active';
                                            $category_save->created_at = date('Y-m-d H:i:s');*/
                                            $insertArray=array("name"=>$value['category'],"image"=>"noimage.jpg","type"=>"category",'description'=>'',"status"=>"active");
                                            $insertIdCat=Category::insertGetId($insertArray);

                                            if ($insertIdCat) {
                                                $type = Category::where('name', $value['type'])->where('parent_id', $insertIdCat)->where('status', '!=', 'delete')->first();
                                                if (empty($type)) {
                                                    $insertArray=array("name"=>$value['type'],"parent_id"=>$insertIdCat,"image"=>"noimage.jpg","type"=>"type",'description'=>'',"status"=>"active");
                                                    $insertId=Category::insertGetId($insertArray);
                                                    /*$type_save = new Category;
                                                    $type_save->name = $value['type'];
                                                    $type_save->parent_id = $category_save->id;
                                                    $type_save->image = 'noimage.jpg';
                                                    $type_save->type = 'type';
                                                    $type_save->description = 'Test';
                                                    $type_save->status = 'active';
                                                    $type_save->created_at = date('Y-m-d H:i:s');*/
                                                    if ($insertId) {
                                                        $strain = Category::where('name', $value['strain'])->where('parent_id', $insertId)->where('status', '!=', 'delete')->first();
                                                        if (empty($strain)) {
                                                            $strain_save = new Category;
                                                            $strain_save->name = $value['strain'];
                                                            $strain_save->parent_id = $insertId;
                                                            $strain_save->image = 'noimage.jpg';
                                                            $strain_save->type = 'strain';
                                                            $strain_save->description = 'Test';
                                                            $strain_save->status = 'active';
                                                            $strain_save->created_at = date('Y-m-d H:i:s');
                                                            $strain_save->save();
                                                        }
                                                    }
                                                }else{
                                                    $strain = Category::where('name', $value['strain'])->where('parent_id', $type->id)->where('status', '!=', 'delete')->first();
                                                    if (empty($strain)) {
                                                        $strain_save = new Category;
                                                        $strain_save->name = $value['strain'];
                                                        $strain_save->parent_id = $type->id;
                                                        $strain_save->image = 'noimage.jpg';
                                                        $strain_save->type = 'strain';
                                                        $strain_save->description = 'Test';
                                                        $strain_save->status = 'active';
                                                        $strain_save->created_at = date('Y-m-d H:i:s');
                                                        $strain_save->save();
                                                    }
                                                }
                                            }
                                        }else{
                                            $type = Category::where('name', $value['type'])->where('parent_id', $cat->id)->where('status', '!=', 'delete')->first();

                                            if (empty($type)) {
                                                $insertArray=array("name"=>$value['type'],"parent_id"=>$cat->id,"image"=>"noimage.jpg","type"=>"type",'description'=>'',"status"=>"active");

                                               
                                                $insertId=Category::insertGetId($insertArray);
                                             
                                               /* $type_save = new Category;
                                                $type_save->name = $value['type'];
                                                $type_save->parent_id = $cat->id;
                                                $type_save->image = 'noimage.jpg';
                                                $type_save->type = 'type';
                                                $type_save->description = 'Test';
                                                $type_save->status = 'active';
                                                $type_save->created_at = date('Y-m-d H:i:s');*/

                                                if ($insertId) {
                                                    
                                                    $strain = Category::where('name', $value['strain'])->where('parent_id', $insertId)->where('status', '!=', 'delete')->first();
                                                    if (empty($strain)) {
                                                        $strain_save = new Category;
                                                        $strain_save->name = $value['strain'];
                                                        $strain_save->parent_id = $insertId;
                                                        $strain_save->image = 'noimage.jpg';
                                                        $strain_save->type = 'strain';
                                                        $strain_save->description = 'Test';
                                                        $strain_save->status = 'active';
                                                        $strain_save->created_at = date('Y-m-d H:i:s');
                                                        $strain_save->save();
                                                    }
                                                }
                                            }else{
                                                $strain = Category::where('name', $value['strain'])->where('parent_id', $type->id)->where('status', '!=', 'delete')->first();
                                                if (empty($strain)) {
                                                    $strain_save = new Category;
                                                    $strain_save->name = $value['strain'];
                                                    $strain_save->parent_id = $type->id;
                                                    $strain_save->image = 'noimage.jpg';
                                                    $strain_save->type = 'strain';
                                                    $strain_save->description = 'Test';
                                                    $strain_save->status = 'active';
                                                    $strain_save->created_at = date('Y-m-d H:i:s');
                                                    $strain_save->save();
                                                }
                                            }
                                        }
                                    } else {
                                        if ($value['category'] == null) {
                                            Request::session()->flash('error', 'Category is blank at line ' . $i);
                                            return redirect()->back();
                                        }
                                    }
                                  
                                    /*if ($value['category'] != null) {
                                        $cat = Category::where('name', $value['category'])->where('status', '!=', 'delete')->first();
                                        if (!isset($cat->id)) {
                                            Request::session()->flash('error', 'Category doesnot exists at line ' . $i);
                                            return redirect()->back();
                                        }
                                    } else {
                                        if ($value['category'] != null) {
                                            Request::session()->flash('error', 'Category is blank at line ' . $i);
                                            return redirect()->back();
                                        }
                                    }*/
                                    
                                    
                                    /*if ($value['strain'] != null) {
                                        $strain = Category::where('name', $value['strain'])->where('status', '!=', 'delete')->first();
                                        if (!isset($strain->id)) {
                                            Request::session()->flash('error', 'Sub strain doesnot exists at line ' . $i);
                                            return redirect()->back();
                                        }
                                    } else {
                                        if ($value['strain'] != null) {
                                            Request::session()->flash('error', 'Sub strain is blank at line ' . $i);
                                            return redirect()->back();
                                        }
                                    }*/
                                    /*if ($value['image_filename'] == null) {
                                            Request::session()->flash('error', 'Image is blank at line ' . $i);
                                            return redirect()->back();
                                    }*/
                                    if ($value['product_name'] == null) {
                                        Request::session()->flash('error', 'Product Name is blank at line ' . $i);
                                        return redirect()->back();
                                    }
                    
                                    if ($value['product_description'] == null) {
                                            /*Request::session()->flash('error', 'Product Description is blank at line ' . $i);
                                            return redirect()->back();*/
                                    } 
                                    /*if($value != 'image_filename' && $value != 'category' && $value != 'type' && $value != 'strain' && $value != 'name' && $value != 'product_description' && $value != 'price' && $value != 'qty' && $value != 'featured' && $value != 'dispensary' && $value != 'stock'){
                                        
                                    }else{
                                        Request::session()->flash('error', 'Please add right file' . $i);
                                        return redirect()->back();
                                    }*/
                                     if ($value['product_name'] != null) {
                                        $brand = array();
                                        $dispensary = array();
                                        $brand = Brand::where('name',$value['brand'])->first();
                                        if (empty($brand)) {
                                            /*Request::session()->flash('error', 'Brand doesnot exists at line ' . $i);
                                            return redirect()->back();*/
                                        }else{
                                             $dispensary = Dispensary::where('location_id',$value['location_id'])->where('brand_id',$brand->id)->first();
                                            if (empty($dispensary)) {
                                                /*Request::session()->flash('error', 'Location doesnot exists at line ' . $i);
                                                return redirect()->back();*/
                                            }
                                        }
                                        $type = array();
                                        $strain_website = array();
                                        $category = array();
                                        $category = Category::where('name',$value['category'])->whereNull('parent_id')->first();
                                        if (empty($category)) {
                                            
                                            $CustomLog = new CustomLog;
                                            $CustomLog->sno = $i;
                                            $CustomLog->title = $value['category'].' category does not exit admin';
                                            $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                                            $CustomLog->type = 'product';
                                            $CustomLog->status = 'active';
                                            $CustomLog->created_at = date('Y-m-d H:i:s');
                                            $CustomLog->save();
                                            
                                            /*Request::session()->flash('error', 'Category doesnot exists at line ' . $i);
                                            return redirect()->back();*/
                                        }else{
                                            $type = Category::where('name', $value['type'])->where('parent_id', $category->id)->first();
                                            if (empty($type)) {
                                                
                                                $CustomLog = new CustomLog;
                                                $CustomLog->sno = $i;
                                                $CustomLog->title = $value['type'].' type does not exit admin';
                                                $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                                                $CustomLog->type = 'product';
                                                $CustomLog->status = 'active';
                                                $CustomLog->created_at = date('Y-m-d H:i:s');
                                                $CustomLog->save();
                                                
                                                /*Request::session()->flash('error', 'Type doesnot exists at line ' . $i);
                                                return redirect()->back();*/
                                            }else{
                                               $strain_website = Category::where('name',$value['strain'])->where('parent_id', $type->id)->first();
                                                if (empty($strain_website)) {
                                                    
                                                    $CustomLog = new CustomLog;
                                                    $CustomLog->sno = $i;
                                                    $CustomLog->title = $value['strain'].' strain does not exit admin';
                                                    $CustomLog->description = json_encode($value, JSON_PRETTY_PRINT);
                                                    $CustomLog->type = 'product';
                                                    $CustomLog->status = 'active';
                                                    $CustomLog->created_at = date('Y-m-d H:i:s');
                                                    $CustomLog->save();
                                                    
                                                    /*Request::session()->flash('error', 'Strain doesnot exists at line ' . $i);
                                                    return redirect()->back();*/
                                                }  
                                            }
                                        } 
                                        /*$category_sub = Category::where('name',$value['category'])->first();
                                        if (!isset($category_sub->id)) {
                                            Request::session()->flash('error', 'Sub Category doesnot exists at line ' . $i);
                                            return redirect()->back();
                                        }*/
                                        
                                        
                                        /*$strain_website_sub = Category::where('name',$value['strain'])->first();
                                        if (!isset($cstrain_website_sub->id)) {
                                            Request::session()->flash('error', 'Sub Strain doesnot exists at line ' . $i);
                                            return redirect()->back();
                                        }*/

                                        if (!empty($dispensary) && !empty($brand) && !empty($category) && !empty($type) && !empty($strain_website)) {
                                            $j++;
                                            $prod = Product::where('name', $value['product_name'])->where('dispensary_id', $dispensary->id)->where('brand_id', $brand->id)->first();
                                            if (!$prod) {
                                                
                                                $prod = new Product;
                                                $prod->brand_id = $brand->id;
                                                $prod->parent_id = $category->id;
                                               // $prod->sub_parent_id = $category_sub->id;
                                                $prod->type_id = $type->id;
                                                $prod->strain_id = $strain_website->id;
                                               // $prod->sub_strain_id = $strain_website_sub->id;
                                                $prod->dispensary_id = $dispensary->id;
                                                $prod->name = $value['product_name'];
                                                $prod->amount = $value['amount'];
                                                $prod->sub_amount = 0;
                                                $prod->thc = $value['thc'];
                                                $prod->cbd = $value['cbd'];
                                                $prod->price = $value['price_original'];
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
                                               // $prod->qty = $value['qty'];
                                                if (isset($value['qty']) && !empty($value['qty'])) {
                                                    $prod->qty = $value['qty'];
                                                }else{
                                                    $prod->qty = 500;
                                                }
                                                
                                                $prod->save();
                                                
                                            }else{
                                                $prod->brand_id = $brand->id;
                                                $prod->parent_id = $category->id;
                                               // $prod->sub_parent_id = $category_sub->id;
                                                $prod->type_id = $type->id;
                                                $prod->strain_id = $strain_website->id;
                                               // $prod->sub_strain_id = $strain_website_sub->id;
                                                $prod->dispensary_id = $dispensary->id;
                                                $prod->name = $value['product_name'];
                                                $prod->amount = $value['amount'];
                                                $prod->sub_amount = 0;
                                                $prod->thc = $value['thc'];
                                                $prod->cbd = $value['cbd'];
                                                $prod->price = $value['price_original'];
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
                                               // $prod->qty = $value['qty'];
                                                if (isset($value['qty']) && !empty($value['qty'])) {
                                                    $prod->qty = $value['qty'];
                                                }else{
                                                    $prod->qty = 500;
                                                }
                                                $prod->save();
                                                /*if ($prod->manage_stock == '1') {
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
                                                }*/

                                                if ($prod->manage_stock == '1') {
                                                    
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
                           
                            /*$i = 2;
                            foreach($row as $key => $value){
                               
                                $i++;
                            }*/
                        }
                        /*if ($j == 0) {
                            Request::session()->flash('error', 'Data not store please check log');
                            return redirect()->back();
                        }else{
                            Request::session()->flash('success', 'Data imported successfully');
                            return redirect()->back();
                        }*/
                    }
                    if ($j == 0) {
                        Request::session()->flash('error', 'Data not store please check log');
                        return redirect()->back();
                    }else{
                        Request::session()->flash('success', 'Data imported successfully');
                        return redirect()->back();
                    }
                }else{
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