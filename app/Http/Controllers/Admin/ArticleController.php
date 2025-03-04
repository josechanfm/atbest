<?php

namespace App\Http\Controllers\Admin;

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
        // $pageSize = $request->pagination['pageSize'] ?? 10;
        // $currentPage = $request->pagination['currentPage'] ?? 1;

        $articles = Article::where(function ($query) use ($request) {
            if (!empty($request->search)) {
                if (!empty($request->search['category'])) {
                    $query->where('category_code', $request->search['category']);
                }
                if (!empty($request->search['title'])) {
                    $query->where('title', 'like', '%' . $request->search['title'] . '%');
                }
            }
        })->orderByRaw('ISNULL(sequence), sequence ASC')->paginate($request->per_page);
        //dd($articles);
        return Inertia::render('Admin/Articles', [
            // 'classifies'=>Classify::whereBelongsTo(session('organization'))->get(),
            'organizations' => Organization::all(),
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
        return Inertia::render('Admin/Article', [
            'organizations' => Organization::all(),
            'articleCategories' => Config::item('article_categories'),
            'article' => (object)['sequence'=>0],
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
        $data['user_id'] = auth()->user()->id;
        $data['author'] = auth()->user()->name;
        $article = Article::create($data);

        if ($request->file('thumbnail_upload')) {
            $file = $request->file('thumbnail_upload');
            $fileName = $article->id . '_' . $file->getClientOriginalName();
            $file->move(public_path('articles'), $fileName);
            $article->thumbnail = '/articles/' . $fileName;
            $article->save();
        }


        return redirect()->route('admin.articles.index');
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
        return Inertia::render('Admin/Article', [
            'organizations' => Organization::all(),
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
        $data = $request->all();

        if ($request->file('thumbnail_upload')) {
            $file = $request->file('thumbnail_upload');
            $fileName = $article->id . '_' . $file->getClientOriginalName();
            $file->move(public_path('articles'), $fileName);
            $data['thumbnail'] = '/articles/' . $fileName;
        }
        $article->update($data);
        $article->save();

        return redirect()->route('admin.articles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        $article->delete();
        return redirect()->back();
    }
    public function deleteImage(Article $article)
    {
        unlink(public_path($article->thumbnail));
        $article->thumbnail = null;
        $article->save();
        return redirect()->back();
    }

    public function sequence(Request $request)
    {
        //dd($request->all());
        foreach ($request->all() as $row) {
            Article::where('id', $row['id'])->update(['sequence' => $row['sequence']]);
        }
        return redirect()->back();
    }
}
