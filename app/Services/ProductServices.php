<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use Illuminate\Support\Facades\Session;

// Object Class
class productObj {
  public $product_id = '';
  public $product_name = '';
  public $product_sku = '';
  public $description = '';
  public $images = '';
  public $product_variant;
  public $product_variant_prices;
}

class ProductServices
{
  // Get products function========
  public function getProducts()
  {
    // Varient dropdown value from database
    $variants = $this->getVarient();
    // Products list from database
    $product_list = Product::with('prices')->with('variant')->paginate(5);
    $data['product_list'] = $product_list;
    $data['variants'] = $variants;
    return $data;
  }

  // Get Search products function========
  public function getSearchProducts($request)
  {
     //dd($request->all());
    // Varient dropdown value from database
    $variants = $this->getVarient();
    // Search Products list from database

      // Set parameters into session 
      if(isset($request->title)){
        Session::put('title', $request->title);
      }
      if(!isset($request->title) && !isset($request->page)){
        Session::put('title', '');
      }
      if(isset($request->date)){
        Session::put('date', $request->date);
      }
      if(!isset($request->date) && !isset($request->page)){
        Session::put('date', '');
      }
      if(isset($request->variant)){
        Session::put('variant', $request->variant);
      }
      if(!isset($request->variant) && !isset($request->page)){
        Session::put('variant', '');
      }
      if(isset($request->price_from)){
        Session::put('price_from', $request->price_from);
      }
      if(!isset($request->price_from) && !isset($request->page)){
        Session::put('price_from', '');
      }
      if(isset($request->price_to)){
        Session::put('price_to', $request->price_to);
      }
      if(!isset($request->price_to) && !isset($request->page)){
        Session::put('price_to', '');
      }

      $title = Session::get('title');
      $date = Session::get('date');
      $variant = Session::get('variant');
      $price_from = Session::get('price_from');
      $price_to = Session::get('price_to');


        $product_list = Product::query();
        if($title){
            $product_list->where('title','like','%'. $title.'%');
        }
        if($date){
            $product_list->where('created_at',$date);
        }
        $product_list =  $product_list->with(['prices' => function($query) use ($price_from,$price_to){
            if($price_from && $price_to){
                $query->whereBetween('price',[$price_from,$price_to]);
            }
        }]);
        $product_list =  $product_list->with(['variant' => function($query) use ($variant){
            if($variant){
                $query->where('id',$variant);
            }
        }]);
      $product_list =  $product_list->paginate(5);
      $data['product_list'] = $product_list;
      $data['variants'] = $variants;
      return $data;
  }

  // Store products function========
  public function storeProducts($request)
  {
    try{
        //DB::beginTransaction();
        //return $request->all();
        // Produc table data entry
        $product = new Product;
        $product->title = $request->title;
        $product->description = $request->description;
        $product->sku = $request->sku;
        $product->created_at = date('Y-m-d h:i:sa');
        $product->save();

        //product_variants table data entry
        $variants = $request->product_variant;
        foreach($variants as $variant){
            foreach($variant['tags'] as $val){
                $variant_data = new ProductVariant;
                $variant_data->variant = $val;
                $variant_data->variant_id =  $variant['option'];
                $variant_data->product_id = $product->id;
                $variant_data->created_at = date('Y-m-d h:i:sa');
                $variant_data->save();
            }
        }

        //product_variants price table data entry
        $product_variant_prices = $request->product_variant_prices;
        foreach($product_variant_prices as $variant_prices){
            // variant id prepare
            $array = explode('/',($variant_prices['title'] ?? ''));
            $id_one = ProductVariant::where('variant',$array[0] ?? '')->where('product_id',$product->id)->value('id');
            $id_two = ProductVariant::where('variant',$array[1] ?? '')->where('product_id',$product->id)->value('id');
            $id_three = ProductVariant::where('variant',$array[2] ?? '')->where('product_id',$product->id)->value('id');
            // Insert Data 
            $prices = new ProductVariantPrice;
            $prices->product_variant_one = $id_one;
            $prices->product_variant_two =  $id_two;
            $prices->product_variant_three = $id_three;
            $prices->price = $variant_prices['price'];
            $prices->stock = $variant_prices['stock'];
            $prices->product_id = $product->id;
            $prices->created_at = date('Y-m-d h:i:sa');
            $prices->save();
        }

        //Product Image Upload & save to database
        // $destination = '/image/product/'.$product->id.'/';
        // if(!File::exists($destination)){
        //     File::makeDirectory($destination, 0775, true);
        // }
        // $images = $request->file('product_image');
        // if ($request->hasFile('product_image')){
        //     foreach ($images as $item){
        //         $var = date_create();
        //         $time = date_format($var, 'YmdHis');
        //         $imageName = $time . '-' . $item->getClientOriginalName();
        //         $path = public_path() . $destination . $imageName;
        //         $item->move(public_path() . $destination, $imageName);
        //         $arr[] = $imageName;
        //     }
        // } 

        return response()->json(array('status' => 200, 'message' => 'Data Successfully Saved !'));

      }catch(\Exception $e){
          ///DB::rollback();
          return response()->json(array('status' => 401, 'message' => $e));
      }  
  }


  // Update products function========
  public function updateProduct($request)
  {
    try{
        //product id
        $id = $request->id;
        // Updated Produc table data
        $product = Product::find($id);
        $product->title = $request->title;
        $product->description = $request->description;
        $product->sku = $request->sku;
        $product->updated_at = date('Y-m-d h:i:sa');
        $product->save();

        //Updated product_variants table data
        $delete_product_variant = ProductVariant::where('product_id',$id)->delete();
        $variants = $request->product_variant;
        foreach($variants as $variant){
            foreach($variant['tags'] as $val){
                $variant_data = new ProductVariant;
                $variant_data->variant = $val;
                $variant_data->variant_id =  $variant['option'];
                $variant_data->product_id = $product->id;
                $variant_data->created_at = date('Y-m-d h:i:sa');
                $variant_data->save();
            }
        }

        //Updated product_variants price table data entry
        $delete_product_variant_prices = ProductVariantPrice::where('product_id',$id)->delete();
        $product_variant_prices = $request->product_variant_prices;
        foreach($product_variant_prices as $variant_prices){
            // variant id prepare
            $array = explode('/',($variant_prices['title'] ?? ''));
            $id_one = ProductVariant::where('variant',$array[0] ?? '')->where('product_id',$product->id)->value('id');
            $id_two = ProductVariant::where('variant',$array[1] ?? '')->where('product_id',$product->id)->value('id');
            $id_three = ProductVariant::where('variant',$array[2] ?? '')->where('product_id',$product->id)->value('id');
            // Insert Data 
            $prices = new ProductVariantPrice;
            $prices->product_variant_one = $id_one;
            $prices->product_variant_two =  $id_two;
            $prices->product_variant_three = $id_three;
            $prices->price = $variant_prices['price'];
            $prices->stock = $variant_prices['stock'];
            $prices->product_id = $product->id;
            $prices->created_at = date('Y-m-d h:i:sa');
            $prices->save();
        }
        return response()->json(array('status' => 200, 'message' => 'Data Successfully Updated !'));

      }catch(\Exception $e){
          return response()->json(array('status' => 401, 'message' => $e));
      }  
  }

  // Update products function========
  public function editProductData($product)
  {
    try{
        //product id
        $data = new productObj();
        $id = $product->id;
        $product_variant = [];
        $product = Product::with('prices')->with('variant')->where('id',$id)->first();
        $data->product_id = $id;
        $data->product_name = $product->title;
        $data->product_sku = $product->sku;
        $data->description = $product->description;
        $variant = $product->variant;
        $variant = $variant->groupBy('variant_id');
        foreach($variant as $key => $var){
        $varData = new productObj();
        $varData->option = $key;
        $tag = [];
            foreach($var as $v){
                array_push($tag,$v->variant);
            }
        $varData->tags = $tag;    
        array_push($product_variant,$varData); 
        }
        $data->product_variant = $product_variant;

        $product_variant_prices = [];
        $prices = $product->prices;
        foreach($prices as $price){
            $priceData = new productObj();
            $one = ProductVariant::where('id',$price->product_variant_one ?? '')->where('product_id',$id)->value('variant');
            $two = ProductVariant::where('id',$price->product_variant_two ?? '')->where('product_id',$id)->value('variant');
            $three = ProductVariant::where('id',$price->product_variant_three ?? '')->where('product_id',$id)->value('variant');
            $title = $one . '/' . $two . '/' . $three;
            $priceData->title = $title;
            $priceData->price = $price->price;
            $priceData->stock = $price->stock;
            array_push($product_variant_prices,$priceData);
        }
        $data->product_variant_prices = $product_variant_prices;
        $variants = Variant::all();
        $variants->allData = json_encode($data);
        $data->variantsData =  $variants;
        $allData  = json_encode($data);
        $data_all['allData'] = $allData;
        return $data_all;
      }catch(\Exception $e){
          return  $e;
      }  
  }


  //Get Varient fnction ==========
  public function getVarient(){
    $variant = ProductVariant::select('product_variants.*','variants.title as v_title')
        ->leftJoin('variants','variants.id','product_variants.variant_id')
        ->get();
    $variants = $variant->groupBy('variant_id');
    return $variants;
  }

}
