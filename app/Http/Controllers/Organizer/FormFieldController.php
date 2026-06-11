<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Config;
use App\Models\Organization;
use App\Models\Form;
use App\Models\FormField;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class FormFieldController extends Controller
{
    public function __construct()
    {
        // 確保權限與 Form 模型綁定
        $this->authorizeResource(Form::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Form $form)
    {
        $this->authorize('view', $form);
        
        $templates = Config::item('template_options') ?? [];
        
        // 注入自定義的 Organization 範本選項
        $templates[] = [
            'value' => 'organizations',
            'label' => 'Organization',
            'template' => Organization::select('abbr_en as value', 'name_zh as label')
                ->where('status', true)
                ->get()
                ->toArray()
        ];

        return Inertia::render('Organizer/FormFields', [
            'templateOptions' => $templates,
            'fieldTypes' => Config::item('field_types') ?? [],
            'form' => $form,
            // 依 sequence 遞增排序，確保拖曳後的順序正確渲染
            'fields' => $form->fields()->orderBy('sequence', 'asc')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Form $form, Request $request)
    {
        $request->validate([
            'form_id'     => 'required|exists:forms,id',
            'field_label' => 'required|string|max:255',
            'type'        => 'required|string',
        ]);

        // 防呆：取當前該表單內 sequence 的最大值 + 1，避免序號重複
        $maxSequence = FormField::where('form_id', $request->form_id)->max('sequence') ?? 0;

        $field = new FormField();
        $field->form_id     = $request->form_id;
        $field->sequence    = $maxSequence + 1;
        $field->field_name  = $request->field_name;
        $field->field_label = $request->field_label;
        $field->type        = $request->type;
        // Vue 前端若已轉為 JSON 字串，這裡直接寫入，否則可維持 array/string 自動轉換
        $field->options     = $request->options;
        $field->required    = $request->input('required', 0);
        $field->in_column   = $request->input('in_column', 0);
        $field->direction   = $request->input('direction', 'V');
        $field->rule        = $request->rule;
        $field->validate    = $request->validate;
        $field->remark      = $request->remark;
        $field->save();

        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Form $form, Request $request)
    {
        $request->validate([
            'id'          => 'required|exists:form_fields,id',
            'form_id'     => 'required|exists:forms,id',
            'field_label' => 'required|string|max:255',
            'type'        => 'required|string',
        ]);

        $field = FormField::findOrFail($request->id);
        $field->form_id     = $request->form_id;
        $field->field_name  = $request->field_name;
        $field->field_label = $request->field_label;
        $field->type        = $request->type;
        $field->options     = $request->options;
        $field->direction   = $request->input('direction', 'V');
        $field->required    = $request->input('required', 0);
        $field->in_column   = $request->input('in_column', 0);
        $field->remark      = $request->remark;
        $field->save();

        return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Form $form, FormField $field)
    {
        // 確保要刪除的欄位確實屬於該表單
        if ($field->form_id === $form->id) {
            $field->delete();
        }

        return redirect()->back();
    }

    /**
     * 配合 SortableJS / 原生拖曳排序的專用 API
     */
    public function fieldsSequence(Form $form, Request $request)
    {
        // 接收 Vue 傳過來的 { fields: [...] }
        $fieldsData = $request->input('fields', []);

        if (empty($fieldsData)) {
            return redirect()->back();
        }

        // 使用資料庫 Transaction 交易加速並防範斷點
        DB::transaction(function () use ($form, $fieldsData) {
            foreach ($fieldsData as $record) {
                // 加強全域安全性檢查：限制只能變更當前指定表單 ($form->id) 下的欄位
                FormField::where('id', $record['id'])
                    ->where('form_id', $form->id)
                    ->update(['sequence' => $record['sequence']]);
            }
        });

        return redirect()->back();
    }
}