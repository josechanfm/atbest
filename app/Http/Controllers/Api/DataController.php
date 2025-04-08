<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Member;
class DataController extends Controller
{
    public function searchUsers(Request $request){
        $users = User::where('name', 'like', '%' . $request->name . '%')->get();
        return response()->json($users);
    }
    public function searchMembers(Request $request){
        $users = Member::where('given_name', 'like', '%' . $request->name . '%')->get();
        return response()->json($users);
    }

}
