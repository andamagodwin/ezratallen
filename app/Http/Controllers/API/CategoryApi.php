<?php

namespace App\Http\Controllers\API;

use App\BannerGallary;
use App\category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\product;
use App\subCategory;
use App\User;
use Illuminate\Support\Facades\DB;

class CategoryApi extends Controller
{



    //get one category picture for each of the category


    public function getBanner()
    {


        $arrayId = [];
        $data = [];

        $banners = BannerGallary::all();


        foreach ($banners as $c) {

            $status_id = in_array($c->category_id, $arrayId);

            if ($status_id) {
            } else {
                array_push($arrayId, $c->category_id);
                array_push($data, $c);
            }
        }

        return response()->json(['images' => $data]);
    }

    public function get_categories_with_count(Request $request, $id)
    {




        //return $request;

        $banner = BannerGallary::where('category_id', $id)->get();

        if ($request->sub_id && $request->sub_id != 'undefined') {



            //status 1 is for popular product
            if ($request->status == 1) {

                $category_product = product::where('sub_category_id', $request->sub_id)
                    ->where('is_approved', 1)
                    ->orderBy('view_count', 'Desc')->paginate(12);

                return response()->json([
                    'product' => $category_product,
                    'message' => true,
                    'banner' => $banner

                ]);
            }
            //status 2 is for New product
            if ($request->status == 2) {

                $category_product = product::where('sub_category_id', $request->sub_id)
                    ->where('is_approved', 1)
                    ->orderBy('created_at', 'Desc')->paginate(12);

                return response()->json([
                    'product' => $category_product,
                    'message' => true,
                    'banner' => $banner
                ]);
            }


            //status 3 is for sorting price in descending
            if ($request->status == 3) {

                $category_product = product::where('sub_category_id', $request->sub_id)
                    ->where('is_approved', 1)
                    ->orderBy('price', 'Desc')->paginate(12);

                return response()->json([
                    'product' => $category_product,
                    'message' => 'Price',
                    'banner' => $banner
                ]);
            }

            //status 4 is for sorting price in Ascending order
            if ($request->status == 4) {

                $category_product = product::where('sub_category_id', $request->sub_id)
                    ->where('is_approved', 1)
                    ->orderBy('price')->paginate(12);

                return response()->json([
                    'product' => $category_product,
                    'message' => 'Price',
                    'banner' => $banner
                ]);
            }

            $category = category::all();
            $category_array = [];

            $category_product = product::where('sub_category_id', $request->sub_id)->paginate(12);


            foreach ($category as $category_data) {

                $product_count = product::where('category_id', $id)->count();

                $category_data->product_count = $product_count;

                array_push($category_array, $category_data);
            }

            //return first status incase available
            if ($request->status_first) {

                $category_product = product::where('sub_category_id', $request->sub_id)
                    ->where('is_approved', 1)
                    ->orderBy('price')->paginate(12);

                return response()->json([
                    'categories' => $category_array,
                    'product' => $category_product,
                    'message' => 'Price',
                    'banner' => $banner
                ]);
            }

            return response()->json([
                'categories' => $category_array,
                'product' => $category_product,
                'message' => true,
                'banner' => $banner
            ]);
        }











        //filtering according to category depending on the param sent

        //status 1 is for popular product
        if ($request->status == 1) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('view_count', 'Desc')->paginate(12);

            return response()->json([
                'product' => $category_product,
                'message' => true,
                'banner' => $banner

            ]);
        }
        //status 2 is for New product
        if ($request->status == 2) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('created_at', 'Desc')->paginate(12);

            return response()->json([
                'product' => $category_product,
                'message' => true,
                'banner' => $banner
            ]);
        }


        //status 3 is for sorting price in descending
        if ($request->status == 3) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price', 'Desc')->paginate(12);

            return response()->json([
                'product' => $category_product,
                'message' => 'Price',
                'banner' => $banner
            ]);
        }

        //status 4 is for sorting price in Ascending order
        if ($request->status == 4) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price')->paginate(12);

            return response()->json([
                'product' => $category_product,
                'message' => 'Price',
                'banner' => $banner
            ]);
        }

        $category = category::all();
        $category_array = [];

        $category_product = product::where('category_id', $id)
            ->where('is_approved', 1)
            ->paginate(12);


        foreach ($category as $category_data) {

            $product_count = product::where('category_id', $category_data->id)
                ->where('is_approved', 1)
                ->count();

            $category_data->product_count = $product_count;

            array_push($category_array, $category_data);
        }

        //return first status incase available
        if ($request->status_first) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price')->paginate(12);

            return response()->json([
                'categories' => $category_array,
                'product' => $category_product,
                'message' => 'Price',
                'banner' => $banner
            ]);
        }

        return response()->json([
            'categories' => $category_array,
            'product' => $category_product,
            'message' => true,
            'banner' => $banner
        ]);
    }


    public function categoryPaginate($id)
    {

        $category_product = product::where('category_id', $id)
            ->where('is_approved', 1)
            ->orderBy('view_count', 'Desc')->paginate(10);
        return response()->json([
            'category_product' => $category_product
        ]);
    }


    public function get_category_for_mobile(Request $request, $id)
    {



        $banner = BannerGallary::where('category_id', $id)->get();
        //Popular filtering 

        if ($request->status == 1) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('view_count', 'Desc')->paginate(10);
            return response()->json([
                'category_product' => $category_product
            ]);
        }

        //Recent filtering filtering 

        if ($request->status == 2) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('created_at', 'Desc')->paginate(6);
            return response()->json([
                'category_product' => $category_product
            ]);
        }

        //High price  filtering filtering 

        if ($request->status == 3) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price', 'Desc')
                ->paginate(6);

            return response()->json([
                'category_product' => $category_product
            ]);
        }

        //Low price  filtering filtering 

        if ($request->status == 4) {

            $category_product = product::where('category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price')->paginate(6);
            return response()->json([
                'category_product' => $category_product
            ]);
        }

        $product_count = product::where('category_id', $id)->where('is_approved', 1)->count();
        $sub_category = subCategory::where('category_id', $id)->get();
        $category_product = product::where('category_id', $id)->where('is_approved', 1)->paginate(6);

        return response()->json([
            'count' => $product_count,
            'sub_category' => $sub_category,
            'category_product' => $category_product,
            'banner' => $banner
        ]);
    }



    public function get_subcategory_for_mobile(Request $request, $id)
    {

        //Popular filtering 

        if ($request->status == 1) {

            $category_product = product::where('sub_category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('view_count', 'Desc')->paginate(6);
            return response()->json([
                'category_product' => $category_product,
                'status' => true
            ]);
        }

        //Recent filtering filtering 

        if ($request->status == 2) {

            $category_product = product::where('sub_category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('created_at', 'Desc')->paginate(6);
            return response()->json([
                'category_product' => $category_product
            ]);
        }

        //High price  filtering filtering 

        if ($request->status == 3) {

            $category_product = product::where('sub_category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price', 'Desc')
                ->paginate(6);

            return response()->json([
                'category_product' => $category_product
            ]);
        }

        //Low price  filtering filtering 

        if ($request->status == 4) {

            $category_product = product::where('sub_category_id', $id)
                ->where('is_approved', 1)
                ->orderBy('price')->paginate(6);
            return response()->json([
                'category_product' => $category_product
            ]);
        }

        $category_product = product::where('sub_category_id', $id)->where('is_approved', 1)->paginate(6);

        return response()->json([

            'category_product' => $category_product

        ]);
    }


    public function filterUserProduct(Request $request, $id)
    {



        //product::where('user_id', $id)->paginate(6);

        //status 1 is for popular product
        if ($request->status == 1) {

            $list = product::where('user_id', $id)->orderBy('view_count', 'Desc')->paginate(6);

            return response()->json(['list' => $list]);
        }
        //status 2 is for New product
        if ($request->status == 2) {

            $list = product::where('user_id', $id)->orderBy('created_at', 'Desc')->paginate(6);

            return response()->json(['list' => $list]);
        }


        //status 3 is for sorting price in descending
        if ($request->status == 3) {

            $list = product::where('user_id', $id)->orderBy('price', 'Desc')->paginate(6);
            return response()->json(['list' => $list]);
        }

        //status 4 is for sorting price in Ascending order
        if ($request->status == 4) {

            $list = product::where('user_id', $id)->orderBy('price')->paginate(6);

            return response()->json(['list' => $list]);
        }
    }


    public function get_sub_categories($id)
    {

        $category_product = product::where('sub_category_id', $id)
            ->where('is_approved', 1)
            ->orderBy('created_at', 'Desc')->paginate(15);
        return response()->json([
            'product' => $category_product,
            'status' => true
        ]);
    }

    public function sub_category_store($id)
    {
        
        DB::statement("SET SQL_MODE=''");
        
        $products = product::join('users', 'products.user_id', '=', 'users.id')
            ->where('sub_category_id', $id)->groupBy('products.user_id')
            ->select('users.id', 'users.full_name', 'users.email', 'users.created_at')
            ->paginate(15);

        foreach ($products as $product) {

            $product->prod = product::where('user_id', $product->id)
                ->where('sub_category_id', $id)->where('is_approved', 1)
                ->take(3)
                ->get();
        }

        return response()->json([
            'status' => true,
            'stores' => $products
        ]);
    }
}
