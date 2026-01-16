<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Organization;
use App\Models\Member;
use App\Models\User;
use App\Models\Config;
use Illuminate\Support\Facades\Password;

class OrganizationController extends Controller
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
        $organizations = Organization::where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['parish'])) {
                    $query->where('parish', $request->search['parish']);
                }
                if (!empty($request->search['abbr'])) {
                    $query->where('abbr', 'like', '%' . $request->search['abbr'] . '%');
                }
                if (!empty($request->search['name_zh'])) {
                    $query->where('name_zh', 'like', '%' . $request->search['name_zh'] . '%');
                }
            }
        })->with('members')->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Admin/Organizations', [
            'cardStyles' => Config::item('card_styles'),
            'organizations' => $organizations,
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
        Organization::create($request->all());
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
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            // 必要欄位
            'parish' => 'nullable|string|max:255',
            'abbr' => 'nullable|string|max:50',
            'name_zh' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'registration_code' => 'nullable|string|max:100',
            'president' => 'nullable|string|max:255',
            'card_style' => 'nullable|string|max:50',
            
            // 可選欄位
            'name_pt' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'href' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:255',
            'founded_at' => 'nullable|date',
            'status' => 'nullable|boolean',
            
            'organizer_member_ids' => 'nullable',
        ]);

        try {
            // 更新基本資訊
            $organization->update($validated);
            
            // 處理用戶關聯
            if ($request->has('organizer_member_ids')) {
                $organization->users()->sync($request->organizer_member_ids);
            }
            
            // 處理組織者設定
            $this->updateOrganizers($organization, $request->organizer_member_ids ?? []);
            
            return redirect()->back()->with('success', '組織資訊更新成功！');
        
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();
        return redirect()->back();
    }

    public function members(Organization $organization)
    {
        //$members=Member::whereBelongsTo($)->get();
        return Inertia::render('Admin/OrganizationMembers', [
            'members' => $organization->members,
        ]);
    }
    public function createLogin(Member $member)
    {

        dd($member);
        if (!$member->hasUser()) {
            $user = $member->createUser();
        } else {
            $user = $member->user;
        }

        Password::broker(config('fortify.passwords'))->sendResetLink(
            ['email' => $user->email]
        );
    }

    public function masquerade(Organization $organization)
    {
        session(['organization' => $organization]);
        return to_route('organizer.dashboard');
    }

    private function updateOrganizers(Organization $organization, array $organizerIds)
    {
        // 開始資料庫事務
        try {
            // 1. 先將該組織所有成員的 is_organizer 設為 0
            $organization->members()->update(['is_organizer' => 0]);
            
            // 2. 如果有指定組織者，將他們設為 1
            if (!empty($organizerIds)) {
                // 只更新屬於這個組織的成員
                $validMemberIds = $organization->members()
                    ->whereIn('id', $organizerIds)
                    ->pluck('id')
                    ->toArray();
                
                if (!empty($validMemberIds)) {
                    $organization->members()
                        ->whereIn('id', $validMemberIds)
                        ->update(['is_organizer' => 1]);
                }
            }
            
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
