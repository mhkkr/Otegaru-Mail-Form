<?php

/**
 * 送信を管理するクラス
 */
class Send
{
    use Utility;

    public function __construct()
    {
        $this->requireVendor();

        $body = $this->generateBody();
        $timestamp = $this->issueTimeStamp();
        $result = $this->setup($body, $timestamp);
        $successful = $this->successful($result);

        $this->after($body, $result, $timestamp, $successful);
    }

    /**
     * ライブラリーの読み込み
     */
    protected function requireVendor()
    {
        require(__DIR__ . '/../vendor/qdmail.php');
        if ($this->isSmtpUse()) {
            require(__DIR__ . '/../vendor/qdsmtp.php');
        }
    }

    /**
     * 設定の「メール本文のテンプレート」を基に展開された入力内容
     * @return string $body
     */
    protected function generateBody()
    {
        $body = '';

        $Template = new TemplateExtends();
        foreach (Store::getFormOutput() as $key => $val) {
            $body .= $Template->generate('send', [
                'key' => $key,
            ]);
        }

        return trim($body);
    }

    /**
     * タイムスタンプを発行
     * @return array [[0] => $Date->format('Ymd'), [1] => 0001 (都度 +1 される) or 乱数]
     */
    protected function issueTimeStamp()
    {
        $Date = new DateTime();
        $timestamp = 1;

        // タイムスタンプを取得
        $file = __DIR__ . '/../data/timestamp/' . $this->h($_SERVER['SERVER_NAME']) . '.txt';
        if (is_writable($file)) {
            $count = file($file);
            if (is_array($count) && isset($count[0]) && preg_match('/\A[0-9]+\z/', $count[0])) {
                $timestamp = intval($count[0]) + 1; // 数値化してプラス1
            }
            $timestamp = sprintf('%04d', $timestamp); // 0埋め 0001 みたいになる

            // 保存して返す
            file_put_contents($file, $timestamp, LOCK_EX);
            return [$Date->format('Ymd'), $timestamp];
        } else {
            return [$Date->format('Ymd'), mt_rand()];
        }
    }

    /**
     * すべての送信に成功したか
     * @param  array $result 送信結果
     * @return bool
     */
    protected function successful(array $result)
    {
        if (empty($result)) {
            return false;
        } else {
            // 一つでも失敗していれば false
            foreach ($result as $key => $val) {
                if (!$val['status']) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * 送信をセットする
     * @param  string $body
     * @param  array  $timestamp
     * @return array  $result    ['admin' => ['status' => '(bool), 'error_statment' => (array)]] ※'error_statment' はエラーのときのみセットされる
     */
    protected function setup(string $body, array $timestamp)
    {
        // 送信結果を記録
        $result = [];

        $Qdmail = new Qdmail();

        // 各宛先設定
        foreach (Store::getConfig()['send'] as $key => $val) {
            if ($val['to_email'] && $val['from_email']) {
                // 文字コードを指定
                $Qdmail->charsetHeader(Store::getConfig()['setting']['charset']);
                $Qdmail->charsetBody(Store::getConfig()['setting']['charset']);

                // SMTPを使うか
                if ($this->isSmtpUse()) {
                    $Qdmail->smtp(true);
                    $Qdmail->smtpServer(Store::getConfig()['smtp']['option']);
                }

                // 送信処理
                if ($this->try($body, $timestamp, $Qdmail, $val)) {
                    $result[$key]['status'] = true;
                } else {
                    $result[$key]['status'] = false;
                    $result[$key]['error_statment'] = $Qdmail->errorStatment(false);
                }

                $Qdmail->reset();
            }
        }

        return $result;
    }

    /**
     * 送信を試みる
     * @param  string  $body
     * @param  array   $timestamp
     * @param  object  $Qdmail
     * @param  array   $config
     * @return bool
     */
    protected function try(string $body, array $timestamp, Object $Qdmail, array $config)
    {
        // 宛設定
        $subject       = $this->optimize($config['subject'], $timestamp, true);
        $to_email      = $this->optimize($config['to_email'], $timestamp, true);
        $to_name       = $this->optimize($config['to_name'], $timestamp, true);
        $from_email    = $this->optimize($config['from_email'], $timestamp, true);
        $from_name     = $this->optimize($config['from_name'], $timestamp, true);
        $replyto_email = isset($config['replyto_email']) ? $this->optimize($config['replyto_email'], $timestamp, true) : '';
        $replyto_name  = isset($config['replyto_name']) ? $this->optimize($config['replyto_name'], $timestamp, true) : '';
        $header        = $this->optimize($config['header'], $timestamp, false);
        $footer        = $this->optimize($config['footer'], $timestamp, false);

        // 題名
        $Qdmail->subject($subject);

        // ヘッダー・本文・フッター
        $Qdmail->text($header . $body . $footer);

        // 送り主
        $Qdmail->from($from_email, $from_name);

        // 返信先
        if ($replyto_email && $replyto_name) $Qdmail->replyto($replyto_email, $replyto_name);

        // 送り先
        $to_email_list = explode(',', $to_email);
        $to_list = [
            'email' => [],
            'name' => [],
        ];
        foreach ($to_email_list as $key => $val) {
            if (isset($to_email_list[$key]) && !$this->isEmpty($to_email_list[$key])) {
                $to_list['email'][] = trim($to_email_list[$key]);
                $to_list['name'][] = $to_name;
            }
        }
        $Qdmail->to($to_list['email'], $to_list['name']);

        return $Qdmail->send();
    }

    /**
     * 送信用のキーワードを置き換えて最適化する
     * @param  string $str
     * @param  array  $timestamp
     * @param  bool   $is_tag_remove
     * @return string $str
     */
    protected function optimize(string $str, array $timestamp, bool $is_tag_remove)
    {
        // 文字列でないならそのまま返す
        if (!is_string($str)) {
            return $str;
        }

        if (
            isset($timestamp[0]) && preg_match('/\A[0-9]+\z/', $timestamp[0]) &&
            isset($timestamp[1]) && preg_match('/\A[0-9]+\z/', $timestamp[1])
        ) {
            $str = str_replace('{{ timestamp }}', $timestamp[0] . '-' . $timestamp[1], $str);
        }

        // 残りの {{ hoge }} は、項目設定のキーを補足して出力を変換。当てはまらなければ空欄置換で削除する
        preg_match_all('/{{\s[^}]*\s}}/', $str, $match);
        if ($match) {
            foreach ($match[0] as $key => $val) {
                $val_replace = $val;
                $val_replace = str_replace('{{ ', '', $val_replace);
                $val_replace = str_replace(' }}', '', $val_replace);
                $output = Store::getFormOutput();
                $keyId = $this->keyFirstWithSettingId($val_replace);
                if (isset($output[$keyId]) && !$this->isEmpty($output[$keyId])) {
                    $str = str_replace($val, $output[$keyId], $str);
                } else {
                    $str = str_replace($val, '', $str);
                }
            }
        }

        // タグを除去、メールヘッダで異物になる文字を空欄置換で削除する
        if ($is_tag_remove) {
            $str = strip_tags($str);
            $str = str_replace(';', '', $str);
            $str = trim($str);
        }

        return $str;
    }

    /**
     * 送信完了後に実行される処理
     * @param string $body       送信本文 設定の「メール本文のテンプレート」を基に展開された入力内容
     * @param array  $result     送信結果 ['admin' => ['status' => '(bool), 'error_statment' => (array)]] ※'error_statment' はエラーのときのみセットされる
     * @param array  $timestamp  日付連番 [[0] => $Date->format('Ymd'), [1] => 0001 (都度 +1 される) or 乱数]
     * @param bool   $successful 成功判定 すべての送信に成功したか
     */
    protected function after(string $body, array $result, array $timestamp, bool $successful)
    {
        // 状態による完了表示へ移動する
        if ($successful) {
            header('Location: ' . Store::getConfig()['setting']['success_url']);
        } else {
            header('Location: ' . Store::getConfig()['setting']['failure_url']);
        }
        exit();
    }

    /**
     * SMTP 認証を使用するか
     * @return bool
     */
    protected function isSmtpUse()
    {
        if (
            isset(Store::getConfig()['smtp']['host']) && !$this->isEmpty(Store::getConfig()['smtp']['host'])
            && isset(Store::getConfig()['smtp']['port']) && !$this->isEmpty(Store::getConfig()['smtp']['port'])
            && isset(Store::getConfig()['smtp']['from']) && !$this->isEmpty(Store::getConfig()['smtp']['from'])
            && isset(Store::getConfig()['smtp']['protocol']) && !$this->isEmpty(Store::getConfig()['smtp']['protocol'])
            && isset(Store::getConfig()['smtp']['user']) && !$this->isEmpty(Store::getConfig()['smtp']['user'])
            && isset(Store::getConfig()['smtp']['password']) && !$this->isEmpty(Store::getConfig()['smtp']['password'])
        ) {
            return true;
        }
        return false;
    }
}
