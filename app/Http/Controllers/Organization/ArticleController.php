<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Config;
use App\Models\Classify;
use App\Models\Article;
use App\Models\Organization;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //dd(session('member'));
        $pageSize = $request->pagination['pageSize'] ?? 10;
        $currentPage = $request->pagination['currentPage'] ?? 1;
        $articles = Article::whereBelongsTo(session('organization'))->orderBy('sequence', 'DESC')->where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['category'])) {
                    $query->where('category_code', $request->search['category']);
                }
                if (!empty($request->search['title'])) {
                    $query->where('title', 'like', '%' . $request->search['title'] . '%');
                }
            }
        })->paginate($pageSize, ['*'], 'page', $currentPage);
        return Inertia::render('Organization/Articles', [
            'classifies' => Classify::whereBelongsTo(session('organization'))->get(),
            'articleCategories' => Config::item('article_categories'),
            'articles' => $articles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $article = Article::make([
            'organization_id' => session('organization')->id,
            'published' => false,
            'public' => false
        ]);
        return Inertia::render('Organization/Article', [
            'classifies' => Classify::whereBelongsTo(session('organization'))->get(),
            'articleCategories' => Config::item('article_categories'),
            'article' => $article
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
        $data = $request->all();
        //$data['organization_id'] = session('organization')->id;
        $data['user_id'] = auth()->user()->id;
        $data['author'] = auth()->user()->name;
        //$article = Article::create($data);

        $article=session('organization')->articles()->create($data);
        if($request->file('banner_image')){
            $article->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $article->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }

        // if ($request->file('thumbnail_upload')) {
        //     $file = $request->file('thumbnail_upload');
        //     $fileName = $article->id . '_' . $file->getClientOriginalName();
        //     $file->move(public_path('images/articles/thumbnail'), $fileName);
        //     $article->thumbnail = '/images/articles/thumbnail/' . $fileName;
        //     $article->save();
        // }

        return redirect()->route('manage.articles.index');
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
    public function edit(Article $article)
    {
        $article->banner_url=$article->getFirstMediaUrl('banner');
        $article->thumb_url=$article->getFirstMediaUrl('thumb');
        return Inertia::render('Organization/Article', [
            'classifies' => Classify::whereBelongsTo(session('organization'))->get(),
            'articleCategories' => Config::item('article_categories'),
            'article' => $article
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Article $article)
    {
        $article->update($request->all());
        if($request->file('banner_image')){
            $article->addMedia($request->file('banner_image')[0]['originFileObj'])->toMediaCollection('banner');
        }
        if($request->file('thumb_image')){
            $article->addMedia($request->file('thumb_image')[0]['originFileObj'])->toMediaCollection('thumb');
        }
        return redirect()->route('manage.articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        if (session('organization')->id == $article->organization_id) {
            $article->delete();
        }
        return redirect()->back();
    }
    public function deleteImage(Article $article)
    {
        if (file_exists($article->thumbnail)) {
            unlink(public_path($article->thumbnail));
        }
        $article->thumbnail = null;
        $article->save();
        return redirect()->back();
    }
    public function sequence(Request $request)
    {
        // dd($request->all());
        foreach ($request->all() as $row) {
            Article::where('id', $row['id'])->update(['sequence' => $row['sequence']]);
        }
        return redirect()->back();
    }
}
