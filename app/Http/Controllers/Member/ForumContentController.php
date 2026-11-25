<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ForumContent;
use App\Models\Forum;
use Inertia\Inertia;

class ForumContentController extends Controller
{
    //
    public function store(Forum $forum, Request $request)
    {
        $data = $request->all();
        $data['parent_id'] = 0;
        $data['forum_id'] = $forum->id;
        $data['user_id'] = session('member')->user_id;
        ForumContent::create($data);
        return redirect()->back();
    }

    public function show(Forum $forum, ForumContent $content)
    {
        return Inertia::render('Member/ForumContent', [
            'forum' => $forum,
            'content' => $content,
        ]);
    }

    public function replyContent(Forum $forum, Request $request)
    {
        $data = $request->all();
        $data['forum_id'] = $forum->id;
        $data['user_id'] = session('member')->user_id;

        ForumContent::create($data);
        return redirect()->back();
    }
}
