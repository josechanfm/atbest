<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feature;
use App\Models\Organization;
use Inertia\Inertia;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Inertia::render('Admin/Features', [
            'organizations' => Organization::all(),
            'features' => Feature::all()
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $feature = Feature::make();
        return Inertia::render('Admin/FeatureEdit', [
            'organizations' => Organization::all(),
            'feature' => (object)[],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $feature=Feature::create($request->all());
        if($request->file('image_upload')){
            $file=$request->file('image_upload');
            $fileName=$feature->id.'_'.$file->getClientOriginalName();
            $file->move(public_path('thumbnail/features'), $fileName);
            $feature->image='/thumbnail/features/'.$fileName;
            $feature->save();
        }
        return to_route('admin.features.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Feature $feature)
    {
        return Inertia::render('Admin/FeatureEdit', [
            'organizations' => Organization::all(),
            'feature' => $feature
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Feature $feature, Request $request)
    {
        $data=$request->all();
        if($request->file('image_upload')){
            $file=$request->file('image_upload');
            $fileName=$feature->id.'_'.$file->getClientOriginalName();
            $file->move(public_path('images/features'), $fileName);
            $data['image']='/images/features/'.$fileName;
        }
        $feature->update($data);
        return to_route('admin.features.index');
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
    public function deleteImage(Feature $feature){
        unlink(public_path($feature->image));
        $feature->image=null;
        $feature->save();
        return redirect()->back();
    }
}
