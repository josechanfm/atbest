<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Organization;
use App\Models\Member;
use App\Models\User;
use App\Support\CardStyles;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
                    $query->where('abbr_en', 'like', '%' . $request->search['abbr'] . '%');
                }
                if (!empty($request->search['name_zh'])) {
                    $query->where('name_zh', 'like', '%' . $request->search['name_zh'] . '%');
                }
            }
        })->with('members')->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Admin/Organizations', [
            'cardStyles' => CardStyles::presets(),
            'organizations' => $organizations,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return Inertia::render('Admin/OrganizationForm', [
            'organization' => null,
            'cardStyles' => CardStyles::presets(),
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
        $validated = $request->validate([
            'abbr_en' => 'required|string|max:255',
            'registration_code' => 'nullable|string|max:100',
            'name_zh' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'name_pt' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'href' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:255',
            'avatar_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'card_style' => is_array($request->input('card_style'))
                ? 'nullable|array:name,background,bg_image,logo,color,bg_color,font_color,font_style,preset'
                : 'nullable|string|max:50',
            'card_style.name' => 'nullable|string|max:255',
            'card_style.background' => 'nullable|string|max:500',
            'card_style.bg_image' => 'nullable|string|max:500',
            'card_style.logo' => 'nullable|string|max:500',
            'card_style.color' => 'nullable|string|max:100',
            'card_style.bg_color' => 'nullable|string|max:100',
            'card_style.font_color' => 'nullable|string|max:100',
            'card_style.font_style' => 'nullable|string|max:500',
            'card_style.preset' => 'nullable|string|max:100',
            'card_style_logo_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'card_style_bg_image_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'president' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'status' => 'nullable|boolean',
        ]);
        if (empty($validated['card_style'])) {
            unset($validated['card_style']);
        }
        $this->saveOrganization($request, new Organization(), $validated);
        return to_route('admin.organizations.index');
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
    public function edit(Organization $organization)
    {
        return Inertia::render('Admin/OrganizationForm', [
            'organization' => $organization->load('members'),
            'cardStyles' => CardStyles::presets(),
        ]);
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
            'abbr_en' => 'required|string|max:255',
            'name_zh' => 'nullable|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'registration_code' => 'nullable|string|max:100',
            'president' => 'nullable|string|max:255',
            'card_style' => is_array($request->input('card_style'))
                ? 'nullable|array:name,background,bg_image,logo,color,bg_color,font_color,font_style,preset'
                : 'nullable|string|max:50',
            'card_style.name' => 'nullable|string|max:255',
            'card_style.background' => 'nullable|string|max:500',
            'card_style.bg_image' => 'nullable|string|max:500',
            'card_style.logo' => 'nullable|string|max:500',
            'card_style.color' => 'nullable|string|max:100',
            'card_style.bg_color' => 'nullable|string|max:100',
            'card_style.font_color' => 'nullable|string|max:100',
            'card_style.font_style' => 'nullable|string|max:500',
            'card_style.preset' => 'nullable|string|max:100',
            'card_style_logo_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'card_style_bg_image_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'organizer_member_ids_present' => 'sometimes|boolean',
            
            // 可選欄位
            'name_pt' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'href' => 'nullable|string|max:255',
            'avatar' => 'nullable|string|max:500',
            'avatar_upload' => 'nullable|image|mimes:jpeg,png|extensions:jpg,jpeg,png|max:3072',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'logo' => 'nullable|string|max:500',
            'website' => 'nullable|string|max:255',
            'founded_at' => 'nullable|date',
            'status' => 'nullable|boolean',
            
            'organizer_member_ids' => 'sometimes|array',
            'organizer_member_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('members', 'id')->where('organization_id', $organization->id),
            ],
        ]);

        if ($request->boolean('organizer_member_ids_present') && !array_key_exists('organizer_member_ids', $validated)) {
            $validated['organizer_member_ids'] = [];
        }
        $this->saveOrganization($request, $organization, $validated);

        return to_route('admin.organizations.index')->with('success', '組織資訊更新成功！');
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
        $organization->members()->update(['is_organizer' => 0]);

        if (!empty($organizerIds)) {
            $organization->members()->whereIn('id', $organizerIds)->update(['is_organizer' => 1]);
        }
    }

    private function saveOrganization(Request $request, Organization $organization, array $validated): void
    {
        $uploads = ['logo' => 'card_style_logo_upload', 'bg_image' => 'card_style_bg_image_upload'];
        $imageInputs = ['avatar_upload', 'card_style_logo_upload', 'card_style_bg_image_upload'];
        $oldStyle = is_array($organization->card_style) ? $organization->card_style : [];
        $oldAvatar = $organization->avatar;
        $newPaths = [];
        $disk = Storage::disk('public');

        foreach ($uploads as $input) {
            if ($request->hasFile($input) && !is_array($validated['card_style'] ?? null)) {
                throw ValidationException::withMessages([$input => 'Select a card style before uploading an image.']);
            }
        }

        try {
            DB::transaction(function () use ($request, $organization, $validated, $uploads, $imageInputs, &$newPaths) {
                $data = $validated;
                foreach ($imageInputs as $input) {
                    unset($data[$input]);
                }
                unset($data['organizer_member_ids_present']);

                if (!$organization->exists) {
                    $organization->fill($data);
                    $organization->save();
                }

                foreach ($imageInputs as $input) {
                    if ($request->hasFile($input)) {
                        $path = $request->file($input)->store("organizations/{$organization->id}", 'public');
                        if ($path === false) {
                            throw new \RuntimeException("Failed to store organization image for {$input}.");
                        }
                        $newPaths[] = $path;
                        if ($input === 'avatar_upload') {
                            $data['avatar'] = '/storage/' . $path;
                        } else {
                            $field = array_search($input, $uploads, true);
                            $data['card_style'][$field] = '/storage/' . $path;
                        }
                    }
                }

                $organization->fill($data);
                $organization->save();
                if (array_key_exists('organizer_member_ids', $data)) {
                    $this->updateOrganizers($organization, $data['organizer_member_ids']);
                }
            });
        } catch (\Throwable $exception) {
            foreach ($newPaths as $path) {
                if (!$disk->delete($path)) {
                    \Log::error('Failed to clean up card style upload after save failure.', ['path' => $path]);
                }
            }
            throw $exception;
        }

        $style = is_array($organization->card_style) ? $organization->card_style : [];
        $currentImages = [$organization->avatar, $style['logo'] ?? null, $style['bg_image'] ?? null];
        foreach ([$oldAvatar, $oldStyle['logo'] ?? null, $oldStyle['bg_image'] ?? null] as $old) {
            $id = preg_quote((string) $organization->id, '~');
            $managed = is_string($old) && (
                preg_match("~^/storage/organizations?/{$id}/[a-zA-Z0-9]{40}\\.(?:jpg|jpeg|png)$~i", $old)
                || preg_match("~^/storage/organizations/{$id}[0-9]{6}/(?:site_logo|card_logo|card_background)/[a-zA-Z0-9]{40}\\.(?:jpg|jpeg|png)$~i", $old)
                || preg_match("~^/storage/organization-card-styles/{$id}/(?:logo|bg_image)/[a-zA-Z0-9]{40}\\.(?:jpg|jpeg|png|gif)$~i", $old)
            );
            if ($managed && !in_array($old, $currentImages, true) && !$this->cardImageIsReferenced($old, $organization->id)) {
                $path = substr($old, strlen('/storage/'));
                if ($disk->exists($path) && !$disk->delete($path)) {
                    throw new \RuntimeException('Organization saved, but failed to delete the previous image.');
                }
            }
        }
    }

    private function cardImageIsReferenced(string $url, int $organizationId): bool
    {
        foreach (CardStyles::presets() as $preset) {
            if (is_array($preset) && in_array($url, $preset, true)) {
                return true;
            }
        }

        foreach (Organization::whereKeyNot($organizationId)->select('id', 'avatar', 'card_style')->cursor() as $other) {
            if ($other->avatar === $url || (is_array($other->card_style) && in_array($url, $other->card_style, true))) {
                return true;
            }
        }

        return false;
    }
}
