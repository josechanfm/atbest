<?php

namespace App\Http\Controllers\Widget\Admin;

use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Poll;

class DashboardController extends Controller
{
    public function index(){
        //$widget=Permission::create(['name'=>'widget','guard_name'=>'web']);

        //dd(auth()->user()->givePermissionTo('widget'));

        return Inertia::render('Widget/Admin/Dashboard',[
            'polls'=>Poll::all(),
            'configs'=>'widget configs'
        ]);
    }
}
