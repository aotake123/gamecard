<?php

//共通変数・関数ファイルを読み込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('パスワード再発行認証キー入力ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証は無し　できない人が使うので

//SESSIONに認証キーがあるか確認、無ければリダイレクト
if(empty($_SESSION['auth_key'])){
    header("Location:passRemindSend.php"); //認証キー送信ページへ
}

//==============================
// パスワード再発行認証キーページ 画面処理
//==============================
//POST送信されていた場合
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    
    //変数に認証キーを代入
    $auth_key = $_POST['token'];
    
    //未入力チェック
    validRequired($auth_key, 'token');
    
    if(empty($err_msg)){
        debug('未入力チェックOK。');
        
        //固定長チェック
        validLength($auth_key, 'token');
        //半角チェック
        validHalf($auth_key, 'token');
        
        if(empty($err_msg)){
            debug('バリデーションOK。');
            
            if($auth_key !== $_SESSION['auth_key']){
                $err_msg['common'] = MSG15;
            }
            if(time() > $_SESSION['auth_key_limit']){
                $err_msg['common'] = MSG16;
            }
            
            if(empty($err_msg)){
                debug('認証OK');
                $pass = makeRandKey(); //パスワード生成
                debug('新規パスワード：'.$pass); //開発中のみの表示
                
                //例外処理
                try {
                    //DB接続
                    $dbh = dbConnect();
                    //SQL文作成
                    $sql = 'UPDATE users SET pass = :pass WHERE email = :email AND delete_flg = 0';
                    $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
                    //クエリ実行
                    $stmt = queryPost($dbh, $sql, $data);
                    
                    //クエリ成功の場合
                    if($stmt){
                        debug('クエリ成功。');
                        
                        //メールを送信
                        $from = 'tasukuoki3@gmail.com';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】| 対局カード GAMECARD';
                        //EOTはEndOfFileの略。ABCでもなんでもいい。先頭の<<<の後の文字列と合わせること。最後のEOTの前後に空白など何も入れてはいけない
                        //EOT内の半角空白もすべてそのまま半角空白として扱われるのでインデントはしないこと
                        $comment = <<<EOT

本メールアドレス宛にパスワード再発行を致しました。
下記のURLにて再発行パスワードをご入力頂き、ログインください。

ログインページ：http://ikizama-design.com/gamecard/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードの変更をお願いします

///////////////////////////////////////////
対局カード GAMECARD 管理事務局
URL http://webukatu.com/
E-mail　info@webukatu.com
///////////////////////////////////////////
EOT;
                    sendMail($from, $to, $subject, $comment);
                    
                    //セッション削除
                    session_unset(); //IDがなくなると下のメッセージが表示されなくなってしまう
                    $_SESSION['msg_success'] = SUC03;
                    debug('セッション変数の中身：'.print_r($_SESSION,true));
                    
                    header("Location:login.php"); //ログインページへ
                        
                    }else{
                        debug('クエリに失敗しました。');
                        $err_msg['common'] = MSG07;
                    }
                    
                } catch (Exception $e){
                    error_log('エラー発生：' . $e->getMessage());
                    $err_msg['common'] = MSG07; 
                }
            }
        }
    }
}
    
    
?>

<?php
$siteTitle = 'パスワード認証 | 対局カード';
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
                 <h2 class="title">パスワード認証</h2>

                            
                     <form action="" method="post" class="form">
                         <p>ご指定のメールアドレス宛に送らせて頂いた【パスワード再発行認証】メール内にある「認証キー」をご入力ください。</p>
  
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
                            認証キー
                            <input type="text" name="token" value="<?php echo getFormData('token') ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['token'])) echo sanitize($err_msg['token']); ?>
                        </div>
                         <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="再発行する">
                        </div>
                   </form>        
                </div>
              </div>
              <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
            </section>
                        
        </div>
        
    <!-- footer -->
    
    <?php
    require('footer.php');
    ?>