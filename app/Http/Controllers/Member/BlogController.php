<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use App\Models\BlogContent;

class BlogController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Member/Blogs', [
            'blogs' => Blog::whereBelongsTo(session('member')->organization)->get()
        ]);
    }

    public function show(Blog $blog)
    {
        return Inertia::render('Member/Blog', [
            'blog' => $blog,
            'blog_contents' => BlogContent::where('blog_id', $blog->id)->where('reply_id', 0)->orderBy('created_at', 'desc')->get()
        ]);
    }
    public function mediaUpload(Request $request)
    {
        $path = Storage::putFile('public/images/blog', $request->file);
        $path = Storage::url($path);
        return response()->json([
            'path' => $path,
        ]);
        // Storage::aa;
    }

    public function addContent(Request $request)
    {
        //blog_id, reply_id, title, content
        $data = $request->all();
        $data['user_id'] = session('member')->user_id;
        BlogContent::create($data);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
        }
    }
}
