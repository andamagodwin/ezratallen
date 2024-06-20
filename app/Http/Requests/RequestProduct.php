<?php

namespace App\Http\Requests;

use App\ProductRequest;
use Illuminate\Foundation\Http\FormRequest;
use Image;

class RequestProduct extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'user_id'=>'required',
            'category_id'=>'required',
            'name'=>'required',
            'description'=>'required',
            'quantity'=>'required',
            'unit'=>'required',
            'picture'=>'required'
        ];
    }

    /**
     * product request submission implementation
     */

    public function save(){

    try {
        //code...saving product request submission
        $picture_url=$this->saveImage($this->picture);
          
        $product_request=ProductRequest::create([
            'user_id'=>$this->user_id,
            'category_id'=>$this->category_id,
            'name'=>$this->name,
            'description'=>$this->description,
            'quantity'=>$this->quantity,
            'unit'=>$this->unit,
            'picture'=>$picture_url
        ]);

        return response()->json(['status'=>true, 'product'=>$product_request]);

    } catch (\Throwable $th) {
        //throw $th;
         // Anything that went wrong
        abort(500, 'Could not create Product Request submission');
    }

    }


    public function saveImage($picture){
        $fileName = md5(microtime()) . '_product_request.' . $picture->getClientOriginalExtension();

        $image_path =  public_path("storage/productRequest/" . $fileName);

        Image::make($picture)->save($image_path);
        
        $path = 'productRequest/' . $fileName;
        return $path;
    }
}
