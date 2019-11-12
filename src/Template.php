<?php

namespace OtegaruMailForm\Src;

/**
 * テンプレートを管理するクラス
 */
class Template
{
    use Utility;

    /** @var array QUEUE_LIST キーワードの置き換えキューのリストを定義する */
    const QUEUE_LIST = [
        'BaseInput',
        'BaseOutput',
        'Required',
        'Error',
        'Id',
        'Label',
        'AddBody',
        'Final',
    ];

    /**
     * 入力、確認、メールの HTML / 本文 のテンプレートを展開する
     * @param  string $mode
     * @param  array  $options
     * @return string キーワードが置き換えられた HTML / 本文
     */
    public function generate(string $mode, array $options)
    {
        // 必須の引数を確認。 mode と $options['key'] が未定義の場合は空文字を返す。
        if (!(isset($mode) && !$this->isEmpty($mode) && isset($options['key']) && !$this->isEmpty($options['key']))) {
            return '';
        }
        $keyId = $this->keyFirstWithSettingId($options['key']);
        $keyOrigin = $this->keyDefinedName($options['key']);

        $config = Store::getConfig();

        // 項目が定義されているかを確認。未定義の場合は空文字を返す。
        if (!isset($config['item'][$keyId])) {
            Store::setVerifyItemDefined($keyId, $keyOrigin);
            return '';
        }

        $body = $this->baseTemplate($mode, $options);
        $body = $this->queue($body, $mode, $options);
        return $this->retrunBody($body, $mode, $options);
    }

    /**
     * HTML / 本文 の基礎を取得する
     * @param  string $mode
     * @param  array  $options
     * @return string
     */
    protected function baseTemplate(string $mode, array $options)
    {
        $config = Store::getConfig();

        if (isset($options['return_body']) && !$this->isEmpty($options['return_body'])) {
            return $options['return_body'];
        } else {
            if ($mode === 'edit') {
                return $config['template']['edit_return_body'];
            } elseif ($mode === 'confirm') {
                return $config['template']['confirm_return_body'];
            } elseif ($mode === 'send') {
                return $config['template']['send_return_body'];
            }
        }
    }

    /**
     * キーワードの置き換えキュー
     * @param  string $mode
     * @param  array  $options
     * @return string
     */
    protected function queue(string $body, string $mode, array $options)
    {
        foreach (static::QUEUE_LIST as $key) {
            $method_name = 'replace' . trim($key);
            if (method_exists($this, $method_name)) {
                $body = $this->$method_name($body, $mode, $options);
            }
        }
        return $body;
    }

    /**
     * キーワードの置き換え: {{ tag }}, {{ value }}, {{ attribute }}
     * for input
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceBaseInput(string $body, string $mode, array $options)
    {
        $keyId = $this->keyFirstWithSettingId($options['key']);
        $keyOrigin = $this->keyDefinedName($options['key']);

        $config = Store::getConfig();
        $input = Store::getFormInput();

        $item = $config['item'][$keyId];

        if ($mode !== 'edit') {
            return $body;
        }

        // セッションからフォーム値を取得した際、インデックスに相違があるとエラーが出るため一度確認する
        $input_value = '';
        if ((Store::getPostSubmit() === 'rewrite' || Store::getPostSubmit() === 'confirm') && isset($input[$keyId])) {
            $input_value = $input[$keyId];
        }

        // TODO: このスイッチも動的メソッドで呼び出すように変更する
        // TODO: partsReplaceBaseInputTypeHome というメソッド名にする？
        switch ($item['type']) {
            case 'hidden':
            case 'text':
            case 'search':
            case 'tel':
            case 'url':
            case 'email':
            case 'password':
            case 'datetime':
            case 'date':
            case 'month':
            case 'week':
            case 'time':
            case 'datetime-local':
            case 'number':
            case 'range':
            case 'color':
                $tag_name = 'input';

                // テンプレートタグを呼び出し、{{ tag }} と置き換える
                $body = str_replace('{{ tag }}', $item['prefix_input'] . $config['template']['tag_input'] . $item['suffix_input'], $body);

                // HTMLを解析し、 type 属性を追加する
                $dom = $this->domInstanceHtmlParse($body, '//' . $tag_name);
                for ($i = 0; $i < $dom[1]->length; $i++) {
                    $tag = $dom[1]->item($i);
                    $tag->removeAttribute('type');
                    $tag->setAttribute('type', $item['type']);
                }
                $body = $this->domContainerRemoveHtmlSave($dom[0]);

                // 入力値を追加する
                $body = str_replace('{{ value }}', $this->h($input_value), $body);
                break;
            case 'file':
                // TODO: 未実装
                break;
            case 'checkbox':
                $tag_name = 'input';
                $body_list = '';

                // テンプレートタグを呼び出し、入力値を追加する
                foreach ($item['list'] as $key => $val) {
                    $v = $item['value_in_val'] ? $val : $key;
                    $tag = $config['template']['tag_input_multiple'];
                    $tag = str_replace('{{ value }}', $v, $tag);
                    $tag = str_replace('{{ valueName }}', $item['prefix_input'] . $val . $item['suffix_input'], $tag);
                    $body_list .= $tag;
                }

                // 配列を文字連結し、含まれているかの比較を容易にするために前後にカンマをつける
                $input_value = is_array($input_value) ? implode(',', $input_value) : $input_value;
                $input_value = ',' . $input_value . ',';

                // HTMLを解析し、 type 属性と checked 属性を追加する
                $dom = $this->domInstanceHtmlParse($body_list, '//' . $tag_name);
                for ($i = 0; $i < $dom[1]->length; $i++) {
                    $tag = $dom[1]->item($i);
                    $tag->removeAttribute('type');
                    $tag->setAttribute('type', $item['type']);

                    if ($tag->hasAttribute('value')) {
                        $val = $tag->getAttribute('value');
                        if (strpos($input_value, ',' . $val . ',') !== false) {
                            $tag->removeAttribute('checked');
                            $tag->setAttribute('checked', 'checked');
                        }
                    }
                }
                $body_list = $this->domContainerRemoveHtmlSave($dom[0]);

                // すべて未チェックの場合に $_POST に拾わせる用
                $body_list = '<input type="hidden" name="' . $keyId . '" value="">' . $body_list;

                // {{ tag }} と置き換える
                $body = str_replace('{{ tag }}', $body_list, $body);
                break;
            case 'radio':
                $tag_name = 'input';
                $body_list = '';

                // テンプレートタグを呼び出し、入力値を追加する
                foreach ($item['list'] as $key => $val) {
                    $v = $item['value_in_val'] ? $val : $key;
                    $tag = $config['template']['tag_input_multiple'];
                    $tag = str_replace('{{ value }}', $v, $tag);
                    $tag = str_replace('{{ valueName }}', $item['prefix_input'] . $val . $item['suffix_input'], $tag);
                    $body_list .= $tag;
                }

                // HTMLを解析し、 type 属性と checked 属性を追加する
                $dom = $this->domInstanceHtmlParse($body_list, '//' . $tag_name);
                for ($i = 0; $i < $dom[1]->length; $i++) {
                    $tag = $dom[1]->item($i);
                    $tag->removeAttribute('type');
                    $tag->setAttribute('type', $item['type']);

                    if ($tag->hasAttribute('value')) {
                        $val = $tag->getAttribute('value');
                        if ($input_value === $val) {
                            $tag->removeAttribute('checked');
                            $tag->setAttribute('checked', 'checked');
                        }
                    }
                }
                $body_list = $this->domContainerRemoveHtmlSave($dom[0]);

                // すべて未チェックの場合に $_POST に拾わせる用
                $body_list = '<input type="hidden" name="' . $keyId . '" value="">' . $body_list;

                // {{ tag }} と置き換える
                $body = str_replace('{{ tag }}', $body_list, $body);
                break;
            case 'select':
                $tag_name = 'select';
                $body_list = '';

                // テンプレートタグを呼び出し、{{ tag }} と置き換える
                $body = str_replace('{{ tag }}', $config['template']['tag_select'], $body);

                // 指定がある場合、先頭に NULL 扱いの値を追加する
                if ($item['select_first_null']) {
                    $body_list = '<option label="' . $item['select_first_null'] . '" value="">' . $item['select_first_null'] . '</option>';
                }

                // option タグ生成
                foreach ($item['list'] as $key => $val) {
                    $v = $item['value_in_val'] ? $val : $key;
                    $str = $item['prefix_input'] . $val . $item['suffix_input'];
                    $body_list .= '<option label="' . $str . '" value="' . $v . '" ' . ($input_value === (string) $v ? 'selected="selected"' : '') . '>' . $str . '</option>';
                }

                // 入力値を追加する
                $body = str_replace('{{ value }}', $body_list, $body);
                break;
            case 'textarea':
                $tag_name = 'textarea';

                // テンプレートタグを呼び出し、{{ tag }} と置き換える
                $body = str_replace('{{ tag }}', $item['prefix_input'] . $config['template']['tag_textarea'] . $item['suffix_input'], $body);

                // 入力値を追加する
                $body = str_replace('{{ value }}', $this->h($input_value), $body);
                break;
        }

        Store::setVerifyItemTag($keyId, $keyOrigin);

        // 属性 {{ attribute }}
        if (isset($options['attribute']) && !$this->isEmpty($options['attribute']) && is_array($options['attribute']) && $tag_name) {
            $dom = null;
            $dom = $this->domInstanceHtmlParse($body, '//' . $tag_name);
            for ($i = 0; $i < $dom[1]->length; $i++) {
                $tag = $dom[1]->item($i);
                foreach ($options['attribute'] as $attribute_key => $attribute_val) {
                    if (!$this->isEmpty($attribute_key)) {
                        $tag->removeAttribute($attribute_key);
                        $tag->setAttribute($attribute_key, $attribute_val);
                    }
                }
            }
            $body = $this->domContainerRemoveHtmlSave($dom[0]);
        }

        return $body;
    }

    /**
     * キーワードの置き換え: {{ tag }}, {{ value }}, {{ attribute }}
     * for output
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceBaseOutput(string $body, string $mode, array $options)
    {
        $keyId = $this->keyFirstWithSettingId($options['key']);
        $keyOrigin = $this->keyDefinedName($options['key']);

        $config = Store::getConfig();
        $output = Store::getFormOutput();

        // ※このメソッドだけ [$keyId] の index まで取得しないので注意
        $item = $config['item'];

        if (!($mode === 'confirm' || $mode === 'send')) {
            return $body;
        }

        $output_value = '';

        // TODO: partsReplaceBaseOutput というメソッド名にする？
        /**
         * ['label_output'] を配慮したクロージャ―
         * @param  array        $item
         * @param  string|array $output
         * @return string       $output_value
         */
        $base = function (array $item, $output) {
            $output_value = '';
            if ($item['type'] === 'checkbox' || $item['type'] === 'radio' || $item['type'] === 'select') {
                $array_val = (array) $output;
                foreach ($array_val as $val) {
                    // セレクトの場合で先頭が NULL 扱いではないなら、値が 0 の場合はスキップする
                    if ($item['type'] === 'select' && !$item['select_first_null'] && $val === '0') {
                        continue;
                    }
    
                    $fix_val = '';
    
                    if ($item['value_in_val']) {
                        if (!$this->isEmpty($val, true)) {
                            $fix_val = $val;
                        } else {
                            continue;
                        }
                    } else {
                        if (isset($item['list'][$val])) {
                            $fix_val = $item['list'][$val];
                        } else {
                            continue;
                        }
                    }
    
                    $output_value .= (!$this->isEmpty($output_value) ? $item['multiple_delimiter'] : '') .
                        $item['prefix_output'] . $fix_val . $item['suffix_output'];
                }
            } else {
                if (!$this->isEmpty($output, true)) {
                    $output_value = $item['prefix_output'] . $output . $item['suffix_output'];
                }
            }
            return $output_value;
        };

        // ['label_output'] が同一の場合の {{ value }} の連結
        if (isset($item[$keyId]['label_output']) && !$this->isEmpty($item[$keyId]['label_output'], true)) {
            foreach ($config['item'] as $key => $val) {
                if ($val['label_output'] === $item[$keyId]['label_output']) {
                    $output_value .= $base($item[$key], $output[$key]);
                }
            }
        } else {
            $output_value = $base($item[$keyId], $output[$keyId]);
        }

        // 未入力であった場合
        if ($this->isEmpty($output_value, true)) {
            $output_value = $config['template']['blank'];
        }

        if ($mode === 'confirm') {
            $body = str_replace('{{ value }}', nl2br($this->h($output_value)), $body);
        } elseif ($mode === 'send') {
            $body = str_replace('{{ value }}', $output_value, $body);
        }

        return $body;
    }

    /**
     * キーワードの置き換え: {{ required }}
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceRequired(string $body, string $mode, array $options)
    {
        $keyId = $this->keyFirstWithSettingId($options['key']);

        $config = Store::getConfig();
        $item = $config['item'][$keyId];

        if ($item['required']) {
            $body = str_replace('{{ required }}', $config['template']['required'], $body);

            // タグ内に required="" を埋め込む
            if ($mode === 'edit') {
                $body = preg_replace('/<(input|select|textarea)/', '<$1 required', $body);
            }
        }
        $body = str_replace('{{ required }}', $config['template']['any'], $body);

        return $body;
    }

    /**
     * キーワードの置き換え: {{ error_body }}, {{ error_text }}
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceError(string $body, string $mode, array $options)
    {
        $keyId = $this->keyFirstWithSettingId($options['key']);

        $config = Store::getConfig();
        $error = Store::getFormError();

        // セッションからフォーム値を取得した際、インデックスに相違があるとエラーが出るため一度確認する
        if (isset($error[$keyId]) && $error[$keyId]) {
            $error_text = $error[$keyId];
            if (isset($options['error_body'])) {
                $error_body = str_replace('{{ error_text }}', $error_text, $options['error_body']);
            } elseif (isset($config['template']['error_body']) && !$this->isEmpty($config['template']['error_body'])) {
                $error_body = str_replace('{{ error_text }}', $error_text, $config['template']['error_body']);
            } else {
                $error_body = $error_text;
            }
            $body = str_replace('{{ error_body }}', $error_body, $body);
        }
        return $body;
    }

    /**
     * キーワードの置き換え: {{ id }}
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceId(string $body, string $mode, array $options)
    {
        $keyId = $this->keyFirstWithSettingId($options['key']);

        $config = Store::getConfig();
        $item = $config['item'][$keyId];

        if ($mode === 'edit' && $item['type'] === 'checkbox') {
            $body = str_replace('{{ id }}', $keyId . '[]', $body);
        } else {
            $body = str_replace('{{ id }}', $keyId, $body);
        }

        return $body;
    }

    /**
     * キーワードの置き換え:  {{ label }}
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceLabel(string $body, string $mode, array $options)
    {
        if (isset($options['label']) && !$this->isEmpty($options['label'])) {
            $body = str_replace('{{ label }}', $options['label'], $body);
        } else {
            $keyId = $this->keyFirstWithSettingId($options['key']);
            $keyOrigin = $this->keyDefinedName($options['key']);

            $config = Store::getConfig();
            $item = $config['item'][$keyId];

            $label = $keyOrigin;
            if ($mode === 'edit') {
                if (isset($item['label_input']) && !$this->isEmpty($item['label_input'])) {
                    $label = $item['label_input'];
                }
            } elseif ($mode === 'confirm') {
                if (isset($item['label_input']) && !$this->isEmpty($item['label_input'])) {
                    $label = $item['label_input'];
                } elseif (isset($item['label_output']) && !$this->isEmpty($item['label_output'])) {
                    $label = $item['label_output'];
                }
            } elseif ($mode === 'send') {
                if (isset($item['label_output']) && !$this->isEmpty($item['label_output'])) {
                    $label = $item['label_output'];
                }
            }
            $body = str_replace('{{ label }}', $label, $body);
        }
        return $body;
    }

    /**
     * キーワードの置き換え: {{ add_body }}
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceAddBody(string $body, string $mode, array $options)
    {
        if (isset($options['add_body']) && !$this->isEmpty($options['add_body'])) {
            $body = str_replace('{{ add_body }}', $options['add_body'], $body);
        }
        return $body;
    }

    /**
     * キーワードの置き換え: 最終処理
     * 置き換わらなかったすべての {{ hoge }} を空欄置換で削除する
     * @param  string $body
     * @param  string $mode
     * @param  array  $options
     * @return string $body
     */
    protected function replaceFinal(string $body, string $mode, array $options)
    {
        return preg_replace('/{{\s[^}]*\s}}/', '', $body);
    }

    /**
     * Body を返すか判断する
     * @param  string $mode
     * @param  array  $options
     * @return string $body or 空欄
     */
    protected function retrunBody(string $body, string $mode, array $options)
    {
        $isReturn = true;

        $keyId = $this->keyFirstWithSettingId($options['key']);

        $config = Store::getConfig();
        $item = $config['item'][$keyId];

        // ['label_output'] が同一の場合の {{ value }} の連結
        if (($mode === 'confirm' || $mode === 'send') && isset($item['label_output']) && !$this->isEmpty($item['label_output'])) {
            $i = 0;
            $first = false;
            foreach ($config['item'] as $key => $val) {
                if ($val['label_output'] === $item['label_output']) {
                    // 配列の中の同一の ['label_output'] で一番最初に出現する項目を特定する
                    if (!$first && $key === $keyId && $i === 0) {
                        $first = true;
                    }

                    // 最初でない場合 body を返さないように制限する
                    if (!$first) {
                        $isReturn = false;
                    }

                    $i++;
                }
            }
        }

        if ($isReturn) {
            return $body;
        } else {
            return '';
        }
    }

    /**
     * DOM インスタンスの生成と HTML を解析する
     * @param  string $html       文字列(HTML 形式)
     * @param  string $expression 実行する XPath 式
     * @return array              [object, object]
     */
    protected function domInstanceHtmlParse($html, $expression)
    {
        $dom = new \DOMDocument();
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', Store::getConfig()['setting']['charset']));

        $xpath = new \DOMXPath($dom);
        $html = $xpath->evaluate($expression);

        return [$dom, $html];
    }

    /**
     * DOM インスタンスを保存し、ラッパーされる <DOCTYPE>, <html>, <body> タグを削除する
     * @param  Object 
     * @return string
     */
    protected function domContainerRemoveHtmlSave($dom)
    {
        return preg_replace(
            '/^<!DOCTYPE.+?>/',
            '',
            str_replace(
                ['<html>', '</html>', '<body>', '</body>'],
                ['', '', '', ''],
                $dom->saveHTML()
            )
        );
    }
}
