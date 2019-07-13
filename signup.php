<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
    
//POST送信されているかどうか確認
if(!empty($_POST)){
//変数にPOST送信されたユーザー情報を代入
    $email = $_POST['email'];
    $pass = $_POST['pass'];
    $pass_re = $_POST['pass_re'];
//未入力チェック
    validRequired($email,'email');
    validRequired($pass,'pass');
    validRequired($pass_re,'pass_re');
//emailの形式チェック
    validEmail($email,'email');
//emailの最大文字数チェック
    validMaxLen($email,'email');
//email重複チェック

//パスワードの半角英数字チェック
    validHalf($pass,'pass');
//パスワードの最大文字数チェック
    validMaxLen($pass,'pass');
//パスワードの最小文字数チェック
    validMinLen($pass,'pass');
//パスワードとパスワード再入力が合っているかチェック
    validMatch($pass,$pass_re,'pass');
    
//例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'INSERT INTO users (email,pass,create_date) VALUES (:email,:pass,:create_date)';
        $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT),
                     'create_date' => date('Y-m-d H:i:s'));
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        //クエリ成功の場合
        if($stmt){
            //ログイン有効期限（デフォルトを1時間とする）
            $sesLimit = 60*60;
            //最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            //ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();
            
            debug('セッション変数の中身：'.print_r($_SESSION,true));          
            header('Location:index.php');  //マイページへ    
        }
        
    } catch(Exception $e){
        error_log('エラー発生' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
    
    //名前保存用のもうひとつの例外処理
        try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'INSERT INTO users2 (id,email) VALUES (:id,:email)';
        $data = array(':id' => $_SESSION['user_id'], ':email' => $email);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data); 
        
        //クエリ成功の場合
        if($stmt){
            //ログイン有効期限（デフォルトを1時間とする）
            $sesLimit = 60*60;
            //最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            //ユーザーIDを格納
            $_SESSION['user_id'] = $dbh->lastInsertId();
            
            debug('セッション変数の中身：'.print_r($_SESSION,true));          
            header('Location:profEdit.php');  //マイページへ    
        }
        
    } catch(Exception $e){
        error_log('エラー発生' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }

}




?>

<?php
$siteTitle = 'ユーザー登録 | 対局カード';
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
                 <h2 class="title">ユーザー登録</h2>

                            
                     <form action="" method="post" class="form">
  
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
                        <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
                            パスワード
                            <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo sanitize($_POST['pass']); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['pass'])) echo sanitize($err_msg['pass']); ?>
                        </div>
                        <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
                            パスワード（再入力）
                            <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo sanitize($_POST['pass_re']); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['pass_re'])) echo sanitize($err_msg['pass_re']); ?>
                        </div>
                        <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="登録する">
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