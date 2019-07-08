/* jslint browser: true */

//こう書くとDOMが作り終わった時にその中身の処理が実行される
$(function() {
    //ラジオボタンで「目数差」が入力されたら数値が入力できる形式にする
    //それまでは数値入力欄はdisabled（非活性）にしておく

    //再編集時に、フォームに数値が最初から入力されていた場合、即非活性化解除をする（調整中）

    //ラジオボタンが切り替わった時「目数差」に指定されてるか確認するイベントをセット
    $('input[name="g_winHow"]:radio').change(function() {
        //押されたラジオボタンの中身(value)を取得してラジオボタンの指定箇所に中身が入っているか確認
        if($(this).val() == 2) {
            //指定しているならformを活性にする（disを外す）
            $('.js-disabled-form').prop('disabled', false);
        } else {
            //指定していなければそのまま非活性状態を継続
            $('.js-disabled-form').prop('disabled', true);
            //「目数差」入力後に他の選択肢に切り替えた場合はフォームの中身を削除
            $('input[name="g_winHow_moku"]:text').val("");
        }
    });
});
