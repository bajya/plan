<?php

namespace App\Http\Controllers\Backend;
use App\Library\Helper;
use App\Library\Notify;    
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Category;
use App\Dispensary;
use App\ProductType;
use App\Strain;
use App\Product;
use App\ProductFavourite;
use App\UserNotificationLimitation;
use App\Brand;
use App\Filemanager;
use App\ImportFile;
use App\CustomLog;
use App\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Image;
use URL;
use Zip;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use App\Events\BulkImageCrop;
use App\Imports\ProductImport;
use App\Imports\ProductUpdate;
use App\Exports\ProductExport;
use Symfony\Component\Finder\SplFileInfo;

class ProductController extends Controller {
    public $product;
    public $category;
    public $dispensary;
    public $strain;
    public $categories;
    public $dispensarys;
    public $strains;
    public $currency;
    public $logs;

    public function __construct() {
        $this->product = new Product;
        $this->logs = new CustomLog;
        $this->category = new Category;
        $this->strain = new Strain;
        $this->dispensary = new Brand;
        $this->currency = '$';

        $records2 = $this->dispensary->fetchAdminBrands();
        $list2 = $records2->get();
        //dd($list2);
        $result2 = [];
        $dispensarys = [];
        $i2 = 1;
        foreach ($list2 as $key2 => $value2) {
            $dispensarys[$value2->id] = $i2 . ". " . $value2->name;
            $root2 = $value2;
            //dd($root2->childCat);
            $dispensarys = $this->setList($root2, $dispensarys, $i2, 1);
            $i2++;
        }
        $this->dispensarys = $dispensarys;
        $records = $this->category->fetchCategories();
        $list = $records->get();
        $result = [];
        $categories = [];
        $i = 1;
        foreach ($list as $key => $value) {
            $categories[$value->id] = $i . ". " . $value->name;
            $root = $value;
            $categories = $this->setList($root, $categories, $i, 1);
            $i++;
        }
        $this->categories = $categories;

        $records1 = $this->strain->fetchStrains();
        $list1 = $records1->get();
        $result1 = [];
        $strains = [];
        $i1 = 1;
        foreach ($list1 as $key1 => $value1) {
            $strains[$value1->id] = $i1 . ". " . $value1->name;
            $root1 = $value1;
            $strains = $this->setList($root1, $strains, $i1, 1);
            $i1++;
        }
        $this->strains = $strains;
        $this->columns = [
            "select", "product_code", "product_id", "category_id", "subcategory_id", "name", "description", "image", "manage_stock", "is_featured", "status", "activate", "action", "fav_count",
        ];

        $this->middleware('permission:product-list|product-create|product-edit|product-delete', ['only' => ['index','store']]);
        $this->middleware('permission:product-create', ['only' => ['create','store']]);
        $this->middleware('permission:product-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:product-delete', ['only' => ['destroy']]);
    }
    public function setListIndex($root, $categories, $i, $level) {
        if (isset($root->childCat) && !empty($root->childCat)) {
            $child = $root->childCat;
            $j = 1;
            foreach ($child as $ch) {
                if ($ch->status != 'delete') {
                    $k = "&nbsp;&nbsp;" . $i . "." . $j;
                    $categories[$ch->id] = $k . '. ' . $ch->name;
                    $categories = $this->setList($ch, $categories, $k, ++$level);
                    $level = 2;
                    $j++;
                }
            }
            $root = $child;
        }
        
        return $categories;
    }
    public function setList($root, $categories, $i, $level) {
        if (isset($root->childCat) && !empty($root->childCat)) {
            $child = $root->childCat;
            $j = 1;
            foreach ($child as $ch) {
                if ($ch->status != 'delete') {
                    $k = "&nbsp;&nbsp;" . $i . "." . $j;
                    $categories[$root->id . '-' . $ch->id] = $k . '. ' . $ch->name;
                    $categories = $this->setList($ch, $categories, $k, ++$level);
                    $level = 2;
                    $j++;
                }
            }
            $root = $child;
        }
        return $categories;
    }
    public function index() {
        
        $list = $this->category->fetchCategories()->get();
        $result = [];
        $categories = [];
        $i = 1;

        foreach ($list as $key => $value) {
            $categories[$value->id] = $i . ". " . $value->name;
            $root = $value;
            $categories = $this->setListIndex($root, $categories, $i, 1);
            $i++;
        }
        $brands = Brand::select('id','name')->where('status', '!=','delete')->orderBy('name', 'asc')->get();
        $dispensarys = array();
        $categorys = Category::select('id','name')->where('status', '!=','delete')->whereNull('parent_id')->orderBy('name', 'asc')->get();
        $producttypes = array();
        $strains = Strain::select('id','name')->where('status', '!=', 'delete')->orderBy('name', 'asc')->groupBy('name')->get();
        return view('backend.products.index', compact('categories', 'brands', 'dispensarys', 'categorys', 'producttypes', 'strains'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function productsAjax(Request $request) {
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->product->fetchProducts($request, $this->columns);
        $total = $records->count();
        if (isset($request->start)) {
            $products = $records->offset($request->start)->limit($request->length)->get();
        } else {
            $products = $records->offset($request->start)->limit($total)->get();
        }
        $result = [];
        $no = 1;
        foreach ($products as $product) {
            $data = [];   
            $data['select'] = '<div class="form-check form-check-flat"><label class="form-check-label"><input type="checkbox" class="form-check-input" name="user_id[]" value="' . $product->id . '"><i class="input-helper"></i></label></div>';
            $data['product_sku'] = isset($product->product_sku) && !empty($product->product_sku) ? $product->product_sku : '-';
            $data['brand_id'] = isset($product->brand->name) && !empty($product->brand->name) ? ucfirst($product->brand->name) : '-';
            $data['dispensary'] = isset($product->dispensary->name) && !empty($product->dispensary->name) ? $product->dispensary->name : '-';
            $data['category_id'] = isset($product->category->name) && !empty($product->category->name) ? $product->category->name : '-';
            $data['type_id'] = isset($product->type->name) && !empty($product->type->name) ? $product->type->name : '-';
            $data['strain_id'] = isset($product->strain->name) && !empty($product->strain->name) ? $product->strain->name : '-';

            $data['subcategory_id'] = ($product->subcategory != null) ? $product->subcategory->name : '-';
            $data['name'] = $product->name;
            $data['price'] = $product->price;
            $data['image'] = ($product->image_url != null) ? '<img src="'.$product->image_url.'" width="70" />' : '-';


            $data['description'] = ($product->description != null) ? $product->description : '-';
            
            $data['is_featured'] = ucfirst(config('constants.CONFIRM.' . $product->is_featured));
            $data['status'] = ucfirst(config('constants.STATUS.' . $product->status));
            $data['sno']         = $no++;
            $data['fav_count'] = $product->active_favs != null ? count($product->active_favs) : 0;
            $data['notification'] = '<div class="dt-buttons float-right"><a href="javascript:void(0)" data-href="'.route('sendNotificationProduct').'" data-id="'. $product->id .'" class="btn dt-button py-2  sendNotificationAction">Send</a></div>';

            //$data['manage_stock'] = ucfirst(config('constants.CONFIRM.' . $product->manage_stock));
            $data['manage_stock'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($product->manage_stock == '1' ? ' checked' : '') . ' data-id="' . $product->id . '" data-on-color="success" data-off-color="info" data-on-text="Yes" data-off-text="No" data-size="mini" name="cstock" class="stockProduct"></div></div>';
            $data['activate'] = '<div class="bt-switch"><div class="col-md-2"><input type="checkbox"' . ($product->status == 'active' ? ' checked' : '') . ' data-id="' . $product->id . '" data-on-color="success" data-off-color="info" data-on-text="Active" data-off-text="Inactive" data-size="mini" name="cstatus" class="statusProduct"></div></div>';
           

            $action = '';

            if (Helper::checkAccess(route('editProduct'))) {
                $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('editProduct', ['id' => $product->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="Edit"><i class="fa fa-pencil"></i></a>';
            }
            $action .= '&nbsp;&nbsp;&nbsp;<a href="' . route('viewProduct', ['id' => $product->id]) . '" class="toolTip" data-toggle="tooltip" data-placement="bottom" title="View Detail"><i class="fa fa-eye"></i></a>';
            
            if (Helper::checkAccess(route('deleteProduct'))) {
                $action .= '&nbsp;&nbsp;&nbsp;<a href="javascript:;" class="toolTip deleteProduct" data-toggle="tooltip" data-id="' . $product->id . '" data-placement="bottom" title="Delete"><i class="fa fa-times"></i></a>';
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
        $url = route('addProduct');
        $product = new Product;
        //$categories = $this->categories;
       // $strains = $this->strains;
        //$dispensarys = $this->dispensarys;
        $brands = Brand::select('id','name')->where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
        $dispensarys = array();
        $categories = Category::select('id','name')->where('status', '!=', 'delete')->whereNull('parent_id')->orderBy('name', 'asc')->get();
        $producttypes = array();
        $strains = Strain::select('id','name')->where('status', '!=', 'delete')->orderBy('name', 'asc')->groupBy('name')->get();
        //return view('backend.products.create', compact('type', 'url', 'product', 'categories', 'dispensarys', 'strains','producttypes', 'producttypes'));
        return view('backend.products.create', compact('type', 'url', 'product', 'brands', 'categories', 'dispensarys', 'strains','producttypes', 'producttypes'));
    }

    /**
     * check for unique name during adding new product
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function checkProduct(Request $request, $id = null) {
        if (isset($request->product_name)) {
            $check = Product::where('name', $request->product_name);
            if (isset($id) && $id != null) {
                $check = $check->where('id', '!=', $id);
            }
            if (isset($request->category) && $request->category != null) {
                $check = $check->where('parent_id', $request->category);
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
        // dd($request->all());
        $validate = Validator($request->all(), [
            'product_name' => 'required',
            'product_image' => 'required|mimes:jpeg,png,jpg,gif,svg',
            'category_id' => 'required',
            'type_id' => 'required',
            'strain_id' => 'required',
            //'amount' => 'required',
            //'thc' => 'required',
            //'cbd' => 'required',
            'price_color_code' => 'required',
            'prod_description' => 'required',
            'price' => 'required',
            'qty' => 'required',
            'dispensary_id' => 'required',
        ]);
        $attr = [
            'product_name' => 'Product Name',
            'product_image' => 'Product Image',
            'category_id' => 'Category',
            'type_id' => 'Type',
            'strain_id' => 'Strain',
            //'amount' => 'Amount',
            //'thc' => 'Thc',
            //'cbd' => 'Thc',
            'price_color_code' => 'Price Color',
            'prod_description' => 'Description',
            'price' => 'Price',
            'qty' => 'Quantity',
            'dispensary_id' => 'Dispensary',
        ];
        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->route('createProduct')->withInput($request->all())->withErrors($validate);
        } else {
            try {
                $product = new Product;

                $product->product_code = Helper::generateNumber('products', 'product_code');
                $filename = "";
                if ($request->hasfile('product_image')) {
                    $file = $request->file('product_image');
                    $filename = time() . $file->getClientOriginalName();
                    $filename = str_replace(' ', '', $filename);
                    $filename = str_replace('.jpeg', '.jpg', $filename);
                    $file->move(public_path('uploads/products'), $filename);

                    //store image thumbnails
                   /* $path = 'uploads/products/';
                    $filePath = public_path('uploads/products/' . $filename);
                    $thumbnail_name = 'small-'.$filename;
                    $img = Image::make($filePath)->resize(150, 100, function ($constraint) {
                        $constraint->aspectRatio();
                    });
                    $img->save(public_path($path.'small/'.$thumbnail_name));

                   
                    $thumbnail_name = 'medium-'.$filename;
                    $img = Image::make($filePath)->resize(300, 185, function ($constraint) {
                        $constraint->aspectRatio();
                    });

                    $img->save(public_path($path.'medium/'.$thumbnail_name));*/

                    //Helper::compress_image(public_path('uploads/products/' . $filename), 100);
                }
                if ($filename != "") {
                    $product->image = $filename;
                }
                

                /*$dispensary = explode('-', $request->dispensary_id);
                $product->brand_id = isset($dispensary[0]) && !empty($dispensary[0]) ? $dispensary[0] : 0;
                $product->dispensary_id = isset($dispensary[1]) && !empty($dispensary[1]) ? $dispensary[1] : 0;*/

                /*$category = explode('-', $request->category_id);
                $product->parent_id = isset($category[0]) && !empty($category[0]) ? $category[0] : 0;
                $product->sub_parent_id = isset($category[1]) && !empty($category[1]) ? $category[1] : 0;*/

                $product->dispensary_id = $request->post('dispensary_id');
                $product->brand_id = $request->post('brand_id');
                $product->parent_id = $request->post('category_id');
                $product->type_id = $request->post('type_id');
                $product->strain_id = $request->post('strain_id');

                /*$strain = explode('-', $request->strain_id);
                $product->strain_id = isset($strain[0]) && !empty($strain[0]) ? $strain[0] : 0;
                $product->sub_strain_id = isset($strain[1]) && !empty($strain[1]) ? $strain[1] : 0;*/
                $product->name = $request->post('product_name');
                $product->description = $request->post('prod_description');
                $product->price = $request->post('price');
                $product->discount_price = $request->post('discount_price');
                $product->qty = $request->post('qty');
                $product->price_color_code = $request->post('price_color_code');
                $product->product_sku = $request->post('product_sku');
                $product->amount = $request->post('amount');
                $product->thc = $request->post('thc');
                $product->cbd = $request->post('cbd');
                $product->image_url = $request->post('image_url');
                $product->product_url = $request->post('product_url');
                $product->status = trim($request->post('status'));
                $product->is_featured = trim($request->post('is_featured'));
                $product->manage_stock = trim($request->post('manage_stock'));
                $product->created_at = date('Y-m-d H:i:s');
                $product->updated_at = date('Y-m-d H:i:s');

                
                if ($product->save()) {
                    $request->session()->flash('success', 'Product added successfully');
                    return redirect()->route('products');
                } else {
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('products');
                }
            } catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('products');
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
            $product = Product::where('id', $id)->first();
            if (isset($product->id)) {
                return view('backend.products.view', compact('product'));

            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('products');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('products');
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
            $product = Product::where('id', $id)->first();
            if (isset($product->id)) {
                $type = 'edit';
                $url = route('updateProduct', ['id' => $product->id]);
               // $categories = $this->categories;
               // $strains = $this->strains;
                //$dispensarys = $this->dispensarys;
                $brands = Brand::select('id','name')->where('status', '!=', 'delete')->get();
                $dispensarys = Dispensary::select('id','name')->where('status', '!=', 'delete')->where('brand_id', $product->brand_id)->orderBy('name', 'asc')->get();
                $categories = Category::select('id','name')->where('status', '!=', 'delete')->whereNull('parent_id')->orderBy('name', 'asc')->get();
                $producttypes = Category::select('id','name')->where('status', '!=', 'delete')->where('parent_id', $product->parent_id)->orderBy('name', 'asc')->get();
               /* $strains = Strain::select('id','name')->where('status', '!=', 'delete')->where('brand_id', $product->brand_id)->where('dispensary_id', $product->dispensary_id)->orderBy('name', 'asc')->get();*/
                $strains = Strain::select('id','name')->where('status', '!=', 'delete')->orderBy('name', 'asc')->groupBy('name')->get();
                return view('backend.products.create', compact('product', 'type', 'url', 'brands', 'categories', 'dispensarys', 'strains','producttypes'));
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('products');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('products');
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
            $product = Product::where('id', $id)->first();
            if (isset($product->id)) {
                $validate = Validator($request->all(), [
                    'product_name' => 'required',
                    'category_id' => 'required',
                    'type_id' => 'required',
                    'strain_id' => 'required',
                    //'amount' => 'required',
                    //'thc' => 'required',
                    //'cbd' => 'required',
                    'price_color_code' => 'required',
                    'prod_description' => 'required',
                    'price' => 'required',
                    'qty' => 'required',
                    'dispensary_id' => 'required',
                ]);
                $attr = [
                    'product_name' => 'Product Name',
                    'category_id' => 'Category',
                    'type_id' => 'Type',
                    'strain_id' => 'Strain',
                   // 'amount' => 'Strain',
                    //'thc' => 'Thc',
                    //'cbd' => 'Thc',
                    'price_color_code' => 'Price Color',
                    'prod_description' => 'Description',
                    'price' => 'Price',
                    'qty' => 'Quantity',
                    'dispensary_id' => 'Dispensary',
                ];

                $validate->setAttributeNames($attr);

                if ($validate->fails()) {
                    return redirect()->route('editProduct', ['id' => $product->id])->withInput($request->all())->withErrors($validate);
                } else {
                    try {
                        $filename = "";
                        if ($request->hasfile('product_image')) {
                            $file = $request->file('product_image');
                            $filename = time() . $file->getClientOriginalName();
                            $filename = str_replace(' ', '', $filename);
                            $filename = str_replace('.jpeg', '.jpg', $filename);
                            $file->move(public_path('uploads/products'), $filename);
                            
                            //store image thumbnails
                            /*$path = 'uploads/products/';
                            $filePath = public_path('uploads/products/' . $filename);
                            $thumbnail_name = 'small-'.$filename;
                            $img = Image::make($filePath)->resize(150, 100, function ($constraint) {
                                $constraint->aspectRatio();
                            });
                            $img->save(public_path($path.'small/'.$thumbnail_name));

                            $thumbnail_name = 'medium-'.$filename;
                            $img = Image::make($filePath)->resize(300, 185, function ($constraint) {
                                $constraint->aspectRatio();
                            });

                            $img->save(public_path($path.'medium/'.$thumbnail_name));*/

                            //Helper::compress_image(public_path('uploads/products/' . $filename), 100);
                            if ($product->image != null && file_exists(public_path('uploads/products/' . $product->image))) {
                                if ($product->image != 'noimage.jpg') {
                                   // unlink(public_path('uploads/products/' . $product->image));
                                }
                            }
                        }
                        if ($filename != "") {
                            $product->image = $filename;
                        }
                        /*$dispensary = explode('-', $request->dispensary_id);
                        $product->brand_id = isset($dispensary[0]) && !empty($dispensary[0]) ? $dispensary[0] : 0;
                        $product->dispensary_id = isset($dispensary[1]) && !empty($dispensary[1]) ? $dispensary[1] : 0;*/

                        /*$category = explode('-', $request->category_id);
                        $product->parent_id = isset($category[0]) && !empty($category[0]) ? $category[0] : 0;
                        $product->sub_parent_id = isset($category[1]) && !empty($category[1]) ? $category[1] : 0;*/
                        $product->dispensary_id = $request->post('dispensary_id');
                        $product->brand_id = $request->post('brand_id');
                        $product->parent_id = $request->post('category_id');
                        $product->type_id = $request->post('type_id');
                        $product->strain_id = $request->post('strain_id');

                        /*$strain = explode('-', $request->strain_id);
                        $product->strain_id = isset($strain[0]) && !empty($strain[0]) ? $strain[0] : 0;
                        $product->sub_strain_id = isset($strain[1]) && !empty($strain[1]) ? $strain[1] : 0;*/
                        $product->name = $request->post('product_name');
                        $product->description = $request->post('prod_description');
                        $product->price = $request->post('price');
                        $product->discount_price = $request->post('discount_price');
                        $product->qty = $request->post('qty');
                        $product->price_color_code = $request->post('price_color_code');
                        $product->product_sku = $request->post('product_sku');
                        $product->amount = $request->post('amount');
                        $product->thc = $request->post('thc');
                        $product->cbd = $request->post('cbd');
                        $product->image_url = $request->post('image_url');
                        $product->product_url = $request->post('product_url');
                        $product->status = trim($request->post('status'));
                        $product->is_featured = trim($request->post('is_featured'));
                        $product->manage_stock = trim($request->post('manage_stock'));
                        $product->updated_at = date('Y-m-d H:i:s');
                        if ($product->save()) {
                            $dispensary = Dispensary::where('id',$request->post('dispensary_id'))->first();
                            if ($dispensary) {
                                $brand = Brand::where('id',$request->post('brand_id'))->first();
                                if ($brand) {
                                    if ($product->manage_stock == '1') {
                                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                                        if (!empty($productFav)) {
                                            foreach ($productFav as $fav => $fav_value) {
                                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where('type', 'in')->where("product_id", $product->id)->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                { 
                                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                    { 
                                                        $limitation_data = new UserNotificationLimitation;
                                                        $limitation_data->user_id = $fav_value->user_id;
                                                        $limitation_data->product_id = $product->id;
                                                        $limitation_data->type = 'in';
                                                        $limitation_data->save();
                                                        $mobiles = $userData->phone_code.$userData->mobile;
                                                        $otp_message = 'Laravel – '.$product->name.' is in stock at '. $brand->name.' in '.$dispensary->name;
                                                        $sms = $otp_message;
                                                       // $this->otpSend($mobiles,$sms);

                                                        $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                                        $this->pushNotificationSendActive($userData, $push);
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        /*$productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', '!=', 'inactive')->get();
                                        if (!empty($productFav)) {
                                            foreach ($productFav as $fav => $fav_value) {
                                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where('type', 'out')->where("product_id", $product->id)->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                { 
                                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                    { 
                                                        $limitation_data = new UserNotificationLimitation;
                                                        $limitation_data->user_id = $fav_value->user_id;
                                                        $limitation_data->product_id = $product->id;
                                                        $limitation_data->type = 'out';
                                                        $limitation_data->save();
                                                        $mobiles = $userData->phone_code.$userData->mobile;
                                                        $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $brand->name.' in '.$dispensary->name;
                                                        $sms = $otp_message;
                                                        //$this->otpSend($mobiles,$sms);

                                                        $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                                        $this->pushNotificationSendActive($userData, $push);
                                                    }
                                                }
                                            }
                                        }*/
                                    }
                                }
                            }
                            $request->session()->flash('success', 'Product updated successfully');
                            return redirect()->route('products');
                        } else {
                            $request->session()->flash('error', 'Something went wrong. Please try again later.');
                            return redirect()->route('products');
                        }
                    } catch (Exception $e) {
                        $request->session()->flash('error', 'Something went wrong. Please try again later.');
                        return redirect()->route('products');
                    }

                }
            } else {
                $request->session()->flash('error', 'Invalid Data');
                return redirect()->route('products');
            }
        } else {
            $request->session()->flash('error', 'Invalid Data');
            return redirect()->route('products');
        }

    }

    // activate/deactivate product
    public function updateStatus(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $product = Product::find($request->statusid);

            if (isset($product->id)) {
                if ($request->status == 'active') {
                    $maincats = Category::where('id', $product->parent_id)->where('type', 'category')->where('status', 'active')->first();
                    if (!empty($maincats)) {
                        $product->status = $request->status;
                        
                    }else{
                        $request->session()->flash('error', 'Unable to update product. Beacuase category not active yet.');
                        return redirect()->back();
                    }
                    $maintypes = Category::where('id', $product->type_id)->where('type', 'type')->where('status', 'active')->first();
                    if (!empty($maintypes)) {
                        $product->status = $request->status;
                        
                    }else{
                        $request->session()->flash('error', 'Unable to update product. Beacuase type not active yet.');
                        return redirect()->back();
                    }
                    $mainstrains = Strain::where('id', $product->strain_id)->where('status', 'active')->first();
                    if (!empty($mainstrains)) {
                        $product->status = $request->status;
                    }else{
                        $request->session()->flash('error', 'Unable to update product. Beacuase strain not active yet.');
                        return redirect()->back();
                    }
                }else{
                    $product->status = $request->status;
                }


                //$product->status = $request->status;
                if (isset($request->is_featured)) {
                    $product->is_featured = $request->is_featured;
                }
                if ($product->save()) {
                    $request->session()->flash('success', 'Product updated successfully.');
                    return redirect()->back();
                } else {
                    $request->session()->flash('error', 'Unable to update product. Please try again later.');
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

    // activate/deactivate product
    public function updateStatusAjax(Request $request) {

        if (isset($request->statusid) && $request->statusid != null) {
            $product = Product::find($request->statusid);
            $checkData = 0;
            if (isset($product->id)) {
                if ($request->status == 'active') {
                    $maincats = Category::where('id', $product->parent_id)->where('type', 'category')->where('status', 'active')->first();
                    if (!empty($maincats)) {
                        $product->status = $request->status;
                    }else{
                        $checkData = 1;
                        echo json_encode(['status' => 0, 'message' => 'Unable to update product. Beacuase category not active yet.']);
                    }
                    $maintypes = Category::where('id', $product->type_id)->where('type', 'type')->where('status', 'active')->first();
                    if (!empty($maintypes)) {
                        $product->status = $request->status;
                    }else{
                        $checkData = 1;
                        echo json_encode(['status' => 0, 'message' => 'Unable to update product. Beacuase type not active yet.']);
                    }
                    $mainstrains = Strain::where('id', $product->strain_id)->where('status', 'active')->first();
                    if (!empty($mainstrains)) {
                        $product->status = $request->status;
                    }else{
                        $checkData = 1;
                        echo json_encode(['status' => 0, 'message' => 'Unable to update product. Beacuase strain not active yet.']);
                    }
                }else{
                    $product->status = $request->status;
                }
                if ($checkData == 0) {
                    if ($product->save()) {
                        echo json_encode(['status' => 1, 'message' => 'Product updated successfully.']);
                    } else {
                        echo json_encode(['status' => 0, 'message' => 'Unable to update product. Please try again later.']);
                    }
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
        }

    }
    // activate/deactivate product
    public function updateStockAjax(Request $request) {
        try {
            if (isset($request->statusid) && $request->statusid != null) {
                $product = Product::find($request->statusid);

                if (isset($product->id)) {
                    $product->manage_stock = $request->status;
                    if ($product->save()) {

                        if ($product->manage_stock == '1') {
                            $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                            if (!empty($productFav)) {
                                foreach ($productFav as $fav => $fav_value) {
                                    if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'in')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                    { 
                                        if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                        { 
                                            $limitation_data = new UserNotificationLimitation;
                                            $limitation_data->user_id = $fav_value->user_id;
                                            $limitation_data->product_id = $product->id;
                                            $limitation_data->type = 'in';
                                            $limitation_data->save();
                                            $mobiles = $userData->phone_code.$userData->mobile;
                                            $otp_message = 'Laravel – '.$product->name.' is in stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                            $sms = $otp_message;
                                            //$this->otpSend($mobiles,$sms);

                                            $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                            $this->pushNotificationSendActive($userData, $push);
                                        }
                                    }
                                }
                            }
                        }else{
                            /*$productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', '!=', 'inactive')->get();
                            if (!empty($productFav)) {
                                foreach ($productFav as $fav => $fav_value) {
                                    if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                    { 
                                        if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                        { 
                                            $limitation_data = new UserNotificationLimitation;
                                            $limitation_data->user_id = $fav_value->user_id;
                                            $limitation_data->product_id = $product->id;
                                            $limitation_data->type = 'out';
                                            $limitation_data->save();
                                            $mobiles = $userData->phone_code.$userData->mobile;
                                            $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                            $sms = $otp_message;
                                            //$this->otpSend($mobiles,$sms);

                                            $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                            $this->pushNotificationSendActive($userData, $push);
                                        }
                                    }
                                }
                            }*/
                        }
                        echo json_encode(['status' => 1, 'message' => 'Product stock updated successfully.']);
                    } else {
                        echo json_encode(['status' => 0, 'message' => 'Unable to update product stock. Please try again later.']);
                    }
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
            }
        } catch (\Exception $ex) {
            echo json_encode(['status' => 0, 'message' => $ex]);
        }

    }
    // activate/deactivate product
    public function on_changeAjax(Request $request) {
        $html = '';
        if (isset($request->id) && $request->id != null) {
            $html.= '<option value="">'.$request->select.'</option>';
            if ($request->attr_modal == 'Dispensary') {
                $model = '\\App\\Dispensary';
                $model_data = $model::select('id','name')->where("brand_id", $request->id)->where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
            }else if ($request->attr_modal == 'Strain') {
                $model = '\\App\\Strain';
                $model_data = $model::select('id','name')->where("brand_id", $request->brand_id)->where("dispensary_id", $request->id)->where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
            }else if ($request->attr_modal == 'Category') {
                $model = '\\App\\Category';
                $model_data = $model::select('id','name')->where("parent_id", $request->id)->where('status', '!=', 'delete')->orderBy('name', 'asc')->get();
            }else{
                $model_data = array();
                $html = '';
            }

            if (!empty($model_data)) {
                foreach ($model_data as $key => $value) {
                    $html.='<option value="'.$value->id.'">'.ucfirst($value->name).'</option>';
                }
            }
            return $html;
        } else {
            $html.= '<option value="">'.$request->select.'</option>';
            return $html;
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request) {
        if (isset($request->deleteid) && $request->deleteid != null) {
            $product = Product::find($request->deleteid);

            if (isset($product->id)) {
                $product->status = 'delete';
                if ($product->save()) {
                    echo json_encode(['status' => 1, 'message' => 'Product deleted successfully.']);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Unable to delete product. Please try again later.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
            }
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
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
                $product = Product::find($id);

                if (isset($product->id)) {
                    $product->status = 'delete';
                    if ($product->save()) {
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Products deleted successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all products were deleted. Please try again later.']);
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
                $product = Product::find($id);
                if (isset($product->id)) {
                    if ($product->status == 'active') {
                        $product->status = 'inactive';
                    } elseif ($product->status == 'inactive') {
                        $maincats = Category::where('id', $product->parent_id)->where('type', 'category')->where('status', 'active')->first();
                        if (!empty($maincats)) {
                            $product->status = $request->status;
                            
                        }else{
                            $product->status = 'inactive';
                            $count--;
                        }
                        $maintypes = Category::where('id', $product->type_id)->where('type', 'type')->where('status', 'active')->first();
                        if (!empty($maintypes)) {
                            $product->status = $request->status;
                            
                        }else{
                            $product->status = 'inactive';
                            $count--;
                        }
                        $mainstrains = Strain::where('id', $product->strain_id)->where('status', 'active')->first();
                        if (!empty($mainstrains)) {
                            $product->status = $request->status;
                        }else{
                            $product->status = 'inactive';
                            $count--;
                        }
                    }
                    if ($product->save()) {
                        $count++;
                    }
                }
            }
            if ($count == $ids) {
                echo json_encode(["status" => 1, 'ids' => json_encode($request->ids), 'message' => 'Products updated successfully.']);
            } else {
                echo json_encode(["status" => 0, 'message' => 'Not all products were updated. Beacuase some parent data not active yet.']);
            }
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    public function bulkchangeStatusAll(Request $request) {
        if (isset($request->status) && $request->status != null) {
            DB::transaction(function () use($request) {
                
                if ($request->status == 'active') {
                    $count = 0;
                    $products = Product::select('id', 'parent_id', 'type_id', 'strain_id', 'status')->where('status', 'inactive')->where('status', '!=', 'delete')->get();
                    $ids = count($products);
                    if (!empty($products)) {
                        foreach ($products as $key => $product) {
                            $maincats = Category::where('id', $product->parent_id)->where('type', 'category')->where('status', 'active')->first();
                            if (!empty($maincats)) {
                                $product->status = 'active';
                                
                            }else{
                                $product->status = 'inactive';
                                $count--;
                            }
                            $maintypes = Category::where('id', $product->type_id)->where('type', 'type')->where('status', 'active')->first();
                            if (!empty($maintypes)) {
                                $product->status = 'active';
                                
                            }else{
                                $product->status = 'inactive';
                                $count--;
                            }
                            $mainstrains = Strain::where('id', $product->strain_id)->where('status', 'active')->first();
                            if (!empty($mainstrains)) {
                                $product->status = 'active';
                            }else{
                                $product->status = 'inactive';
                                $count--;
                            }
                            if ($product->save()) {
                                $count++;
                            }
                        }
                    }
                    if ($count == $ids) {
                        echo json_encode(["status" => 1, 'message' => 'All Products successfully active.']);
                    } else {
                        echo json_encode(["status" => 0, 'message' => 'Not all products were active. Beacuase some parent data not active yet.']);
                    }
                }else if ($request->status == 'inactive') {
                    Product::where('status', '!=', 'delete')->update(array('status' => $request->status));
                    echo json_encode(["status" => 1, 'message' => 'All Products successfully Inactive.']);
                }else{
                    echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
                } 
            }, 5);
            
            
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    public function sendNotificationProduct(Request $request) {
        if (isset($request->id) && $request->id != null) {
            DB::transaction(function () use($request) {
                $product = Product::find($request->id);

                if (isset($product->id)) {
                    if ($product->manage_stock == '1') {
                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                        if (!empty($productFav)) {
                            foreach ($productFav as $fav => $fav_value) {
                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'in')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                { 
                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                    { 
                                        $limitation_data = new UserNotificationLimitation;
                                        $limitation_data->user_id = $fav_value->user_id;
                                        $limitation_data->product_id = $product->id;
                                        $limitation_data->type = 'in';
                                        $limitation_data->save();
                                        $mobiles = $userData->phone_code.$userData->mobile;
                                        $otp_message = 'Laravel – '.$product->name.' is in stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                        $sms = $otp_message;
                                       // $this->otpSend($mobiles,$sms);
                                        $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                        $this->pushNotificationSendActive($userData, $push);
                                    }
                                }
                            }
                        }
                    }else{
                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', 'active')->get();
                        if (!empty($productFav)) {
                            foreach ($productFav as $fav => $fav_value) {
                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                { 
                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                    { 
                                        $limitation_data = new UserNotificationLimitation;
                                        $limitation_data->user_id = $fav_value->user_id;
                                        $limitation_data->product_id = $product->id;
                                        $limitation_data->type = 'out';
                                        $limitation_data->save();
                                        $mobiles = $userData->phone_code.$userData->mobile;
                                        $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                        $sms = $otp_message;
                                       // $this->otpSend($mobiles,$sms);

                                        $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                        $this->pushNotificationSendActive($userData, $push);
                                    }
                                }
                            }
                        }
                    }
                    echo json_encode(['status' => 1, 'message' => 'Successfully notification send favourite user.']);
                } else {
                    echo json_encode(['status' => 0, 'message' => 'Invalid Product']);
                }
                
            }, 5);
            
            
        } else {
            echo json_encode(["status" => 0, 'message' => 'Invalid Data']);
        }
    }
    /**
     * Show the form for importing product sheet
     *
     * @return \Illuminate\Http\Response
     */
   public function importAjeet(Request $request) {
        $url = route('importProducts');
        if (strtolower($request->method()) == 'post') {
            $validate = Validator($request->all(), [
                'product_import' => 'required|mimes:csv,zip',
            ]);
            $attr = [
                'product_import' => 'File Csv and Zip',
            ];
            $validate->setAttributeNames($attr);

            if ($validate->fails()) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('importProducts')->withInput($request->all())->withErrors($validate);
            } else {
                $uniqueId = 0;
                //if (CustomLog::where('type', 'product')->delete() >= 0) {
                    if (request()->file('product_import')->extension() == 'csv') {
                        $filenameArray = explode("_",substr(request()->file('product_import')->getClientOriginalName(), 0, strrpos(request()->file('product_import')->getClientOriginalName(), ".")));

                        if( in_array("products" ,$filenameArray))
                        {
                            $file_name = 'products';
                        }else{
                            $file_name = '';
                        }  
                        if ($file_name == 'products') {
                            $insertData = array();
                            $uniqueId = Helper::generateNumber('import_files', 'id');
                            $file = request()->file('product_import');
                            $filename = time() .$uniqueId. $file->getClientOriginalName();
                            $file->move(public_path('pendingfile'), $filename);
                            $insertData['filename'] = $filename;
                            $insertData['status'] = 0;
                            $insertData['type'] = "product";
                            ImportFile::insert($insertData);
                            /*$file = fopen($request->product_import->getRealPath(), "r");
                            $completeSheetData = array();
                            while (!feof($file)) {
                                $completeSheetData[] = fgetcsv($file);
                            }
                            fclose($file);

                            $heading = $completeSheetData[0];

                            $data = array_slice($completeSheetData, 1);

                            $Allparts = (array_chunk($data, 100));

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
                                $insertData[$key]['type'] = "product";
                                fclose($file);
                            }
                            ImportFile::insert($insertData);*/
                        }else{
                            $request->session()->flash('error', 'Please put file name ex. name_products.');
                            return redirect()->route('importProducts');
                        }
                        


                    }else{
                        \File::deleteDirectory('uploads/productszip/zip-csv');
                        $zip = Zip::open($request->product_import);
                        $zip->extract('uploads/productszip/zip-csv');
                        $files = \File::allFiles('uploads/productszip/zip-csv');
                        if (!empty($files)) {
                            foreach ($files as $key => $file) {
                                if ($file != null) {
                                    $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                    if( in_array("products" ,$filenameArray))
                                    {
                                        $file_name = 'products';
                                    }else{
                                        $file_name = '';
                                    }
                                    if ($file_name == 'products') {

                                        $insertData = array();
                                        $uniqueId = Helper::generateNumber('import_files', 'id');
                                        //$file = request()->file('product_import');
                                        $filename = time() .$uniqueId. $file->getFilename();
                                        \File::move($file, public_path('pendingfile/') . $filename);
                                        $insertData['filename'] = $filename;
                                        $insertData['status'] = 0;
                                        $insertData['type'] = "product";
                                        ImportFile::insert($insertData);


                                        /*$tempFile = fopen($file->getRealPath(), "r");
                                        $completeSheetData = array();
                                        while (!feof($tempFile)) {
                                            $completeSheetData[] = fgetcsv($tempFile);
                                        }

                                        fclose($tempFile);
                                        $heading = $completeSheetData[0];

                                        $data = array_slice($completeSheetData, 1);

                                        $Allparts = (array_chunk($data, 100));
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
                                            $insertData[$key]['type'] = "product";
                                            fclose($file);
                                        }
                                        ImportFile::insert($insertData);*/
                                    }else{
                                        $request->session()->flash('error', 'Please put file name ex. name_products.');
                                        return redirect()->route('importProducts');
                                    }
                                }
                            }
                           // \Artisan::call('cache:clear');
                        }

                    }
                    $request->session()->flash('success', 'Data imported successfully Please wait 40 to 60 seconds, your data will appear');
                    return redirect()->route('importProducts');
                /*}else{
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('importProducts');
                }*/
                
            }  
        }
        return view('backend.products.import', compact('url'));
    }
    public function import(Request $request) {
        ini_set('max_execution_time', 0);
        $url = route('importProducts');
        if (strtolower($request->method()) == 'post') {
            $validate = Validator($request->all(), [
                'product_import' => 'required|mimes:csv,zip',
            ]);
            $attr = [
                'product_import' => 'File Csv and Zip',
            ];
            $validate->setAttributeNames($attr);

            if ($validate->fails()) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('importProducts')->withInput($request->all())->withErrors($validate);
            } else {
                $uniqueId = 0;
                //if (CustomLog::where('type', 'product')->delete() >= 0) {
                    if (request()->file('product_import')->extension() == 'csv') {
                        $filenameArray = explode("_",substr(request()->file('product_import')->getClientOriginalName(), 0, strrpos(request()->file('product_import')->getClientOriginalName(), ".")));

                        if( in_array("products" ,$filenameArray))
                        {
                            $file_name = 'products';
                        }else{
                            $file_name = '';
                        }  
                        if ($file_name == 'products') {
                            $file = fopen($request->product_import->getRealPath(), "r");
                            $completeSheetData = array();
                            while (!feof($file)) {
                                $completeSheetData[] = fgetcsv($file);
                            }
                           
                            fclose($file);
                           
                            $heading = $completeSheetData[0];
                            $keyFind = '';
                            if (!empty($heading)) {
                                foreach ($heading  as $keyL => $valueL) {
                                    if ($valueL == 'location_id') {
                                        $keyFind = $keyL;
                                    }
                                }
                            }
                            $data = array_slice($completeSheetData, 1);
                            
                            $arrayLocationId = array();
                            if ($keyFind != '') {
                                if (!empty($data)) {
                                    foreach ($data  as $keyL => $valueL) {
                                        if (isset($valueL[$keyFind])) {
                                            $arrayLocationId[] = $valueL[$keyFind];
                                        } 
                                    }
                                }
                            }
                            $uniqueLocationId = array_unique($arrayLocationId);
                            if (!empty($uniqueLocationId)) {
                                $dispensaryId = Dispensary::whereIn('location_id', $uniqueLocationId)->where('status', '!=', 'delete')->pluck('id')->toArray();
                                if (!empty($dispensaryId)) {
                                    DB::transaction(function () use($dispensaryId) {
                                        Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->update(array('update_stock' => 'Yes'));

                                        /*$productList = Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->get();

                                        if (!empty($productList)) {
                                            foreach ($productList as $key => $product) {
                                                $product->manage_stock = 0;
                                                if ($product->save()) {
                                                    if ($product->manage_stock == '0') {
                                                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', '!=', 'inactive')->get();
                                                        if (!empty($productFav)) {
                                                            foreach ($productFav as $fav => $fav_value) {
                                                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                                { 
                                                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                    { 
                                                                        $limitation_data = new UserNotificationLimitation;
                                                                        $limitation_data->user_id = $fav_value->user_id;
                                                                        $limitation_data->product_id = $product->id;
                                                                        $limitation_data->type = 'out';
                                                                        $limitation_data->save();
                                                                        $mobiles = $userData->phone_code.$userData->mobile;
                                                                        $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                                                        $sms = $otp_message;
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
                                        }*/
                                    }, 5);
                                }
                            }
                            
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
                                $insertData[$key]['type'] = "product";
                                fclose($file);
                            }
                            ImportFile::insert($insertData);
                            $productFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->get();
                            if (!empty($productFiles)) {
                                foreach ($productFiles as $key => $productFile) {
                                    if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                        $file = public_path("pendingfile/" . $productFile->filename);
                                        ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

                                        Excel::import(new ProductImport, $file);
                                        ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                                        if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                            unlink(public_path('pendingfile/' . $productFile->filename));
                                            
                                        }
                                        ImportFile::where("id",$productFile->id)->delete();
                                    }
                                }
                            }
                            if (!ImportFile::first()) {
                                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                                    DB::transaction(function () {
                                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                                    }, 5);
                                }
                            }
                        }else{
                            $request->session()->flash('error', 'Please put file name ex. name_products.');
                            return redirect()->route('importProducts');
                        }
                    }else{
                        \File::deleteDirectory('uploads/productszip/zip-csv');
                        $zip = Zip::open($request->product_import);
                        $zip->extract('uploads/productszip/zip-csv');
                        $files = \File::allFiles('uploads/productszip/zip-csv');
                        if (!empty($files)) {
                            foreach ($files as $key => $file) {
                                if ($file != null) {
                                    $from_path = public_path('uploads/productszip/zip-csv');
                                        //if ($file->getFilename() == 'images') {
                                    if ($file->getExtension() == 'png' || $file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg' || $file->getExtension() == 'gif' || $file->getExtension() == 'svg') {
                                        $from_path_image = $from_path.'/images'; 
                                        $files_zip_images = \File::allFiles($from_path_image);
                                        if (!empty($files_zip_images) && (count($files_zip_images) > 0)) {
                                            foreach ($files_zip_images as $v1 => $files_zip_image) {
                                                if ($files_zip_image != null) {
                                                    if (!file_exists(public_path('uploads/products/' . $files_zip_image->getFilename()))) {
                                                        if ($files_zip_image->getExtension() == 'png' || $files_zip_image->getExtension() == 'jpg' || $files_zip_image->getExtension() == 'jpeg' || $files_zip_image->getExtension() == 'gif' || $files_zip_image->getExtension() == 'svg') {
                                                            $destination = public_path('uploads/products');
                                                            \File::move($files_zip_image, $destination .'/'. $files_zip_image->getFilename());
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }else{
                                        $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                        if( in_array("products" ,$filenameArray))
                                        {
                                            $file_name = 'products';
                                        }else{
                                            $file_name = '';
                                        }
                                        if ($file_name == 'products') {
                                            $tempFile = fopen($file->getRealPath(), "r");
                                            $completeSheetData = array();
                                            while (!feof($tempFile)) {
                                                $completeSheetData[] = fgetcsv($tempFile);
                                            }

                                            fclose($tempFile);
                                            $heading = $completeSheetData[0];
                                            $keyFind = '';
                                            if (!empty($heading)) {
                                                foreach ($heading  as $keyL => $valueL) {
                                                    if ($valueL == 'location_id') {
                                                        $keyFind = $keyL;
                                                    }
                                                }
                                            }
                                            $data = array_slice($completeSheetData, 1);
                                            $arrayLocationId = array();
                                            if ($keyFind != '') {
                                                if (!empty($data)) {
                                                    foreach ($data  as $keyL => $valueL) {
                                                        if (isset($valueL[$keyFind])) {
                                                            $arrayLocationId[] = $valueL[$keyFind];
                                                        } 
                                                    }
                                                }
                                            }
                                            $uniqueLocationId = array_unique($arrayLocationId);
                                            if (!empty($uniqueLocationId)) {
                                                $dispensaryId = Dispensary::whereIn('location_id', $uniqueLocationId)->where('status', '!=', 'delete')->pluck('id')->toArray();
                                                if (!empty($dispensaryId)) {
                                                    DB::transaction(function () use($dispensaryId) {
                                                        Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->update(array('update_stock' => 'Yes'));
                                                       /*$productList = Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->get();

                                                        if (!empty($productList)) {
                                                            foreach ($productList as $key => $product) {
                                                                $product->manage_stock = 0;
                                                                if ($product->save()) {
                                                                    if ($product->manage_stock == '0') {
                                                                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', '!=', 'inactive')->get();
                                                                        if (!empty($productFav)) {
                                                                            foreach ($productFav as $fav => $fav_value) {
                                                                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                                                { 
                                                                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                                    { 
                                                                                        $limitation_data = new UserNotificationLimitation;
                                                                                        $limitation_data->user_id = $fav_value->user_id;
                                                                                        $limitation_data->product_id = $product->id;
                                                                                        $limitation_data->type = 'out';
                                                                                        $limitation_data->save();
                                                                                        $mobiles = $userData->phone_code.$userData->mobile;
                                                                                        $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                                                                        $sms = $otp_message;
                                                                                       // $this->otpSend($mobiles,$sms);
                                                                                        $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                            $this->pushNotificationSendActive($userData, $push);
                                                                                    }
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }*/
                                                    }, 5);
                                                }
                                            }
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
                                                $insertData[$key]['type'] = "product";
                                                fclose($file);
                                            }
                                            ImportFile::insert($insertData);
                                            $productFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->get();
                                            if (!empty($productFiles)) {
                                                foreach ($productFiles as $key => $productFile) {
                                                    if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                                        $file = public_path("pendingfile/" . $productFile->filename);
                                                        ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

                                                        Excel::import(new ProductImport, $file);
                                                        ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                                                        if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                                            unlink(public_path('pendingfile/' . $productFile->filename));
                                                            
                                                        }
                                                        ImportFile::where("id",$productFile->id)->delete();
                                                    }
                                                }
                                            }
                                            if (!ImportFile::first()) {
                                                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                                                    DB::transaction(function () {
                                                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                                                    }, 5);
                                                }
                                                
                                            }
                                        }else{
                                            $request->session()->flash('error', 'Please put file name ex. name_products.');
                                            return redirect()->route('importProducts');
                                        }
                                    }
                                }
                            }
                           // \Artisan::call('cache:clear');
                        }
                    }
                    $request->session()->flash('success', 'Data imported successfully Please wait 40 to 60 seconds, your data will appear');
                    return redirect()->route('importProducts');
                /*}else{
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('importProducts');
                }*/
                
            }  
        }
        return view('backend.products.import', compact('url'));
    }

    public function importCron(Request $request) {
        ini_set('max_execution_time', 0);
        $url = route('importProducts');
        if (strtolower($request->method()) == 'post') {
            $validate = Validator($request->all(), [
                'product_import' => 'required|mimes:csv,zip',
            ]);
            $attr = [
                'product_import' => 'File Csv and Zip',
            ];
            $validate->setAttributeNames($attr);

            if ($validate->fails()) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('importProducts')->withInput($request->all())->withErrors($validate);
            } else {
                $uniqueId = 0;
                //if (CustomLog::where('type', 'product')->delete() >= 0) {
                    if (request()->file('product_import')->extension() == 'csv') {
                        $filenameArray = explode("_",substr(request()->file('product_import')->getClientOriginalName(), 0, strrpos(request()->file('product_import')->getClientOriginalName(), ".")));

                        if( in_array("products" ,$filenameArray))
                        {
                            $file_name = 'products';
                        }else{
                            $file_name = '';
                        }  
                        if ($file_name == 'products') {
                            $file = fopen($request->product_import->getRealPath(), "r");
                            $completeSheetData = array();
                            while (!feof($file)) {
                                $completeSheetData[] = fgetcsv($file);
                            }
                           
                            fclose($file);
                           
                            $heading = $completeSheetData[0];
                            $keyFind = '';
                            if (!empty($heading)) {
                                foreach ($heading  as $keyL => $valueL) {
                                    if ($valueL == 'location_id') {
                                        $keyFind = $keyL;
                                    }
                                }
                            }
                            $data = array_slice($completeSheetData, 1);
                            
                            $arrayLocationId = array();
                            if ($keyFind != '') {
                                if (!empty($data)) {
                                    foreach ($data  as $keyL => $valueL) {
                                        if (isset($valueL[$keyFind])) {
                                            $arrayLocationId[] = $valueL[$keyFind];
                                        } 
                                    }
                                }
                            }
                            $uniqueLocationId = array_unique($arrayLocationId);
                            if (!empty($uniqueLocationId)) {
                                $dispensaryId = Dispensary::whereIn('location_id', $uniqueLocationId)->where('status', '!=', 'delete')->pluck('id')->toArray();
                                if (!empty($dispensaryId)) {
                                    DB::transaction(function () use($dispensaryId) {
                                        Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->update(array('update_stock' => 'Yes'));

                                        /*$productList = Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->get();

                                        if (!empty($productList)) {
                                            foreach ($productList as $key => $product) {
                                                $product->manage_stock = 0;
                                                if ($product->save()) {
                                                    if ($product->manage_stock == '0') {
                                                        $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('pause_expire_time', '<', date('Y-m-d'))->where('is_user_status', '!=', 'inactive')->get();
                                                        if (!empty($productFav)) {
                                                            foreach ($productFav as $fav => $fav_value) {
                                                                if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                                { 
                                                                    if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                    { 
                                                                        $limitation_data = new UserNotificationLimitation;
                                                                        $limitation_data->user_id = $fav_value->user_id;
                                                                        $limitation_data->product_id = $product->id;
                                                                        $limitation_data->type = 'out';
                                                                        $limitation_data->save();
                                                                        $mobiles = $userData->phone_code.$userData->mobile;
                                                                        $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                                                        $sms = $otp_message;
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
                                        }*/
                                    }, 5);
                                }
                            }
                            
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
                                $insertData[$key]['type'] = "product";
                                fclose($file);
                            }
                            ImportFile::insert($insertData);
                        }else{
                            $request->session()->flash('error', 'Please put file name ex. name_products.');
                            return redirect()->route('importProducts');
                        }
                    }else{
                        \File::deleteDirectory('uploads/productszip/zip-csv');
                        $zip = Zip::open($request->product_import);
                        $zip->extract('uploads/productszip/zip-csv');
                        $files = \File::allFiles('uploads/productszip/zip-csv');
                        if (!empty($files)) {
                            foreach ($files as $key => $file) {
                                if ($file != null) {
                                    $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                    if( in_array("products" ,$filenameArray))
                                    {
                                        $file_name = 'products';
                                    }else{
                                        $file_name = '';
                                    }
                                    if ($file_name == 'products') {
                                        $tempFile = fopen($file->getRealPath(), "r");
                                        $completeSheetData = array();
                                        while (!feof($tempFile)) {
                                            $completeSheetData[] = fgetcsv($tempFile);
                                        }

                                        fclose($tempFile);
                                        $heading = $completeSheetData[0];
                                        $keyFind = '';
                                        if (!empty($heading)) {
                                            foreach ($heading  as $keyL => $valueL) {
                                                if ($valueL == 'location_id') {
                                                    $keyFind = $keyL;
                                                }
                                            }
                                        }
                                        $data = array_slice($completeSheetData, 1);
                                        $arrayLocationId = array();
                                        if ($keyFind != '') {
                                            if (!empty($data)) {
                                                foreach ($data  as $keyL => $valueL) {
                                                    if (isset($valueL[$keyFind])) {
                                                        $arrayLocationId[] = $valueL[$keyFind];
                                                    } 
                                                }
                                            }
                                        }
                                        $uniqueLocationId = array_unique($arrayLocationId);
                                        if (!empty($uniqueLocationId)) {
                                            $dispensaryId = Dispensary::whereIn('location_id', $uniqueLocationId)->where('status', '!=', 'delete')->pluck('id')->toArray();
                                            if (!empty($dispensaryId)) {
                                                DB::transaction(function () use($dispensaryId) {
                                                    Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->update(array('update_stock' => 'Yes'));
                                                   /*$productList = Product::where('status', '!=','delete')->where('manage_stock', 1)->whereIn('dispensary_id', $dispensaryId)->get();

                                                    if (!empty($productList)) {
                                                        foreach ($productList as $key => $product) {
                                                            $product->manage_stock = 0;
                                                            if ($product->save()) {
                                                                if ($product->manage_stock == '0') {
                                                                    $productFav = ProductFavourite::select('user_id')->where('user_id', '>', 0)->where('product_id', $product->id)->where('status', 'active')->where('pause_expire_time', '<', date('Y-m-d'))->where('pause_status', 'active')->get();
                                                                    if (!empty($productFav)) {
                                                                        foreach ($productFav as $fav => $fav_value) {
                                                                            if (!UserNotificationLimitation::where("user_id", $fav_value->user_id)->where("product_id", $product->id)->where('type', 'out')->whereDate('created_at', '=', date('Y-m-d'))->first()) 
                                                                            { 
                                                                                if ($userData = User::where("id", $fav_value->user_id)->where('status', 'active')->first()) 
                                                                                { 
                                                                                    $limitation_data = new UserNotificationLimitation;
                                                                                    $limitation_data->user_id = $fav_value->user_id;
                                                                                    $limitation_data->product_id = $product->id;
                                                                                    $limitation_data->type = 'out';
                                                                                    $limitation_data->save();
                                                                                    $mobiles = $userData->phone_code.$userData->mobile;
                                                                                    $otp_message = 'Laravel – '.$product->name.' is out of stock at '. $product->brand->name.' in '.$product->dispensary->name;
                                                                                    $sms = $otp_message;
                                                                                   // $this->otpSend($mobiles,$sms);
                                                                                    $push = array('sender_id' => 1, 'notification_type' => 'favourite', 'notification_count' => 0, 'title' => $otp_message, 'description' => $otp_message);
                                        $this->pushNotificationSendActive($userData, $push);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }*/
                                                }, 5);
                                            }
                                        }
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
                                            $insertData[$key]['type'] = "product";
                                            fclose($file);
                                        }
                                        ImportFile::insert($insertData);
                                    }else{
                                        $request->session()->flash('error', 'Please put file name ex. name_products.');
                                        return redirect()->route('importProducts');
                                    }
                                }
                            }
                           // \Artisan::call('cache:clear');
                        }
                    }
                    $request->session()->flash('success', 'Data imported successfully Please wait 40 to 60 seconds, your data will appear');
                    return redirect()->route('importProducts');
                /*}else{
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('importProducts');
                }*/
                
            }  
        }
        return view('backend.products.import', compact('url'));
    }
    public function importold(Request $request) {
        $url = route('importProducts');
        if (strtolower($request->method()) == 'post') {
            $validate = Validator($request->all(), [
                'product_import' => 'required|mimes:csv,zip',
            ]);
            $attr = [
                'product_import' => 'File Csv and Zip',
            ];
            $validate->setAttributeNames($attr);

            if ($validate->fails()) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->route('importProducts')->withInput($request->all())->withErrors($validate);
            } else {
                if (CustomLog::where('type', 'product')->delete() >= 0) {
                    if (request()->file('product_import')->extension() == 'csv') {
                        Excel::import(new ProductImport,request()->file('product_import'));
                    }else{
                        \File::deleteDirectory('uploads/productszip/zip-csv');
                        $zip = Zip::open($request->product_import);
                        $zip->extract('uploads/productszip/zip-csv');
                        $files = \File::allFiles('uploads/productszip/zip-csv');
                        if (!empty($files)) {
                            foreach ($files as $key => $file) {
                                if ($file != null) {
                                    try {
                                        Excel::import(new ProductImport, $file);
                                    } catch (NoTypeDetectedException $e) {
                                        //$request->session()->flash('error', 'Sorry you are using a wrong format to upload files.');
                                        //return redirect()->back();
                                    }
                                }
                            }
                           // \Artisan::call('cache:clear');
                        }
                        $request->session()->flash('success', 'Data imported successfully');
                    }
                }else{
                    $request->session()->flash('error', 'Something went wrong. Please try again later.');
                    return redirect()->route('importProducts');
                }
                
            }  
        }
        return view('backend.products.import', compact('url'));
    }

    public function exportView()
    {   
        return view('backend.products.export');
    }

    public function export(Request $request)
    {   
        if ($request->id == null) {
            $id = ['all'];
        } else {
            $id = explode(',', $request->id);
        }
        $columns = Product::pluck('name')->sort();

        return Excel::download(new ProductExport($id,$columns), 'products.xlsx');
    }

    public function bulkUpdate(Request $request)
    {
        $validate = Validator($request->all(), [
            'product_update' => 'required',
        ]);

        $attr = [
            'product_update' => 'File',
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            return redirect()->route('exportView')->withInput($request->all())->withErrors($validate);
        } else {
            Excel::import(new ProductUpdate,request()->file('product_update'));
            return redirect()->route('exportView');
        }
    }
    public function imageUpload()
    {
        return view('backend.products.image-upload');
    }
    
    public function bulkImageUpload(Request $request){
        $validate = Validator($request->all(), [
            'image_upload' => 'required|mimes:zip',

        ]);

        $attr = [
            'image_upload' => 'Zip File',
        ];

        $validate->setAttributeNames($attr);

        if ($validate->fails()) {
            $request->session()->flash('error', 'Something went wrong. Please try again later.');
            return redirect()->route('imageUpload')->withInput($request->all())->withErrors($validate);
        } else {
            try {
                \File::deleteDirectory('uploads/products/zip-images');
                $zip = Zip::open($request->image_upload);
                $zip->extract('uploads/products/zip-images');

                $files = \File::allFiles('uploads/products/zip-images');
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
                                
                                $destination = public_path('uploads/products');
                                \File::move($file, $destination .'/'. $imageName);
                                                                
                                //Helper::compress_image(public_path('uploads/products/' . $imageName), 100);
                                $imageName = str_replace('.jpeg', '.jpg', $imageName);
                                 $filemanager->type = 'product';
                                $filemanager->name = str_replace('.jpeg', '.jpg', $imageName);
                                $filemanager->image = str_replace('.jpeg', '.jpg', $imageName);
                                $filemanager->created_at = date('Y-m-d H:i:s');
                                $filemanager->save();
                            }
                        }
                    }
                   // \Artisan::call('cache:clear');
                    

                   
                }
                //event(New BulkImageCrop(Product::first())); 
                
                session()->flash('success','Zip Images uploaded successfully');
                return redirect()->route('filemanagers');
            } catch (Exception $e) {
                $request->session()->flash('error', 'Something went wrong. Please try again later.');
                return redirect()->back();
            }

        }
    }
    public function productLogsAjax(Request $request) {
        if (isset($request->search['value'])) {
            $request->search = $request->search['value'];
        }else{
            $request->search = '';
        }
        if (isset($request->order[0]['column'])) {
            $request->order_column = $request->order[0]['column'];
            $request->order_dir = $request->order[0]['dir'];
        }
        $records = $this->logs->fetchLog($request, $this->columns);
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

 
}
