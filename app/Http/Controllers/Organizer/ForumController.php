<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ForumContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ForumController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Organizer/Forums', [
            'forums' => Forum::where('organization_id', session('organization')->id)->with('user')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['organization_id'] = session('organization')->id;
        $data['user_id'] = auth()->user()->id;

        Forum::create($data);

        return redirect()->back();
    }
    public function show(Forum $forum)
    {
        return Inertia::render('Organizer/ForumContents', [
            'forum' => $forum,
            'contents' => ForumContent::where('forum_id', $forum->id)->get()
        ]);
    }
    public function update(Forum $forum, Request $request)
    {
        $forum->update($request->all());
        return redirect()->back();
    }

    public function destroy(Forum $forum)
    {
        $forum->delete();

        return redirect()->back();
    }
}
