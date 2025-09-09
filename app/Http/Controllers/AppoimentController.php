<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appoiment;

class AppoimentController extends Controller {
    public function saveAppoiment(Request $request) {
        try {
            dd($request->all());
            $appoiment = Appoiment::create([
                'customer_id'         => $request->customer_id,
                'appoiment_status_id' => 1,
                'date'                => $request->date,
                'horary'              => $request->horary,
                'cost'                => 100,
                'created_by'          => auth()->user()->id
            ]);
        } catch (\Throwable $th) {
            return Response::response('Lo sentimos ocurrio un error.<br>Si el problema persiste contacta a soporte.', 'Ocurrio un error '.$th->getMessage(), true, 500);
        }
    }
}
