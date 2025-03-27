<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Certificate;
use App\Models\CertificateMember;
use App\Models\Member;
class CertificateMemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Certificate $certificate)
    {
        // dd(session('organization'), $certificate->load('members'), session('organization')->members);
        return Inertia::render('Organizer/CertificateMembers',[
            'organization'=>session('organization'),
            'certificate'=>$certificate->load('members'),
            'members'=>session('organization')->members
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
    public function store(Certificate $certificate, Request $request)
    {
        //dd($certificate, $request->all());
        $certificate->members()->attach($request->member_id, $request->all());
        //CertificateMember::create($request->all());

        return redirect()->back();
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
    public function update(Certificate $certificate, Member $member, Request $request)
    {   
        //dd($certificate, $member, $request->all());
        $certificateMember=CertificateMember::find($request->id);
        $certificateMember->update($request->all());
        return redirect()->back();
        //return response()->json($request->all()); 
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
}
