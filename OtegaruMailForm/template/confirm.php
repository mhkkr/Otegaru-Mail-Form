<p>問題ないようでしたら「送信する」をクリックしてください。</p>

{{ OtegaruMailForm.$template('confirm') }}

<div class="form-group  row">
    <form class="col-sm-10" action="{{ OtegaruMailForm.rewrite_url }}" method="post" name="{{ OtegaruMailForm.id }}">
        <input type="hidden" name="token" value="{{ OtegaruMailForm.token }}">
        <input type="hidden" name="submit" value="rewrite">
        <button class="btn  btn-default">
            編集する
        </button>
    </form>
    <form class="col-sm-10" action="{{ OtegaruMailForm.complete_url }}" method="post" name="{{ OtegaruMailForm.id }}">
        <input type="hidden" name="token" value="{{ OtegaruMailForm.token }}">
        <input type="hidden" name="submit" value="complete">
        <button class="btn  btn-primary">
            送信する
        </button>
    </form>
</div>