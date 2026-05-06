<?php

return [

    'required' => ':attribute が未入力または形式が間違っています。',
    'required_if' => ':attribute が未入力またはファイルが選択されていません。',

    'attributes' => [
        'organization_id' => '得意先',
        'deceased_name' => '故人名',
        'last_name' => '姓',
        'first_name' => '名',
        'deceased_furigana' => '故人名（ふりがな）',
        'gender'            => '性別',
        'application_date'  => '申込日',
        'delivery_date'     => '納品予定日時',
        'staff_name'        => '担当者',
        'funeral_datetime'  => '葬儀日時',
        'members'       => '会員情報',
        'organization'  => '得意先',
        'company_name'  => '会社名',
        'zip_code'      => '郵便番号',
        'address1'      => '市町村区',
        'address2'      => '番地',
        'tel'           => 'TEL',
        'fax'           => 'FAX',
        'email'         => 'メールアドレス',
    ],
];
