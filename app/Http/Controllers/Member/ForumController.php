<?php
namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\ForumContent;

class ForumController extends Controller
{
    //
    public function index()
    {
        
        return Inertia::render('Member/Forums', [
            'forums' => Forum::forUser(),
        ]);
    }

    public function show(Forum $forum)
    {
        $theForum = Forum::forUser()->find($forum->id);
        if (!$theForum) {
            return Inertia::render('Error', [
                'message' => '留言板不存在或沒有權限.'
            ]);
        }
        return Inertia::render('Member/Forum', [
            'forum' => $theForum,
            'forum_contents' => ForumContent::where('forum_id', $forum->id)->where('reply_id', 0)->orderBy('created_at', 'desc')->get()
        ]);
    }
    public function mediaUpload(Request $request)
    {
        $path = Storage::putFile('public/images/forum', $request->file);
        $path = Storage::url($path);
        return response()->json([
            'path' => $path,
        ]);
        // Storage::aa;
    }

    public function addContent(Request $request)
    {
        //forum_id, parent_id, title, content
        $data = $request->all();
        $data['user_id'] = session('member')->user_id;
        ForumContent::create($data);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = Storage::putFile('public/images/forum', $file);
            $path = Storage::url($path);
        }
    }
}
