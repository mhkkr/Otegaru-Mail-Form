<?php

/**
 * 状態を管理するクラス
 */
class Store
{
    /** @var array $verify_config 警告用の検査配列 全設定 */
    private static $verify_config = [];

    /** @var array $verify_item_defined 警告用の検査配列 項目の設定定義 */
    private static $verify_item_defined = [];

    /** @var array $verify_item_tag 警告用の検査配列 項目のタグ出力 */
    private static $verify_item_tag = [];

    /** @var array $config 設定 */
    private static $config = [];

    /** @var array $input 入力値 */
    private static $input = [];

    /** @var array $output 出力値 */
    private static $output = [];

    /** @var array $error エラー値 */
    private static $error = [];

    /** @var string $submit_name 送信モード名 */
    private static $submit_name = '';

    /** @var string $screen_name スクリーン名 */
    private static $screen_name = '';

    /**
     * 設定の検査結果を保存する
     * @param string $key
     * @param string $name
     */
    public static function setVerifyConfig(string $key, string $name)
    {
        static::$verify_config[$key] = $name;
    }

    /**
     * 設定の検査結果を取得する
     * @return array
     */
    public static function getVerifyConfig()
    {
        return static::$verify_config;
    }

    /**
     * 項目の未定義の検査結果を保存する
     * @param string $key
     * @param string $name
     */
    public static function setVerifyItemDefined(string $key, string $name)
    {
        static::$verify_item_defined[$key] = $name;
    }

    /**
     * 項目の未定義の検査結果を取得する
     * @return array
     */
    public static function getVerifyItemDefined()
    {
        return static::$verify_item_defined;
    }

    /**
     * 項目のタグ記述の検査結果を保存する
     * @param string $key
     * @param string $name
     */
    public static function setVerifyItemTag(string $key, string $name)
    {
        static::$verify_item_tag[$key] = $name;
    }

    /**
     * 項目のタグ記述の検査結果を取得する
     * @return array
     */
    public static function getVerifyItemTag()
    {
        return static::$verify_item_tag;
    }

    /**
     * 設定を保存する
     * @param string $key
     * @param array  $config
     */
    public static function setConfig(string $key, array $config)
    {
        static::$config[$key] = $config;
    }

    /**
     * 設定を取得する
     * @return array
     */
    public static function getConfig()
    {
        return static::$config;
    }

    /**
     * 入力値を保存する
     * @param string       $key
     * @param string|array $input
     */
    public static function setFormInput(string $key, $input)
    {
        static::$input[$key] = $input;
    }

    /**
     * 入力値を取得する
     * @return array
     */
    public static function getFormInput()
    {
        return static::$input;
    }

    /**
     * 出力値を保存する
     * @param string       $key
     * @param string|array $output
     */
    public static function setFormOutput(string $key, $output)
    {
        static::$output[$key] = $output;
    }

    /**
     * 出力値を取得する
     * @return array
     */
    public static function getFormOutput()
    {
        return static::$output;
    }

    /**
     * エラー値を保存する
     * @param string       $key
     * @param string|array $error
     */
    public static function setFormError(string $key, $error)
    {
        static::$error[$key] = $error;
    }

    /**
     * エラー値を取得する
     * @return array
     */
    public static function getFormError()
    {
        return static::$error;
    }

    /**
     * 送信モード名を保存する
     * @param string $submit_name
     */
    public static function setSubmitName(string $submit_name)
    {
        static::$submit_name = $submit_name;
    }

    /**
     * 送信モード名を取得する
     * @return string
     */
    public static function getSubmitName()
    {
        return static::$submit_name;
    }

    /**
     * スクリーン名を保存する
     * @param string $screen_name
     */
    public static function setScreenName(string $screen_name)
    {
        static::$screen_name = $screen_name;
    }

    /**
     * スクリーン名を取得する
     * @return string
     */
    public static function getScreenName()
    {
        return static::$screen_name;
    }
}
