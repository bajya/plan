<?php

namespace App\Http\Controllers;

use App\User;
use App\Message;
use App\Notificationuser;
use App\CMS;
use App\Push;
use App\ProductFavourite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

use App\Library\Helper;
use App\Library\Notify;
use App\ImportFile;
use App\CustomLog;
use App\State;
use App\Brand;
use App\Dispensary;
use App\Product;
use App\UserPlan;
use Spatie\Permission\Models\Role;
use Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use App\Imports\DispensaryImport;
use Illuminate\Support\Arr;
use Log;
use Zip;
use File;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // select all users except logged in user
        //$users = User::where('id', '!=', Auth::id())->get();

        // count how many messages are unread from the selected user
        $users = DB::select("select users.id, users.name, users.avatar, users.email, count(is_read) as unread
        from users LEFT JOIN messages ON users.id = messages.from and is_read = 0 and messages.to =".Auth::id()." where users.id !=".Auth::id()." 
        group by users.id, users.name, users.avatar, users.email");

        return view('home', ['users' => $users]);
    }

    public function getMessage ($user_id) {
        $my_id = Auth::id();

        // when click to see message selected user's message will be read, update
        Message::where(['from' => $user_id, 'to' => $my_id])->update(['is_read' => 1]);

        // getting all message for selected user
        // getting those message which is from = Auth::id() and to = user_id OR from = user_id and to = Auth::id();
        $messages = Message::where(function ($query) use ($user_id, $my_id) {
            $query->where('from', $my_id)->where('to', $user_id);
        })->orWhere(function ($query) use ($user_id, $my_id) {
            $query->where('from', $user_id)->where('to', $my_id);
        })->get();

        return view('messages.index', ['messages' => $messages]);
    }

    public function sendMessage(Request $request) {
        $from = Auth::id();
        $to = $request->receiver_id;
        $message = $request->message;
        $filename = '';
        if($request->hasFile("files")) {
            $filename = $this->uploadImage($request);
            $filename = url('/').'/uploads/message/'.$filename;
        }
        $data = new Message();
        $data->from = $from;
        $data->to = $to;
        $data->message = $message;
        $data->image = $filename;
        $data->is_read = 0; // message will be unread when sending message
        $data->save();

        // pusher
        $options = array (
            'cluster' => 'mt1',
            'useTLS' => true
        );

        $pusher = new Pusher (
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from' => $from, 'to' => $to];
        $pusher->trigger('my-channel', 'my-event', $data);
    }
    private function uploadImage($request)
    {
        $file = $request->file('files');
        $filename = md5(uniqid()) . "." . $file->getClientOriginalExtension();

        $file->move(public_path('uploads/message'), $filename);

        return $filename;
    }
    public function cronJob(Request $request) {
        try {
               
            ini_set('max_execution_time', 0);
            \Artisan::call('config:clear');
           
            $path = rtrim(base_path(), "/public_html/appAdmin").'/daily_updates';
            if (file_exists($path)) {
                $files = \File::allFiles($path);
            }else{
                $files = array();
            }
            if (!empty($files) && (count($files) > 0)) {

                $uniqueId = 0;
                foreach ($files as $key => $file) {
                    
                    if ($file != null) {
                            //if ($file->getFilename() == 'images') {
                        if ($file->getExtension() == 'png' || $file->getExtension() == 'jpg' || $file->getExtension() == 'jpeg' || $file->getExtension() == 'gif' || $file->getExtension() == 'svg') {
                            $from_path_image = $path.'/images'; 
                            if (file_exists($from_path_image)) {
                                $files_zip_images = \File::allFiles($from_path_image);
                            }else{
                                $from_path_image = $path;
                                if (file_exists($from_path_image)) {
                                    $files_zip_images = \File::allFiles($from_path_image);
                                }else{
                                    $files_zip_images = array();
                                }
                            }
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
                                if (file_exists($from_path_image.'/images')) {
                                    \File::deleteDirectory($from_path_image.'/images');
                                }
                            }
                            
                        }else{
                            if ($file->getExtension() == 'csv') {
                                //echo '<pre>'; print_r($file->getExtension()); die;
                                $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                if( in_array("products" ,$filenameArray))
                                {
                                    $file_name = 'products';
                                }else if( in_array("locations" ,$filenameArray))
                                {
                                    $file_name = 'locations';
                                }else{
                                    $file_name = '';
                                }
                                if ($file_name == 'products') {
                                    $file1 = fopen($file->getRealPath(), "r");
                                    $completeSheetData = array();
                                    while (!feof($file1)) {
                                        $completeSheetData[] = fgetcsv($file1);
                                    }
                                    fclose($file1);
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
                                            }, 5);
                                        }
                                    }
                                     //Excel::import(new ProductImport, $file);
                                    $Allparts = (array_chunk($data, 500));

                                    $insertData = array();
                                    foreach ($Allparts as $key => $parts) {

                                        $uniqueId = Helper::generateNumber('import_files', 'id');
                                        $orgName = time() . $key . $uniqueId . ".csv";
                                        $fileName = public_path("pendingfile/" . $orgName);
                                        $file1 = fopen($fileName, "w");
                                        fputcsv($file1, $heading);

                                        foreach ($parts as $key1 => $val) {
                                            if (!empty($val)) {
                                                fputcsv($file1, $val);
                                            }                       
                                        }
                                        $insertData[$key]['filename'] = $orgName;
                                        $insertData[$key]['status'] = 0;
                                        $insertData[$key]['type'] = "product";
                                        fclose($file1);
                                    }
                                    ImportFile::insert($insertData);
                                    
                                }else{
                                    if ($file_name == 'locations') {
                                       // Excel::import(new DispensaryImport, $file);
                                        $file1 = fopen($file->getRealPath(), "r");
                                        $completeSheetData = array();
                                        while (!feof($file1)) {
                                            $completeSheetData[] = fgetcsv($file1);
                                        }

                                        fclose($file1);

                                        $heading = $completeSheetData[0];

                                        $data = array_slice($completeSheetData, 1);

                                        $Allparts = (array_chunk($data, 500));



                                        $insertData = array();
                                        foreach ($Allparts as $key => $parts) {

                                            $uniqueId = Helper::generateNumber('import_files', 'id');
                                            $orgName = time() . $key . $uniqueId . ".csv";
                                            $fileName = public_path("pendingfile/" . $orgName);
                                            $file1 = fopen($fileName, "w");
                                            fputcsv($file1, $heading);

                                            foreach ($parts as $key1 => $val) {
                                                if (!empty($val)) {
                                                    fputcsv($file1, $val);
                                                }                       
                                            }
                                            $insertData[$key]['filename'] = $orgName;
                                            $insertData[$key]['status'] = 0;
                                            $insertData[$key]['type'] = "location";
                                            fclose($file1);
                                        }
                                        ImportFile::insert($insertData);
                                    }
                                }
                                if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                                    unlink($path.'/' . $file->getFilename());
                                }
                            }else{
                                if ($file->getExtension() == 'zip') {
                                    DB::transaction(function () use($file, $path) {
                                        $zip = Zip::open($file);
                                        $zip->extract($path);
                                        $zip->close();
                                    }, 5);
                                    
                                    if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                                        unlink($path.'/' . $file->getFilename());
                                    }
                                    $from_path = $path.'/'.rtrim($file->getFilename(), ".zip");
                                    sleep(10);

                                    if (file_exists($from_path)) {
                                        $files_zip = \File::allFiles($from_path);
                                    }else{
                                        if (file_exists($path)) {
                                            $files_zip = \File::allFiles($path);
                                        }else{
                                            $files_zip = array();
                                        }
                                    }
                                    if (!empty($files_zip) && (count($files_zip) > 0)) {
                                        $uniqueId = 0;
                                        foreach ($files_zip as $v => $file_all) {
                                            
                                            if ($file_all != null) {
                                                //if ($file_all->getFilename() == 'images') {
                                                if ($file_all->getExtension() == 'png' || $file_all->getExtension() == 'jpg' || $file_all->getExtension() == 'jpeg' || $file_all->getExtension() == 'gif' || $file_all->getExtension() == 'svg') {
                                                    
                                                    $from_path_image = $from_path.'/images'; 
                                                    if (file_exists($from_path_image)) {
                                                        $files_zip_images = \File::allFiles($from_path_image);
                                                    }else{
                                                        if (file_exists($path)) {
                                                            $files_zip_images = \File::allFiles($path);
                                                        }else{
                                                            $files_zip_images = array();
                                                        }
                                                    }
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
                                                        \File::deleteDirectory($from_path_image.'/images');
                                                    }
                                                    
                                                }else{
                                                    if ($file_all->getExtension() == 'csv') {
                                                        $filenameArray = explode("_",substr($file_all->getFilename(), 0, strrpos($file_all->getFilename(), ".")));
                                                        if( in_array("products" ,$filenameArray))
                                                        {
                                                            $file_name = 'products';
                                                        }else if( in_array("locations" ,$filenameArray))
                                                        {
                                                            $file_name = 'locations';
                                                        }else{
                                                            $file_name = '';
                                                        }
                                                        if ($file_name == 'products') {
                                                            $file1 = fopen($file_all->getRealPath(), "r");
                                                            $completeSheetData = array();
                                                            while (!feof($file1)) {
                                                                $completeSheetData[] = fgetcsv($file1);
                                                            }
                                                            fclose($file1);
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
                                                                    }, 5);
                                                                }
                                                            }
                                                           // Excel::import(new ProductImport, $file_all);
                                                            $Allparts = (array_chunk($data, 500));

                                                            $insertData = array();
                                                            foreach ($Allparts as $key => $parts) {

                                                                $uniqueId = Helper::generateNumber('import_files', 'id');
                                                                $orgName = time() . $key . $uniqueId . ".csv";
                                                                $fileName = public_path("pendingfile/" . $orgName);
                                                                $file1 = fopen($fileName, "w");
                                                                fputcsv($file1, $heading);

                                                                foreach ($parts as $key1 => $val) {
                                                                    if (!empty($val)) {
                                                                        fputcsv($file1, $val);
                                                                    }                       
                                                                }
                                                                $insertData[$key]['filename'] = $orgName;
                                                                $insertData[$key]['status'] = 0;
                                                                $insertData[$key]['type'] = "product";
                                                                fclose($file1);
                                                            }
                                                            ImportFile::insert($insertData);
                                                            
                                                        }else{
                                                            if ($file_name == 'locations') {
                                                                //Excel::import(new DispensaryImport, $file_all);
                                                                $file1 = fopen($file_all->getRealPath(), "r");
                                                                $completeSheetData = array();
                                                                while (!feof($file1)) {
                                                                    $completeSheetData[] = fgetcsv($file1);
                                                                }

                                                                fclose($file1);

                                                                $heading = $completeSheetData[0];

                                                                $data = array_slice($completeSheetData, 1);

                                                                $Allparts = (array_chunk($data, 500));



                                                                $insertData = array();
                                                                foreach ($Allparts as $key => $parts) {

                                                                    $uniqueId = Helper::generateNumber('import_files', 'id');
                                                                    $orgName = time() . $key . $uniqueId . ".csv";
                                                                    $fileName = public_path("pendingfile/" . $orgName);
                                                                    $file1 = fopen($fileName, "w");
                                                                    fputcsv($file1, $heading);

                                                                    foreach ($parts as $key1 => $val) {
                                                                        if (!empty($val)) {
                                                                            fputcsv($file1, $val);
                                                                        }                       
                                                                    }
                                                                    $insertData[$key]['filename'] = $orgName;
                                                                    $insertData[$key]['status'] = 0;
                                                                    $insertData[$key]['type'] = "location";
                                                                    fclose($file1);
                                                                }
                                                                ImportFile::insert($insertData);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                            if ($file_all->getFilename() != null && file_exists($path.'/' . $file_all->getFilename()) && $file_all->getExtension() != 'zip') {
                                                    unlink($path.'/' . $file_all->getFilename());
                                            }
                                        }
                                    }
                                    if (file_exists($from_path)) {
                                        \File::deleteDirectory($from_path);
                                    }
                                   // \File::deleteDirectory($from_path);
                                }
                            }
                            if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename()) && $file->getExtension() != 'zip') {
                                    unlink($path.'/' . $file->getFilename());
                            }
                        }
                        if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                                    unlink($path.'/' . $file->getFilename());
                        }
                        
                    }
                }
            }else{
                /*if (!ImportFile::whereNull('start_date')->first()) {
                    ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
                }*/
                $locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();


                if (!empty($locationsFiles) && count($locationsFiles) > 0) {
                    foreach ($locationsFiles as $key => $locationsFile) {
                        ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                        if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            $file = public_path("pendingfile/" . $locationsFile->filename);

                            Excel::import(new DispensaryImport, $file);
                            ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                            if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                                unlink(public_path('pendingfile/' . $locationsFile->filename));
                                
                            }
                            ImportFile::where("id",$locationsFile->id)->delete();
                        }
                    }
                }
                $productFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->get();
                    
                    if (!empty($productFiles)) {

                        foreach ($productFiles as $key => $productFile) {
                            ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                            if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                //dd($productFile);
                                $file = public_path("pendingfile/" . $productFile->filename);
                                

                                Excel::import(new ProductImport, $file);
                                ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                                if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                                    unlink(public_path('pendingfile/' . $productFile->filename));
                                    
                                }
                                ImportFile::where("id",$productFile->id)->delete();
                            }
                        }
                    }
                //Product::where('product_out', 'Yes')->update(array('manage_stock' => 0));
              \Artisan::call('config:clear');
                //Log::info(['cron' => 'Cron End']);

                if (!ImportFile::whereNull('start_date')->first()) {
                    if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                        DB::transaction(function () {
                            Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                        }, 5);
                    } 
                }
                $plan_res = UserPlan::select('id', 'status')->where('plan_expire_time', '<' ,date('Y-m-d'))->where('status', 'active')->get();
                if (!empty($plan_res)) {
                    foreach ($plan_res as $key => $value) {
                        UserPlan::where('id', $value->id)->update(array('status' => 'old'));
                    }
                }
            }
            /*if (!ImportFile::whereNull('start_date')->first()) {
                ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
            }*/
            $locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();
            if (!empty($locationsFiles) && count($locationsFiles) > 0) {
                foreach ($locationsFiles as $key => $locationsFile) {
                    ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                    if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                        $file = public_path("pendingfile/" . $locationsFile->filename);
                        

                        Excel::import(new DispensaryImport, $file);
                        ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            unlink(public_path('pendingfile/' . $locationsFile->filename));
                            
                        }
                        ImportFile::where("id",$locationsFile->id)->delete();
                    }
                }
            }
            $productFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->get();
            if (!empty($productFiles) && count($productFiles) > 0) {
                foreach ($productFiles as $key => $productFile) {
                    ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                    if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
                        $file = public_path("pendingfile/" . $productFile->filename);
                        

                        Excel::import(new ProductImport, $file);
                        ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                            unlink(public_path('pendingfile/' . $productFile->filename));
                            
                        }
                        ImportFile::where("id",$productFile->id)->delete();
                    }
                }
            }
            
           \Artisan::call('config:clear');

            if (!ImportFile::whereNull('start_date')->first()) {
                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                    DB::transaction(function () {
                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                    }, 5);
                } 
            }

            if (file_exists($path.'/images')) {
                \File::deleteDirectory($path.'/images');
            }
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
    
    
    public function cronJobStock(Request $request) {
        try {
               
            ini_set('max_execution_time', 0);
            \Artisan::call('config:clear');
            
            $locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();
            if (!empty($locationsFiles) && count($locationsFiles) > 0) {
                foreach ($locationsFiles as $key => $locationsFile) {
                    ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                    if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                        $file = public_path("pendingfile/" . $locationsFile->filename);
                        

                        Excel::import(new DispensaryImport, $file);
                        ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            unlink(public_path('pendingfile/' . $locationsFile->filename));
                            
                        }
                        ImportFile::where("id",$locationsFile->id)->delete();
                    }
                }
            }
            $productFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->get();
            if (!empty($productFiles) && count($productFiles) > 0) {
                foreach ($productFiles as $key => $productFile) {
                    ImportFile::where("id",$productFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
                    if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
                        $file = public_path("pendingfile/" . $productFile->filename);
                        

                        Excel::import(new ProductImport, $file);
                        ImportFile::where("id",$productFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($productFile->filename != null && file_exists(public_path('pendingfile/' . $productFile->filename))) {
                            unlink(public_path('pendingfile/' . $productFile->filename));
                            
                        }
                        ImportFile::where("id",$productFile->id)->delete();
                    }
                }
            }
            
            
           \Artisan::call('config:clear');
           // Log::info(['stock'=>'start']);
            if (!ImportFile::whereNull('start_date')->first()) {
                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                    DB::transaction(function () {
                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                    }, 5);
                } 
            }
            DB::transaction(function () {
                $user_fav = ProductFavourite::select('id', 'is_user_status', 'pause_status')->where('pause_expire_time', '<=' ,date('Y-m-d'))->where('is_user_status', 'pause')->where('pause_status', 'active')->get();
                if (!empty($user_fav)) {
                    foreach ($user_fav as $key1 => $value1) {
                        ProductFavourite::where('id', $value1->id)->update(array('is_user_status' => 'active', 'pause_status' => 'inactive'));
                    }
                }
                
                ImportFile::whereNotNull('start_date')->delete();
            }, 5);
        } catch (\Exception $ex) {
            dd($ex);
        }
    }
    public function cronJobO(Request $request) {
        try {
               
            ini_set('max_execution_time', 0);
           
            $path = rtrim(base_path(), "/public_html/appAdmin").'/daily_updates';
           // dd($path);
           // $path = public_path('uploads/daily_updates');
           // echo '<pre>'; print_r($path); die;
            //$files = \File::allFiles('uploads/daily_updates');
            $files = \File::allFiles($path);
            if (!empty($files)) {
                $uniqueId = 0;
                foreach ($files as $key => $file) {
                    if ($file != null) {
                        if ($file->getExtension() == 'csv') {
                            $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                            if( in_array("products" ,$filenameArray))
                            {
                                $file_name = 'products';
                            }else if( in_array("locations" ,$filenameArray))
                            {
                                $file_name = 'locations';
                            }else{
                                $file_name = '';
                            }
                            if ($file_name == 'products') {
                                $file1 = fopen($file->getRealPath(), "r");
                                $completeSheetData = array();
                                while (!feof($file1)) {
                                    $completeSheetData[] = fgetcsv($file1);
                                }
                                fclose($file1);
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
                                        }, 5);
                                    }
                                }
                                
                                $Allparts = (array_chunk($data, 500));

                                $insertData = array();
                                foreach ($Allparts as $key => $parts) {

                                    $uniqueId = Helper::generateNumber('import_files', 'id');
                                    $orgName = time() . $key . $uniqueId . ".csv";
                                    $fileName = public_path("pendingfile/" . $orgName);
                                    $file1 = fopen($fileName, "w");
                                    fputcsv($file1, $heading);

                                    foreach ($parts as $key1 => $val) {
                                        if (!empty($val)) {
                                            fputcsv($file1, $val);
                                        }                       
                                    }
                                    $insertData[$key]['filename'] = $orgName;
                                    $insertData[$key]['status'] = 0;
                                    $insertData[$key]['type'] = "product";
                                    fclose($file1);
                                }
                                ImportFile::insert($insertData);
                                
                            }else{
                                if ($file_name == 'locations') {
                                    $file1 = fopen($file->getRealPath(), "r");
                                    $completeSheetData = array();
                                    while (!feof($file1)) {
                                        $completeSheetData[] = fgetcsv($file1);
                                    }

                                    fclose($file1);

                                    $heading = $completeSheetData[0];

                                    $data = array_slice($completeSheetData, 1);

                                    $Allparts = (array_chunk($data, 500));



                                    $insertData = array();
                                    foreach ($Allparts as $key => $parts) {

                                        $uniqueId = Helper::generateNumber('import_files', 'id');
                                        $orgName = time() . $key . $uniqueId . ".csv";
                                        $fileName = public_path("pendingfile/" . $orgName);
                                        $file1 = fopen($fileName, "w");
                                        fputcsv($file1, $heading);

                                        foreach ($parts as $key1 => $val) {
                                            if (!empty($val)) {
                                                fputcsv($file1, $val);
                                            }                       
                                        }
                                        $insertData[$key]['filename'] = $orgName;
                                        $insertData[$key]['status'] = 0;
                                        $insertData[$key]['type'] = "location";
                                        fclose($file1);
                                    }
                                    ImportFile::insert($insertData);
                                }
                            }
                            if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                                unlink($path.'/' . $file->getFilename());
                            }
                        }else{
                            $zip = Zip::open($file);
                            $zip->extract($path);
                            $zip->close();
                            if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                                unlink($path.'/' . $file->getFilename());
                            }
                            $from_path = $path.'/'.rtrim($file->getFilename(), ".zip"); 
                            $files = \File::allFiles($from_path);
                            if (!empty($files)) {
                                $uniqueId = 0;
                                foreach ($files as $key => $file) {
                                    if ($file != null) {
                                        if ($file->getExtension() == 'csv') {
                                            $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                            if( in_array("products" ,$filenameArray))
                                            {
                                                $file_name = 'products';
                                            }else if( in_array("locations" ,$filenameArray))
                                            {
                                                $file_name = 'locations';
                                            }else{
                                                $file_name = '';
                                            }
                                            if ($file_name == 'products') {
                                                $file1 = fopen($file->getRealPath(), "r");
                                                $completeSheetData = array();
                                                while (!feof($file1)) {
                                                    $completeSheetData[] = fgetcsv($file1);
                                                }
                                                fclose($file1);
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
                                                        }, 5);
                                                    }
                                                }
                                                
                                                $Allparts = (array_chunk($data, 500));

                                                $insertData = array();
                                                foreach ($Allparts as $key => $parts) {

                                                    $uniqueId = Helper::generateNumber('import_files', 'id');
                                                    $orgName = time() . $key . $uniqueId . ".csv";
                                                    $fileName = public_path("pendingfile/" . $orgName);
                                                    $file1 = fopen($fileName, "w");
                                                    fputcsv($file1, $heading);

                                                    foreach ($parts as $key1 => $val) {
                                                        if (!empty($val)) {
                                                            fputcsv($file1, $val);
                                                        }                       
                                                    }
                                                    $insertData[$key]['filename'] = $orgName;
                                                    $insertData[$key]['status'] = 0;
                                                    $insertData[$key]['type'] = "product";
                                                    fclose($file1);
                                                }
                                                ImportFile::insert($insertData);
                                                
                                            }else{
                                                if ($file_name == 'locations') {
                                                    $file1 = fopen($file->getRealPath(), "r");
                                                    $completeSheetData = array();
                                                    while (!feof($file1)) {
                                                        $completeSheetData[] = fgetcsv($file1);
                                                    }

                                                    fclose($file1);

                                                    $heading = $completeSheetData[0];

                                                    $data = array_slice($completeSheetData, 1);

                                                    $Allparts = (array_chunk($data, 500));



                                                    $insertData = array();
                                                    foreach ($Allparts as $key => $parts) {

                                                        $uniqueId = Helper::generateNumber('import_files', 'id');
                                                        $orgName = time() . $key . $uniqueId . ".csv";
                                                        $fileName = public_path("pendingfile/" . $orgName);
                                                        $file1 = fopen($fileName, "w");
                                                        fputcsv($file1, $heading);

                                                        foreach ($parts as $key1 => $val) {
                                                            if (!empty($val)) {
                                                                fputcsv($file1, $val);
                                                            }                       
                                                        }
                                                        $insertData[$key]['filename'] = $orgName;
                                                        $insertData[$key]['status'] = 0;
                                                        $insertData[$key]['type'] = "location";
                                                        fclose($file1);
                                                    }
                                                    ImportFile::insert($insertData);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            \File::deleteDirectory($from_path);
                        }
                        
                    }
                }
            }else{
                /*if (!ImportFile::whereNull('start_date')->first()) {
                    ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
                }*/
                $locationsFile = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->first();
                if (!empty($locationsFile)) {
                    if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                        $file = public_path("pendingfile/" . $locationsFile->filename);
                        ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

                        Excel::import(new DispensaryImport, $file);
                        ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            unlink(public_path('pendingfile/' . $locationsFile->filename));
                            ImportFile::where("id",$locationsFile->id)->delete();
                        }
                    }
                }else{
                    $productFile = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->first();
                    if (!empty($productFile)) {
                        if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
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
                //Product::where('product_out', 'Yes')->update(array('manage_stock' => 0));
              \Artisan::call('config:clear');
                //Log::info(['cron' => 'Cron End']);

                if (!ImportFile::whereNull('start_date')->first()) {
                    if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                        DB::transaction(function () {
                            Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                        }, 5);
                    } 
                }
                $plan_res = UserPlan::select('id', 'status')->where('plan_expire_time', '<' ,date('Y-m-d'))->where('status', 'active')->get();
                if (!empty($plan_res)) {
                    foreach ($plan_res as $key => $value) {
                        UserPlan::where('id', $value->id)->update(array('status' => 'old'));
                    }
                }
            }

            /*if (!ImportFile::whereNull('start_date')->first()) {
                ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
            }*/
            $locationsFiles = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->get();
            if (!empty($locationsFiles)) {
                foreach ($locationsFiles as $key => $locationsFile) {
                    if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                        $file = public_path("pendingfile/" . $locationsFile->filename);
                        ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);

                        Excel::import(new DispensaryImport, $file);
                        ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);

                        if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            unlink(public_path('pendingfile/' . $locationsFile->filename));
                            ImportFile::where("id",$locationsFile->id)->delete();
                        }
                    }
                }
            }
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
                            ImportFile::where("id",$productFile->id)->delete();
                        }
                    }
                }
            }
            
            //Product::where('product_out', 'Yes')->update(array('manage_stock' => 0));
           \Artisan::call('config:clear');
            //Log::info(['cron' => 'Cron End']);

            if (!ImportFile::whereNull('start_date')->first()) {
                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                    DB::transaction(function () {
                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                    }, 5);
                } 
            }
        } catch (\Exception $ex) {
            
        }
    }
    public function cronJob1(Request $request) {
        try {
           /* $current_time = date('Y-m-d H:i:s');
            $pushs = Push::where('is_send', 0)->get();

            if (!empty($pushs)) {
                foreach ($pushs as $key => $push) {

                    foreach ($push->push_user as $key => $push_user){
                        $user = User::select('id', 'notification', 'email_alert')->where('id', $push_user->user_id)->first();
                        
                        if (!empty($user)) {
                            if ($user->notification == 'Yes') {
                                $this->pushNotificationSend($user, $push);
                            }
                        }
                    }
                    $push->is_send = 1;
                    $push->save();
                }
            }*/
            
                   //Log::info(['cron' => 'Cron Start']);
        ini_set('max_execution_time', 0);
       
        $path = rtrim(base_path(), "/public_html/appAdmin").'/daily_updates';
       // dd($path);
       // $path = public_path('uploads/daily_updates');
       // echo '<pre>'; print_r($path); die;
        //$files = \File::allFiles('uploads/daily_updates');
        $files = \File::allFiles($path);
        if (!empty($files)) {
            $uniqueId = 0;
            foreach ($files as $key => $file) {
                if ($file != null) {
                    if ($file->getExtension() == 'csv') {
                        $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                        if( in_array("products" ,$filenameArray))
                        {
                            $file_name = 'products';
                        }else if( in_array("locations" ,$filenameArray))
                        {
                            $file_name = 'locations';
                        }else{
                            $file_name = '';
                        }
                        if ($file_name == 'products') {
                            $file1 = fopen($file->getRealPath(), "r");
                            $completeSheetData = array();
                            while (!feof($file1)) {
                                $completeSheetData[] = fgetcsv($file1);
                            }
                            fclose($file1);
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
                                    }, 5);
                                }
                            }
                            
                            $Allparts = (array_chunk($data, 500));

                            $insertData = array();
                            foreach ($Allparts as $key => $parts) {

                                $uniqueId = Helper::generateNumber('import_files', 'id');
                                $orgName = time() . $key . $uniqueId . ".csv";
                                $fileName = public_path("pendingfile/" . $orgName);
                                $file1 = fopen($fileName, "w");
                                fputcsv($file1, $heading);

                                foreach ($parts as $key1 => $val) {
                                    if (!empty($val)) {
                                        fputcsv($file1, $val);
                                    }                       
                                }
                                $insertData[$key]['filename'] = $orgName;
                                $insertData[$key]['status'] = 0;
                                $insertData[$key]['type'] = "product";
                                fclose($file1);
                            }
                            ImportFile::insert($insertData);
                            
                        }else{
                            if ($file_name == 'locations') {
                                $file1 = fopen($file->getRealPath(), "r");
                                $completeSheetData = array();
                                while (!feof($file1)) {
                                    $completeSheetData[] = fgetcsv($file1);
                                }

                                fclose($file1);

                                $heading = $completeSheetData[0];

                                $data = array_slice($completeSheetData, 1);

                                $Allparts = (array_chunk($data, 500));



                                $insertData = array();
                                foreach ($Allparts as $key => $parts) {

                                    $uniqueId = Helper::generateNumber('import_files', 'id');
                                    $orgName = time() . $key . $uniqueId . ".csv";
                                    $fileName = public_path("pendingfile/" . $orgName);
                                    $file1 = fopen($fileName, "w");
                                    fputcsv($file1, $heading);

                                    foreach ($parts as $key1 => $val) {
                                        if (!empty($val)) {
                                            fputcsv($file1, $val);
                                        }                       
                                    }
                                    $insertData[$key]['filename'] = $orgName;
                                    $insertData[$key]['status'] = 0;
                                    $insertData[$key]['type'] = "location";
                                    fclose($file1);
                                }
                                ImportFile::insert($insertData);
                            }
                        }
                        if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                            unlink($path.'/' . $file->getFilename());
                        }
                    }else{
                        $zip = Zip::open($file);
                        $zip->extract($path);
                        $zip->close();
                        if ($file->getFilename() != null && file_exists($path.'/' . $file->getFilename())) {
                            unlink($path.'/' . $file->getFilename());
                        }
                        $from_path = $path.'/'.rtrim($file->getFilename(), ".zip"); 
                        $files = \File::allFiles($from_path);
                        if (!empty($files)) {
                            $uniqueId = 0;
                            foreach ($files as $key => $file) {
                                if ($file != null) {
                                    if ($file->getExtension() == 'csv') {
                                        $filenameArray = explode("_",substr($file->getFilename(), 0, strrpos($file->getFilename(), ".")));
                                        if( in_array("products" ,$filenameArray))
                                        {
                                            $file_name = 'products';
                                        }else if( in_array("locations" ,$filenameArray))
                                        {
                                            $file_name = 'locations';
                                        }else{
                                            $file_name = '';
                                        }
                                        if ($file_name == 'products') {
                                            $file1 = fopen($file->getRealPath(), "r");
                                            $completeSheetData = array();
                                            while (!feof($file1)) {
                                                $completeSheetData[] = fgetcsv($file1);
                                            }
                                            fclose($file1);
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
                                                    }, 5);
                                                }
                                            }
                                            
                                            $Allparts = (array_chunk($data, 500));

                                            $insertData = array();
                                            foreach ($Allparts as $key => $parts) {

                                                $uniqueId = Helper::generateNumber('import_files', 'id');
                                                $orgName = time() . $key . $uniqueId . ".csv";
                                                $fileName = public_path("pendingfile/" . $orgName);
                                                $file1 = fopen($fileName, "w");
                                                fputcsv($file1, $heading);

                                                foreach ($parts as $key1 => $val) {
                                                    if (!empty($val)) {
                                                        fputcsv($file1, $val);
                                                    }                       
                                                }
                                                $insertData[$key]['filename'] = $orgName;
                                                $insertData[$key]['status'] = 0;
                                                $insertData[$key]['type'] = "product";
                                                fclose($file1);
                                            }
                                            ImportFile::insert($insertData);
                                            
                                        }else{
                                            if ($file_name == 'locations') {
                                                $file1 = fopen($file->getRealPath(), "r");
                                                $completeSheetData = array();
                                                while (!feof($file1)) {
                                                    $completeSheetData[] = fgetcsv($file1);
                                                }

                                                fclose($file1);

                                                $heading = $completeSheetData[0];

                                                $data = array_slice($completeSheetData, 1);

                                                $Allparts = (array_chunk($data, 500));



                                                $insertData = array();
                                                foreach ($Allparts as $key => $parts) {

                                                    $uniqueId = Helper::generateNumber('import_files', 'id');
                                                    $orgName = time() . $key . $uniqueId . ".csv";
                                                    $fileName = public_path("pendingfile/" . $orgName);
                                                    $file1 = fopen($fileName, "w");
                                                    fputcsv($file1, $heading);

                                                    foreach ($parts as $key1 => $val) {
                                                        if (!empty($val)) {
                                                            fputcsv($file1, $val);
                                                        }                       
                                                    }
                                                    $insertData[$key]['filename'] = $orgName;
                                                    $insertData[$key]['status'] = 0;
                                                    $insertData[$key]['type'] = "location";
                                                    fclose($file1);
                                                }
                                                ImportFile::insert($insertData);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        \File::deleteDirectory($from_path);
                    }
                    
                }
            }
        }else{
            /*if (!ImportFile::whereNull('start_date')->first()) {
                ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
            }*/
            $locationsFile = ImportFile::where("status", 0)->whereNull('start_date')->where("type","location")->first();
            if (!empty($locationsFile)) {
                    if (file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                        $file = public_path("pendingfile/" . $locationsFile->filename);
                        ImportFile::where("id",$locationsFile->id)->update(["start_date"=>date("Y-m-d H:i:s")]);
    
                        Excel::import(new DispensaryImport, $file);
                        ImportFile::where("id",$locationsFile->id)->update(['status'=>1,"end_date"=>date("Y-m-d H:i:s")]);
    
                        if ($locationsFile->filename != null && file_exists(public_path('pendingfile/' . $locationsFile->filename))) {
                            unlink(public_path('pendingfile/' . $locationsFile->filename));
                            ImportFile::where("id",$locationsFile->id)->delete();
                        }
                    }
                

            }else{
                $productFile = ImportFile::where("status", 0)->whereNull('start_date')->where("type","product")->first();
                if (!empty($productFile)) {
                    if (file_exists(public_path('pendingfile/' . $productFile->filename))) {
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
            //Product::where('product_out', 'Yes')->update(array('manage_stock' => 0));
           \Artisan::call('config:clear');
            //Log::info(['cron' => 'Cron End']);

            if (!ImportFile::whereNull('start_date')->first()) {
                if ($product = Product::where('status', '!=', 'delete')->where('update_stock', 'Yes')->first()) {
                    DB::transaction(function () {
                        Product::where('update_stock', 'Yes')->where('status', '!=', 'delete')->update(array('manage_stock' => 0, 'update_stock' => 'No'));
                    }, 5);
                }
                
            }
            $plan_res = UserPlan::select('id', 'status')->where('plan_expire_time', '<' ,date('Y-m-d'))->where('status', 'active')->get();
            if (!empty($plan_res)) {
                foreach ($plan_res as $key => $value) {
                    UserPlan::where('id', $value->id)->update(array('status' => 'old'));
                }
            }
        }
        } catch (\Exception $ex) {
            
        }
    }
    public function getMaintenance(Request $request, $slug)
    {
        return view('welcome'); die;
    }
    public function getPage(Request $request, $slug)
    {
        $cms = CMS::where('status', 'active')->where('slug', $slug)->first();
        if ($cms) {
            return view('frontend.pages.page', compact('cms'));
        }else{
            return view('error.404', compact('cms'));
        } 
    }
    public function pushNotificationSend($user, $push) {
        try
        {
            $notification=new Notificationuser();
            $notification->sender_id = 1;
            $notification->receiver_id = $user->id;
            if (isset($push['notification_type']) && !empty($push['notification_type'])) {
                $notification->notification_type = $push['notification_type'];
            }else{
                $notification->notification_type = 'admin';
            }
            $notification->title = $push['title'];
            $notification->description = $push['description'];
            $notification->status = 'active';
            $notification->save();
            
                $sound = true;
            
                $alert = true;
            
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
                        $this->sendIosNotification($apptoken, $headtitle, $extramessage, $sound, $alert);
                    }
                }
            }
            return [];
        } catch (\Exception $ex) {
            dd($ex);
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
