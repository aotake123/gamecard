<?php
//==============================
//ログ
//==============================

//ログを取るかどうか
ini_set('log_error','on');
//ログの出力ファイルを指定
ini_set('error_log','php.log');

//==============================
//デバッグ
//==============================
//デバッグフラグ
$debug_flg = true;
//デバッグログ関数
function debug($str){
    global $debug_flg;
    if(!empty($debug_flg)){
        error_log('デバッグ：'.$str);
    }
}

//==============================
//セッション準備・セッション有効期限を伸ばす
//==============================
//セッションファイルの置き場を変更する(/var/tmp/以下に置くと30日は削除されない)
session_save_path("/var/tmp");
//ガーベージコレクションが削除するセッションの有効期限を設定(30日以上経っているものに対してだけ100分の１の確率で削除)
ini_set('session.gc_maxlifetime',60*60*24*30);
//ブラウザを閉じても削除されないようにクッキー自体の有効期限を伸ばす
ini_set('session.cookie_lifetime', 60*60*24*30);
//セッションを使う
session_start();
//現在のセッションIDを新しく生成し直す（なりすましのセキュリティ対策）
session_regenerate_id();

//==============================
//画面表示処理開始ログ吐き出し関数
//==============================
function debugLogstart(){
    debug('>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>>画面表示処理開始');
    debug('セッションID：'.session_id());
    debug('セッション変数の中身：'.print_r($_SESSION,true));
    debug('現在日時タイムスタンプ：'.time());
    if(!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])){
        debug('ログイン期限日時タイムスタンプ：'.( $_SESSION['login_date'] + $_SESSION['login_limit']));   
    }
}

//==============================
//エラーメッセージ定数
//==============================

define('MSG01','入力必須です');
define('MSG02','Emailの形式で入力してください');
define('MSG03','パスワード(再入力が合っていません)');
define('MSG04','半角英数字のみご利用頂けます');
define('MSG05','6文字以上で入力してください');
define('MSG06','256文字以内で入力してください');
define('MSG07','エラーが発生しました。しばらく経ってからやり直してください');
define('MSG08','そのEmailは既に登録されています');
define('MSG09','メールアドレスまたはパスワードが違います');
define('MSG10','電話番号の形式が違います');
define('MSG11','郵便番号の形式が違います');
define('MSG12','古いパスワードが違います');
define('MSG13','古いパスワードと同じです');
define('MSG14','文字で入力してください。');
define('MSG15','正しくありません。');
define('MSG16','有効期限が切れています');
define('MSG17','半角英数字のみご使用頂けます。');
define('MSG18','黒番と白番のどちらにもあなたが含まれていません');
define('MSG19','黒番と白番に同一のプレイヤーが入力されています');
define('SUC01','パスワードを変更しました');
define('SUC02','プロフィールを変更しました');
define('SUC03','メールを送信しました');
define('SUC04','登録しました');



//==============================
//グローバル変数
//==============================

//エラーメッセージ格納用の配列
$err_msg = array();

//==============================
//バリデーション関数
//==============================

//バリデーション関数（入出力チェック）
function validrequired($str,$key){
    if($str === ''){    //金額フォームなどを考えると数値の0はOKにし、空文字はダメにする
        global $err_msg;
        $err_msg[$key] = MSG01;
    }
}
//バリデーション関数（Email形式チェック）
function validEmail($str,$key){
    if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG02;
    }
}
//バリデーション関数（同値かチェック）
function validMatch($str1,$str2,$key){
    if($str1 !== $str2){
        global $err_msg;
        $err_msg[$key] = MSG03;
    }
}
//バリデーション関数（最小文字数チェック）
function validMinLen($str,$key,$min = 6){
    if(mb_strlen($str) < $min){
        global $err_msg;
        $err_msg[$key] = MSG05;
    } 
}
//バリデーション関数（最大文字数チェック）
function validMaxLen($str,$key,$max = 256){
    if(mb_strlen($str) > $max ){
        global $err_msg;
        $err_msg[$key] = MSG06;
    }
}
//バリデーション関数（半角チェック）
function validHalf($str,$key){
   if(!preg_match("/^[a-zA-Z0-9]+$/", $str)){
       global $err_msg;
       $err_msg[$key] = MSG04;
   }
}
//固定長チェック
function validLength($str, $key, $len = 8){
    if(mb_strlen($str) !== $len){
        global $err_msg;
        $err_msg[$key] = $len . MSG14;
    }
}
//半角数字チェック
function validNumber($str,$key){
    if(!preg_match("/^[0-9]+$/", $str)){
        global $err_msg;
        $err_msg[$key] = MSG15;
    }
}

//パスワードチェック
function validPass($str,$key){
    //半角英数字チェック
    validHalf($str, $key);
    //最大文字数チェック
    validMaxLen($str, $key);
    //最小文字数チェック
    validMinLen($str, $key);
}

//エラーメッセージ表示
function getErrMsg($key){
    global $err_msg;
    if(!empty($err_msg[$key])){
        return $err_msg[$key];
    }
}

//バリデーション関数（Email重複確認用関数）
function validEmailDup($str){
    global $err_msg;
    //例外処理
    try{
        //DB接続
        $dbh = dbconnect();
        
        //SQL文作成
        //$sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        //$data = array(':email' => $email);
        //SQL文（クエリー作成）
        //$stmt = $dbh->prepare('SELECT count(*) FROM users2 WHERE email = :email');
        //クエリ実行
        //$stmt = queryPost($dbh,$sql,$data);

        //SQL文作成
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
        $data = array(':email' => $str);
        //SQL文（クエリー作成）
        $stmt = $dbh->prepare('SELECT count(*) FROM users WHERE email = :email');
        //プレースホルダに値をセットし、SQL文を実行
        $stmt->execute(array(':email' => $str)); 
 
        //クエリ結果の値を取得
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        
        //array_shift関数は配列の先頭を取り出す関数です。クエリ結果は配列形式で入っているので、array_shiftで先頭を取り出して判定します
        if(!empty(array_shift($result))){
            $err_msg['email'] = MSG08;
        }   
    } catch(Exception $e){
        error_log('エラー発生：' . $e->getMessage());
        $err_msg['email'] = MSG07;
    }
}

//selectboxチェック
function validSelect($str,$key){
    if(!preg_match("/^[0-9]+$/",$str)){
        global $err_msg;
        $err_msg['key'] = MSG15;
    }
}

//DB接続関数
function dbConnect(){
    //DBへの接続準備
    $dsn = 'mysql:dbname=gamecard;host=localhost;charset=utf8';
    $user = 'root';
    $password = 'root';
    $options = array(
        //SQL実行時に例外をスロー
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // デフォルトフェッチモードを連想配列形式に設定
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
        // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
        PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        );
    //PDOオブジェクト生成
    $dbh = new PDO($dsn, $user, $password, $options);
    return $dbh;
}


//DBクエリ実行関数
function queryPost($dbh, $sql, $data){
  //クエリー作成
  $stmt = $dbh->prepare($sql);
  //プレースホルダに値をセットし、SQL文を実行
  if(!$stmt->execute($data)){
    debug('クエリに失敗しました。');
    debug('失敗したSQL：'.print_r($stmt,true));
    $err_msg['common'] = MSG07;
    return 0;
  }
  debug('クエリ成功。');
  return $stmt;
}

function getUser($u_id){
    debug('ユーザー情報を取得します。');
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        //クエリ結果のデータを1レコード返却
        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
        
    } catch(Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
    //取得してきたクエリ結果のデータを返却
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getProduct($u_id, $p_id){
    debug('対局情報を取得します');
    debug('ユーザーID：'.u_id);
    debug('対局ID：'.$g_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM game WHERE id = :u_id AND g_id = :g_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id, 'g_id' => $g_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //クエリ結果のデータを1レコード返却
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}
function getPlayer(){
    debug('対局者情報を取得します。');
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //SQL文作成
        $sql = 'SELECT id,userseiname FROM users WHERE delete_flg = 0';
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            //クエリ結果の全データを取得
            return $stmt->fetchAll();
        }else{
            return false;
        }
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getProductList($currentMinNum = 1,$player,$span = 20){
    debug('対局件数や中身などいろいろな情報を取得します。');
    debug('playerの中身：'.$player);

    //例外処理
    try{
        
        //DBへ接続
        $dbh = dbConnect();
        //件数用のSQL文作成
        $sql = 'SELECT g_id FROM game';
        if(!empty($player)) {
            $sql .= ' WHERE g_black = '.$player;
            $sql .= ' OR g_white = '.$player;
         }
        //if(!empty($player2 && $player !== 0)){
            //$sql .= ' WHERE g_black = '.$player2;
            //$sql .= ' OR WHERE g_white = '.$player2;
        //}
        $data = array();
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount(); //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span); //総ページ数 ceilは切り上げ関数（端数も含めてページを出してる）
        if(!$stmt){
            return false;
        }
        
        $sql = 'SELECT * FROM game AS g
            LEFT JOIN users AS u1 ON g.g_black = u1.id
            LEFT JOIN users2 AS u2 ON g.g_white = u2.id
            INNER JOIN power AS p ON g.b_power = p.power_id
            INNER JOIN power2 AS p2 ON g.w_power = p2.power_id2
            INNER JOIN teai AS t ON g.g_teai = t.teai_id
            INNER JOIN time AS ti ON g.g_time = ti.time_id
            LEFT JOIN result AS r ON g.g_result = r.result_id
            LEFT JOIN winHow AS w ON g.g_winHow = w.winHow_id
            ';
        
        //ページング用のSQL文作成
        if(!empty($player)) {
            $sql .= ' WHERE g_black = '.$player;
            $sql .= ' OR g_white = '.$player;
         }
        //並び替え
        $sql .= ' ORDER BY g_year DESC,g_month DESC,g_date DESC,g_time DESC ';
        //ページング表示
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        
        $data = array();
        debug('SQL文の表示：'.$sql);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //クエリ結果のデータを全レコードを格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getMyGameList($currentMinNum = 1,$span = 10,$u_id){
    debug('自分だけの対局情報を取得します');
    debug('ユーザーID：'.$u_id);
   debug('対局件数や中身などいろいろな情報を取得します。');
    //例外処理
    try{
        //DBへ接続
        $dbh = dbConnect();
        //件数用のSQL文作成
        $sql = 'SELECT g_id FROM game WHERE g_black = :u_id AND delete_flg = 0
                OR g_white = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        $rst['total'] = $stmt->rowCount(); //総レコード数
        $rst['total_page'] = ceil($rst['total']/$span); //総ページ数 ceilは切り上げ関数（端数も含めてページを出してる）
        if(!$stmt){
            return false;
        }
        
        //ページング用のSQL文作成
        $sql = 'SELECT * FROM game AS g
            LEFT JOIN users AS u1 ON g.g_black = u1.id
            LEFT JOIN users2 AS u2 ON g.g_white = u2.id
            INNER JOIN power AS p ON g.b_power = p.power_id
            INNER JOIN power2 AS p2 ON g.w_power = p2.power_id2
            INNER JOIN teai AS t ON g.g_teai = t.teai_id
            INNER JOIN time AS ti ON g.g_time = ti.time_id
            LEFT JOIN result AS r ON g.g_result = r.result_id
            LEFT JOIN winHow AS w ON g.g_winHow = w.winHow_id
            WHERE g_black = :u_id OR g_white = :u_id
            ORDER BY g_year DESC,g_month DESC,g_date DESC,g_time DESC
            ';

        //検索用欄
        $sql .= ' LIMIT '.$span.' OFFSET '.$currentMinNum;
        $data = array(':u_id' => $u_id);
        debug('SQL：'.$sql);
        //クエリ実行
        $stmt = queryPost($dbh, $sql, $data);
        
        if($stmt){
            //クエリ結果のデータを全レコードを格納
            $rst['data'] = $stmt->fetchAll();
            return $rst;
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}


function getMyGameCount($u_id){
    debug('自分だけの対局件数を取得します');
    debug('ユーザーID：'.$u_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM game WHERE g_black = :u_id AND delete_flg = 0 OR g_white = :u_id AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            //件数を変数に返す
            return $stmt->rowCount(); //総レコード数
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getMyGameCountWin($u_id){
    debug('自分だけの対局勝利件数を取得します');
    debug('ユーザーID：'.$u_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM game WHERE g_black = :u_id AND g_result = 1 AND delete_flg = 0
                OR g_white = :u_id AND g_result = 1 AND delete_flg = 0';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
             debug('クエリ中身勝利件数：'.print_r($stmt,true));

        
        if($stmt){
            //件数を変数に返す
            return $stmt->rowCount(); //総レコード数
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getMyPower($u_id){
    debug('自分だけの棋力を取得します');
    debug('ユーザーID：'.$u_id);
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM users AS u
        INNER JOIN power AS p ON u.power = p.power_id
        WHERE id = :u_id';
        $data = array(':u_id' => $u_id);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);
           debug('クエリ中身棋力：'.print_r($stmt,true));
 
        
        if($stmt){
            return $stmt->fetchAll();
            
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}

function getWhiteName($g_white){
    debug('対局毎の白番の対局者情報を一時取得し保存します');
    //例外処理
    try{
        //DB接続
        $dbh = dbConnect();
        $sql = 'SELECT * FROM game AS g
            INNER JOIN users AS u ON g.g_white = u.id
            WHERE g_white = g_white';
        $data = array('g_white' => $g_white);
        //クエリ実行
        $stmt = queryPost($dbh,$sql,$data);

        if($stmt){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
        
    } catch (Exception $e){
        error_log('エラー発生：' . $e->getMessage());
    }
}


//==============================
//メール送信
//==============================
function sendMail($from,$to,$subject,$comment){
    if(!empty($to) && !empty($subject) && !empty($comment)){
        //文字化けしないように設定（お決まりパターン）
        mb_language("Japanese"); //現在使っている言語を設定する
        mb_internal_encoding("UTF-8"); //内部の日本語をどうエンコーディング（機械が分かる言葉へ変換）するかを設計
        
        //メールを送信（送信結果はtrueかfalseで返ってくる）
        $result = mb_send_mail($to, $subject, $comment, "From:".$from);
        //送信結果を判定
        if($result){
            debug('メールを送信しました。');
        }else{
            debug('【エラー発生】メールの送信に失敗しました。');
        }
    }
}

//==============================
//その他
//==============================
//サニタイズ
function sanitize($str){
    return htmlspecialchars($str,ENT_QUOTES);
}

//フォーム入力保持
function getFormData($str, $flg = false){
    if($flg){
        $method = $_GET;
    }else{
        $method = $_POST;
    }
    global $dbFormData;
    //ユーザーデータがある場合
    if(!empty($dbFormData)){
        //フォームのエラーがある場合
        if(!empty($err_msg[$str])){
            //POSTにデータがある場合
            if(isset($method[$str])){
                return sanitize($method[$str]);
            }else{
                //ない場合（基本ありえない）はDBの情報を表示
                return sanitize($dbFormData[$str]);
            }
        }else{
            //POSTにデータがあり、DBの情報と違う場合
            if(isset($method[$str]) && $method[$str] !== $dbFormData[$str]){
                return sanitize($method[$str]);
            }else{
                return sanitize($dbFormData[$str]);
            }
        }
    }else{
        if(isset($method[$str])){
            return sanitize($method[$str]);
        }
    }
}

function getSessionFlash($key){
    if(!empty($_SESSION[$key])){
        $data = $_SESSION[$key];
        $_SESSION[$key] = '';
        return $data;
    }
}

function makeRandKey($length = 8) {
    static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $str = '';
    for ($i = 0; $i < $length; ++$i) {
        $str .= $chars[mt_rand(0, 61)];
    }
    return $str;
}
// 画像処理
function uploadImg($file, $key){
  debug('画像アップロード処理開始');
  debug('FILE情報：'.print_r($file,true));
  
  if (isset($file['error']) && is_int($file['error'])) {
    try {
      // バリデーション
      // $file['error'] の値を確認。配列内には「UPLOAD_ERR_OK」などの定数が入っている。
      //「UPLOAD_ERR_OK」などの定数はphpでファイルアップロード時に自動的に定義される。定数には値として0や1などの数値が入っている。
      switch ($file['error']) {
          case UPLOAD_ERR_OK: // OK
              break;
          case UPLOAD_ERR_NO_FILE:   // ファイル未選択の場合
              throw new RuntimeException('ファイルが選択されていません');
          case UPLOAD_ERR_INI_SIZE:  // php.ini定義の最大サイズが超過した場合
          case UPLOAD_ERR_FORM_SIZE: // フォーム定義の最大サイズ超過した場合
              throw new RuntimeException('ファイルサイズが大きすぎます');
          default: // その他の場合
              throw new RuntimeException('その他のエラーが発生しました');
      }
      
      // $file['mime']の値はブラウザ側で偽装可能なので、MIMEタイプを自前でチェックする
      // exif_imagetype関数は「IMAGETYPE_GIF」「IMAGETYPE_JPEG」などの定数を返す
      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) { // 第三引数にはtrueを設定すると厳密にチェックしてくれるので必ずつける
          throw new RuntimeException('画像形式が未対応です');
      }

      // ファイルデータからSHA-1ハッシュを取ってファイル名を決定し、ファイルを保存する
      // ハッシュ化しておかないとアップロードされたファイル名そのままで保存してしまうと同じファイル名がアップロードされる可能性があり、
      // DBにパスを保存した場合、どっちの画像のパスなのか判断つかなくなってしまう
      // image_type_to_extension関数はファイルの拡張子を取得するもの
      $path = 'uploads/'.sha1_file($file['tmp_name']).image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) { //ファイルを移動する
          throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      // 保存したファイルパスのパーミッション（権限）を変更する
      chmod($path, 0644);
      
      debug('ファイルは正常にアップロードされました');
      debug('ファイルパス：'.$path);
      return $path;

    } catch (RuntimeException $e) {

      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();

    }
  }
}

//ページング
// $currentPageNum : 現在のページ数
// $totalPageNum : 総ページ数
// $link : 検索用GETパラメータリンク
// $pageColNum : ページネーション表示数
function pagination( $currentPageNum, $totalPageNum, $link = '', $pageColNum = 5){
  // 現在のページが、総ページ数と同じ　かつ　総ページ数が表示項目数以上なら、左にリンク４個出す
  if( $currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  // 現在のページが、総ページ数の１ページ前なら、左にリンク３個、右に１個出す
  }elseif( $currentPageNum == ($totalPageNum-1) && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  // 現ページが2の場合は左にリンク１個、右にリンク３個だす。
  }elseif( $currentPageNum == 2 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  // 現ページが1の場合は左に何も出さない。右に５個出す。
  }elseif( $currentPageNum == 1 && $totalPageNum >= $pageColNum){
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  // 総ページ数が表示項目数より少ない場合は、総ページ数をループのMax、ループのMinを１に設定
  }elseif($totalPageNum < $pageColNum){
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  // それ以外は左に２個出す。
  }else{
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  
  echo '<div class="pagination">';
    echo '<ul class="pagination-list">';
      if($currentPageNum != 1){
        echo '<li class="list-item"><a href="?p=1&p_id='.$link.'">&lt;</a></li>';
      }
      for($i = $minPageNum; $i <= $maxPageNum; $i++){
        echo '<li class="list-item ';
        if($currentPageNum == $i ){ echo 'active'; }
        echo '"><a href="?p='.$i.'&p_id='.$link.'">'.$i.'</a></li>';
      }
      if($currentPageNum != $maxPageNum){
        echo '<li class="list-item"><a href="?p='.$maxPageNum.'&p_id='.$link.'">&gt;</a></li>';
      }
    echo '</ul>';
  echo '</div>';
}

//画像表示用関数
function showImg($path){
    if(empty($path)){
        return 'img/sample-img.png';
    }else{
        return $path;
    }
}

//GETパラメータ付与
// $del_key :付与から取り除きたいGETパラメータのキー
function appendGetParam($arr_del_key = array()){
    if(!empty($_GET)){
        $str = '?';
        foreach($_GET as $key =>$val){
            if(!in_array($key,$arr_del_key,true)){ //取り除きたいパラメータじゃない場合にurlにくっつけるパラメータを生成
                $str .= $key. '=' .$val. '&';
            }
        }
        $str = mb_substr($str, 0, -1, "UTF-8");
        echo $str;
    }
}

?>