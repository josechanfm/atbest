<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute 必須接受。',
    'accepted_if' => '當 :other 為 :value 時，:attribute 必須接受。',
    'active_url' => ':attribute 不是有效的網址。',
    'after' => ':attribute 必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 必須是 :date 之後或相等的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號和底線。',
    'alpha_num' => ':attribute 只能包含字母和數字。',
    'array' => ':attribute 必須是一個陣列。',
    'ascii' => ':attribute 只能包含單字節字母數字字符和符號。',
    'before' => ':attribute 必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必須是 :date 之前或相等的日期。',
    'between' => [
        'array' => ':attribute 必須有 :min 到 :max 個項目。',
        'file' => ':attribute 必須在 :min 到 :max KB 之間。',
        'numeric' => ':attribute 必須在 :min 到 :max 之間。',
        'string' => ':attribute 必須在 :min 到 :max 個字符之間。',
    ],
    'boolean' => ':attribute 欄位必須為 true 或 false。',
    'confirmed' => ':attribute 確認不匹配。',
    'current_password' => '密碼不正確。',
    'date' => ':attribute 不是有效的日期。',
    'date_equals' => ':attribute 必須等於 :date。',
    'date_format' => ':attribute 不符合格式 :format。',
    'decimal' => ':attribute 必須有 :decimal 位小數。',
    'declined' => ':attribute 必須拒絕。',
    'declined_if' => '當 :other 為 :value 時，:attribute 必須拒絕。',
    'different' => ':attribute 和 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須在 :min 到 :max 位數字之間。',
    'dimensions' => ':attribute 圖片尺寸無效。',
    'distinct' => ':attribute 欄位有重複值。',
    'doesnt_end_with' => ':attribute 不能以以下之一結尾：:values。',
    'doesnt_start_with' => ':attribute 不能以以下之一開頭：:values。',
    'email' => ':attribute 必須是有效的電子郵件地址。',
    'ends_with' => ':attribute 必須以以下之一結尾：:values。',
    'enum' => '所選的 :attribute 無效。',
    'exists' => '所選的 :attribute 無效。',
    'file' => ':attribute 必須是一個檔案。',
    'filled' => ':attribute 欄位必須有值。',
    'gt' => [
        'array' => ':attribute 必須多於 :value 個項目。',
        'file' => ':attribute 必須大於 :value KB。',
        'numeric' => ':attribute 必須大於 :value。',
        'string' => ':attribute 必須多於 :value 個字符。',
    ],
    'gte' => [
        'array' => ':attribute 必須有 :value 個或更多項目。',
        'file' => ':attribute 必須大於或等於 :value KB。',
        'numeric' => ':attribute 必須大於或等於 :value。',
        'string' => ':attribute 必須多於或等於 :value 個字符。',
    ],
    'image' => ':attribute 必須是一張圖片。',
    'in' => '所選的 :attribute 無效。',
    'in_array' => ':attribute 欄位不存在於 :other 中。',
    'integer' => ':attribute 必須是一個整數。',
    'ip' => ':attribute 必須是一個有效的 IP 地址。',
    'ipv4' => ':attribute 必須是一個有效的 IPv4 地址。',
    'ipv6' => ':attribute 必須是一個有效的 IPv6 地址。',
    'json' => ':attribute 必須是一個有效的 JSON 字串。',
    'lowercase' => ':attribute 必須是小寫。',
    'lt' => [
        'array' => ':attribute 必須少於 :value 個項目。',
        'file' => ':attribute 必須小於 :value KB。',
        'numeric' => ':attribute 必須小於 :value。',
        'string' => ':attribute 必須少於 :value 個字符。',
    ],
    'lte' => [
        'array' => ':attribute 必須不多於 :value 個項目。',
        'file' => ':attribute 必須小於或等於 :value KB。',
        'numeric' => ':attribute 必須小於或等於 :value。',
        'string' => ':attribute 必須少於或等於 :value 個字符。',
    ],
    'mac_address' => ':attribute 必須是一個有效的 MAC 地址。',
    'max' => [
        'array' => ':attribute 不能多於 :max 個項目。',
        'file' => ':attribute 不能大於 :max KB。',
        'numeric' => ':attribute 不能大於 :max。',
        'string' => ':attribute 不能多於 :max 個字符。',
    ],
    'max_digits' => ':attribute 不能多於 :max 位數字。',
    'mimes' => ':attribute 必須是以下類型的檔案：:values。',
    'mimetypes' => ':attribute 必須是以下類型的檔案：:values。',
    'min' => [
        'array' => ':attribute 必須至少有 :min 個項目。',
        'file' => ':attribute 必須至少 :min KB。',
        'numeric' => ':attribute 必須至少 :min。',
        'string' => ':attribute 必須至少 :min 個字符。',
    ],
    'min_digits' => ':attribute 必須至少有 :min 位數字。',
    'missing' => ':attribute 欄位必須缺失。',
    'missing_if' => '當 :other 為 :value 時，:attribute 欄位必須缺失。',
    'missing_unless' => '除非 :other 是 :value，否則 :attribute 欄位必須缺失。',
    'missing_with' => '當 :values 存在時，:attribute 欄位必須缺失。',
    'missing_with_all' => '當 :values 存在時，:attribute 欄位必須缺失。',
    'multiple_of' => ':attribute 必須是 :value 的倍數。',
    'not_in' => '所選的 :attribute 無效。',
    'not_regex' => ':attribute 格式無效。',
    'numeric' => ':attribute 必須是一個數字。',
    'password' => [
        'letters' => ':attribute 必須包含至少一個字母。',
        'mixed' => ':attribute 必須包含至少一個大寫和一個小寫字母。',
        'numbers' => ':attribute 必須包含至少一個數字。',
        'symbols' => ':attribute 必須包含至少一個符號。',
        'uncompromised' => '此 :attribute 已出現在數據洩露中。請選擇不同的 :attribute。',
    ],
    'present' => ':attribute 欄位必須存在。',
    'prohibited' => ':attribute 欄位被禁止。',
    'prohibited_if' => '當 :other 為 :value 時，:attribute 欄位被禁止。',
    'prohibited_unless' => '除非 :other 在 :values 中，否則 :attribute 欄位被禁止。',
    'prohibits' => ':attribute 欄位禁止 :other 存在。',
    'regex' => ':attribute 格式無效。',
    'required' => ':attribute 欄位是必填的。',
    'required_array_keys' => ':attribute 欄位必須包含以下項目：:values。',
    'required_if' => '當 :other 為 :value 時，:attribute 欄位是必填的。',
    'required_if_accepted' => '當 :other 被接受時，:attribute 欄位是必填的。',
    'required_unless' => '除非 :other 在 :values 中，否則 :attribute 欄位是必填的。',
    'required_with' => '當 :values 存在時，:attribute 欄位是必填的。',
    'required_with_all' => '當 :values 存在時，:attribute 欄位是必填的。',
    'required_without' => '當 :values 不存在時，:attribute 欄位是必填的。',
    'required_without_all' => '當 :values 都不存在時，:attribute 欄位是必填的。',
    'same' => ':attribute 和 :other 必須匹配。',
    'size' => [
        'array' => ':attribute 必須包含 :size 個項目。',
        'file' => ':attribute 必須是 :size KB。',
        'numeric' => ':attribute 必須是 :size。',
        'string' => ':attribute 必須是 :size 個字符。',
    ],
    'starts_with' => ':attribute 必須以以下之一開頭：:values。',
    'string' => ':attribute 必須是一個字串。',
    'timezone' => ':attribute 必須是一個有效的時區。',
    'unique' => ':attribute 已經被使用。',
    'uploaded' => ':attribute 上傳失敗。',
    'uppercase' => ':attribute 必須是大寫。',
    'url' => ':attribute 必須是一個有效的網址。',
    'ulid' => ':attribute 必須是一個有效的 ULID。',
    'uuid' => ':attribute 必須是一個有效的 UUID。',
    
    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => '名稱',
        'email' => '電郵',
        'password' => '密碼',
        'registration_code' => '註冊碼',
    ],

];
