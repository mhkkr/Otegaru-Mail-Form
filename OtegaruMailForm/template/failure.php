<p class="text-danger">送信に失敗しました…。</p>
<p>「{{ OtegaruMailForm.title }}」いただきありがとうございました。</p>
<p>大変申し訳ございません。何らかの理由で、送信に失敗致しました。大変お手数ですが、内容をお確かめの上、やり直していただく必要がございます。それでも失敗する場合は、大変恐れ入りますが、下記までご連絡をお願いいたします。</p>
<p>
    https://www.example.com/<br>
    〒000-0000 〇〇県□□市△△区11-22<br>
    TEL:000-0000-0000　FAX:000-0000-0000
</p>

<div class="form-group  row">
    <div class="col-sm-10">
        <a class="btn btn-default" href="{{ OtegaruMailForm.edit_url }}">
            最初からやり直す
        </a>
    </div>
</div>