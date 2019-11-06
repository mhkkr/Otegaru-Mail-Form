<?php

/**
 * バリデーションを管理するクラス
 */
class Validation
{
    use Utility;

    /**
     * 同一値であるか
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function confirm(array $form, string $key, string $val)
    {
        $item = Store::getConfig()['item'][$key];
        
        if (isset($item['confirm'])) {
            if ($form[$this->keyFirstWithSettingId($item['confirm'])] !== $val) {
                return '入力値が' . $item['confirm'] . 'と同一である必要があります。';
            }
        } else {
            return '設定で指定がるべき同一値の参照先が不明でした。';
        }
    }

    /**
     * 空欄である必要がある
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function empty(array $form, string $key, string $val)
    {
        if (!$this->isEmpty($val, true)) {
            return '入力してはいけません。';
        }
    }

    /**
     * 文字列であるか
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function text(array $form, string $key, string $val)
    {
        if (!is_string($val)) {
            return '文字列ではないようです。';
        }
    }

    /**
     * ひらがなのみ
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function hira(array $form, string $key, string $val)
    {
        if (!preg_match('/\A[ぁ-ゞ]+\z/u', $val)) {
            return 'ひらがなのみにしてください。';
        }
    }

    /**
     * カタカナのみ
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function kana(array $form, string $key, string $val)
    {
        if (!preg_match('/\A[ァ-ヴ][ァ-ヴー・]+\z/', $val)) {
            return 'カタカナのみにしてください。';
        }
    }

    /**
     * URL
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function url(array $form, string $key, string $val)
    {
        if (!(filter_var($val, FILTER_VALIDATE_URL) && preg_match('@^https?+://@i', $val))) {
            return 'URLの形式ではないようです。';
        }
    }

    /**
     * メールアドレス
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function email(array $form, string $key, string $val)
    {
        if (!$this->isValidEmail($val)) {
            return 'メールアドレスの形式ではないようです。';
        }
    }

    /**
     * 郵便番号 1234567 or 123-4567
     *          7桁の数字 もしくは 3桁の数字＋ハイフン＋7桁の数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function zip(array $form, string $key, string $val)
    {
        if (!(preg_match('/\A\d{7}\z/', $val) || preg_match('/\A\d{3}\-\d{4}\z/', $val))) {
            return '郵便番号の形式ではないようです。';
        }
    }

    /**
     * 郵便番号 7桁の数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function zipNumber(array $form, string $key, string $val)
    {
        if (!preg_match('/\A\d{7}\z/', $val)) {
            return '数字のみの7桁にしてください。';
        }
    }

    /**
     * 郵便番号 3桁の数字＋ハイフン＋7桁の数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function zipStrict(array $form, string $key, string $val)
    {
        if (!preg_match('/\A\d{3}\-\d{4}\z/', $val)) {
            return '000-0000の形式にしてください。';
        }
    }

    /**
     * 郵便番号 3桁の数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function zipA(array $form, string $key, string $val)
    {
        if (!preg_match('/\A\d{3}\z/', $val)) {
            return '数字のみの3桁にしてください。';
        }
    }

    /**
     * 郵便番号 7桁の数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function zipB(array $form, string $key, string $val)
    {
        if (!preg_match('/\A\d{4}\z/', $val)) {
            return '数字のみの4桁にしてください。';
        }
    }

    /**
     * 電話番号 10桁～13桁までの数字 ハイフンを含んでも可
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function tel(array $form, string $key, string $val)
    {
        if (!(preg_match('/\A[0-9\-]+\z/', $val) && ((strlen($val) === 10) || (strlen($val) === 11) || (strlen($val) === 12) || (strlen($val) === 13)))) {
            return '電話番号の形式ではないようです。';
        }
    }

    /**
     * 電話番号 10桁～13桁までの数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function telNumber(array $form, string $key, string $val)
    {
        if (!(preg_match('/\A[0-9]+\z/', $val) && ((strlen($val) === 10) || (strlen($val) === 11) || (strlen($val) === 12) || (strlen($val) === 13)))) {
            return '数字のみの10桁～13桁にしてください。';
        }
    }

    /**
     * 電話番号 10桁～13桁までの数字 ハイフンを含まなければならない
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function telStrict(array $form, string $key, string $val)
    {
        if (!(preg_match('/\A\d{2,4}+-\d{2,4}+-\d{4}\z/', $val) && ((strlen($val) === 10) || (strlen($val) === 11) || (strlen($val) === 12) || (strlen($val) === 13)))) {
            return '0000-0000-0000の形式にしてください。';
        }
    }

    /**
     * 電話番号 2桁～4桁までの数字
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function telAbc(array $form, string $key, string $val)
    {
        if (!preg_match('/\A\d{2,4}\z/', $val)) {
            return '数字のみの2～4文字にしてください。';
        }
    }

    /**
     * 数字のみ 正規表現を使用
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function number(array $form, string $key, string $val)
    {
        if (!preg_match('/\A[0-9]+\z/', $val)) {
            return '数字のみにしてください。';
        }
    }

    /**
     * 数字のみ is_numeric() を使用
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function numeric(array $form, string $key, string $val)
    {
        if (!is_numeric($val)) {
            return '数字のみにしてください。';
        }
    }

    /**
     * 数字のみ is_int() を使用
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function int(array $form, string $key, string $val)
    {
        if (!is_int($val)) {
            return '数字のみにしてください。';
        }
    }

    /**
     * 数字のみ ctype_digit() を使用
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    public function ctypeDigit(array $form, string $key, string $val)
    {
        if (!ctype_digit($val)) {
            return '数字のみにしてください。';
        }
    }

    /**
     * メールアドレスのバリデーション
     * 
     * 参考:
     * @link https://qiita.com/mpyw/items/346f1789ad0e1b969ebc#2015811-%E8%BF%BD%E8%A8%98-1
     *
     * @param  string $email
     * @param  bool   $check_dns
     * @return bool
     */
    public function isValidEmail(string $email, bool $check_dns = false)
    {
        function OtegaruMailFormValidationFilterEmail(string $email)
        {
            if (version_compare(phpversion(), '7.1.0', '>=')) {
                return filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE);
            } else {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            }
        }
        switch (true) {
            case false === OtegaruMailFormValidationFilterEmail($email):
            case !preg_match('/@([^@\[]++)\z/', $email, $m):
                return false;
            case !$check_dns:
            case checkdnsrr($m[1], 'MX'):
            case checkdnsrr($m[1], 'A'):
            case checkdnsrr($m[1], 'AAAA'):
                return true;
            default:
                return false;
        }
    }
}
