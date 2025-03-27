<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\BlogContent;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    //
    public function index()
    {
        return Inertia::render('Organizer/Blogs', [
            'blogs' => Blog::where('organization_id', session('organization')->id)->with('user')->get()
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $data['organization_id'] = session('organization')->id;
        $data['user_id'] = auth()->user()->id;

        Blog::create($data);

        return redirect()->back();
    }
    public function show(Blog $blog)
    {
        return Inertia::render('Organizer/BlogContents', [
            'blog' => $blog,
            'contents' => BlogContent::where('blog_id', $blog->id)->get()
        ]);
    }
    public function update(Blog $blog, Request $request)
    {
        $blog->update($request->all());
        return redirect()->back();
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();

        return redirect()->back();
    }
}
