<?php

namespace OtegaruMailForm\Src;

/**
 * ユーティリティークラス
 */
trait Utility
{
    /**
     * キーの名前にフォームの識別名を先頭に付けて返す
     * @param  string $key  
     * @return string
     */
    public function keyFirstWithSettingId(string $key)
    {
        if (isset($key) && !$this->isEmpty($key) && strpos($key, Store::getConfig()['setting']['id']) === false) {
            return Store::getConfig()['setting']['id'] . '_' . $key;
        }
        return $key;
    }

    /**
     * 設定で定義した通りのキーの名前を返す
     * @param  string $key  
     * @return string
     */
    public function keyDefinedName(string $key)
    {
        if (isset($key) && !$this->isEmpty($key)) {
            return str_replace(Store::getConfig()['setting']['id'] . '_', '', $key);
        }
        return $key;
    }

    /**
     * 空である
     * @param  string|array $test        テストする値
     * @param  bool         $compare_flag 空白と比較する式のフラグ
     * @return bool
     */
    public function isEmpty($test, Bool $compare_flag = false)
    {
        if (is_array($test)) {
            $array_return = true;
            foreach ($test as $array) {
                $array_return = $this->isEmpty($array, $compare_flag);
                if (!$array_return) {
                    return $array_return;
                }
            }
        } else {
            if ($compare_flag) {
                return trim($test) === '';
            } else {
                return empty(trim($test));
            }
        }
        return true;
    }

    /**
     * 特殊文字をHTMLエンティティに変換する
     * @param  string $str
     * @return string
     */
    public function h(string $str)
    {
        return htmlspecialchars($str, ENT_QUOTES, Store::getConfig()['setting']['charset']);
    }
}
