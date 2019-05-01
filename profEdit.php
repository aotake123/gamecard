<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('プロフィール編集ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
// 画面処理
//==============================

//新規登録か編集か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;

//DBからユーザーデータを取得
$dbFormData = getUser($_SESSION['user_id']);
$dbFormData2 = getUser($_SESSION['user_id']);

debug('取得したユーザー情報：'.print_r($dbFormData,true));

//POSTされていた場合
if(!empty($_POST)){
    debug('POST通信があります。');
    debug('POST情報：'.print_r($_POST,true));
    
    //変数にユーザー情報を代入
    $usersei = $_POST['usersei'];
    $username = $_POST['username'];
    $sex = $_POST['sex'];
    $groupname = $_POST['groupname'];
    $power = $_POST['power'];
    $email = $_POST['email'];
    $userseiname = $usersei.$username;
    $userseiname2 = $userseiname;


    
    // 画像をアップロードし、パスを格納
    $pic = ( !empty($_FILES['pic']['name']) ) ? uploadImg($_FILES['pic'],'pic') : '';
    // 画像をPOSTしてない（登録していない）が既にDBに登録されている場合、DBのパスを入れる（POSTには反映されないので）
    $pic = ( empty($pic) && !empty($dbFormData['pic']) ) ? $dbFormData['pic'] : $pic;
    
    //DBの情報と入力情報が異なる場合にバリデーションを行う
    if($dbFormData['usersei'] !== $usersei){
        //名前の最大文字数チェック
        validMaxLen($usersei,'usersei');
    }
    if($dbFormData['username'] !== $username){
        //名前の最大文字数チェック
        validMaxLen($username,'username');
    }
    if($dbFormData['email'] !== $_POST['email']){
        //Emailの最大文字数チェック
        ValidMaxLen($email,'email');
        if(empty($err_msg['email'])){
            //Emailの重複チェック
            validEmailDup($email);
        }
        //emailの形式チェック
        validEmail($email,'email');
        //emailの未入力チェック
        validRequired($email,'email');
    }
    
    if(empty($err_msg)){
        debug('バリデーションOKです');

        //例外処理
        try{
            //DBへ接続
            $dbh = dbConnect();
            //SQL文作成
            $sql = 'UPDATE users SET usersei = :u_sei, username = :u_name, userseiname = :u_seiname, sex = :sex, groupname = :groupname, power = :power, email = :email, pic = :pic WHERE id = :u_id';
            $data = array(':u_sei' => $usersei, ':u_name' => $username, ':u_seiname' => $userseiname, ':groupname' => $groupname, ':power' => $power, 
                          ':sex' => $sex, ':email' => $email, ':pic' => $pic, ':u_id' => $dbFormData['id']);
            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
            
       //白番の対局者と関連付けるDB作成
            //DBへ接続
            $dbh = dbConnect();
            //SQL文作成
            $sql = 'UPDATE users2 SET userseiname2 = :u_seiname2, email = :email WHERE id = :u_id2';
            $data = array(':u_seiname2' => $userseiname2, ':email' => $email, ':u_id2' => $dbFormData2['id']);

            //クエリ実行
            $stmt = queryPost($dbh,$sql,$data);
            
            //クエリ成功の場合
            if($stmt){
                $_SESSION['msg_success'] = SUC02;
                debug('マイページへ遷移します。');
                header("Location:mypage.php"); //マイページへ
            }
        } catch (Exception $e){
            error_log('エラー発生：' . $e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');

?>


<?php
$siteTitle = 'プロフィール編集画面 | 対局カード';
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
                 <h2 class="title">プロフィール編集画面</h2>

                            
                     <form action="" method="post" class="form" enctype="multipart/form-data">
  
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                            姓
                            <input type="text" name="usersei" value="<?php echo getFormData('usersei'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['usersei'])) echo $err_msg['usersei']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['usersei'])) echo 'err'; ?>">
                            名
                            <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
                        </label>
                        <div class="username">
                            <?php if(!empty($err_msg['username'])) echo $err_msg['username']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['sex'])) echo 'err'; ?>">
                            性別
                            <select name="sex" id="id_sex">
                                <option value="0" <?php if(empty(getFormData('sex'))) echo 'selected="selected"'; ?>>選択してください</option>    
                                <option value="1" <?php if(getFormData('sex') == 1) echo 'selected="selected"'; ?>>男性</option>
                                <option value="2" <?php if(getFormData('sex') == 2) echo 'selected="selected"'; ?>>女性</option>
                            </select>
                            
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['sex'])) echo $err_msg['sex']; ?>
                        </div>
                        

                        <label class="<?php if(!empty($err_msg['groupname'])) echo 'err'; ?>">
                            グループ名
                             <select name="groupname" id="id_groupname">
                                <option value="0" <?php if(empty(getFormData('groupname'))) echo 'selected="selected"'; ?>>選択してください</option>    
                                <option value="1" <?php if(getFormData('groupname') == 1) echo 'selected="selected"'; ?>>IGO AMIGO</option>
                            </select>
                            
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['m_id'])) echo $err_msg['m_id']; ?>
                        </div>

                        <label class="<?php if(!empty($err_msg['power'])) echo 'err'; ?>">
                            棋力
                            <select name="power" id="id_power">
                                <option value="0" <?php if(empty(getFormData('power'))) echo 'selected="selected"'; ?>>選択してください</option> 
                                <option value="1" <?php if(getFormData('power') == 1) echo 'selected="selected"'; ?>>20級</option>
                                <option value="2" <?php if(getFormData('power') == 2) echo 'selected="selected"'; ?>>19級</option>
                                <option value="3" <?php if(getFormData('power') == 3) echo 'selected="selected"'; ?>>18級</option>
                                <option value="4" <?php if(getFormData('power') == 4) echo 'selected="selected"'; ?>>17級</option>
                                <option value="5" <?php if(getFormData('power') == 5) echo 'selected="selected"'; ?>>16級</option>
                                <option value="6" <?php if(getFormData('power') == 6) echo 'selected="selected"'; ?>>15級</option>
                                <option value="7" <?php if(getFormData('power') == 7) echo 'selected="selected"'; ?>>14級</option>
                                <option value="8" <?php if(getFormData('power') == 8) echo 'selected="selected"'; ?>>13級</option>
                                <option value="9" <?php if(getFormData('power') == 9) echo 'selected="selected"'; ?>>12級</option>
                                <option value="10" <?php if(getFormData('power') == 10) echo 'selected="selected"'; ?>>11級</option>
                                <option value="11" <?php if(getFormData('power') == 11) echo 'selected="selected"'; ?>>10級</option>
                                <option value="12" <?php if(getFormData('power') == 12) echo 'selected="selected"'; ?>>9級</option>
                                <option value="13" <?php if(getFormData('power') == 13) echo 'selected="selected"'; ?>>8級</option>
                                <option value="14" <?php if(getFormData('power') == 14) echo 'selected="selected"'; ?>>7級</option>
                                <option value="15" <?php if(getFormData('power') == 15) echo 'selected="selected"'; ?>>6級</option>
                                <option value="16" <?php if(getFormData('power') == 16) echo 'selected="selected"'; ?>>5級</option>
                                <option value="17" <?php if(getFormData('power') == 17) echo 'selected="selected"'; ?>>4級</option>
                                <option value="18" <?php if(getFormData('power') == 18) echo 'selected="selected"'; ?>>3級</option>
                                <option value="19" <?php if(getFormData('power') == 19) echo 'selected="selected"'; ?>>2級</option>
                                <option value="20" <?php if(getFormData('power') == 20) echo 'selected="selected"'; ?>>1級</option>
                                <option value="21" <?php if(getFormData('power') == 21) echo 'selected="selected"'; ?>>初段</option>
                                <option value="22" <?php if(getFormData('power') == 22) echo 'selected="selected"'; ?>>二段</option>
                                <option value="23" <?php if(getFormData('power') == 23) echo 'selected="selected"'; ?>>三段</option>
                                <option value="24" <?php if(getFormData('power') == 24) echo 'selected="selected"'; ?>>四段</option>
                                <option value="25" <?php if(getFormData('power') == 25) echo 'selected="selected"'; ?>>五段</option>
                                <option value="26" <?php if(getFormData('power') == 26) echo 'selected="selected"'; ?>>六段</option>
                                <option value="27" <?php if(getFormData('power') == 27) echo 'selected="selected"'; ?>>七段</option>
                            </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['power'])) echo $err_msg['power']; ?>
                        </div>
                                                           
                        <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                            Email
                            <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                        </div>
                             プロフィール画像                                             
                        <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:370px;line-height:370px;">
                            <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                            <input type="file" name="pic" value="<?php echo getFormData('pic'); ?>">
                            <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                         </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; ?>
                        </div>
                        
                         <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="更新">
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