<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Approbate;
use App\Models\Portfolio;
use App\Models\Member;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Member/Profile', [
            'member' => auth()->user()->member,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $member = Member::find($id);
        if($request->file('avatar')){
            // if($member->avatar!=null){
            //     if(Storage::exists($member->avatar)){
            //         Storage::delete($member->avatar);
            //     }
            // }
            $file = $request->file('avatar');
            $fileName=$member->id.'_'.time().'.png';
            $file->move(public_path('avatars'), $fileName);
            // $path = Storage::putFile('public/images/avatars', $file);
            $data['avatar']='/avatars/'.$fileName;
        }
        //dd($data);
        $member->update($data);
        $member->organization;
        session(['member'=>$member]);
        return redirect()->back();
        //return to_route('member.dashboard');
        //return redirect()->back()->with('message',$data['avatar']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function changePassword(Request $request){
        $member=Member::find($request->id);
        if($member->doesntExist() || $member->user->doesntExist()){
            return Inertia::render('Error',[
                'message'=>'Illigal Operation'
            ]);
        }
        if(Hash::check($request->old, $member->user->password) && $request->new == $request->confirm ){
            $member->user->password=Hash::make($request->new);
            $member->user->save();
            return redirect()->back();
        }else{
            return redirect()->back()->withErrors(['message'=>'old password incorrect']);
        }
    }

}
