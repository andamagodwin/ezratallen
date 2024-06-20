<?php

namespace App\Jobs;

use App\product;
use App\ProductGallery;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Intervention\Image\Facades\Image;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use PHPUnit\Util\Json;

class compressImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //

        $product = product::all();




        //return "fine";

        ini_set('max_execution_time', 0);

        foreach ($product as $c) {

       

            try {

                $path = public_path("storage/product/" . $c->picture);



                if (File::exists($path)) {

                    //return "mundruku";

                    $fileName = md5(microtime()) . '_product.' . 'webp';

                    $image_path =  public_path("storage/cov/" . $fileName);

                    $image =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image->encode('webp');
                    //$image->resize(650, null);

                    $image->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($image_path);



                    $image_path =  public_path("storage/prod/" . $fileName);

                    $image_main =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    $image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image_main->save($image_path);


                    $c->picture = $fileName;
                    $c->save();


                    //get all the image galleries for the product and resize it 
                    $banner = ProductGallery::where('product_id', $c->id)->get();

                    foreach ($banner as $b) {

                        $path = public_path("storage/product/" . $b->image_path);

                        if (File::exists($path)) {


                            $fileName = md5(microtime()) . '_product_gallery.' . 'webp';

                            $image_path =  public_path("storage/prod/" . $fileName);


                            $image_main =  Image::make($path);
                            //encode the image 
                            $image_main->encode('webp');
                            //$image->resize(650, null);

                            $image_main->resize(780, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                            $image_main->save($image_path);
                            $b->image_path = $fileName;
                            $b->save();
                        }
                    }
                }

            } catch (Exception $e) {


           

                //return response()->json(['error'=>$e]);



                Log::info($e);

                // return response()->json(['error' => $e]);

                $path = public_path("storage/product/" . $c->picture);

                if (File::exists($path)) {

                    $fileName = md5(microtime()) . '_product.' . $path->getClientOriginalExtension();

                    $image_path =  public_path("storage/cov/" . $fileName);

                    $image =  Image::make($path);
                    //resize the image 


                    //encode the image 
                    // $image->encode('webp');
                    //$image->resize(650, null);

                    $image->resize(300, 300, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image->save($image_path);



                    $image_path =  public_path("storage/prod/" . $fileName);

                    $image_main =  Image::make($path);
                    //resize the image 




                    //encode the image 
                    //$image_main->encode('webp');
                    //$image->resize(650, null);

                    $image_main->resize(780, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $image_main->save($image_path);


                    $c->picture = $fileName;
                    $c->save();


                    //get all the image galleries for the product and resize it 
                    $banner = ProductGallery::where('product_id', $c->id)->get();

                    foreach ($banner as $b) {

                        $path = public_path("storage/product/" . $b->image_path);


                        if (File::exists($path)) {

                            $fileName = md5(microtime()) . '_product.' . $path->getClientOriginalExtension();

                            $image_path =  public_path("storage/prod/" . $fileName);


                            $image_main =  Image::make($path);
                            //encode the image 
                            //$image_main->encode('webp');
                            //$image->resize(650, null);

                            $image_main->resize(780, null, function ($constraint) {
                                $constraint->aspectRatio();
                                $constraint->upsize();
                            });
                            $image_main->save($image_path);
                            $b->image_path = $fileName;
                            $b->save();
                        }
                    }
                }
            }
        }


        Log::info("Execution  finished ");
    }
}
