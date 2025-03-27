<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Email;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return Inertia::render('Organizer/Emails',[
            'emails'=>Email::whereBelongsTo(session('organization'))->paginate($request->per_page)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Organizer/Email',[
            'email'=>Email::make(['organization_id'=>session('organization')->id])
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
        $data=$request->all();
        $data['organization_id']=session('organization')->id;
        $data['sender']=session('organization')->email??'noreply@atbest.net';
        $data['user_id']=auth()->user()->id;
        $emailData=[
            'sender'=>$data['sender'],
            'reciptient'=>$data['recipient'],
            'subject'=>$data['subject'],
            'body'=>$data['message']
        ];
        Mail::send('emails.simple-email',$emailData, function($message) use($emailData){
            $message->from($emailData['sender'],'Atbest.net')
                    ->to($emailData["recipient"])
                    ->subject($emailData["subject"]);
        });

        Email::create($data);
        return redirect()->back()->with('success', 'Email sent successfully!');

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
    public function edit(Email $email)
    {

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
        //
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
