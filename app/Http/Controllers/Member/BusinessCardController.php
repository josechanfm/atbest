<?php
namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\BusinessCard;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessCardController extends Controller
{
    /**
     * Display the business card editor.
     */
    public function edit(Request $request)
    {
        // Eager load member and organization relations
        $user = $request->user()->load([
            'member:id,user_id,organization_id,family_name,display_name,avatar',
            'member.organization:id,name_zh,name_en'
            ]);
        //dd($user);
        // Ensure a business card record exists for the authenticated user
        $businessCard = $user->businessCard()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'content' => [
                    'name_zh'  => $user->member->name_zh ?? '', 
                    'name_en'  => $user->member->name_en ?? '', 
                    'title_zh' => '', 
                    'title_en' => ''
                ],
                'bio'     => ['summary' => '', 'details' => '']
            ]
        );

        return Inertia::render('Member/BusinessCardEdit', [
            'businessCard' => $businessCard,
            'user'         => $user, // Pass user with member relation to frontend
        ]);
    }

    /**
     * Update the business card details.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'content'             => 'required|array',
            'content.name_zh'     => 'nullable|string|max:255',
            'content.name_en'     => 'nullable|string|max:255',
            'content.title_zh'    => 'nullable|string|max:255',
            'content.title_en'    => 'nullable|string|max:255',
            'bio'                 => 'nullable|array',
        ]);
        $user->businessCard()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'content' => $request->input('content', []),
                'bio'     => $request->input('bio', []),
            ]
        );

        return redirect()->back()->with('success', '數碼名片更新成功');
    }

    public function show($uuid)
    {
        // Find the business card by UUID or throw a 404, eager loading user, member, and organization
        $businessCard = BusinessCard::where('uuid', $uuid)
            ->with([
                'user.member' => fn ($query) => $query->select('id', 'user_id', 'organization_id', 'display_name', 'avatar'),
                'user.member.organization' => fn ($query) => $query->select('id', 'name_zh', 'name_en'),
            ])
            ->firstOrFail();

        return Inertia::render('Member/BusinessCardShow', [
            'businessCard' => $businessCard,
        ]);
    }
}