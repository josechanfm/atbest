<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\Config;
use App\Models\Member;
use App\Models\Organization;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageSize = $request->pagination['pageSize'] ?? 10;
        $currentPage = $request->pagination['currentPage'] ?? 1;
        $events = Organization::find(session('organization')->id)->events()->where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['title_en'])) {
                    $query->where('title_en', 'like', '%' . $request->search['title_en'] . '%');
                }
            }
        })->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Organization/Events', [
            'events' => $events,
            'categories' => Config::item('event_categories', session('organization'))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Organization/Event', [
            'event' => new Event,
            'categories' => Config::item('event_categories', session('organization'))
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
        // $data = $request->all();
        // $data['organization_id'] = session('organization')->id;
        // Event::create($data);
        // dd($request->all());
        $event=session('organization')->events()->create($request->all());
        if($request->file('banner_image')){
            $event->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $event->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }

        return to_route('manage.events.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        // $event->banner_url=$event->getFirstMediaUrl('banner');
        // $event->thumb_url=$event->getFirstMediaUrl('thumb');
        //dd($event);
        return Inertia::render('Organization/Event', [
            'event' => $event,
            'categories' => Config::item('event_categories', session('organization'))
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        //dd($event);
        $event->update($request->all());
        if($request->file('banner_image')){
            $event->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $event->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }

        return to_route('manage.events.index');
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

    public function deleteImage(Event $event, Request $request)
    {
        $event->getFirstMedia($request->collection)->delete();
        return redirect()->back();
    }

}
