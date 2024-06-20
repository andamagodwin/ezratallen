<?php

namespace App\Http\Controllers\API;

use App\FarmsellAgent as AppFarmsellAgent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\saveAgent;

class FarmsellAgent extends Controller
{
    //
    public function save(saveAgent $request)
    {

        return $request->save();
    }


    public function getApplications()
    {

        $application = AppFarmsellAgent::all();


        return response()->json(['result' => $application]);
    }
}
