<?php

namespace App\Exports;

use App\Product;
use App\Dispensary;
use App\Category;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
// use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    public function __construct(array $id, $columns)
    {   
        $this->id = $id;
        $this->columns = $columns;
        $product =  ['id', 'location_id', 'category_website', 'type', 'strain_website', 'product_name','product_description', 'featured', 'stock', 'Status', 'price_original', 'qty'];

       // $this->headings = array_merge($product,$this->columns->toArray());
        $this->headings = $product;
    }

    // public function __construct(array $id)
    // {   
    //     $this->id = $id;
    // }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection() 
    {
        if($this->id[0] <> 'all') {
            return Product::with('category')->whereIn('products.id',$this->id)->select('products.*')->get();
        }else{
            return Product::with('category')->select('*')->get();
        }
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @var Personne with risks
     * @return array
     */
    public function map($product): array
    {
        $list = [ 
            'id' => $product->id,
            'location_id' => empty($product->dispensary) ? '-' : $product->dispensary->name,
            'category_website' => ($product->category != null) ? $product->category->name : '-',
            'type' => ($product->type != null) ? $product->type->name : '-',
            'strain_website' => ($product->strain != null) ? $product->strain->name : '-',
            'product_name' => $product->name,
            'product_description' => $product->description,
            'featured' => $product->is_featured == '1' ? 'Yes':'No',
            'stock' => $product->manage_stock == '1' ? 'Yes':'No',
            'status' => $product->status == 'active' ? 'Active':'Inactive',
            'price_original' => $product->price,
            'qty' => $product->qty,
        ];
        return $list;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $rowCount = $event->getSheet()->getDelegate()->getHighestRow();
                $protection = $event->getSheet()->getDelegate()->getProtection();
                $protection->setPassword('lockUpdate');
                $event->sheet->getStyle('I2:Z'.$rowCount)->getProtection()->setLocked(Protection::PROTECTION_UNPROTECTED);
                $protection->setSheet(true);
            },
        ];
    }
}
