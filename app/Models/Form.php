<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Image\Manipulations;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Str;


class Form extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    protected $fillable = ['uuid','organization_id', 'name', 'title', 'description', 'welcome', 'thanks','thumbnail', 'require_login', 'for_member', 'published', 'with_attendance'];

    public static function boot(){
        parent::boot();
        self::creating(function($model){
            $model->uuid=hash('crc32b',rand());
        });
        static::updating(function ($model){
            if(empty($model->uuid)){
                $model->uuid=hash('crc32b',rand());
                //$model->uuid=Str::uuid();
            }
        });
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this
            ->addMediaConversion('preview')
            ->fit(Manipulations::FIT_CROP, 300, 300)
            ->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('form_content');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function fields()
    {
        return $this->hasMany(FormField::class)->orderBy('sequence');
    }
    //extra fields show in entry table
    public function in_column_fields()
    {
        return $this->hasMany(FormField::class)->where('in_column', 1);
    }
    //entry table column headers, for frontend table view and export to excel
    public function entryColumns()
    {
        $columns[] = (object)['title' => '#', 'dataIndex' => 'uid'];
        if($this->for_member || $this->require_login){
            $columns[] = (object)['title' => 'Username', 'dataIndex' => 'username'];
        }
        foreach ($this->in_column_fields as $column) {
            $columns[] = (object)['title' => $column->field_name, 'dataIndex' => 'extra_' . $column->id];
        }
        $columns[] = (object)['title' => 'Submit at', 'dataIndex' => 'submitted_at'];
        $columns[] = (object)['title' => 'Action', 'dataIndex' => 'operation'];
        return $columns;
    }

    public function entries()
    {
        return $this->hasMany(Entry::class)->with('records');
    }
    //entries for frontend table view and export to excel
    public function tableEntries()
    {
        $entries = $this->entries;
        $fields = $this->in_column_fields;
        foreach ($entries as $entry) {
            $entry->member;
            foreach ($fields as $field) {
                $f = $entry->records->where('form_field_id', $field->id)->first();
                if ($f) {
                    if ($field->type == 'radio') {
                        $fieldOptions = $field->options;
                        $value = array_filter($fieldOptions, function ($item) use ($f) {
                            return $item['value'] == $f->field_value;
                        });
                        $valueItem = reset($value);
                        $entry['extra_' . $field->id] = $valueItem->label ?? '';
                        // dd($entry);
                    } else if ($field->type == 'checkbox') {
                        $fieldOptions = $field->options;
                        $fieldValue = json_decode($f->field_value);
                        $value = array_filter($fieldOptions, function ($item) use ($fieldValue) {
                            return in_array($item->value, $fieldValue);
                        });
                        $labels = [];
                        foreach ($value as $item) {
                            $labels[] = $item->label;
                        }
                        $result = implode(',', $labels);
                        $entry['extra_' . $field->id] = $result;
                    } else {
                        $entry['extra_' . $field->id] = $f->field_value;
                    }
                }
            }
        }
        return $entries;
    }
    public function records()
    {
        $fields = $this->fields;
        $entries = $this->entries;
        $list = [];
        foreach ($entries as $e => $entry) {
            $tmp = [];
            foreach ($fields as $f => $field) {
                // $tmp['entry_id'] = $entry->id;
                $tmp[$field->id] = '';
            }
            foreach ($entry->records as $r => $record) {
                $tmp[$record->form_field_id] = $record->field_value;
            }
            array_push($list, $tmp);
        }
        return collect($list);
    }

    public function excelRecords()
    {
        $form_fields = $this->fields;
        $list = [];
        // $this->form->fields->pluck('field_label')->toArray();
        foreach ($this->entries as $entry) {
            $entry_records = $entry->records;
            collect($form_fields)->map(function ($field, $key) use ($entry_records, &$table_data) {
                $entry_record = collect($entry_records)->filter(function ($item) use ($field) {
                    return $item->form_field_id == $field->id;
                })->first();
                if ($field->type == 'radio') {
                    $value = array_filter($field->options, function ($item) use ($entry_record) {
                        return $item['value'] === $entry_record?->field_value;
                    });
                    // dd($value);
                    $table_data[$field->field_label] = reset($value)->label ?? '';
                    // 
                } else if ($field->type == 'checkbox') {
                    $value = array_filter(json_decode($field->options), function ($item) use ($entry_record) {
                        return in_array($item->value, json_decode($entry_record->field_value));
                    });
                    $labels = [];
                    foreach ($value as $item) {
                        $labels[] = $item->label;
                    }
                    $result = implode(',', $labels);
                    $table_data[$field->field_label] = $result;
                } else {
                    $table_data[$field->field_label] = $entry_record?->field_value;
                };
            });
            array_push($list, $table_data);
        }
        return collect($list);
    }
    public function hasChild()
    {
        return $this->fields()->exists();
    }

}
