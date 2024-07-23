<?php

namespace App\Imports;

use Session;
use Request;
use App\Library\Helper;
use App\Product;
use App\Model\CollectionVariation;
use App\Model\Collection as ItemCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class ProductUpdate implements ToCollection,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection  $rows)
    {
        if ($rows->count()) {
            $count = 0;
            $error = 0;
            if(isset($rows[0])){
                foreach ($rows as $key => $value) {
                    $arr_value = [];
                    if(!empty($value['id']) && !empty($value['location_id']) && !empty($value['category_website']) && !empty($value['type']) && !empty($value['strain_website'])  && !empty($value['product_name'])  && !empty($value['product_description']) && !empty($value['featured']) && !empty($value['stock']) && !empty($value['price_original']) && !empty($value['qty'])) 
                    {
                        //save product
                        $prod = Product::find($value['id']);
                        if (isset($prod->id)) {
                            $prod->qty                  =  $value['qty'];
                            $prod->price                =  $value['price_original'];
                            if($prod->save()){
                                $count++;
                            }
                        }else{
                            Request::session()->flash('error', 'Product with name at line ' . $count++ . ' doesn"t exists');
                            return redirect()->back();
                        }
                    }else{
                        Request::session()->flash('error', 'Blank data or unable to read data from file.');
                        return redirect()->back();
                    }
                }
                if ($count > 0) {
                    Request::session()->flash('success', 'Data imported successfully');
                    return redirect()->back();
                } else {
                    Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
                    return redirect()->back();
                }
            }else{
                Request::session()->flash('error', 'Unable to import complete data. Please verify and import again.');
                return redirect()->back();
            }
        }
    }
}