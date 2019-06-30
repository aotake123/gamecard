<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行メール送信ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証は無し　できない人が使うので

//==============================
// パスワード再発行メール送信ページ 画面処理
//==============================
//POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    
    //変数にPOST情報代入
    $email = $_POST['email'];
    
    //未入力チェック
    validRequired($email, 'email');
    
    if(empty($err_msg)){
        debug('未入力チェックOK。');
        
        //emailの形式チェック
        validEmail($email, 'email');
        //emailの最大文字数チェック
        validMaxLen($email, 'email');
        
        if(empty($err_msg)){
            debug('バリデーションOK');
            
            
            //例外処理
            try{
                //DB接続
                $dbh = dbConnect();
                //SQL文作成
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                //クエリ実行
                $stmt = queryPost($dbh,$sql,$data);
                //クエリ結果の値を取得
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                //EmailがDBに登録されている場合
                if($stmt && array_shift($result)){
                    debug('クエリ成功。DB登録あり。');
                    $_SESSION['msg_success'] = SUC03;
                    
                    $auth_key = makeRandKey(); //認証キー生成
                    
                    //メールを送信
                    $from = 'info@webukatu.com';
                    $to = $email;
                    $subject = '【パスワード再発行認証】| 対局カード GAMECARD';
                   //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない
                    //EOT内の半角空白もすべてそのまま半角空白として扱われるのでインデントはしないこと
                    $comment = <<<EOT

本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://localhost:8888/gamecard/passRemindRecieve.php
認証キー：{$auth_key}
※認証キーの有効期限は30分となります

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://localhost:8888/gamecard/passRemindSend.php

///////////////////////////////////////////
対局カード GAMECARD 管理事務局
URL http://webukatu.com/
E-mail　info@webukatu.com
///////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);
                    
                    //認証に必要な情報をセッションへ保存
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] = time()+(60*30); //現在時刻より30分後のUNIXタイムスタンプを入れる
                    
                    header("Location:mypage.php"); //マイページへ
                }else{
                    debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                    $err_msg['common'] = MSG07;
                }
                
            } catch(Exception $e){
                error_log("エラー発生" . $e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}
debug('画面表示処理終了　<<<<<<<<<<<<<<<<<<<<<<');

?>



<?php
$siteTitle = 'パスワード再発行 | 対局カード';
require('head.php');
?>

<body class="page-signup page-1colum">
    
    <!-- header -->  
    <?php
    require('header.php');
    ?>
    
        <!-- main contents -->
        <div id="contents" class="site-width">
           
            <!-- main -->
            <section id="main">

              <div class="form-container">
              
 
                 <div class="form_wrap">
                 <h2 class="title">パスワード再発行</h2>

                            
                     <form action="" method="post" class="form">
                         <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
  
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo sanitize($err_msg['common']); ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                            Email
                            <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo sanitize($_POST['email']); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['email'])) echo sanitize($err_msg['email']); ?>
                        </div>
                        
                         <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="送信する">
                        </div>
                   </form>        
                </div>
              </div>
            </section>
                        
        </div>
        
    <!-- footer -->
    
    <?php
    require('footer.php');
    ?>