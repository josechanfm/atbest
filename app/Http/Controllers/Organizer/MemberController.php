<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Config;
use App\Models\Organization;
use App\Models\Member;
use App\Models\User;
use App\Exports\MemberExport;
use App\Imports\MemberImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\Email;

class MemberController extends Controller
{
    public function __construct()
    {
        //$this->authorizeResource(Organization::class);
        $this->authorizeResource(Member::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageSize = $request->pagination['pageSize'] ?? 10;
        $currentPage = $request->pagination['currentPage'] ?? 1;
        // dd(session('organization'));

        $members = session('organization')->members()->where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['given_name'])) {
                    $query->where('given_name', 'like', '%' . $request->search['given_name'] . '%');
                }
                if (!empty($request->search['family_name'])) {
                    $query->where('family_name', 'like', '%' . $request->search['family_name'] . '%');
                }
            }
        })->paginate($pageSize, ['*'], 'page', $currentPage);
        // dd($members);
        return Inertia::render('Organizer/Members', [
            //'members'=>session('organization')->members
            'members' => $members
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
        $data = $request->all();
        $data['organization_id'] = session('organization')->id;
        $member = Member::create($data);
        //$member->organization()->attach(session('organization')->id);
        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Member $member)
    {
        return Inertia::render('Organizer/Member', [
            'member' => $member,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Organization $organization, Member $member)
    {
        // return Inertia::render('Organizer/MemberEdit',[
        //     //'member'=>$member->belongsToOrganization($organization)->first(),
        //     'member'=>$member,
        // ]);
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
        // dd($member,session('organization'));
        if ($member->ownedBy(session('organization'))) {
            //$member->organizations()->detach();
            //$member->delete();
            $member->dismiss=true;
            $member->save();
        }
        return redirect()->back();
    }

    public function createLogin(Member $member)
    {
        $this->authorize('update', $member);
        if (empty($member->user)) {
            $user = User::where('email', $member->email)->first();
            if ($user) {
                return response()->json(['result' => false, 'message' => 'Email already in used!']);
            } else {
                $user = $member->createUser();
            }
        } else {
            return response()->json(['result' => false, 'message' => 'Login Account already created!']);
            $user = $member->user;
        }
        Password::broker(config('fortify.passwords'))->sendResetLink(
            ['email' => $user->email]
        );
        return response()->json(['result' => true, 'message' => 'Login account created, please check you email.']);
    }
    public function export()
    {
        return Excel::download(new MemberExport, 'member.xlsx');
    }

    public function resetPassword(Member $member)
    {
        $data = [
            'organization_id' => session('organization') ? session('organization')->id : 0,
            'user_id' => auth()->user()->id,
            'sender' => 'noreplay@atbest.net',
            'recipient' => $member->user->email,
            'subject' => 'Reset Password',
            'message' => 'Reset password Template: ' . $member->family_name, ', ' . $member->given_name,
        ];
        $emailData = [
            'sender' => $data['sender'],
            'recipient' => $data['recipient'],
            'subject' => $data['subject'],
            'username' => $member->family_name . ', ' . $member->given_name
        ];
        Mail::send('emails.reset-password', $emailData, function ($message) use ($emailData) {
            $message->from($emailData['sender'], 'Atbest.net')
                ->to($emailData["recipient"])
                ->subject($emailData["subject"]);
        });
        Email::create($data);
        return redirect()->back()->with('success', 'Email sent successfully!');
    }

    public function import(Request $request){

       $organization = session('organization');
       $import = new MemberImport($organization);

       $import->import(request()->file('file'));

       return response()->json([
        'errors' => $import->failures()
    ]);
    }
}
