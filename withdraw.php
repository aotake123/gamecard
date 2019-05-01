<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('ユーザー登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
//画面処理
//==============================
//POST送信されていた場合
if(!empty($_POST)){
    debug('POST通信があります');
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        //sql文作成
        $sql1 = 'UPDATE users SET delete_flg = 1 Where id = :us_id';
        //$sql2 = 'UPDATE game SET delete_flg = 1 Where game_id = :us_id';
        //データ流し込み
        $data = array(':us_id' => $_SESSION['user_id']);
        //クエリ実行
        $stmt1 = queryPost($dbh,$sql1,$data);
        //$stmt2 = queryPost($dbh,$sql2,$data);
        
        //クエリ実行成功の場合（最悪userテーブルのみ削除成功していれば良しとする）
        if($stmt1){
            //セッション削除
            session_destroy();
            debug('セッション変数の中身：'.print_r($_SESSION,true));
            debug('トップページへ遷移します。');
            header('Location:index.php');
        }else{
            debug('クエリが失敗しました。');
            $err_msg['common'] = MSG07;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了　<<<<<<<<<<<<<<<<<<<<<<');


?>


<?php
$siteTitle = '退会 | 対局カード';
require('head.php');
?>

<body class="page-withdraw page-1colum">
   
   <style>
       .form .btn{
           float: none;
       }
       .form{
           text-align: center;
       }
    
       
    </style>
    
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
                 <h2 class="title">退会</h2>

                            
                     <form action="" method="post" class="form">
  
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                        </div>
                       <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="退会する" name="submit">
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