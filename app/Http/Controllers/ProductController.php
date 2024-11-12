<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\ProductStoreRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage; //php artisan storage:link

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all(); //All Product
        //$product = Product::paginate(5);
        
        //Return Json Response
        return response()->json([
            'status'=>true,
            'message'=>'Product Listed Succesfully',
            'products'=>$products
        ],200);
    }
    public function store(ProductStoreRequest $request)
    {
        try {
            $name=$request->name;
            $price= $request->price;
            $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
            
            Storage::disk('public')->put($imageName,file_get_contents($request->image));
            
            Product::create([
                'name'=>$name,
                'image'=>$imageName,
                'price'=>$price
            ]);
            //Return Json Response
            return response()->json([
                'results'=>"Product successfully created. '$name' -- '$imageName' -- '$price' "

            ],200);

        }catch (\Exception $e){
            //Return Json Response
            return response()->json([
                'message'=>'Somthing went really wrong!'
            ],500);
        }
    }

    public function show($id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message'=>'Product Not Found.'
            ],404);
        }
        return response()->json([
            'product'=>$product 
        ],200);
    }
    
    public function update(ProductStoreRequest $request,$id)
    {

        try {
            $product = Product::find($id);
            if(!$product){
                return response()->json([
                    'message'=>'Product Not Found.'
                ],404);
            }

            echo "request :$request->image";
            $product->name = $request->name;
            $product->price = $request->price;

            if($request->image){

                $storage = Storage::disk('public');

                //delete the old image 
                if($storage->exists($product->image))
                $storage->delete($product->image);
                
                //Image Name
                $imageName = Str::random(32) . "." . $request->image->getClientOriginalExtension();
                $product->image = $imageName;

                //Image save in public folder
                $storage->put($imageName,file_get_contents($request->image));

            }

            //Update Product
            $product->save();


            return response()->json([
                'message'=>'Product Successfully updated.'
            ],200);
        }catch (\Exception $e){
            return response()->json([
                'message'=>'Somthing went wrong!'
            ],500);
        }
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if(!$product){
            return response()->json([
                'message'=>'Product Not Found.'
            ],404);
        }
    
    //public storage
    $storage = Storage::disk('public');

    //Image delete
    if($storage->exists($product->image))
        $storage->delete($product->image);

    //Delete Product 
    $product->delete();

    
    
        return response()->json([
            'message'=>"Product Successfully Deleted."
        ],200);
    }
}
