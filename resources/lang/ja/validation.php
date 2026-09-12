<?php

return [

    'required' => ':attribute が未入力または形式が間違っています。',
    'required_if' => ':attribute が未入力またはファイルが選択されていません。',

    'attributes' => [
        'corp_name'     => '法人名',
        'clinic_name'   => '病院名',
        'rep_position'  => '代表者肩書',
        'rep_last_name' => '姓',
        'rep_first_name'=> '名',
        'last_name'     => '姓',
        'first_name'    => '名',
        'members'       => '会員情報',
        'organization'  => '得意先',
        'zip_code'      => '郵便番号',
        'address1'      => '都道府県',
        'address2'      => '市町村区',
        'address3'      => '番地',
        'tel'           => 'TEL',
        'fax'           => 'FAX',
        'email'         => 'メールアドレス',

        // ────────────────────────────────────
        // 変更点：配列（wildcard）バリデーション用の属性名。
        // Laravelは "licenses.0.email" のような実際のインデックスを、
        // "licenses.*.email" というパターンに自動的に対応させて解決する。
        // ────────────────────────────────────

        // 申込みフォーム：ライセンス対象者（先生）
        'licenses.*.last_name'   => '先生の姓',
        'licenses.*.first_name'  => '先生の名',
        'licenses.*.email'       => '先生のメールアドレス',
        'licenses.*.position'    => '先生の役職',

        // 管理画面：契約先の先生一括登録・編集（syncMembers）
        'members.*.last_name'       => '先生の姓',
        'members.*.first_name'      => '先生の名',
        'members.*.last_name_kana'  => '先生の姓（かな）',
        'members.*.first_name_kana' => '先生の名（かな）',
        'members.*.member_number'   => '会員番号',
        'members.*.position'        => '先生の役職',
        'members.*.gender'          => '先生の性別',
        'members.*.birthdate'       => '先生の生年月日',
        'members.*.tel'             => '先生の電話番号',
        'members.*.mobile'          => '先生の携帯番号',
        'members.*.fax'             => '先生のFAX番号',
        'members.*.email'           => '先生のメールアドレス',
        'members.*.personal_email'  => '先生の個人メールアドレス',
        'members.*.member_type'     => '会員種別',
        'members.*.joined_at'       => '入会日',
        'members.*.withdrawn_at'    => '退会日',

        // 先生ごとの住所（members.*.addresses.*）
        'members.*.addresses.*.postal_code' => '先生の住所（郵便番号）',
        'members.*.addresses.*.address1'    => '先生の住所（都道府県）',
        'members.*.addresses.*.address2'    => '先生の住所（市区町村）',
        'members.*.addresses.*.address3'    => '先生の住所（番地・建物名）',
        'members.*.addresses.*.tel'         => '先生の住所（電話番号）',
        'members.*.addresses.*.fax'         => '先生の住所（FAX番号）',

        // 学歴・学位・役職歴・委員歴（MemberController）
        'degrees.*.degree'          => '学位',
        'degrees.*.obtained_at'     => '取得年',
        'roles.*.role'              => '役職',
        'roles.*.started_at'        => '就任年',
        'roles.*.ended_at'          => '退任年',
        'committees.*.committee'    => '委員名',
        'committees.*.started_at'   => '就任年',
        'committees.*.ended_at'     => '退任年',

        // 病院・組織の各種住所（location/shipping/billing）
        'organization.name'           => '法人名',
        'organization.abbr'           => '施設名',
        'organization.rep_position'   => '代表者役職',
        'organization.rep_last_name'  => '代表者の姓',
        'organization.rep_first_name' => '代表者の名',
        'location_address.postal_code' => '所在地（郵便番号）',
        'location_address.address1'    => '所在地（都道府県）',
        'location_address.address2'    => '所在地（市区町村）',
        'location_address.address3'    => '所在地（番地・建物名）',
        'location_address.email'       => '所在地のメールアドレス',
        'shipping_address.postal_code' => '郵送先（郵便番号）',
        'shipping_address.address1'    => '郵送先（都道府県）',
        'shipping_address.address2'    => '郵送先（市区町村）',
        'shipping_address.address3'    => '郵送先（番地・建物名）',
        'shipping_address.email'       => '郵送先のメールアドレス',
        'billing_address.postal_code'  => '請求先（郵便番号）',
        'billing_address.address1'     => '請求先（都道府県）',
        'billing_address.address2'     => '請求先（市区町村）',
        'billing_address.address3'     => '請求先（番地・建物名）',
        'billing_address.email'        => '請求先のメールアドレス',
    ],
];