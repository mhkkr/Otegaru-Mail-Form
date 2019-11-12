<p class="text-success">送信が完了致しました！</p>
<p>
    「{{ OtegaruMailForm.title }}」いただきありがとうございました。<br>
    入力いただきましたメールアドレス宛にお問い合わせ内容を送信しております。<br>
    内容を確認次第、折返しご連絡いたします。
</p>

<div class="form-group  row">
    <div class="col-sm-10">
        <a class="btn btn-primary" href="{{ OtegaruMailForm.edit_url }}">
            もう一度送る
        </a>
    </div>
</div>