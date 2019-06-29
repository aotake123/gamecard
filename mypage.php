<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('マイページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//画面表示用データ取得
//==============================
//GETデータを格納
$g_id = (!empty($_GET['g_id'])) ? $_GET['g_id'] : '';
//ソート基準GETパラメータ入手式
//$sort = (!empty($GET['sort'])) ? $_GET['sort'] : '';

//自分だけのIDデータを取得
$u_id = $_SESSION['user_id'];
//自分の対局件数を取得
$gameCount = getMyGameCount($u_id);
//自分の対局件数（勝利数）を取得
$gameCountWin = getMyGameCountWin($u_id);
$gameCountLose = $gameCount - $gameCountWin;
//棋力を取得
$myPower = getMyPower($u_id);
    debug('棋力データ：'.print_r($myPower,true));


//パラメータ改ざんチェック
//==============================
//GETパラメータはあるが、改ざんされている（URLをいじくった）場合、正しい対局データが取れないのでマイページへ遷移させる
    if(!empty($g_id) && empty($dbFormData)){
        debug('GETパラメータの対局IDが違います。');
        header("Location:mypage.php"); //マイページへ
    }

//ページネーション機構
//==============================
//表示件数
$listSpan = 10;
// カレントページのGETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1−1)*20 = 0、2ページ目なら(2−1)*20 = 20
//自分用の対局データを取得
$gameData = getMyGameList($currentMinNum,$listSpan,$u_id);
    debug('対局データ：'.print_r($gameData,true));
//DBから対局データを取得(件数や中身の情報取得)
//$dbProductData = getMyGame($currentMinNum,$listSpan,$sort);
//DBから対局者データを取得
$dbPlasyerData = getPlayer();


?>

<?php 
$siteTitle = 'HOME';
require('head.php');
?>

<body class="page-home page-2colum">
   <!-- menu -->

    <?php
        require('header.php');
    ?>
    
    <p id="js-show-msg" style="display:none;" class="msg-slide">
        <?php echo getSessionFlash('msg_success'); ?>
    </p>
    
    <!-- main contents -->
    <div id="contents" class="site-width">
        <section id="main">
            <h2>あなたの個人成績</h2>
             <div class="main_prof">
                 <div class="main_prof_left">
                    <img src="<?php echo sanitize($myPower[0]['pic']); ?>" alt="" class="mypage_img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">

                    <img src="<?php echo sanitize($myPower[0]['pic']); ?>" alt="" height="150px" width="150px">
                 </div>
                 <div class="main_prof_right">
                    <p><?php echo sanitize($myPower[0]['userseiname']); ?></p>
                    <p>現在の棋力：<?php echo sanitize($myPower[0]['power_data']); ?></p>
                    <p>戦績： <?php echo sanitize($gameCount); ?>戦<?php echo sanitize($gameCountWin); ?>勝<?php echo sanitize($gameCountLose); ?>敗</p>
                 </div>
             </div>
            <h2>あなたの対局結果一覧</h2>
            
            <div class="search-title">
                <div class="search-left">
                    <span class="total-num"><?php echo sanitize($gameData['total']); ?></span>件の対局履歴が見つかりました
                </div>
                <div class="search-right">
                    <span class="num"><?php echo $currentMinNum+1; ?></span> - <span class="num"><?php echo $currentMinNum+$listSpan; ?></span>件 / <span class="num"><?php echo sanitize($gameData['total']); ?></span>件中
                </div>
            </div>
            
       <div class="reco_wrap">
            <table class="record">
                <thead><tr>
                <th class="reco_item reco_date">日時</th>
                <th class="reco_black">黒番</th>
                <th class="reco_winlose">勝敗</th>
                <th class="reco_white">白番</th>
                <th class="reco_hand">手合</th>
                <th class="reco_eff">結果</th>
                <th class="reco_item"></th>
                </tr>
                
            <?php
                foreach($gameData['data'] as $key => $val):
            ?>            
                <tr>
                <td class="reco_item"><?php echo sanitize($val['g_year']); ?>/<?php echo sanitize($val['g_month']); ?>/<?php echo sanitize($val['g_date']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['userseiname']); ?><?php echo sanitize($val['power_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['result_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['userseiname2']); ?><?php echo sanitize($val['power_data2']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['teai_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['g_winHow_moku']),sanitize($val['winHow_data']); ?>
                <td class="reco_item reco_link">
                <?php if($val['user_id'] === $_SESSION['user_id']){ ?>
                    <a href="registProduct.php?g_id=<?php echo sanitize($val['g_id']); ?>">変更</a>
                <?php } ?>
                </td>
                </tr>
            <?php
             endforeach;
            ?> 
                 </thead>
            </table>           
        </div>
        
        <div class="pagination">
            <ul class="pagination-list">
            
     <?php pagination($currentPageNum, $gameData['total_page']); ?>
  
            </ul>
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