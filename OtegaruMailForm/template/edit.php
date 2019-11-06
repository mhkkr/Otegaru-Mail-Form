<p>
    お問い合わせ専用フォームです。<br>
    ご質問・ご相談などありましたらどうぞお気軽にご連絡ください。<br>
    後日メールまたはお電話にて返信いたします。
</p>

<form action="{{ OtegaruMailForm.confirm_url }}" method="post" name="{{ OtegaruMailForm.id }}" novalidate>
    <input type="hidden" name="token" value="{{ OtegaruMailForm.token }}">
    <input type="hidden" name="submit" value="confirm">

    <?= $template('edit', [
        'key' => 'ボット対策',
        'return_body' => '
            <div style="display: none;">
                <input name="{{ id }}" value="{{ value }}">
                {{ error_body }}
            </div>
        ',
    ]); ?>

    <?= $template('edit', [
        'key' => '法人名',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => '株式会社○○',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => 'お名前',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => 'おてがるめーるふぉーむ',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => 'メールアドレス',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => 'example@exsample.com',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => 'メールアドレス確認',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => 'example@exsample.com',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => '電話番号',
        'attribute' => [
            'maxlength' => '14',
            'placeholder' => '0000-0000-0000',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => '数字',
        'attribute' => [
            'max' => '10',
            'min' => '0',
            'placeholder' => '数字のみ入力してください',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => '数字バリデーションなし',
        'attribute' => [
            'max' => '10',
            'min' => '0',
            'placeholder' => '数字のみ入力してください',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => 'チェックボックス',
    ]); ?>

    <?= $template('edit', [
        'key' => 'ラジオボタン',
    ]); ?>

    <?php
    $pref = $template('edit', [
        'key' => '都道府県',
        'return_body' => '
        <div style="margin-top: 10px;">
          {{ tag }}
          {{ error_body }}
        </div>
      ',
    ]);
    $addr1 = $template('edit', [
        'key' => '市区町村・番地',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => 'ビル・マンションなど',
        ],
        'return_body' => '
        <div style="margin-top: 10px;">
          {{ tag }}
          {{ error_body }}
        </div>
      ',
    ]);
    $addr2 = $template('edit', [
        'key' => '建物名・号室',
        'attribute' => [
            'maxlength' => '100',
            'placeholder' => '市町村区番地',
        ],
        'return_body' => '
        <div style="margin-top: 10px;">
          {{ tag }}
          {{ error_body }}
        </div>
      ',
    ]);
    echo $template('edit', [
        'key' => '郵便番号',
        'label' => 'ご住所',
        'attribute' => [
            'maxlength' => '8',
            'placeholder' => '000-0000',
        ],
        'add_body' => $pref . $addr1 . $addr2,
    ]);
    ?>

    <?= $template('edit', [
        'key' => 'お問い合わせ内容',
        'attribute' => [
            'placeholder' => 'お問い合わせ内容をご入力ください。',
        ],
    ]); ?>

    <?= $template('edit', [
        'key' => '同意',
    ]); ?>

    <div class="form-group  row">
        <div class="col-sm-10">
            <button class="btn btn-primary">
                確認する
            </button>
        </div>
    </div>
</form>