<?php

namespace App\Http\Controllers\API;

use App\ContactTrecking;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatisticsApi extends Controller
{
    //

    public function storeContactRecords(Request $request)
    {

        $ip_address = $request->ip();



        if ($request->user_id && is_int($request->user_id)) {

            $record = ContactTrecking::where('user_id', $request->user_id)
                ->where('product_id', $request->product_id)
                ->first();
            if ($record) {
                $record->number_of_times = ($record->number_of_times + 1);
                $record->save();
            } else {
                $record = new ContactTrecking();
                $record->number_of_times = 1;
                $record->user_id = $request->user_id;
                $record->product_id = $request->product_id;
                $record->ip__address = $ip_address;
                $record->save();
            }




            return response()->json(['result' => true]);
        }



        $record = ContactTrecking::where('ip__address', $ip_address)
            ->where('product_id', $request->product_id)
            ->first();
        if ($record) {
            $record->number_of_times = ($record->number_of_times + 1);
            $record->save();
        } else {
            $record = new ContactTrecking();
            $record->number_of_times = 1;
            // $record->user_id = $request->user_id;
            $record->product_id = $request->product_id;
            $record->ip__address = $ip_address;

            $record->save();
        }

        return response()->json(['result' => true]);
    }
}
