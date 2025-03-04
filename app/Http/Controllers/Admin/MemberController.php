<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Member;
use App\Models\Organization;
use Illuminate\Support\Facades\Password;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //dd(Member::with('organizations')->with('user')->where('id','102')->get());
        // $pageSize = $request->pagination['pageSize'] ?? 10;
        // $currentPage = $request->pagination['currentPage'] ?? 1;
        $members = Member::where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['organization'])) {
                    $query->where('organization_id', $request->search['organization']);
                }
                if (!empty($request->search['given_name'])) {
                    $query->where('given_name', 'like', '%' . $request->search['given_name'] . '%');
                }
                if (!empty($request->search['family_name'])) {
                    $query->where('family_name', 'like', '%' . $request->search['family_name'] . '%');
                }
            }
        })->with('organization')->with('user')->paginate($request->per_page);
        //})->with('organization')->with('user')->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Admin/Members', [
            'members' => $members,
            'organizations' => Organization::where('status', true)->get(),
            //'users' => User::whereNotIn('id', Member::whereNotNull('user_id')->get()->pluck('user_id'))->get()
            'users' => User::all()
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
        Member::create($request->all());
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
    public function update(Request $request, Member $member)
    {
        $member->update($request->all());
        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Member $member)
    {
        $member->delete();
        return redirect()->back();
    }
    public function createLogin(Member $member)
    {

        if (!$member->hasUser()) {
            $user = $member->createUser();
        } else {
            $user = $member->user;
        }

        Password::broker(config('fortify.passwords'))->sendResetLink(
            ['email' => $user->email]
        );
    }
}
