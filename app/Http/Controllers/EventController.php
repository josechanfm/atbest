<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Entry;
use App\Models\Event;
use App\Models\EntryRecord;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(session('organization'));
        //dd(auth()->user()->members->count()>0);
        $events=Event::where('published', true)->where('for_member', false)->with('organization')->orderBy('created_at','DESC')->get();
        if (session()->has('member')) {
                //for own memberslogin
                $memberEvents=Event::where('published',true)->where('for_member',true)->where('organization_id',session('member')->organization->id)->orderBy('created_at','DESC')->get();
                //for those who as account, might not belongs to same organziation
                $userEvents=Event::where('published',true)->where('require_login',true)->where('for_member',false)->orderBy('created_at','DESC')->get();
                $events = $events->merge($memberEvents);
                $events = $events->merge($userEvents);
        }
        return Inertia::render('Event/Events', [
            'events' => $events
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event, Request $request)
    {
        if($request->t!=$event->uuid){ //uuid not correct
            return redirect('events');
        }elseif ($event->published==false) { //not yet publish
            return redirect('events');
        }elseif ($event->require_login && empty(auth()->user()) ) {
            return redirect('events');
        }elseif ($event->for_member) {
            if (session('member')->organization->id != $event->organization_id) {
                return redirect('events');
            }
        };
        return Inertia::render('Event/Event', [
            'event' => $event,
        ]);
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
    public function item(Request $request){
        //dd(Article::where('uuid',$request->t)->where('published',true)->first());
        if(empty($request->t)){ 
            return redirect('events');
        };
        $event=Event::where('uuid',$request->t)->first();
        if(empty($fevent)){ //uuid not correct
            return redirect('forms');
        }elseif ($event->published==false) { //not yet publish
            return redirect('events');
        }elseif ($event->require_login && empty(auth()->user()) ) {
            return redirect('events');
        }elseif ($event->for_member) {
            if (session('member')->organization->id != $event->organization_id) {
                return redirect('events');
            }
        };
        return Inertia::render('Event/Event', [
            'event' => $event,
        ]);
    }
}
