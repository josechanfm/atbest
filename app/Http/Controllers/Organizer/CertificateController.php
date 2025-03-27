<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Organization;
use App\Models\Certificate; 
use App\Models\Config;
use Illuminate\Support\Facades\Storage;
//use Spatie\MediaLibrary\MediaCollections\Models\Media;

class CertificateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Certificate::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //  dd(Organization::find(session('organization')->id)->certificates);
        return Inertia::render('Organizer/Certificates',[
            'certificates'=>session('organization')->fresh()->certificates,
            'certificate_categories'=>Config::item('certificate_categories')
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
        $data=$request->all();
        // $data['organization_id']=session('organization')->id;
        // Certificate::create($data);

        $certificate= new Certificate($data);
        $certificate->organization()->associate(session('organization'));
        $certificate->save();
        if($request->file('cert_logo_upload')){
            $file=$request->file('cert_logo_upload');
            $certificate->addMedia($file)->usingName('cert_logo_'.$certificate->id.'.png')->usingFileName('cert_logo'.$certificate->id.'.png')->toMediaCollection('cert_logo');
        }

        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Certificate $certificate)
    {
        return redirect(route('organization.certificate.memebers',[$organization->id,$certificate->id]));
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
    public function update(Certificate $certificate, Request $request)
    {

        // dd($request->file());
        $this->validate($request,[
            'id' => 'required',
            'category_code'=>'required',
            'cert_title'=>'required',
        ]);
        $organization = Organization::find($request->organization_id);
        if($organization->id!=session('organization')->id){
            return redirect()->back();
        };
        
        $certificate->update($request->all());
        //dd($request->all(), $request->file());
        if($request->file('cert_logo_upload')){
            $file=$request->file('cert_logo_upload');
            //$certificate->addMedia($file)->toMediaCollection('cert_logo');
            $certificate->addMedia($file)->usingName('cert_logo_'.$certificate->id.'.png')->usingFileName('cert_logo'.$certificate->id.'.png')->toMediaCollection('cert_logo');

        }

        return redirect()->back();
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
    public function deleteMedia($media){
        $media->delete();
        return redirect()->back();
        // if($certificate->cert_logo){
        //     if(Storage::disk('certificate')->exists($certificate->cert_logo)){
        //         Storage::disk('certificate')->delete($certificate->cert_logo);
        //         $certificate->cert_logo=null;
        //         $certificate->save();
        //     }
        // }
    }

    public function members(Organization $organization, Certificate $certificate){
        $this->authorize('view',$organization);
        $this->authorize('view',$certificate);
        return Inertia::render('Organizer/CertificateMember',[
            'organization'=>$organization,
            'certificate'=>$certificate,
            'members'=>$certificate->members
        ]);
       
    }
}
