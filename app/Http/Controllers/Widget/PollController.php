<?php

namespace App\Http\Controllers\Widget;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Poll;
use App\Models\PollResponse;

class PollController extends Controller
{
    public function polling(Request $request){
        $this->validate($request,[
            'uuid'=>'required',
        ]);

        return Inertia::render('Widget/Polling',[
           'poll'=>Poll::where('uuid',$request->uuid)->first()
        ]);
    }
    public function vote(Request $request){
        $this->validate($request,[
            'poll_id'=>'required',
            'username'=>'required',
            'answer'=>'required',
        ]);
        PollResponse::updateOrCreate(
            ['poll_id'=>$request->poll_id,'ip_address'=>$request->ip(),'username'=>$request->username],
            ['answer'=>$request->answer]
        );
        //PollResponse::create($data);
        return response()->json($request->all());
    }


}
