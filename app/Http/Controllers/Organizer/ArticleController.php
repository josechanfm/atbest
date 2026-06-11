<?php

namespace App\Http\Controllers\Organizer;

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
        
        return Inertia::render('Organizer/Articles', [
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
            'for_member' => false
        ]);
        
        return Inertia::render('Organizer/Article', [
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
        // 驗證資料
        $validated = $request->validate([
            'category_code' => 'required',
            'title' => 'required',
            'intro' => 'nullable',
            'content' => 'nullable',
            'valid_at' => 'required|date',
            'expire_at' => 'nullable|date|after:valid_at',
            'url' => 'nullable|url',
            'published' => 'nullable|boolean',
            'for_member' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumb_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        // 處理布林值欄位，確保是 0 或 1
        $validated['published'] = isset($validated['published']) ? (int) $validated['published'] : 0;
        $validated['for_member'] = isset($validated['for_member']) ? (int) $validated['for_member'] : 0;
        
        // 處理 tags
        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }
        
        $validated['user_id'] = auth()->user()->id;
        $validated['author'] = auth()->user()->name;
        $validated['organization_id'] = session('organization')->id;
        
        // 建立文章
        $article = Article::create($validated);
        
        // 處理 Banner 圖片上傳
        if ($request->hasFile('banner_image')) {
            $article->clearMediaCollection('banner');
            $article->addMedia($request->file('banner_image'))
                    ->usingFileName($this->generateFileName($request->file('banner_image')))
                    ->toMediaCollection('banner');
        }
        
        // 處理縮圖上傳
        if ($request->hasFile('thumb_image')) {
            $article->clearMediaCollection('thumb');
            $article->addMedia($request->file('thumb_image'))
                    ->usingFileName($this->generateFileName($request->file('thumb_image')))
                    ->toMediaCollection('thumb');
        }
    
        return redirect()->route('organizer.articles.index')
                         ->with('success', '文章建立成功');
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
        // 確保權限
        if (session('organization')->id != $article->organization_id) {
            return redirect()->route('organizer.articles.index')
                             ->with('error', '沒有權限編輯此文章');
        }
        
        // 格式化文章資料，加入圖片 URL
        $articleData = $this->formatArticleForResponse($article);
        
        return Inertia::render('Organizer/Article', [
            'classifies' => Classify::whereBelongsTo(session('organization'))->get(),
            'articleCategories' => Config::item('article_categories'),
            'article' => $articleData
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
        // 確保權限
        if (session('organization')->id != $article->organization_id) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => false,
                    'message' => '沒有權限編輯此文章'
                ], 403);
            }
            return redirect()->route('organizer.articles.index')
                             ->with('error', '沒有權限編輯此文章');
        }
        
        // 驗證資料
        $validated = $request->validate([
            'category_code' => 'required',
            'title' => 'required',
            'intro' => 'nullable',
            'content' => 'nullable',
            'valid_at' => 'required|date',
            'expire_at' => 'nullable|date|after:valid_at',
            'url' => 'nullable|url',
            'published' => 'nullable|boolean',
            'for_member' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'thumb_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'delete_banner' => 'nullable|boolean',
            'delete_thumb' => 'nullable|boolean',
        ]);
        
        // 處理布林值欄位，確保是 0 或 1
        $validated['published'] = isset($validated['published']) ? (int) $validated['published'] : 0;
        $validated['for_member'] = isset($validated['for_member']) ? (int) $validated['for_member'] : 0;
        
        // 處理 tags
        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $validated['tags'] = json_encode($validated['tags']);
        }
        
        // 更新文章基本資料
        $article->update($validated);
        
        // 處理 Banner 圖片刪除
        if ($request->input('delete_banner') == '1' || $request->input('delete_banner') === true) {
            $article->clearMediaCollection('banner');
        }
        
        // 處理縮圖刪除
        if ($request->input('delete_thumb') == '1' || $request->input('delete_thumb') === true) {
            $article->clearMediaCollection('thumb');
        }
        
        // 處理 Banner 圖片上傳（會覆蓋現有圖片）
        if ($request->hasFile('banner_image')) {
            $article->clearMediaCollection('banner');
            $article->addMedia($request->file('banner_image'))
                    ->usingFileName($this->generateFileName($request->file('banner_image')))
                    ->toMediaCollection('banner');
        }
        
        // 處理縮圖上傳（會覆蓋現有圖片）
        if ($request->hasFile('thumb_image')) {
            $article->clearMediaCollection('thumb');
            $article->addMedia($request->file('thumb_image'))
                    ->usingFileName($this->generateFileName($request->file('thumb_image')))
                    ->toMediaCollection('thumb');
        }
        
        return redirect()->route('organizer.articles.index')
                         ->with('success', '文章更新成功');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Article $article)
    {
        // 檢查是否有權限刪除
        if (session('organization')->id != $article->organization_id) {
            return redirect()->back()->with('error', '沒有權限刪除此文章');
        }
        
        // 檢查是否已發佈且已有回應（根據您的業務邏輯）
        if ($article->published && $article->entries_count > 0) {
            return redirect()->back()->with('error', '已發佈且有回應的文章不能刪除');
        }
        
        // 刪除關聯的媒體檔案
        $article->clearMediaCollection('banner');
        $article->clearMediaCollection('thumb');
        
        $article->delete();
        
        return redirect()->back()->with('success', '文章已刪除');
    }
    
    /**
     * Delete image from article
     *
     * @param  Article  $article
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteImage(Article $article, Request $request)
    {
        $collection = $request->collection;
        
        if (in_array($collection, ['banner', 'thumb'])) {
            $article->clearMediaCollection($collection);
            
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json([
                    'success' => true,
                    'message' => '圖片已刪除',
                    'article' => $this->formatArticleForResponse($article)
                ]);
            }
        }
        
        return redirect()->back()->with('success', '圖片已刪除');
    }
    
    /**
     * Update sequence of articles
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sequence(Request $request)
    {
        foreach ($request->all() as $row) {
            Article::where('id', $row['id'])->update(['sequence' => $row['sequence']]);
        }
        
        return redirect()->back()->with('success', '排序已更新');
    }
    
    /**
     * Generate unique file name for uploaded image
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     * @return string
     */
    private function generateFileName($file)
    {
        return uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
    }
    
    /**
     * Format article data for response (add image URLs)
     *
     * @param  Article  $article
     * @return array
     */
    private function formatArticleForResponse(Article $article)
    {
        $data = $article->toArray();
        
        // 添加圖片 URL
        $data['banner_url'] = $article->getFirstMediaUrl('banner');
        $data['thumb_url'] = $article->getFirstMediaUrl('thumb');
        
        // 解析 tags 回來（如果儲存時是 JSON）
        if (!empty($data['tags']) && is_string($data['tags'])) {
            $data['tags'] = json_decode($data['tags'], true);
        } elseif (empty($data['tags'])) {
            $data['tags'] = [];
        }
        
        // 確保布林值正確傳送給前端
        $data['published'] = (bool) ($data['published'] ?? false);
        $data['for_member'] = (bool) ($data['for_member'] ?? false);
        
        // 確保日期格式正確，並處理 null 值
        if (isset($data['valid_at']) && $data['valid_at']) {
            $data['valid_at'] = date('Y-m-d', strtotime($data['valid_at']));
        } else {
            $data['valid_at'] = null;
        }
        
        if (isset($data['expire_at']) && $data['expire_at']) {
            $data['expire_at'] = date('Y-m-d', strtotime($data['expire_at']));
        } else {
            $data['expire_at'] = null;
        }
        
        return $data;
    }
}