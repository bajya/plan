<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Library\Helper;
use App\Library\Notify;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ImportFile;
use App\CustomLog;
use App\State;
use App\Brand;
use App\Dispensary;
use App\Product;
use App\User;
use App\UserPlan;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductImport;
use App\Imports\DispensaryImport;
use Illuminate\Support\Arr;
use Log;
use Zip;

class MinuteCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:minute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Log::info(['cron' => 'Cron Start']);
        ini_set('max_execution_time', 0);
       
        $path = rtrim(base_path(), "/public_html/appAdmin").'/daily_updates';
        Log::info(['cron' => $path]);
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
                            
                            $Allparts = (array_chunk($data, 200));

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
                                            
                                            $Allparts = (array_chunk($data, 200));

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
            if (!ImportFile::whereNull('start_date')->first()) {
                ImportFile::whereNotNull('start_date')->whereNull('end_date')->update(array('start_date' => NULL));
            }
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
            \Artisan::call('cache:clear');
            //Log::info(['cron' => 'Cron End']);

            if (!ImportFile::first()) {
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
    }
}
