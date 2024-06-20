<?php

namespace App\Console\Commands;

use App\Helper\ProductHelper;
use App\product;
use Illuminate\Console\Command;

class CalculatePopularProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'popular:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        //
        $product = product::all();

        foreach ($product as $c) {

            $denominator = 1000;

            $view_ratio = ($c->view_count / $denominator);

            $helper = new ProductHelper();
            $avg_rating = $helper->productRating($c->id);

            $sum = $avg_rating + $view_ratio;
            $c->popular_result = $sum;
            $c->save();
        }
    }
}
