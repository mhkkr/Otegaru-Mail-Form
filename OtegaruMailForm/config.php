<?php

/** @var array $setting 全般設定 */
$setting = [];

/** @var array $smtp SMTP 設定 */
$smtp = [];

/** @var array $item 項目設定 */
$item = [];

/** @var array $send 各宛先設定 */
$send = [];

/** @var array $template テンプレート設定 */
$template = [];


/* ---------------------------------------------------------------
  全般設定
  - すべて必須です。
--------------------------------------------------------------- */
// 管理側の基本のメールアドレス
$setting['email'] = 'hoge@example.com';

// 管理側の基本の名称
$setting['name'] = 'お手軽メールフォーム';

// 送信時のメールの題名
$setting['title'] = 'お問い合わせ';

// フォームの識別名
$setting['id'] = 'mailform';

// リファラーチェック用のURL （https://www.example.com/ と文字列で指定を推奨します）
$setting['referer_url']  = '://' . $_SERVER['HTTP_HOST'] . '/';

// 初期画面の URL
$setting['edit_url'] = './';

// 確認画面の URL
$setting['confirm_url'] = './?mode=confirm';

// 編集画面の URL
$setting['rewrite_url'] = './?mode=rewrite';

// 完了・送信時の URL
$setting['complete_url'] = './?mode=complete';

// 送信成功画面の URL
$setting['success_url'] = './?mode=success';

// 送信失敗画面の URL
$setting['failure_url'] = './?mode=failure';

// 文字コード
$setting['charset'] = 'UTF-8';

// 必須項目に入力がなかった時のメッセージ （個々の $item 'required_message' で定義された値がある場合はそちらが優先されます）
$setting['required_message'] = '必須項目です。';


/* ---------------------------------------------------------------
  SMTP 設定
  - SMTP 認証が必要な場合は、メールサーバによって変更してください。
--------------------------------------------------------------- */
// メールサーバのIP (e.g. hoge.example.com)
// $smtp['host'] = '';

// 25 or 587 or ...
// $smtp['port'] = 587;

// Return-path: に設定されるメールアドレス
// $smtp['from'] = $setting['email'];

// 'SMTP_AUTH' or 'SMTP'
// $smtp['protocol'] = 'SMTP_AUTH';

// SMTP認証ユーザ
// $smtp['user'] = $setting['email'];

// SMTP認証パスワード
// $smtp['password'] = '';


/* ---------------------------------------------------------------
  項目設定

  # 記入例の見方
  - 「キーの名前」以外の項目はすべて省略可能です。
  - () は記述の型
  - [] は未セットの場合の標準値
  - // はコメント

  # キーの名前 (string)
  'key' => [
    # 必須にする (bool) [false]
    'required' => true  // true = する, false = しない

    # 必須項目に入力がなかった時のカスタムメッセージ (string) ['']
    'required_message' => '' // 指定がなければ $setting['required_message'] が参照される。

    # HTML の type="" 属性、または select タグ、または textarea タグ (string) ['text']
    'type' => 'text'

    # 'type' が 'select' のとき、先頭に NULL 扱いの値を追加する (string) [null]
    'select_first_null' => '選択してください'

    # 'type' が 'checkbox' or 'radio' or 'select' のときの配列 (array) [[]]
    'list' => [
        'ほげ',
        'ぴよ',
    ]

    # 'type' が 'checkbox' or 'radio' or 'select' のときの value="" を配列番号ではなく文字列にする (bool) [false]
    'value_in_val' => true  // true = する, false = しない

    # 'type' が 'checkbox' のときのみ、確認画面とメール本文中の連結方法を指定する (string|PHP_EOL) ['、']
    'multiple_delimiter' => '、'

    # バリデーション (string) ['text'] ※'type' が 'checkbox' or 'radio' or 'select' 以外に有効。
    # 下記ない場合は ValidationExtends に定義することで検査ができるようになります。
    'validation' => 'confirm' // 内容が同一か確認する。別途 'confirm' オプションで参照先を指定する必要がある。
                    'empty' // 未入力が正しい判定。同時に 'required' => true を指定すると先に進めなくなります。

                    'text' // 文字列。
                    'hira' // ひらがなのみ。
                    'kana' // カタカナのみ。
                    'url' // URL形式。
                    'email' // E-Mail形式。

                    'zip' // 000-0000 または 0000000
                    'zipNumber' // 0000000 限定。
                    'zipStrict' // 000-0000 限定。
                    'zipA' // 000 限定。
                    'zipB' // 0000 限定。

                    'tel' // 0000-0000-0000, 000000000000 など 。
                    'telNumber' // 10桁から13桁の数字のみ。
                    'telStrict' // [2-4]-[2-4]-[4]の組み合わせのみ。
                    'telAbc' // 2桁から4桁の数字のみ。

                    'number'     // 数字のみ。 正規表現を使用。
                    'numeric'    // 数字のみ。 is_numeric() を使用。
                    'int'        // 数字のみ。 is_int() を使用。
                    'ctypeDigit' // 数字のみ。 ctype_digit() を使用。

                    'file' // TODO: 未実装。

    # バリデーションに引っかかった時のカスタムメッセージ (string) ['']
    'validation_message' => ''

    # バリデーションが 'confirm' のときに指定する参照先 (string) [null]
    'confirm' => 'メールアドレス'

    # {{ label }} に出力される文字を指定する (string) ['']
    'label_input'  => '',
    'label_output' => '住所', // 同一指定があれば {{ value }} を連結（グループ化）して出力されます。

    # {{ value }} に先頭に文字を挿入する (string) ['']
    'prefix_input'  => '',
    'prefix_output' => '〒', // 郵便番号のマークなど付けるなどに。

    # {{ value }} に末尾に文字を挿入する (string) ['']
    'suffix_input'  => '',
    'suffix_output' => '円', // 単位など付けるなどに。

    # 確認画面とメール本文から出力を除外する (bool) [false]
    # ボット対策や、同意チェックなど入力時のみ表示させたいときに役立ちます。
    'exclude' => true // true = する, false = しない
  ],
--------------------------------------------------------------- */
$item['ボット対策'] = [
    'validation' => 'empty',
    'exclude' => true,
];
$item['法人名'] = [];
$item['お名前'] = [
    'required' => true,
];
$item['メールアドレス'] = [
    'required' => true,
    'type' => 'email',
    'validation' => 'email',
];
$item['メールアドレス確認'] = [
    'required' => true,
    'type' => 'email',
    'validation' => 'confirm',
    'confirm' => 'メールアドレス',
    'exclude' => true,
];
$item['電話番号'] = [
    'required' => true,
    'type' => 'tel',
    'validation' => 'tel',
];
$item['数字'] = [
    'required' => true,
    'type' => 'number',
    'validation' => 'number',
    'suffix_output' => '円',
];
$item['数字バリデーションなし'] = [
    'required' => true,
    'type' => 'number',
];
$item['チェックボックス'] = [
    'required' => true,
    'type' => 'checkbox',
    'list' => ['おはよう', 'こんにちは', 'こんばんは'],
];
$item['ラジオボタン'] = [
    'required' => true,
    'type' => 'radio',
    'list' => ['おはよう', 'こんにちは', 'こんばんは'],
];
$item['郵便番号'] = [
    'type' => 'tel',
    'validation' => 'zip',
    'prefix_input' => '〒 ',
    'prefix_output' => '〒',
];
$item['都道府県'] = [
    'type' => 'select',
    'select_first_null' => '--都道府県--',
    'list' => ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'],
    'value_in_val' => true,
    'label_output' => 'ご住所',
];
$item['市区町村・番地'] = [
    'label_output' => 'ご住所',
];
$item['建物名・号室'] = [
    'label_output' => 'ご住所',
];
$item['お問い合わせ内容'] = [
    'required' => true,
    'type' => 'textarea',
    'label_input' => 'お問い合わせ<br>内容',
];
$item['同意'] = [
    'required' => true,
    'type' => 'checkbox',
    'list' => ['同意しました'],
    'exclude' => true,
];


/* ---------------------------------------------------------------
  各宛先設定
  - $item で宣言した key (キーの名前) が使えます。
    'メールアドレス' というキーにメールアドレスを入力する想定ならば、
    to や from に {{ メールアドレス }} と設定すると入力値を当てはめることができます。
  - {{ timestamp }} はプログラムにて定めているキーワードです。 "$date->format('Ymd') + お問い合わせ通し番号" に置き換わります。
    不要であれば記述をなくしてください。
  - $send[] をループ処理するので、宛先ごとを増減可能。送りたくないならグループごと削除してください。
    - subject, to_email, to_name, from_email, from_name, header, footer は設定するようにしてください。
    - subject, header, footer が不要の際は空欄にしてください。
    - to_name, from_name が不要の際は false にしてください。
--------------------------------------------------------------- */

$send = [
    // 管理者宛
    'admin' => [
        // 題名
        'subject' => '【' . $setting['name'] . '】' . $setting['title'] . 'が届きました [No.{{ timestamp }}]',

        // 送り先のメールアドレス（複数指定の場合は , で区切る）
        'to_email' => $setting['email'],

        // 送り先の名前
        'to_name' => $setting['name'],

        // 送り主のメールアドレス
        'from_email' => '{{ メールアドレス }}',

        // 送り主の名前
        'from_name' => '{{ お名前 }}',

        // 返信先のメールアドレス (必要があれば)
        // 'replyto_email' => $send['admin']['from_email'],

        // 返信先の名前 (必要があれば)
        // 'replyto_name' => $send['admin']['from_name'],

        // TODO: 未実装
        // 非公開の送り先のメールアドレス (必要があれば)
        // 'bbc_email' => '',

        // ヘッダー
        'header' => '以下の通り「' . $setting['title'] . '」を受付致しました。 [No.{{ timestamp }}]' . PHP_EOL .
            PHP_EOL .
            '////////////////////////////////////////////////////////' . PHP_EOL .
            PHP_EOL,

        // フッター
        'footer' => PHP_EOL .
            PHP_EOL .
            '////////////////////////////////////////////////////////' . PHP_EOL .
            PHP_EOL .
            '内容は以上になります。' . PHP_EOL .
            'ご確認をお願いいたします。',
    ],

    // ユーザー宛設定
    'user' => [
        // 題名
        'subject' => '【' . $setting['name'] . '】' . $setting['title'] . 'を承りました（自動配信メール） [No.{{ timestamp }}]',

        // 送り先のメールアドレス
        'to_email' => '{{ メールアドレス }}',

        // 送り先の名前
        'to_name' => '{{ お名前 }}',

        // 送り主のメールアドレス
        'from_email' => $setting['email'],

        // 送り主の名前
        'from_name' => $setting['name'],

        // 返信先のメールアドレス (必要があれば)
        // 'replyto_email' => $send['user']['from_email'],

        // 返信先の名前 (必要があれば)
        // 'replyto_name' => $send['user']['from_name'],

        // ヘッダー
        'header' => '{{ お名前 }} 様　[No.{{ timestamp }}]' . PHP_EOL .
            PHP_EOL .
            $setting['name'] . 'です。' . PHP_EOL .
            PHP_EOL .
            'この度は「' . $setting['title'] . '」いただきありがとうございました。' . PHP_EOL .
            '内容を確認次第、折返しご連絡いたします。' . PHP_EOL .
            PHP_EOL .
            '////////////////////////////////////////////////////////' . PHP_EOL .
            PHP_EOL,

        // フッター
        'footer' => PHP_EOL .
            PHP_EOL .
            '////////////////////////////////////////////////////////' . PHP_EOL .
            PHP_EOL .
            '内容は以上になります。' . PHP_EOL .
            PHP_EOL .
            '--------------------------------------------------------' . PHP_EOL .
            $setting['name'] . '　https://www.example.com/' . PHP_EOL .
            '〒000-0000 〇〇県□□市△△区11-22' . PHP_EOL .
            'TEL:000-0000-0000　FAX:000-0000-0000' . PHP_EOL .
            '--------------------------------------------------------' . PHP_EOL .
            '※本メールは、自動配信されたものです。' . PHP_EOL,
    ],
];


/* ---------------------------------------------------------------
  テンプレート設定
  - 無効にする場合は、空欄にします。
--------------------------------------------------------------- */
// 未入力の場合
$template['blank'] = '（未入力）';

// 必須 {{ required }} に展開される
$template['required'] = '<span style="font-size: 12px; padding: 5px; line-height: 1; background-color: red; color: #fff;">必須</span>';

// 任意 {{ required }} に展開される
$template['any'] = '<span style="font-size: 12px; padding: 5px; line-height: 1; background-color: green; color: #fff;">任意</span>';

// エラー {{ error_body }} に展開される
$template['error_body'] = '<div style="margin-top: 10px; padding: 5px; background-color: red; color: #fff;">{{ error_text }}</div>';

// メール本文のテンプレート
$template['send_return_body'] = '【{{ label }}】' . PHP_EOL . '{{ value }}' . PHP_EOL . PHP_EOL;

// 確認画面のテンプレート
$template['confirm_return_body'] = '
    <div class="form-group  row">
        <span class="col-sm-2  col-form-label">
            {{ required }}
            {{ label }}
        </span>
        <div class="col-sm-10">
            {{ value }}
        </div>
    </div>
';

// 入力画面のテンプレート
$template['edit_return_body'] = '
    <div class="form-group  row">
        <label class="col-sm-2  col-form-label" for="{{ id }}">
            {{ required }}
            {{ label }}
        </label>
        <div class="col-sm-10">
            {{ tag }}
            {{ error_body }}
            {{ add_body }}
        </div>
    </div>
';

// 入力画面のテンプレートの {{ tag }} に対応する <input /> タグ
$template['tag_input'] = '<input class="form-control" id="{{ id }}" name="{{ id }}" value="{{ value }}">';

// 入力画面のテンプレートの {{ tag }} に対応する <input /> タグ （type="" が checkbox または radio が対象）
$template['tag_input_multiple'] = '<div class="form-check"><label class="form-check-label"><input class="form-check-input" name="{{ id }}" value="{{ value }}">{{ valueName }}</label></div>';

// 入力画面のテンプレートの {{ tag }} に対応する <select /> タグ
$template['tag_select'] = '<select class="form-control" id="{{ id }}" name="{{ id }}">{{ value }}</select>';

// 入力画面のテンプレートの {{ tag }} に対応する <textarea /> タグ
$template['tag_textarea'] = '<textarea class="form-control" id="{{ id }}" name="{{ id }}">{{ value }}</textarea>';


/* ---------------------------------------------------------------
  本体ファイルで読み込み
--------------------------------------------------------------- */
return [
    'setting' => $setting,
    'smtp' => $smtp,
    'item' => $item,
    'send' => $send,
    'template' => $template
];
