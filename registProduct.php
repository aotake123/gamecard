<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('対局登録ページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();
    
//ログイン認証
require('auth.php');

//==============================
//対局登録　画面処理
//==============================

//画面表示用データ取得
//==============================
//GETデータを格納
$g_id = (!empty($_GET['g_id'])) ? $_GET['g_id'] : '';
//DBから商品データを取得
$dbFormData = (!empty($g_id)) ? getProduct($_SESSION['user_id'], $g_id) : '';
//新規登録画面か編集画面か判別用フラグ
$edit_flg = (empty($dbFormData)) ? false : true;
//DBから対局者（カテゴリ)データを取得
$dbPlayerData = getPlayer();
//debug('対局ID：'.$g_id);
debug('フォーム用DBデータ：'.print_r($dbFormData,true));
debug('対局者データ：'.print_r($dbPlayerData,true));

//パラメータ改ざんチェック
//==============================
//GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい対局データが取れないのでマイページへ遷移させる
    if(!empty($g_id) && empty($dbFormData)){
        debug('GETパラメータの対局IDが違います。');
        header("Location:mypage.php"); //マイページへ
    }

//POST送信処理
//==============================
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    debug('FILE情報：'.print_r($_FILES,true));
    
    //変数にユーザー情報を代入
    //対局日時
    $g_year = $_POST['g_year'];
    $g_month = $_POST['g_month'];
    $g_month = sprintf('%02d',$g_month);
    $g_date = $_POST['g_date'];
    $g_date = sprintf('%02d',$g_date);
    $g_time = $_POST['g_time'];
    //黒番
    $g_black = $_POST['g_black'];
    //棋力（黒番）
    $b_power = $_POST['b_power'];
    //白番
    $g_white = $_POST['g_white'];
   //棋力（白番）
    $w_power = $_POST['w_power'];
    //手合い
    $g_teai = $_POST['g_teai'];
    //対局結果
    $g_winHow = $_POST['g_winHow'];
    
    if(!empty($_POST['g_winHow_moku'])) {
        $g_winHow_moku = $_POST['g_winHow_moku'];
    }else{
        $g_winHow_moku = null;   
    }
    //対局勝敗
    $g_result = $_POST['g_result'];
    //更新と新規関係無く、自分が黒番にも白番にも含まれていない対局に対してエラーを出す
    if($g_black !== $_SESSION['user_id'] && $g_white !== $_SESSION['user_id']){
        global $err_msg;
        $err_msg['common'] = MSG18;
    }
    //黒番と白番が同一人物の場合にエラーを出す
    if($g_black === $g_white){
        global $err_msg;
        $err_msg['common'] = MSG19;
    }
    
    //白番対局者の参照元データを取得
    $whiteData = getWhiteName($g_white);
    $userseiname = $whiteData['userseiname'];
    debug('白番対局者日本語データ：'.print_r($whiteData,true));

    
        //新規の場合はセレクトボックスの未選択の場合にバリデーションが
        if(empty($dbFormData)){
            //セレクトボックスチェック
            validSelect($g_year, 'g_year');
            validSelect($g_month, 'g_month');
            validSelect($g_date, 'g_date');
            //validSelect($g_black, 'g_black');
            validSelect($b_power, 'b_power');
            //validSelect($g_white, 'g_white');
            validSelect($w_power, 'w_power');
            //validSelect($g_teai, 'g_teai');
            //validSelect($g_winHow, 'g_winHow');
            //validSelect($g_result, 'g_result');
        }

        //更新の場合はDBの情報と入力情報が異なる場合にバリデーションを行う
        if(isset($dbFormData)){
            
        }

            if(empty($err_msg)){
                debug('バリデーションOKです。');

                //例外処理
                try{
                    //DB接続
                    $dbh = dbConnect();
                    //SQL文作成
                    //編集画面の場合はUPDATE文、新規登録画面の場合はINSERT文を生成
                    debug('DB接続 完了');

                    if($edit_flg){
                        debug('DB更新です。');
                        $sql = 'UPDATE game SET g_year = :g_year, g_month = :g_month, g_date = :g_date, g_time = :g_time, g_black = :g_black, b_power = :b_power,
                            g_white = :g_white, w_power = :w_power, g_teai = :g_teai, g_winHow = :g_winHow, g_winHow_moku = :g_winHow_moku, g_result = :g_result WHERE user_id = :user_id AND g_id = :g_id';
                         $data = array(':g_year' => $g_year, ':g_month' => $g_month, ':g_date' => $g_date, ':g_time' => $g_time, ':g_black' => $g_black, ':b_power' => $b_power,
                                     ':g_white' => $g_white, ':w_power' => $w_power, ':g_teai' => $g_teai, ':g_winHow' => $g_winHow, ':g_winHow_moku' => $g_winHow_moku,':g_result' => $g_result,
                                    ':user_id' => $_SESSION['user_id'], ':g_id' => $g_id);
                    }else{
                        debug('DB新規登録です。');
                        $sql = 'insert into game (g_year,g_month,g_date,g_time,g_black,b_power,g_white,w_power,g_teai,g_winHow,g_winHow_moku,g_result,create_date,user_id)
                            values (:g_year,:g_month,:g_date,:g_time,:g_black,:b_power,:g_white,:w_power,:g_teai,:g_winHow,:g_winHow_moku,:g_result,:create_date,:user_id)';
                        $data = array(':g_year' => $g_year, ':g_month' => $g_month, ':g_date' => $g_date, ':g_time' => $g_time, ':g_black' => $g_black, ':b_power' => $b_power,
                                     ':g_white' => $g_white, ':w_power' => $w_power, ':g_teai' => $g_teai, ':g_winHow' => $g_winHow, ':g_winHow_moku' => $g_winHow_moku, ':g_result' => $g_result,
                                    ':user_id' => $_SESSION['user_id'], ':create_date' => date('Y-m-d H:i:s'));
                    }
                    
                    //テーブル間の表のコピー
                        //debug('テーブル間のコピー');
                        //$sql = 'insert into users2 SELECT id,userseiname,delete_flg,create_date,update_date FROM users';
                        //$data = array(':g_white' => $userseiname, ':create_date' => date('Y-m-d H:i:s'));
                    
                    //中間テーブルへの値の代入
                    //if($edit_flg){
                        //debug('DB更新です。');
                        //$sql = 'UPDATE usersGame SET white = :white,
                        //WHERE user_id = :user_id AND g_id = :g_id';
                         //$data = array(':g_white' => $userseiname, 
                                    //':user_id' => $_SESSION['user_id'], 'g_id' => $g_id);
                    //}else{
                        //debug('DB新規登録です。');
                        //$sql = 'insert into usersGame (white,create_date)
                            //values (:g_white,:create_date)';
                        //$data = array(':g_white' => $userseiname, ':create_date' => date('Y-m-d H:i:s'));
                    //}
                    
                    debug('SQL：'.$sql);
                    debug('流し込みデータ：'.print_r($data,true));
                    //クエリ実行
                    $stmt = queryPost($dbh, $sql, $data);
                               debug('クエリ実行1 完了');


                    //クエリ成功の場合
                    if($stmt){
                        $_SESSION['msg_success'] = SUC04;
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
$siteTitle = '対局結果記入画面 | 対局カード';
require('head.php');
?>

<body class="page-profEdit page-2colum page-logined">
    
    <!-- header -->  
    <?php
    require('header.php');
    ?>
    
        <!-- main contents -->
        <div id="contents" class="site-width">
           
            <!-- main -->
            <section id="main">

              <div class="form-container">
                <h2 class="title"><?php echo (!$edit_flg) ? '対局結果を登録する' : '対局結果を編集する'; ?></h2>
              
 
                 <div class="form_wrap">
 
                
                     <form action="" method="post" class="form" enctype="multipart/form-data" style="width:100%;box-sizing:border-box;">
  
                        <div class="area-msg">
                            <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['g_year'])) echo 'err'; ?>">
                            年
                            <select name="g_year">
                                <option value="2019" <?php if(empty(getFormData('g_year') || getFormData('g_year') == 2019)) echo 'selected="selected"'; ?>>2019</option> 
                                <option value="2018" <?php if(getFormData('g_year') == 2018) echo 'selected="selected"'; ?>>2018</option>
                                <option value="2017" <?php if(getFormData('g_year') == 2017) echo 'selected="selected"'; ?>>2017</option>
                                <option value="2016" <?php if(getFormData('g_year') == 2016) echo 'selected="selected"'; ?>>2016</option>
                                <option value="2015" <?php if(getFormData('g_year') == 2015) echo 'selected="selected"'; ?>>2015</option>
                                <option value="2014" <?php if(getFormData('g_year') == 2014) echo 'selected="selected"'; ?>>2014</option>
                                <option value="2013" <?php if(getFormData('g_year') == 2013) echo 'selected="selected"'; ?>>2013</option>
                                <option value="2012" <?php if(getFormData('g_year') == 2012) echo 'selected="selected"'; ?>>2012</option>
                                <option value="2011" <?php if(getFormData('g_year') == 2011) echo 'selected="selected"'; ?>>2011</option>
                                <option value="2010" <?php if(getFormData('g_year') == 2010) echo 'selected="selected"'; ?>>2010</option>
                                <option value="2009" <?php if(getFormData('g_year') == 2009) echo 'selected="selected"'; ?>>2009</option>
                                <option value="2008" <?php if(getFormData('g_year') == 2008) echo 'selected="selected"'; ?>>2008</option>
                                <option value="2007" <?php if(getFormData('g_year') == 2007) echo 'selected="selected"'; ?>>2007</option>
                                <option value="2006" <?php if(getFormData('g_year') == 2006) echo 'selected="selected"'; ?>>2006</option>
                            </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_year'])) echo $err_msg['g_year']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['g_month'])) echo 'err'; ?>">
                            月
                            <select name="g_month">
                                <option value="0" <?php if(empty(getFormData('g_month'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <option value="1" <?php if(getFormData('g_month') == 1) echo 'selected="selected"'; ?>>1</option>
                                <option value="2" <?php if(getFormData('g_month') == 2) echo 'selected="selected"'; ?>>2</option>
                                <option value="3" <?php if(getFormData('g_month') == 3) echo 'selected="selected"'; ?>>3</option>
                                <option value="4" <?php if(getFormData('g_month') == 4) echo 'selected="selected"'; ?>>4</option>
                                <option value="5" <?php if(getFormData('g_month') == 5) echo 'selected="selected"'; ?>>5</option>
                                <option value="6" <?php if(getFormData('g_month') == 6) echo 'selected="selected"'; ?>>6</option>
                                <option value="7" <?php if(getFormData('g_month') == 7) echo 'selected="selected"'; ?>>7</option>
                                <option value="8" <?php if(getFormData('g_month') == 8) echo 'selected="selected"'; ?>>8</option>
                                <option value="9" <?php if(getFormData('g_month') == 9) echo 'selected="selected"'; ?>>9</option>
                                <option value="10" <?php if(getFormData('g_month') == 10) echo 'selected="selected"'; ?>>10</option>
                                <option value="11" <?php if(getFormData('g_month') == 11) echo 'selected="selected"'; ?>>11</option>
                                <option value="12" <?php if(getFormData('g_month') == 12) echo 'selected="selected"'; ?>>12</option>
                            </select>
                         </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_month'])) echo $err_msg['g_month']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['g_date'])) echo 'err'; ?>">
                            日
                            <select name="g_date">
                                <option value="0" <?php if(empty(getFormData('g_date'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <?php
                                for($i = 1; $i < 32; $i++){
                                ?>
                                    <option value="<?php echo $i; ?>" <?php if(getFormData('g_date') == $i) echo 'selected="selected"'; ?>><?php echo $i; ?></option>;
                                <?php
                                }
                                ?>
                            </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_time'])) echo $err_msg['g_date']; ?>
                        </div>

                       <label class="<?php if(!empty($err_msg['g_time'])) echo 'err'; ?>">
                            対局開始時間
                            <select name="g_time">
                                <option value="0" <?php if(empty(getFormData('g_time'))) echo 'selected="selected"'; ?>>▶︎選択してください</option>       
                                <option value="1" <?php if(getFormData('g_time') == 1) echo 'selected="selected"'; ?>>13:30</option>
                                <option value="2" <?php if(getFormData('g_time') == 2) echo 'selected="selected"'; ?>>13:45</option>
                                <option value="3" <?php if(getFormData('g_time') == 3) echo 'selected="selected"'; ?>>14:00</option>
                                <option value="4" <?php if(getFormData('g_time') == 4) echo 'selected="selected"'; ?>>14:15</option>
                                <option value="5" <?php if(getFormData('g_time') == 5) echo 'selected="selected"'; ?>>14:30</option>
                                <option value="6" <?php if(getFormData('g_time') == 6) echo 'selected="selected"'; ?>>14:45</option>
                                <option value="7" <?php if(getFormData('g_time') == 7) echo 'selected="selected"'; ?>>15:00</option>
                                <option value="8" <?php if(getFormData('g_time') == 8) echo 'selected="selected"'; ?>>15:15</option>
                                <option value="9" <?php if(getFormData('g_time') == 9) echo 'selected="selected"'; ?>>15:30</option>
                                <option value="10" <?php if(getFormData('g_time') == 10) echo 'selected="selected"'; ?>>15:45</option>
                                <option value="11" <?php if(getFormData('g_time') == 11) echo 'selected="selected"'; ?>>16:00</option>
                                <option value="12" <?php if(getFormData('g_time') == 12) echo 'selected="selected"'; ?>>16:15</option>
                                <option value="13" <?php if(getFormData('g_time') == 13) echo 'selected="selected"'; ?>>16:30</option>
                                <option value="14" <?php if(getFormData('g_time') == 14) echo 'selected="selected"'; ?>>16:45</option>
                                <option value="15" <?php if(getFormData('g_time') == 15) echo 'selected="selected"'; ?>>16:30</option>
                             </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_time'])) echo $err_msg['g_time']; ?>
                        </div>
                       
                        <label class="<?php if(!empty($err_msg['g_black'])) echo 'err'; ?>">
                            黒番
                            <select name="g_black">
                                <option value="0" <?php if(empty(getFormData('g_black'))) echo 'selected="selected"'; ?>>▶︎選択してください</option>
                                <?php
                                foreach($dbPlayerData as $key => $val):
                                ?>
                                <option value="<?php echo $val['id']?>" <?php if(getFormData('g_black') === $val['id']) echo 'selected="selected"'; ?>><?php echo $val['userseiname'] ?></option>
                                <?php echo $val['userseiname']; ?>
                                <?php
                                endforeach;
                                ?>
                            </select>
                        </label>
                        <div class="username">
                            <?php if(!empty($err_msg['g_black'])) echo $err_msg['g_black']; ?>
                        </div>
                        
                        <label class="<?php if(!empty($err_msg['b_power'])) echo 'err'; ?>">
                            黒番 棋力
                             <select name="b_power">
                                <option value="0" <?php if(empty(getFormData('b_power'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <option value="1" <?php if(getFormData('b_power') == 1) echo 'selected="selected"'; ?>>20級</option>
                                <option value="2" <?php if(getFormData('b_power') == 2) echo 'selected="selected"'; ?>>19級</option>
                                <option value="3" <?php if(getFormData('b_power') == 3) echo 'selected="selected"'; ?>>18級</option>
                                <option value="4" <?php if(getFormData('b_power') == 4) echo 'selected="selected"'; ?>>17級</option>
                                <option value="5" <?php if(getFormData('b_power') == 5) echo 'selected="selected"'; ?>>16級</option>
                                <option value="6" <?php if(getFormData('b_power') == 6) echo 'selected="selected"'; ?>>15級</option>
                                <option value="7" <?php if(getFormData('b_power') == 7) echo 'selected="selected"'; ?>>14級</option>
                                <option value="8" <?php if(getFormData('b_power') == 8) echo 'selected="selected"'; ?>>13級</option>
                                <option value="9" <?php if(getFormData('b_power') == 9) echo 'selected="selected"'; ?>>12級</option>
                                <option value="10" <?php if(getFormData('b_power') == 10) echo 'selected="selected"'; ?>>11級</option>
                                <option value="11" <?php if(getFormData('b_power') == 11) echo 'selected="selected"'; ?>>10級</option>
                                <option value="12" <?php if(getFormData('b_power') == 12) echo 'selected="selected"'; ?>>9級</option>
                                <option value="13" <?php if(getFormData('b_power') == 13) echo 'selected="selected"'; ?>>8級</option>
                                <option value="14" <?php if(getFormData('b_power') == 14) echo 'selected="selected"'; ?>>7級</option>
                                <option value="15" <?php if(getFormData('b_power') == 15) echo 'selected="selected"'; ?>>6級</option>
                                <option value="16" <?php if(getFormData('b_power') == 16) echo 'selected="selected"'; ?>>5級</option>
                                <option value="17" <?php if(getFormData('b_power') == 17) echo 'selected="selected"'; ?>>4級</option>
                                <option value="18" <?php if(getFormData('b_power') == 18) echo 'selected="selected"'; ?>>3級</option>
                                <option value="19" <?php if(getFormData('b_power') == 19) echo 'selected="selected"'; ?>>2級</option>
                                <option value="20" <?php if(getFormData('b_power') == 20) echo 'selected="selected"'; ?>>1級</option>
                                <option value="21" <?php if(getFormData('b_power') == 21) echo 'selected="selected"'; ?>>初段</option>
                                <option value="22" <?php if(getFormData('b_power') == 22) echo 'selected="selected"'; ?>>二段</option>
                                <option value="23" <?php if(getFormData('b_power') == 23) echo 'selected="selected"'; ?>>三段</option>
                                <option value="24" <?php if(getFormData('b_power') == 24) echo 'selected="selected"'; ?>>四段</option>
                                <option value="25" <?php if(getFormData('b_power') == 25) echo 'selected="selected"'; ?>>五段</option>
                                <option value="26" <?php if(getFormData('b_power') == 26) echo 'selected="selected"'; ?>>六段</option>
                                <option value="27" <?php if(getFormData('b_power') == 27) echo 'selected="selected"'; ?>>七段</option>
                            </select>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['b_power'])) echo $err_msg['b_power']; ?>
                        </div>
                        

                        <label class="<?php if(!empty($err_msg['g_white'])) echo 'err'; ?>">
                            白番
                             <select name="g_white">
                                <option value="0" <?php if(empty(getFormData('g_white'))) echo 'selected="selected"'; ?>>▶︎選択してください</option>
                                <?php
                                foreach($dbPlayerData as $key => $val){
                                ?>
                                <option value="<?php echo $val['id']; ?>" <?php if(getFormData('g_white') === $val['id']) echo 'selected="selected"'; ?>><?php echo $val['userseiname'] ?></option>
                                <?php echo $val['userseiname']; ?>
                                <?php
                                }
                                ?>
                            </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_white'])) echo $err_msg['g_white']; ?>
                        </div>

                        <label class="<?php if(!empty($err_msg['w_power'])) echo 'err'; ?>">
                            白番 棋力
                             <select name="w_power">
                                <option value="0" <?php if(empty(getFormData('w_power'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <option value="1" <?php if(getFormData('w_power') == 1) echo 'selected="selected"'; ?>>20級</option>
                                <option value="2" <?php if(getFormData('w_power') == 2) echo 'selected="selected"'; ?>>19級</option>
                                <option value="3" <?php if(getFormData('w_power') == 3) echo 'selected="selected"'; ?>>18級</option>
                                <option value="4" <?php if(getFormData('w_power') == 4) echo 'selected="selected"'; ?>>17級</option>
                                <option value="5" <?php if(getFormData('w_power') == 5) echo 'selected="selected"'; ?>>16級</option>
                                <option value="6" <?php if(getFormData('w_power') == 6) echo 'selected="selected"'; ?>>15級</option>
                                <option value="7" <?php if(getFormData('w_power') == 7) echo 'selected="selected"'; ?>>14級</option>
                                <option value="8" <?php if(getFormData('w_power') == 8) echo 'selected="selected"'; ?>>13級</option>
                                <option value="9" <?php if(getFormData('w_power') == 9) echo 'selected="selected"'; ?>>12級</option>
                                <option value="10" <?php if(getFormData('w_power') == 10) echo 'selected="selected"'; ?>>11級</option>
                                <option value="11" <?php if(getFormData('w_power') == 11) echo 'selected="selected"'; ?>>10級</option>
                                <option value="12" <?php if(getFormData('w_power') == 12) echo 'selected="selected"'; ?>>9級</option>
                                <option value="13" <?php if(getFormData('w_power') == 13) echo 'selected="selected"'; ?>>8級</option>
                                <option value="14" <?php if(getFormData('w_power') == 14) echo 'selected="selected"'; ?>>7級</option>
                                <option value="15" <?php if(getFormData('w_power') == 15) echo 'selected="selected"'; ?>>6級</option>
                                <option value="16" <?php if(getFormData('w_power') == 16) echo 'selected="selected"'; ?>>5級</option>
                                <option value="17" <?php if(getFormData('w_power') == 17) echo 'selected="selected"'; ?>>4級</option>
                                <option value="18" <?php if(getFormData('w_power') == 18) echo 'selected="selected"'; ?>>3級</option>
                                <option value="19" <?php if(getFormData('w_power') == 19) echo 'selected="selected"'; ?>>2級</option>
                                <option value="20" <?php if(getFormData('w_power') == 20) echo 'selected="selected"'; ?>>1級</option>
                                <option value="21" <?php if(getFormData('w_power') == 21) echo 'selected="selected"'; ?>>初段</option>
                                <option value="22" <?php if(getFormData('w_power') == 22) echo 'selected="selected"'; ?>>二段</option>
                                <option value="23" <?php if(getFormData('w_power') == 23) echo 'selected="selected"'; ?>>三段</option>
                                <option value="24" <?php if(getFormData('w_power') == 24) echo 'selected="selected"'; ?>>四段</option>
                                <option value="25" <?php if(getFormData('w_power') == 25) echo 'selected="selected"'; ?>>五段</option>
                                <option value="26" <?php if(getFormData('w_power') == 26) echo 'selected="selected"'; ?>>六段</option>
                                <option value="27" <?php if(getFormData('w_power') == 27) echo 'selected="selected"'; ?>>七段</option>
                            </select>                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['w_power'])) echo $err_msg['w_power']; ?>
                        </div>
                                                           
                        <label class="<?php if(!empty($err_msg['g_teai'])) echo 'err'; ?>">
                            手合い
                             <select name="g_teai">
                                <option value="0" <?php if(empty(getFormData('g_teai'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <option value="1" <?php if(getFormData('g_teai') == 1) echo 'selected="selected"'; ?>>互先</option>
                                <option value="2" <?php if(getFormData('g_teai') == 2) echo 'selected="selected"'; ?>>定先</option>
                                <option value="3" <?php if(getFormData('g_teai') == 3) echo 'selected="selected"'; ?>>２子</option>
                                <option value="4" <?php if(getFormData('g_teai') == 4) echo 'selected="selected"'; ?>>３子</option>
                                <option value="5" <?php if(getFormData('g_teai') == 5) echo 'selected="selected"'; ?>>４子</option>
                                <option value="6" <?php if(getFormData('g_teai') == 6) echo 'selected="selected"'; ?>>５子</option>
                                <option value="7" <?php if(getFormData('g_teai') == 7) echo 'selected="selected"'; ?>>６子</option>
                                <option value="8" <?php if(getFormData('g_teai') == 8) echo 'selected="selected"'; ?>>７子</option>
                                <option value="9" <?php if(getFormData('g_teai') == 9) echo 'selected="selected"'; ?>>８子</option>
                                <option value="10" <?php if(getFormData('g_teai') == 10) echo 'selected="selected"'; ?>>９子</option>
                            </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_teai'])) echo $err_msg['g_teai']; ?>
                        </div>
                                             
                        <label class="<?php if(!empty($err_msg['g_result'])) echo 'err'; ?>">
                            勝敗結果（持碁は黒の勝利とします）
                           <select name="g_result">
                                <option value="0" <?php if(empty(getFormData('g_result'))) echo 'selected="selected"'; ?>>▶︎選択してください</option> 
                                <option value="1" <?php if(getFormData('g_result') == 1) echo 'selected="selected"'; ?>>黒の勝ち</option>
                                <option value="2" <?php if(getFormData('g_result') == 2) echo 'selected="selected"'; ?>>白の勝ち</option>
                             </select>
                        </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_result'])) echo $err_msg['g_result']; ?>
                        </div>
                                              
                        <label class="<?php if(!empty($err_msg['g_winHow'])) echo 'err'; ?>">
                            対局結果の詳細
                            <div class="form_radio">
                                <div class="form_radio_item"><input type="radio" name="g_winHow" class="option_radios" value="1"
                                 <?php if(getFormData('g_winHow') == 1) echo 'checked="checked"'; ?>>中押し</div>
                                <div class="form_radio_item"><input type="radio" name="g_winHow" class="option_radios" value="2"
                                 <?php if(getFormData('g_winHow') == 2) echo 'checked="checked"'; ?>>目数差</div>
                                <div class="form_radio_item"><input type="radio" name="g_winHow" class="option_radios" value="3"
                                 <?php if(getFormData('g_winHow') == 3) echo 'checked="checked"'; ?>>時間切れ</div>
                            </div>
                        </label>

                            
                        <label class="<?php if(!empty($err_msg['g_winHow_moku'])) echo 'err'; ?>">
                           目数差（半角数字で入力してください）
                           <input type="text" name="g_winHow_moku" value="<?php echo getFormData('g_winHow_moku'); ?>">
                         </label>
                        <div class="area-msg">
                            <?php if(!empty($err_msg['g_winHow_moku'])) echo $err_msg['g_winHow_moku']; ?>
                        </div>             
                        
                         <div class="btn-container">
                            <input type="submit" class="btn btn-mid" value="更新">
                        </div>
                   </form>
                </div>
              </div>
            </section>
                        
        
    <!-- side bar -->
        <?php
        require('sidebar_mypage.php');
        ?>
    </div>    
        
    <!-- footer -->
    
    <?php
    require('footer.php');
    ?>