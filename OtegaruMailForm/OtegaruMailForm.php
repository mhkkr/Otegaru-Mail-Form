<?php

/**
 * Otegaru Mail Form
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
 * @version   1.0.1a
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License
 */

namespace OtegaruMailForm;

spl_autoload_register(function ($class) {
    $class = str_replace('OtegaruMailForm\Src', 'src', $class);
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    require(__DIR__ . '/' . $class . '.php');
});

/**
 * 本体クラスの拡張クラス
 */
class App extends Src\App
{ }

/**
 * 送信を管理するクラスの拡張クラス
 */
class Send extends Src\Send
{ }

/**
 * テンプレートを管理するクラスの拡張クラス
 */
class Template extends Src\Template
{ }

/**
 * バリデーションを管理するクラスの拡張クラス
 */
class Validation extends Src\Validation
{ }
