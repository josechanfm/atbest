<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Entry;
use App\Models\EntryRecord;

class FormController extends Controller
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
        $forms = Form::where('published', true)->where('for_member', false)->with('organization')->orderBy('created_at','DESC')->get();
        if (session()->has('member')) {
                //for own memberslogin
                $memberForms=Form::where('published',true)->where('for_member',true)->where('organization_id',session('member')->organization->id)->orderBy('created_at','DESC')->get();
                //for those who as account, might not belongs to same organziation
                $userForms=Form::where('published',true)->where('require_login',true)->where('for_member',false)->orderBy('created_at','DESC')->get();
                $forms = $forms->merge($memberForms);
                $forms = $forms->merge($userForms);
        }
        return Inertia::render('Form/Forms', [
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
        $form=Form::find($request->form['id']);
        if ($form->published==false) { //not yet publish
            return redirect('forms');
        }elseif ($form->require_login && empty(auth()->user()) ) {
            return redirect('forms');
        }elseif ($form->for_member) {
            if (session('member')->organization->id != $form->organization_id) {
                return redirect('forms');
            }
        };
        $entry = new Entry();
        if ($form->for_member) {
            if(!session()->has('member')){
                return redirect('forms');
            }
            $entry->member_id = session('member')->id;
        }
        $entry->form_id = $request->form['id'];
        $entry->save();
        foreach ($request->fields as $key => $value) {
            $record = new EntryRecord();
            $record->entry_id = $entry->id;
            $record->form_field_id = $key;
            if (is_array($value)) {
                if (isset($value['file'])) {
                    $file = $value['blob'];
                    $path = Storage::putFile('public/images/forms/photos', $file);
                    $record->field_value = Storage::url($path);
                } else {
                    $record->field_value = json_encode($value);
                }
            } else {
                $record->field_value = $value;
            }
            $record->save();
        }
        $form = Form::find($entry->form_id);
        $entry_records = EntryRecord::where('entry_id', $entry->id)->with('form_field')->get();
        return Inertia::render('Form/Success', [
            'form' => $form,
            'entry' => $entry,
            'entry_records' => $entry_records,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form, Request $request)
    {
        // if($request->t!=$form->uuid){ //uuid not correct
        //     return redirect('forms');
        // }elseif ($form->published==false) { //not yet publish
        //     return redirect('forms');
        // }elseif ($form->require_login && empty(auth()->user()) ) {
        //     return redirect('forms');
        // }elseif ($form->for_member) {
        //     if (session('member')->organization->id != $form->organization_id) {
        //         return redirect('forms');
        //     }
        // };
        // $form->fields;
        // return Inertia::render('Form/FormDefault', [
        //     'form' => $form,
        // ]);
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
    public function receipt(Entry $entry){
        echo ('comming soon.');
    }

    public function item(Request $request){
        
        if(empty($request->t)){ 
            return redirect('forms');
        };
        $form=Form::where('uuid',$request->t)->first();
        if(empty($form)){ //uuid not correct
            return redirect('forms');
        }elseif ($form->published==false) { //not yet publish
            return redirect('forms');
        }elseif ($form->require_login && empty(auth()->user()) ) {
            return redirect('forms');
        }elseif ($form->for_member) {
            if (session('member')->organization->id != $form->organization_id) {
                return redirect('forms');
            }
        };
        $form->fields;
        return Inertia::render('Form/FormDefault', [
            'form' => $form,
        ]);

    }
}
