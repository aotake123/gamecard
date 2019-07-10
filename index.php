<?php

//共通変数・関数ファイル
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug('対局カード トップページ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//ログイン認証
require('auth.php');

//==============================
//iNDEX画面処理
//==============================

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
//GETデータを格納
//================================
// カレントページのGETパラメータを取得
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1; //デフォルトは１ページめ
//プレイヤーID
$player = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
//対局ID
$g_id = (!empty($_GET['g_id'])) ? $_GET['g_id'] : '';
// パラメータに不正な値が入っているかチェック
if(!is_int((int)$currentPageNum)){
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
//DBから対局者データを取得
$dbPlayerData = getPlayer();
//DBから商品データを取得
$dbFormData = (!empty($g_id)) ? getProduct($_SESSION['user_id'], $g_id) : '';


// ページネーション関連
//================================
//表示件数
$listSpan = 20;
//現在の表示レコード先頭を算出
$currentMinNum = (($currentPageNum-1)*$listSpan); //1ページ目なら(1−1)*20 = 0、2ページ目なら(2−1)*20 = 20
//DBから対局データを取得(件数や中身の情報取得)
$dbProductData = getProductList($currentMinNum,$player);
//debug('現在のページ：'.$currentPageNum);
//debug('DB用データ：'.print_r($dbFormData,true));
//debug('対局データ：'.print_r($dbProductData,true));
//debug('対局者データ：'.print_r($dbPlayerData,true));

//POST送信処理
//==============================
if(!empty($_POST)){
    debug('POST送信があります。');
    //debug('POST情報：'.print_r($_POST,true));
}
debug('画面表示処理終了　<<<<<<<<<<<<<<<<<<<<<<');

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
    
    <!-- main contents -->
    <div id="contents" class="site-width">
      
       
       <section id="sidebar">
           <form name="" method="get">
               <h1 class="title">対局者から検索</h1>
               <div class="search">
                            <select name="p_id">
                                <option value="0" <?php if(empty(getFormData('p_id',true))) echo 'selected="selected"'; ?>>▶︎選択してください</option>
                                <?php
                                foreach($dbPlayerData as $key => $val){
                                ?>
                                <option value="<?php echo sanitize($val['id']);?>" <?php if(getFormData('p_id',true) == $val['id']){ echo 'selected="selected"'; } ?>><?php echo sanitize($val['userseiname']); ?></option>
                                <?php echo sanitize($val['userseiname']); ?>
                                <?php
                                }
                                ?>
                            </select>
                </div>
                            
<!--               <h1 class="title">対局者2</h1>
                            <select name="p_id2">
                                <option value="0" <?php if(empty(getFormData('p_id',true))) echo 'selected="selected"'; ?>>▶︎選択してください</option>
                                <?php
                                foreach($dbPlayerData as $key => $val){
                                ?>
                                <option value="<?php echo sanitize($val['id']);?>" <?php if(getFormData('c_id',true) == $val['id']){ echo 'selected="selected"'; } ?>><?php echo sanitize($val['userseiname']); ?></option>
                                <?php echo sanitize($val['userseiname']); ?>
                                <?php
                                }
                                ?>
                            </select>
-->
               <input type="submit" value="検索">
           </form>
       </section>
       
        <section id="main">
            <h2>全対局結果の一覧</h2>
        
        <div class="search-title">
            <div class="search-left">
                <span class="total-num"><?php echo sanitize($dbProductData['total']); ?></span>件の対局履歴が見つかりました
            </div>
            <div class="search-right">
                <span class="num"><?php echo sanitize($currentMinNum)+1; ?></span> - <span class="num"><?php echo $currentMinNum+$listSpan; ?></span>件 / <span class="num"><?php echo sanitize($dbProductData['total']); ?></span>件中
            </div>
        </div>
        
        <div class="panel-list">
           
        <div class="reco_wrap">
            <table class="record">
                <thead><tr>
                <th class="reco_item reco_date2">日時</th>
                <th class="reco_black">黒番</th>
                <th class="reco_winlose">勝敗</th>
                <th class="reco_white">白番</th>
                <th class="reco_hand">手合</th>
                <th class="reco_eff">結果</th>
                </tr>

            <?php
                foreach($dbProductData['data'] as $key => $val):
            ?>            
                <tr>
                <td class="reco_item"><?php echo sanitize($val['g_year']); ?>/<?php echo sanitize($val['g_month']); ?>/<?php echo sanitize($val['g_date']); ?> <?php echo sanitize($val['time_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['userseiname']); ?><?php echo sanitize($val['power_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['result_data']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['userseiname2']); ?><?php echo sanitize($val['power_data2']); ?></td>
                <td class="reco_item"><?php echo sanitize($val['teai_data']); ?></td>
                <td class="reco_item">
                    <?php
                    if(!empty($val['g_winHow_moku'])){
                            (string)$g_winHow_moku = $val['g_winHow_moku'];
                            $g_winHow_moku = str_replace('0.5','半',$g_winHow_moku);
                            if(strpos($g_winHow_moku,'.5') !== false){
                                $g_winHow_moku = str_replace('.5','目半差',$g_winHow_moku);
                                $val['winHow_data'] = "";
                            }
                            $replace = str_replace('.0',"",$g_winHow_moku);
                    }else{
                        $replace = "";
                    }
                    ?>
                <?php echo sanitize($replace),sanitize($val['winHow_data']); ?>
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
            
     <?php pagination($currentPageNum, $dbProductData['total_page'], $_GET['p_id']); ?>
  
            </ul>
        </div>

        </section>
               
        </div>
                
    <!-- footer -->
         <?php
        require('footer.php');
        ?>  