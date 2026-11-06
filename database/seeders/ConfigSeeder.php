<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'organization_id' => 0, 'key' => 'article_categories', 'value' =>
                '[{"value":"FEATURE","label":"Feature"},{"value":"BLOG","label":"Blog"},{"value":"NEWS","label":"News"}]'
            ],
            [
                'organization_id' => 0, 'key' => 'bulletin_categories', 'value' =>
                '[{"value": "CMTE","label": "Committee"}, {"value": "GEN","label": "General"}]',
            ],
            [
                'organization_id' => 0, 'key' => 'message_categories', 'value' =>
                '[{"value": "ALL","label": "Everyone"}, 
                  {"value": "ORG","label": "Organization"},
                  {"value": "MEM","label": "Member Only"},
                  {"value": "IND","label": "Individual"}
                 ]',
            ],
            [
                'organization_id' => 0, 'key' => 'event_categories', 'value' =>
                '[{
                    "value": "TRANING",
                    "label": "Traning"
                }, {
                    "value": "COURSE",
                    "label": "Course"
                }, {
                    "value": "OTHER",
                    "label": "Other"
                }]'
            ],
            [
                'organization_id' => 0, 'key' => 'certificate_categories', 'value' =>
                '[{
                    "value": "BELT",
                    "label": "Belt"
                }, {
                    "value": "REFEREE",
                    "label": "Referee"
                }, {
                    "value": "COACH",
                    "label": "Coach"
                }, {
                    "value": "OTHER",
                    "label": "Other"
                }]',
            ],
            [
                'organization_id' => 0, 'key' => 'attendance_status', 'value' =>
                '[{
                        "value": "ATTEND",
                        "label": "Attend"
                    }, {
                        "value": "LATE",
                        "label": "Late"
                    }, {
                        "value": "EXCUSE",
                        "label": "Excuse"
                    }, {
                        "value": "ABSENT",
                        "label": "Absent"
                }]'
            ],
            [
                'organization_id' => 0, 'key' => 'card_styles', 'value' =>
                json_encode(config('card_styles'))
            ],
            [
                'organization_id' => 0, 'key' => 'parishes', 'value' =>
                '[
                    {"value":"F","label_zh":"花地瑪堂區","label_en":"Nossa Senhora de Fátima"},
                    {"value":"A","label_zh":"聖安多尼堂區","label_en":"Santo António"},
                    {"value":"S","label_zh":"大堂區","label_en":"Sé"},
                    {"value":"Z","label_zh":"望德堂區","label_en":"São Lázaro"},
                    {"value":"L","label_zh":"風順堂區","label_en":"São Lourenço"},
                    {"value":"C","label_zh":"嘉模堂區(氹仔)","label_en":"Novo campus da Universidade de Macau"},
                    {"value":"F","label_zh":"聖方濟各堂區(路環)","label_en":"São Francisco Xavier"}
                ]'
            ],
            [
                'organization_id' => 0, 'key' => 'field_types', 'value' =>
                '[
                    { "value": "input", "label_zh": "單行文字","label_en":"Text","label_pt":"Text"},
                    { "value": "textarea", "label_zh": "多行文字","label_en":"Multi Text","label_pt":"Multi Text" },
                    { "value": "richtext", "label_zh": "格式文字" ,"label_en":"Rich Text","label_pt":"Rich Text"},
                    { "value": "radio", "label_zh": "單項選擇" ,"label_en":"Radio","label_pt":"Radio"},
                    { "value": "checkbox", "label_zh": "多項選擇" ,"label_en":"Checkbox","label_pt":"Checkbox"},
                    { "value": "dropdown", "label_zh": "下拉清單" ,"label_en":"Dropdown","label_pt":"Dropdown"},
                    { "value": "true_false", "label_zh": "真/偽" ,"label_en":"True/False","label_pt":"True/False"},
                    { "value": "date", "label_zh": "日期格式" ,"label_en":"Date","label_pt":"Date"},
                    { "value": "datetime", "label_zh": "日期時間" ,"label_en":"Date time","label_pt":"Date time"},
                    { "value": "email", "label_zh": "電郵欄位" ,"label_en":"E-mail","label_pt":"E-mail"},
                    { "value": "number", "label_zh": "數值欄位" ,"label_en":"Number","label_pt":"Numero"},
                    { "value": "photo", "label_zh": "上傳相片" ,"label_en":"Photo","label_pt":"Photo"}
                ]'
            ],
            [
                'organization_id' => 0, 'key' => 'parishes', 'value' =>
                '[
                    {
                        "value": "agree", "label": "Level of Agreement", "template": [
                            {"value": "5","label": "Strongly Agree"},
                            {"value": "4","label": "Argree"},
                            {"value": "3","label": "Neutual"},
                            {"value": "2","label": "Disagree"},
                            {"value": "1","label": "Strongly Disagree"},
                            {"value": "0","label": "Not Applicable"}
                        ]
                    },
                    {
                        "value": "gender","label": "Gender","template": [
                            {"value": "M","label": "Male"},
                            {"value": "F","label": "Female"}
                        ]
                    },
                    {"value": "clear","label": "Reset","template": [
                            {"value": "option_1","label": "option_1"}
                        ]
                    }
                ]'
            ]
        ];
        foreach ($data as $d) {
            DB::table('configs')->insert($d);
            //Config::create($d);
        }
    }
}
