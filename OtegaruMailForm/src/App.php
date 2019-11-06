<?php

/**
 * Otegaru Mail Form 本体ファイル
 *
 * 自分用に使っていたお問い合わせフォームを、
 * 使い回しが可能で、汎用性の高いテンプレートとして改修しました。
 * Qdmail + Qdsmtp のライブラリを使用して送信しています。
 *
 * PHP 7.0 Over
 * 
 * @copyright Copyright 2019 mhkkr
 * @link      https://github.com/mhkkr/Otegaru-Mail-Form
 *            https://github.com/mhkkr/Functions-Form (Old version)
 * @version   1.0.0
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

/**
 * 本体クラス
 */
class App
{
    use Utility;

    /** 
     * @var array SUPPORT_FILE_LIST サポートファイルのリストを定義する
     * [
     *     ファイルパス, コールバックメソッド
     *     [__DIR__ . 'hoge.txt', 'Hoge']
     * ]
     */
    const SUPPORT_FILE_LIST = [];

    public function __construct()
    {
        $config = require(__DIR__ . '/../config.php');

        if ($this->verifyConfig($config)) {
            Store::setConfig('setting', $config['setting']);
            Store::setConfig('smtp', isset($config['smtp']) ? $config['smtp'] : []);
            Store::setConfig('item', $this->updateItemSetup($config['item']));
            Store::setConfig('send', $config['send']);
            Store::setConfig('template', $config['template']);

            $this->viewController();
        }
    }

    /**
     * 必要な設定が抜けていないか検査する
     * @param  array $config
     * @return bool  $verify
     */
    protected function verifyConfig(array $config)
    {
        $verify = true;

        if (!isset($config['setting']) || isset($config['setting']) && empty($config['setting'])) {
            Store::setVerifyConfig('setting', '全般設定');
            $verify = false;
        }
        if (
            isset($config['smtp']['host']) && $this->isEmpty($config['smtp']['host'])
            || isset($config['smtp']['port']) && $this->isEmpty($config['smtp']['port'])
            || isset($config['smtp']['from']) && $this->isEmpty($config['smtp']['from'])
            || isset($config['smtp']['protocol']) && $this->isEmpty($config['smtp']['protocol'])
            || isset($config['smtp']['user']) && $this->isEmpty($config['smtp']['user'])
            || isset($config['smtp']['password']) && $this->isEmpty($config['smtp']['password'])
        ) {
            Store::setVerifyConfig('smtp', 'SMTP 設定');
            $verify = false;
        }
        if (!isset($config['item']) || isset($config['item']) && empty($config['item'])) {
            Store::setVerifyConfig('item', '項目設定');
            $verify = false;
        }
        if (!isset($config['send']) || isset($config['send']) && empty($config['send'])) {
            Store::setVerifyConfig('send', '各宛先設定');
            $verify = false;
        }
        if (!isset($config['template']) || isset($config['template']) && empty($config['template'])) {
            Store::setVerifyConfig('template', 'テンプレート設定');
            $verify = false;
        }

        if (!$verify) {
            Store::setScreenName('error-config');
        }

        return $verify;
    }

    /**
     * 項目設定をアップデート
     * 設定がなかった配列に初期値を付与する
     * @param  array $item
     * @return array $item
     */
    protected function updateItemSetup(array $item)
    {
        foreach ($item as $key => $val) {
            if (!isset($val['key']) || (isset($val['key']) && $this->isEmpty($val['key']))) {
                $val['key'] = $key;
            }

            if (!isset($val['required']) || (isset($val['required']) && !is_bool($val['required']))) {
                $val['required'] = false;
            }

            if (!isset($val['required_message']) || (isset($val['required_message']) && $this->isEmpty($val['required_message']))) {
                $val['required_message'] = Store::getConfig()['setting']['required_message'];
            }

            if (!isset($val['type']) || (isset($val['type']) && $this->isEmpty($val['type']))) {
                $val['type'] = 'text';
            }

            if (!isset($val['select_first_null']) || (isset($val['select_first_null']) && $this->isEmpty($val['select_first_null']))) {
                $val['select_first_null'] = null;
            }

            if (!isset($val['list']) || (isset($val['list']) && !is_array($val['list']))) {
                $val['list'] = [];
            }

            if (!isset($val['value_in_val']) || (isset($val['value_in_val']) && !is_bool($val['value_in_val']))) {
                $val['value_in_val'] = false;
            }

            if (!isset($val['multiple_delimiter']) || (isset($val['multiple_delimiter']) && !is_string($val['multiple_delimiter']))) {
                $val['multiple_delimiter'] = '、';
            }

            if (!isset($val['validation']) || (isset($val['validation']) && $this->isEmpty($val['validation']))) {
                if ($val['type'] === 'checkbox' || $val['type'] === 'radio' || $val['type'] === 'select') {
                    $val['validation'] = null;
                } else {
                    $val['validation'] = 'text';
                }
            }

            if (!isset($val['validation_message']) || (isset($val['validation_message']) && $this->isEmpty($val['validation_message']))) {
                $val['validation_message'] = '';
            }

            if (!isset($val['confirm']) || (isset($val['confirm']) && $this->isEmpty($val['confirm']))) {
                $val['confirm'] = null;
            }

            if (!isset($val['label_input']) || (isset($val['label_input']) && !is_string($val['label_input']))) {
                $val['label_input'] = '';
            }
            if (!isset($val['label_output']) || (isset($val['label_output']) && !is_string($val['label_output']))) {
                $val['label_output'] = '';
            }

            if (!isset($val['prefix_input']) || (isset($val['prefix_input']) && !is_string($val['prefix_input']))) {
                $val['prefix_input'] = '';
            }
            if (!isset($val['prefix_output']) || (isset($val['prefix_output']) && !is_string($val['prefix_output']))) {
                $val['prefix_output'] = '';
            }

            if (!isset($val['suffix_input']) || (isset($val['suffix_input']) && !is_string($val['suffix_input']))) {
                $val['suffix_input'] = '';
            }
            if (!isset($val['suffix_output']) || (isset($val['suffix_output']) && !is_string($val['suffix_output']))) {
                $val['suffix_output'] = '';
            }

            if (!isset($val['exclude']) || (isset($val['exclude']) && !is_bool($val['exclude']))) {
                $val['exclude'] = false;
            }

            // 配列をフォームの識別名を付けた項目名に置き換える
            $item[$this->keyFirstWithSettingId($key)] = $val;
            unset($item[$key]);
        }

        return $item;
    }

    /**
     * 表示コントローラー
     */
    protected function viewController()
    {
        session_cache_limiter('private_noexpire');
        session_cache_expire(180);
        session_name(Store::getConfig()['setting']['id']);
        session_start();

        if (!empty($_POST)) {
            $submit = filter_input(INPUT_POST, 'submit');
            $this->authReferer();
            $this->authToken();
            $this->authRequest();

            if ($submit === 'rewrite') {
                $this->rewriteScreen();
            } elseif ($submit === 'confirm') {
                $this->confirmScreen();
            } elseif ($submit === 'complete') {
                $this->completeScreen();
            } else {
                $this->redirectInitScreen();
            }
        } else {
            $mode = filter_input(INPUT_GET, 'mode');
            if ($mode === 'success') {
                Store::setScreenName('success');
            } elseif ($mode === 'failure') {
                Store::setScreenName('failure');
            } else {
                $this->editScreen();
            }
        }
    }

    /**
     * 正しいリファラーか検査する、認められない場合は初期画面へ
     */
    protected function authReferer()
    {
        if (Store::getConfig()['setting']['referer_url']) {
            if (!isset($_SERVER['HTTP_REFERER']) || (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], Store::getConfig()['setting']['referer_url']) === false)) {
                $this->redirectInitScreen();
            }
        }
    }

    /**
     * 正しいトークンか検査する、認められない場合は初期画面へ
     */
    protected function authToken()
    {
        $post_token = isset($_POST['token']) && !$this->isEmpty($_POST['token']) ? $_POST['token'] : '';
        $token = $this->getToken();
        if ($post_token !== $token || empty($post_token) || empty($token)) {
            $this->redirectInitScreen();
        }
    }

    /**
     * 正しいリクエストか検査する、認められない場合は初期画面へ
     */
    protected function authRequest()
    {
        if (
            !(isset($_SESSION[Store::getConfig()['setting']['id']]) && !empty($_SESSION[Store::getConfig()['setting']['id']]))
            || !(isset($_POST['submit']) && !$this->isEmpty($_POST['submit']))
        ) {
            $this->redirectInitScreen();
        }
    }

    /**
     * バリデーション
     * @param  array        $form $_POST された内容
     * @param  string       $key  キーの名前
     * @param  string|array $val  入力値
     * @return string             警告メッセージ
     */
    protected function validation(array $form, string $key, $val)
    {
        $item = Store::getConfig()['item'][$key];
        $method_name = $item['validation'];

        $Validation = new ValidationExtends();
        if (method_exists($Validation, $method_name)) {
            $validation_message = $Validation->$method_name($form, $key, $val);
            return !$this->isEmpty($validation_message) && !$this->isEmpty($item['validation_message']) ? $item['validation_message'] : $validation_message;
        }
        return '';
    }

    /**
     * 項目設定が必須があり、入力が空でないかを検査する
     * @param  string       $key
     * @param  string|array $val
     * @return string
     */
    protected function required(string $key, $val)
    {
        $item = Store::getConfig()['item'][$key];

        if ($item['required'] && $this->isEmpty($val, true)) {
            return $item['required_message'];
        }
        return '';
    }

    /**
     * フォームに入力された値を登録する
     * @param  array $form           $_POST された内容
     * @return bool  $has_form_error フォーム全体にエラーを含んでいるか
     */
    protected function registerRequestFormContent(array $form)
    {
        $has_form_error = false;
        $config = Store::getConfig();

        foreach ($form as $key => $val) {
            // 項目設定で宣言していない入力はスキップする
            if (!isset($config['item'][$key])) {
                continue;
            }
            $error = false;

            Store::setFormInput($key, $val);

            // 'exclude' の指定があればここで除く
            if (!$config['item'][$key]['exclude']) {
                Store::setFormOutput($key, $val);
            }

            // 必須を検査する
            $error = $this->required($key, $val);
            Store::setFormError($key, $error);

            // バリデーションを検査する
            // - 空白（ここでは演算子比較）、または必須を検査で引っかかったら処理しない
            if (!$this->isEmpty($val, true) && !$error) {
                $error = $this->validation($form, $key, $val);
                Store::setFormError($key, $error);
            }

            if ($error) {
                $has_form_error = true;
            }
        }

        return $has_form_error;
    }

    /**
     * 不正な処理は初期画面にリダイレクト
     */
    protected function redirectInitScreen()
    {
        $this->destroySession();
        header('Location: ' . Store::getConfig()['setting']['edit_url']);
        exit();
    }

    /**
     * 初期画面（入力）
     */
    protected function editScreen()
    {
        Store::setScreenName('edit');
        $this->createSupportFile();
        $this->destroySession();
        $this->setToken();
    }

    /**
     * 編集画面
     */
    protected function rewriteScreen()
    {
        Store::setScreenName('edit');
        Store::setSubmitName('rewrite');

        // セッションから入力値を取得する
        $this->registerRequestFormContent($_SESSION[Store::getConfig()['setting']['id']]['input']);
    }

    /**
     * 確認画面
     */
    protected function confirmScreen()
    {
        Store::setSubmitName('confirm');
        $has_form_error = $this->registerRequestFormContent($_POST);

        if ($has_form_error) {
            Store::setScreenName('edit');
        } else {
            Store::setScreenName('confirm');
        }

        // セッションに入力値を保存する
        $_SESSION[Store::getConfig()['setting']['id']]['input'] = Store::getFormInput();

        // セッションに出力値を保存する
        $_SESSION[Store::getConfig()['setting']['id']]['output'] = Store::getFormOutput();
    }

    /**
     * 完了画面（送信）
     */
    protected function completeScreen()
    {
        Store::setSubmitName('complete');
        $this->send();
    }

    /**
     * トークンを保存する
     */
    protected function setToken()
    {
        $_SESSION[Store::getConfig()['setting']['id']]['token'] = sha1(uniqid(mt_rand(), true));
    }

    /**
     * トークンを取得する
     * @return string
     */
    protected function getToken()
    {
        if (isset($_SESSION[Store::getConfig()['setting']['id']]['token']) && !$this->isEmpty($_SESSION[Store::getConfig()['setting']['id']]['token'])) {
            return $_SESSION[Store::getConfig()['setting']['id']]['token'];
        }
        return '';
    }

    /**
     * セッションを破棄
     */
    protected function destroySession()
    {
        if (isset($_SESSION[Store::getConfig()['setting']['id']])) {
            unset($_SESSION[Store::getConfig()['setting']['id']]);
        }
    }

    /**
     * サポートファイルを生成
     */
    protected function createSupportFile()
    {
        // パーミッションを統一するために、すでにあるディレクトリとファイルからパーミッションを取得する
        $dir_permission  = substr(sprintf('%o', fileperms(__DIR__ . '/../')), -4);
        $file_permission = substr(sprintf('%o', fileperms(__DIR__ . '/../OtegaruMailForm.php')), -4);

        // TODO: 先頭0をつけたまま数値に型変換ができるなら、そうしたい。
        switch ($dir_permission) {
            case '0755':
                $dir_permission = 0755;
                break;
            case '0644':
                $dir_permission = 0644;
                break;
            default:
                $dir_permission = 0777;
                break;
        }
        switch ($file_permission) {
            case '0755':
                $file_permission = 0755;
                break;
            case '0644':
                $file_permission = 0644;
                break;
            default:
                $file_permission = 0777;
                break;
        }

        $support_file_list = [
            [__DIR__ . '/../data/timestamp/' . $this->h($_SERVER['SERVER_NAME']) . '.txt'],
            [__DIR__ . '/../.htaccess', 'Htaccess'],
        ];
        $merge_file_list = array_merge($support_file_list, static::SUPPORT_FILE_LIST);

        foreach ($merge_file_list as $key => $val) {
            if (!isset($val[0]) || $this->isEmpty($val[0])) {
                continue;
            }

            $file = trim($val[0]);
            $method_name = isset($val[1]) && is_string($val[1]) ? 'createSupportFile' . trim($val[1]) : '';

            // ディレクトリがあればファイルだけ作成、なければディレクトリとファイルを作成
            if (!file_exists($file)) {
                $path = pathinfo($file);
                is_readable($path['dirname']) ? touch($file, $file_permission) : (mkdir($path['dirname'], $dir_permission, true) && touch($file, $file_permission));
                chmod($file, $file_permission);
            }

            // コールバックメソッドを実行する
            if (method_exists($this, $method_name)) {
                $this->$method_name($file);
            }
        }
    }

    /**
     * HTTP 閲覧されないように設置ディレクトリー以下のアクセスを拒否する
     * @param string $file ファイルパス
     */
    protected function createSupportFileHtaccess($file)
    {
        if (is_writable($file)) {
            file_put_contents($file, 'order allow,deny' . PHP_EOL . 'deny from all', LOCK_EX);
            chmod($file, 0404);
        }
    }

    /**
     * 送信する
     */
    protected function send()
    {
        // セッションから出力値を取得する
        $output = $_SESSION[Store::getConfig()['setting']['id']]['output'];

        // セッションから復元した出力値を保存する
        foreach ($output as $key => $val) {
            Store::setFormOutput($key, $val);
        }

        $this->destroySession();

        new SendExtends();
    }

    /**
     * 入力、確認、メールの HTML / 本文 のテンプレートを展開する
     * @param  string $mode
     * @param  array  $options
     * @return string キーワードが置き換えられた HTML / 本文
     */
    protected function template(string $mode, array $options)
    {
        $Template = new TemplateExtends();
        return $Template->generate($mode, $options);
    }

    /**
     * HTML を展開する
     */
    public function display()
    {
        $screen_name = Store::getScreenName();
        $template = function (string $mode, array $options) {
            return $this->template($mode, $options);
        };

        ob_start();
        if (file_exists(__DIR__ . '/../template/' . $screen_name . '.php')) {
            require(__DIR__ . '/../template/' . $screen_name . '.php');
        }
        $html = ob_get_clean();

        $html = str_replace('{{ OtegaruMailForm.token }}', $this->h($this->getToken()), $html);
        $html = str_replace('{{ OtegaruMailForm.id }}', $this->h(Store::getConfig()['setting']['id']), $html);
        $html = str_replace('{{ OtegaruMailForm.title }}', $this->h(Store::getConfig()['setting']['title']), $html);
        $html = str_replace('{{ OtegaruMailForm.edit_url }}', $this->h(Store::getConfig()['setting']['edit_url']), $html);
        $html = str_replace('{{ OtegaruMailForm.confirm_url }}', $this->h(Store::getConfig()['setting']['confirm_url']), $html);
        $html = str_replace('{{ OtegaruMailForm.rewrite_url }}', $this->h(Store::getConfig()['setting']['rewrite_url']), $html);
        $html = str_replace('{{ OtegaruMailForm.complete_url }}', $this->h(Store::getConfig()['setting']['complete_url']), $html);

        if ($screen_name === 'confirm') {
            $confirm_tag_html = '';
            foreach (Store::getFormOutput() as $key => $val) {
                $confirm_tag_html .= $template('confirm', [
                    'key' => $key,
                ]);
            }
            $html = str_replace('{{ OtegaruMailForm.$template(\'confirm\') }}', $confirm_tag_html, $html);
        }

        echo $html;
        $this->printWarning();
    }

    /**
     * 警告 HTML を展開する
     */
    protected function printWarning()
    {
        $screen_name = Store::getScreenName();

        if ($screen_name === 'error-config') {
            $config = Store::getVerifyConfig();

            echo '<aside style="background-color: white; border: 1px solid red; clear: both; font: normal 15px/1.5 sans-serif; margin: 50px 20px; padding: 20px 20px 40px; position: relative;">';
            echo '<h5 style="background-color: red; color: white; font: bold 10px/1 sans-serif; padding: 6px 7px 7px 5px; position: absolute; left: 0; top: 0;"># [Otegaru Mail Form] 設定エラー</h5>';

            echo '<div style="display: table; margin: 30px auto 0;">';
            echo '<h6 style="font: bold 16px/1.5 sans-serif; margin: 0 0 5px;">## 設定ファイルに誤りがあります。</h6>';
            echo '<ul style="margin-bottom: 0;">';
            foreach ($config as $key => $val) {
                echo '<li>[' . $key . '] ' . $val . '</li>';
            }
            echo '</ul>';
            echo '</div>';

            echo '</aside>';
        } else
        if ($screen_name === 'edit') {
            $defined = Store::getVerifyItemDefined();
            $tag = Store::getVerifyItemTag();
            $item = Store::getConfig()['item'];

            $count_defined = count($defined);
            $count_tag = count($tag);
            $count_item = count($item);

            if ($count_defined || $count_tag !== $count_item) {
                echo '<aside style="background-color: white; border: 1px solid red; clear: both; font: normal 15px/1.5 sans-serif; margin: 50px 20px; padding: 20px 20px 40px; position: relative;" aria-hidden="true">';
                echo '<h5 style="background-color: red; color: white; font: bold 10px/1 sans-serif; padding: 6px 7px 7px 5px; position: absolute; left: 0; top: 0;"># [Otegaru Mail Form] ご留意ください</h5>';

                if ($count_defined) {
                    echo '<div style="display: table; margin: 30px auto 0;">';
                    echo '<h6 style="font: bold 16px/1.5 sans-serif; margin: 0 0 5px;">## 下記の項目は「項目設定」に宣言されていません。</h6>';
                    echo '<ul style="margin-bottom: 0;">';
                    foreach ($defined as $key => $val) {
                        if (!isset($item[$key])) {
                            echo '<li>' . $val . '</li>';
                        }
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                if ($count_tag !== $count_item) {
                    echo '<div style="display: table; margin: 30px auto 0;">';
                    echo '<h6 style="font: bold 16px/1.5 sans-serif; margin: 0 0 5px;">## 下記の項目は「入力画面」に出力されていません。</h6>';
                    echo '<ul style="margin-bottom: 0;">';
                    foreach ($item as $key => $val) {
                        if (!isset($tag[$key])) {
                            echo '<li>' . $val['key'] . '</li>';
                        }
                    }
                    echo '</ul>';
                    echo '</div>';
                }

                echo '</aside>';
            }
        }
    }
}
