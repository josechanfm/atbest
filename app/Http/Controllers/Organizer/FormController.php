<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Form;
use App\Models\Entry;
use App\Models\EntryRecord;
use App\Models\Event;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Form::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pageSize = $request->pagination['pageSize'] ?? 10;
        $currentPage = $request->pagination['currentPage'] ?? 1;
        
        $forms = Organization::find(session('organization')->id)
            ->forms()
            ->withCount('entries')
            ->where(function ($query) use ($request) {
                if (!empty($request->search)) {
                    if (!empty($request->search['name'])) {
                        $query->where('name', 'like', '%' . $request->search['name'] . '%');
                    }
                    if (!empty($request->search['title'])) {
                        $query->where('title', 'like', '%' . $request->search['title'] . '%');
                    }
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($pageSize, ['*'], 'page', $currentPage);
        
        return Inertia::render('Organizer/Forms', [
            'forms' => $forms
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $form = Form::make([
            'organization_id' => session('organization')->id,
            'require_login' => false,
            'for_member' => false,
            'published' => false,
            'with_attendance' => false,
            'openWelcome' => false,
            'openThanks' => false,
        ]);
        
        return Inertia::render('Organizer/Form', [
            'form' => $form
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
        $this->validate($request, [
            'name' => 'required',
            'title' => 'required',
            'valid_at' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            // 準備資料
            $data = $this->prepareFormData($request);
            $data['organization_id'] = session('organization')->id;
            
            // 建立表單
            $form = Form::create($data);
            
            // 處理圖片上傳
            $this->handleImageUploads($request, $form);
            
            DB::commit();
            
            // 新增成功後返回列表頁
            return redirect()->route('organizer.forms.index')
                ->with('success', '表單建立成功');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Form store error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['message' => '儲存失敗：' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function edit(Form $form)
    {
        // 載入媒體檔案
        $form->load('media');
        
        // 添加圖片 URL 方便前端使用
        $form->banner_url = $form->getFirstMediaUrl('banner');
        $form->thumb_url = $form->getFirstMediaUrl('thumb');
        
        // 加入 entries_count 用於判斷是否可刪除
        $form->entries_count = $form->entries()->count();
        
        return Inertia::render('Organizer/Form', [
            'form' => $form
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form)
    {
        $this->validate($request, [
            'name' => 'required',
            'title' => 'required',
            'valid_at' => 'required|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            // 準備資料
            $data = $this->prepareFormData($request);
            
            // 更新表單
            $form->update($data);
            
            // 處理圖片刪除
            $this->handleImageDeletions($request, $form);
            
            // 處理圖片上傳
            $this->handleImageUploads($request, $form);
            
            DB::commit();
            
            // 更新成功後返回列表頁
            return redirect()->route('organizer.forms.index')
                ->with('success', '表單更新成功');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Form update error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['message' => '更新失敗：' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function destroy(Form $form)
    {
        try {
            if ($form->entries()->count() > 0) {
                return redirect()->back()
                    ->withErrors(['message' => '無法刪除已有回應的表單，請先備份']);
            }
            
            // 刪除相關的媒體檔案
            $form->clearMediaCollection('banner');
            $form->clearMediaCollection('thumb');
            
            $form->delete();
            
            return redirect()->route('organizer.forms.index')
                ->with('success', '表單已刪除');
        } catch (\Exception $e) {
            \Log::error('Form delete error: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['message' => '刪除失敗：' . $e->getMessage()]);
        }
    }
    
    /**
     * Backup form entries before deletion.
     *
     * @param  \App\Models\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function backup(Form $form)
    {
        try {
            $data = new \stdClass();
            $form->load('fields');
            $data->form = $form;
            
            if ($data->form) {
                $entries = Entry::where('form_id', $form->id)
                    ->with('records')
                    ->get();
                $data->entries = $entries;
                
                // 建立備份目錄
                $backupPath = 'dbbackup/' . $form->organization_id;
                if (!Storage::exists($backupPath)) {
                    Storage::makeDirectory($backupPath);
                }
                
                // 儲存備份檔案
                $filename = 'form_' . $form->id . '_' . time() . '.json';
                $filePath = $backupPath . '/' . $filename;
                Storage::put($filePath, json_encode($data, JSON_PRETTY_PRINT));
                
                if (Storage::exists($filePath)) {
                    // 刪除原始資料
                    $ids = Entry::where('form_id', $form->id)->pluck('id')->toArray();
                    EntryRecord::whereIn('entry_id', $ids)->delete();
                    Entry::where('form_id', $form->id)->delete();
                    
                    return response()->json([
                        'success' => true,
                        'message' => '備份成功',
                        'backup_file' => $filename
                    ]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => '備份失敗'
            ], 500);
        } catch (\Exception $e) {
            \Log::error('Backup error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '備份失敗：' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete form image by collection.
     *
     * @param  \App\Models\Form  $form
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteImage(Form $form, Request $request)
    {
        try {
            $collection = $request->input('collection', $request->collection);
            $collectionName = $collection === 'banner' ? 'banner' : 'thumb';
            $form->clearMediaCollection($collectionName);
            
            return response()->json([
                'success' => true,
                'message' => '圖片已刪除'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '刪除失敗：' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Prepare form data from request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private function prepareFormData(Request $request)
    {
        $data = $request->all();
        
        // 移除圖片相關的欄位，避免存入資料庫
        unset($data['banner_image']);
        unset($data['thumb_image']);
        unset($data['delete_banner']);
        unset($data['delete_thumb']);
        unset($data['banner_url']);
        unset($data['thumb_url']);
        unset($data['entries_count']);
        unset($data['openWelcome']);
        unset($data['openThanks']);
        
        // 處理布林值
        $booleanFields = ['require_login', 'for_member', 'published', 'with_attendance'];
        foreach ($booleanFields as $field) {
            if ($request->has($field)) {
                $data[$field] = filter_var($request->input($field), FILTER_VALIDATE_BOOLEAN);
            }
        }
        
        // 處理日期
        if ($request->filled('valid_at')) {
            $data['valid_at'] = $request->input('valid_at');
        }
        
        if ($request->filled('expire_at')) {
            $data['expire_at'] = $request->input('expire_at');
        } else {
            $data['expire_at'] = null;
        }
        
        // 處理富文本內容
        $richtextFields = ['welcome', 'content', 'thanks'];
        foreach ($richtextFields as $field) {
            if ($request->has($field)) {
                $data[$field] = $request->input($field);
            }
        }
        
        return $data;
    }
    
    /**
     * Handle image uploads.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Form  $form
     * @return void
     */
    private function handleImageUploads(Request $request, Form $form)
    {
        // 處理 Banner 圖片
        if ($request->hasFile('banner_image')) {
            $file = $request->file('banner_image');
            
            // 檢查是否是 UploadedFile 物件
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                // 刪除舊的圖片
                $form->clearMediaCollection('banner');
                // 新增新圖片
                $form->addMedia($file)
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('banner');
            }
        }
        
        // 處理縮圖
        if ($request->hasFile('thumb_image')) {
            $file = $request->file('thumb_image');
            
            // 檢查是否是 UploadedFile 物件
            if ($file instanceof \Illuminate\Http\UploadedFile && $file->isValid()) {
                // 刪除舊的圖片
                $form->clearMediaCollection('thumb');
                // 新增新圖片
                $form->addMedia($file)
                    ->usingFileName($file->getClientOriginalName())
                    ->toMediaCollection('thumb');
            }
        }
    }
    
    /**
     * Handle image deletions.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Form  $form
     * @return void
     */
    private function handleImageDeletions(Request $request, Form $form)
    {
        // 處理 Banner 刪除
        if ($request->input('delete_banner') == '1' || $request->input('delete_banner') === true) {
            $form->clearMediaCollection('banner');
        }
        
        // 處理縮圖刪除
        if ($request->input('delete_thumb') == '1' || $request->input('delete_thumb') === true) {
            $form->clearMediaCollection('thumb');
        }
    }
}