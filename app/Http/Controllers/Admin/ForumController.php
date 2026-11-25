<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $forums = Forum::with(['organization:id,name_zh', 'user:id,name'])
            ->where(function ($query) use ($request) {
                if (! empty($request->search)) {
                    if (! empty($request->search['organization_id'])) {
                        $query->where('organization_id', $request->search['organization_id']);
                    }
                    if (! empty($request->search['title'])) {
                        $query->where('title', 'like', '%' . $request->search['title'] . '%');
                    }
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($request->per_page ?? 10);

        return Inertia::render('Admin/Forums', [
            'organizations' => Organization::all(),
            'forums'         => $forums,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/ForumForm', [
            'organizations' => Organization::all(),
            'forum'          => (object) [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
        ]);

        $data['user_id'] = auth()->user()->id;

        Forum::create($data);

        return redirect()->route('admin.forums.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Forum $forum)
    {
        return Inertia::render('Admin/ForumForm', [
            'organizations' => Organization::all(),
            'forum'          => $forum,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Forum $forum)
    {
        $data = $request->validate([
            'organization_id' => ['required', 'exists:organizations,id'],
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['nullable', 'string'],
        ]);

        $forum->update($data);

        return redirect()->route('admin.forums.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Forum $forum)
    {
        $forum->delete();

        return redirect()->route('admin.forums.index');
    }
}
