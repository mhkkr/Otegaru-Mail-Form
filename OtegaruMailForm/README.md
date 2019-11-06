# Otegaru Mail Form
PHP Contact Mail Form

## テンプレート例

```edit.php<?= $template('edit', [
    'key' => 'お名前',
    'label' => 'おなまえ',
    'attribute' => [
        'maxlength' => '100',
        'placeholder' => '山田　太郎',
    ],
    'return_body' => '
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
    ',
    'error_body' => '<p>{{ error_text }}</p>',
    'add_body' => '<p>hoge</p>',
]); ?>```

```例：confirm.php<?= $template('例：confirm', [
    'key' => 'お名前',
    'label' => 'おなまえ',
    'return_body' => '
        <div class="form-group  row">
            <span class="col-sm-2  col-form-label">
                {{ required }}
                {{ label }}
            </span>
            <div class="col-sm-10">
                {{ value }}
            </div>
        </div>
    ',
]); ?>```


## 今後の予定
- ドキュメント整備
- 良さげな CSS と JS を付け加える
- &lt;optgroup&gt; に対応する
- type="file" の対応
- 設定チェックの強化
- BCC 対応


