<?php

/**
 * Otegaru Mail Form 呼び出しファイル
 */

require(__DIR__ . '/src/App.php');

/**
 * 本体クラスの拡張クラス
 */
class OtegaruMailForm extends App
{ }

/**
 * 送信を管理するクラスの拡張クラス
 */
class SendExtends extends Send
{ }

/**
 * テンプレートを管理するクラスの拡張クラス
 */
class TemplateExtends extends Template
{ }

/**
 * バリデーションを管理するクラスの拡張クラス
 */
class ValidationExtends extends Validation
{ }
