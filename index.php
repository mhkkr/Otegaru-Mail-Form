<?php
require(__DIR__ . '/OtegaruMailForm/OtegaruMailForm.php');
$OtegaruMailForm = new OtegaruMailForm\App;
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>お手軽メールフォーム (Otegaru Mail Form)</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
</head>

<body>
    <div style="padding: 20px;">
        <?php $OtegaruMailForm->display(); ?>
    </div>
</body>

</html>