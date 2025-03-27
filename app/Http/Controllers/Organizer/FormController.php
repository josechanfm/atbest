<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Entry;
use App\Models\EntryRecord;
use App\Models\Event;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class FormController extends Controller
{
    public function __construct()
    {
        //$this->authorizeResource(Organization::class);
        $this->authorizeResource(Form::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //dd(Form::find($formId)->entries()->delete());
        // echo ($entries);
        // echo ($form);
        //dd(Organization::find(session('organization')->id)->forms);
        //$this->authorize('view',$organization);
        $pageSize = $request->pagination['pageSize'] ?? 10;
        $currentPage = $request->pagination['currentPage'] ?? 1;
        $forms = Organization::find(session('organization')->id)->forms()->where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['name'])) {
                    $query->where('name', 'like', '%' . $request->search['name'] . '%');
                }
                if (!empty($request->search['title'])) {
                    $query->where('title', 'like', '%' . $request->search['title'] . '%');
                }
            }
        })->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Organizer/Forms', [
            //'organization' => Organization::find(session('organization')->id),
            'forms' => $forms
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = Form::make([
            'organization_id' => session('organization')->id,
            'require_login' => false,
            'for_member' => false,
            'published' => false
        ]);
        $form->media;
        return Inertia::render('Organizer/Form', [
            //'organization' => Organization::find(session('organization')->id),
            //'forms'=>Organization::find(session('organization')->id)->forms
            'form' => $form
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
        $this->validate($request, [
            'organization_id' => 'required',
            'name' => 'required',
            'title' => 'required',
        ]);
        $form = Form::create($request->all());
        if($request->file('banner_image')){
            $form->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $form->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }
        return to_route('manage.forms.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Organization $organization, Form $form)
    {
        // $this->authorize('view',$organization);
        // $this->authorize('view',$form);

        echo 'edit form';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Form $form)
    {
        //dd(Organization::find($form->organization_id));
        // $form->banner_url=$form->getFirstMediaUrl('banner');
        // $form->thumb_url=$form->getFirstMediaUrl('thumb');
        //dd($form);
        return Inertia::render('Organizer/Form', [
            //'organization' => Organization::find(session('organization')->id),
            //'forms'=>Organization::find(session('organization')->id)->forms
            'form' => $form
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form)
    {
        $this->validate($request, [
            'organization_id' => 'required',
            'name' => 'required',
            'title' => 'required',
        ]);
        $form->update($request->all());
        if($request->file('banner_image')){
            $form->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $form->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }
        return to_route('manage.forms.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization, Form $form)
    {
        if ($form->hasChild()) {
            return redirect()->back()->withErrors(['message' => 'No permission or restriced deletion of records with child records.']);
        } else {
            $form->delete();
            return redirect()->back();
        }
    }
    public function backup(Form $form)
    {
        //return response()->json($form);
        $data = new \stdClass();
        $form->fields;
        //$data->form=Form::with('fields')->find($formId);
        $data->form = $form;

        if ($data->form) {
            $data->entries = Entry::where('form_id', 3)->with('records')->get();
            $file = \Storage::put('dbbackup/' . $data->form->organization_id . '/form_' . $data->form->id . '_' . time() . '.txt', json_encode($data));
            if ($file) {
                $data->entries;
            };
            //dd($file);
            $ids = Entry::where('form_id', $data->form->id)->pluck('id')->toArray();
            EntryRecord::whereIn('entry_id', $ids)->delete();
            Entry::where('form_id', $data->form->id)->delete();
        }
        return response()->json($data);
    }

    public function deleteImage(Form $form, Request $request)
    {
        $form->getFirstMedia($request->collection)->delete();
        return redirect()->back();
    }
}
