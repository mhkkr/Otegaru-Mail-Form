<aside style="background-color: white; border: 1px solid red; clear: both; font: normal 15px/1.5 sans-serif; margin: 50px 20px; padding: 20px 20px 40px; position: relative;">
    <h5 style="background-color: red; color: white; font: bold 10px/1 sans-serif; padding: 6px 7px 7px 5px; position: absolute; left: 0; top: 0;"># [{{ OtegaruMailForm.title }}] エラー</h5>
    <div style="display: table; margin: 30px auto 0;">
        <h6 style="font: bold 16px/1.5 sans-serif; margin: 0 0 5px;">## 不正なアクセスを検知しました。</h6>
        <p>リファラーやトークンが正しくない、または有効なセッションがないか、送信内容に誤りがあります。</p>
    </div>
</aside>

<div class="form-group  row">
    <div class="col-sm-10">
        <a class="btn btn-primary" href="{{ OtegaruMailForm.edit_url }}">
            最初からやり直す
        </a>
    </div>
</div>