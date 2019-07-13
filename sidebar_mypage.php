<section id="sidebar">
    <a href="registProduct.php">対局結果を記録する</a>
    <a href="index.php">対戦履歴を検索する</a>
    <?php
    if($_SESSION['user_id'] != 17){
    ?>
    <a href="profEdit.php">プロフィール編集</a>
    <a href="passEdit.php">パスワード変更</a>
    <a href="withdraw.php">退会</a>
    <?php
    }
    ?>
</section>

