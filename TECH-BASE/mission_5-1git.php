<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
</head>
</html>
<?php
//4-1.DB接続設定
$dsn = 'mysql:dbname=データベース名;host=localhost';
$user = 'ユーザ名';
$DB_password = 'パスワード';
$pdo = new PDO($dsn, $user, $DB_password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

//4-2.テーブルがなければテーブルを新たに作成する。これはif文にする必要がない。
$sql = "CREATE TABLE IF NOT EXISTS tbm5_1"
." ("
. "id INT AUTO_INCREMENT PRIMARY KEY,"
. "name CHAR(32),"
. "comment TEXT,"
. "post_date DATETIME," // 追加: 投稿日時を保存するフィールド
. "password VARCHAR(255)" // 追加: パスワード用のカラム
.");";
$stmt = $pdo->query($sql);

//編集対象番号テキストボックスを基本は非表示
$editform_num_type ='hidden';

//パスワードテキストボックスを基本は表示
$password_type = 'text';

//エラーが起きないように変数を初期化・定義
$edit_name = '';
$edit_comment = '';
$edit_num = '';
$password = '';

//4-5.投稿モード.
// 送信が押されたときの処理。
if(isset($_POST["submit"]) && !empty($_POST['comment'])){

    //名前が入力されてなければ「名無しさん」にする。コメントが無ければ投稿されないように。
    if(!empty($_POST['name'])){
        $name = $_POST["name"];
        }else{
        $name = '名無しさん';
        }
    $comment = $_POST["comment"];
    $password = $_POST['password'];
    
    //通常時の送信フォーム
    if(empty($_POST["post_num"])){

        //時間を記録
        $post_date = date("Y/m/d H:i:s");

        //テーブルに記入.SQL
        $sql = "INSERT INTO tbm5_1 (name, comment, post_date, password) VALUES (:name, :comment, :post_date, :password)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':post_date', $post_date, PDO::PARAM_STR);
        $stmt->bindParam(':password', $password, PDO::PARAM_STR);
        $stmt->execute();
    }

    //編集モードの送信フォーム
    elseif(!empty($_POST["post_num"])){
        //編集対象番号テキストボックスから受け取る
        $edit_post_num = $_POST["post_num"];
    
        //エラーが出ないよう初期化
        $row_password = '';
    
        //パスワードをテキストボックスから受け取る
        $password = $_POST['password'];
    
        //sql実行.投稿済みのパスワードを抽出
        $sql = 'SELECT * FROM tbm5_1 WHERE id=:id';
        $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
        $stmt->bindParam(':id', $edit_post_num, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
        $stmt->execute();                              // ←SQLを実行する。
        $results = $stmt->fetchAll();
        foreach ($results as $row){
            //$rowの中にはテーブルのカラム名が入る。sqlの結果からパスワードを抽出
            $row_password = $row['password'];
        }

        //編集対象番号とパスワードチェック
        if($row_password == $password && !empty($password)){
            //4-7.該当番号の投稿内容をデータベースから編集
            $sql = 'UPDATE tbm5_1 SET name=:name,comment=:comment WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindParam(':id', $edit_post_num, PDO::PARAM_INT);
            // $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();
        }else{}

    }
}

//編集モード.bindParamの引数（:nameなど）は4-2でどんな名前のカラムを設定したかで変える必要がある。
elseif(isset($_POST['edit']) && !empty($_POST['edit_num'])){

    //4-6.変更する投稿番号、名前、変更したいコメントはフォームの変数から持ってくる
    //編集ボタン押下後、編集番号に入力されていた数字を編集対象番号テキストボックスにセット
    $edit_post_num = $_POST['edit_num'];
    $password = $_POST['password'];
    
    //エラーが出ないように初期化・定義
    $row_password = '';

    //$rowの中にはテーブルのカラム名が入る。sqlの結果からパスワードを抽出
    $sql = 'SELECT * FROM tbm5_1 WHERE id=:id';
    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->bindParam(':id', $edit_post_num, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
    $stmt->execute();                              // ←SQLを実行する。
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        $row_password = $row['password'];
    }

    if($row_password == $password && !empty($row_password)){
        //編集対象番号テキストボックスを表示
        $editform_num_type = "text";

        //$rowの中にはテーブルのカラム名が入る。sqlの結果から名前とコメントを抽出
        foreach ($results as $row){
            $edit_name = $row['name'];
            $edit_comment = $row['comment'];
            $edit_num = $row['id'];
        }

    }elseif($row_password != $password or empty($row_password)){//投稿にパスワードが無ければ編集できない
        //編集対象番号テキストボックスを非表示
        $editform_num_type = "hidden";
    }else{}//何もしない
}

//4-8.削除モード
elseif(isset($_POST['remove']) && !empty($_POST['remove_num'])){
    //フォームから削除番号を受け取る
    $remove_num = $_POST['remove_num'];

    //エラーが出ないよう初期化
    $row_password = '';

    //パスワードをテキストボックスから受け取る
    $password = $_POST['password'];

    //sql実行.投稿済みのパスワードを抽出
    $sql = 'SELECT * FROM tbm5_1 WHERE id=:id';
    $stmt = $pdo->prepare($sql);                  // ←差し替えるパラメータを含めて記述したSQLを準備し、
    $stmt->bindParam(':id', $remove_num, PDO::PARAM_INT); // ←その差し替えるパラメータの値を指定してから、
    $stmt->execute();                              // ←SQLを実行する。
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る。sqlの結果からパスワードを抽出
        $row_password = $row['password'];
    }

    //パスワードチェック
    if($row_password == $password && !empty($row_password)){
        //該当番号の投稿内容をデータベースから削除
        //削除したい番号をフォームの変数から持ってくる
        $sql = 'delete from tbm5_1 where id=:id';//":id"プレースホルダーで経由
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $remove_num, PDO::PARAM_INT);//プレースホルダー":id"に変数$remove_numを埋め込む
        $stmt->execute();
    }else{//投稿にパスワードが無ければ削除できない
    }
}
?>

<html><!-- (html)フォーム作成 -->
<body>
    <!--タイトル-->
    <span style="font-size: 50px;">掲示板</span>
    
    <!-- 注意書き -->
    <span style="font-size: 15px;" ><br>※削除、編集にはパスワードが必要です。パスワードが無い投稿は編集及び削除ができません。</span>

    <form action="" method="post">
    <!--（新規）送信フォーム-->
    <input type="text" name="name" placeholder= "名前" value= <?= $edit_name ?>>
    <input type="text" name="comment" placeholder="コメント" value= <?= $edit_comment ?>>
        <!--（編集時にだけ表示する編集対象番号テキストボックス）-->
        <input type="<?= $editform_num_type ?>"  name="post_num" placeholder="編集対象番号" value= <?= $edit_num ?> >
    <input type="submit" name="submit" value = "送信"><br>

    <!--削除番号指定フォーム-->
    <input type="text" name="remove_num" placeholder="削除番号">
    <input type="submit" name="remove" value = "削除"><br>

    <!--編集フォーム-->
    <input type="text" name="edit_num" placeholder="編集番号">
    <input type="submit" name="edit" value = "編集"><br>

    <!-- パスワードテキストボックス -->
    <input type="<?= $password_type ?>" name="password" placeholder="パスワード" ><br><br>
    <hr><!-- 水平線のタグ -->

</body>
</html>

<?php
//4-6.テーブル内のデータレコードを抽出して表示する（投稿番号、名前、コメントを表示。パスワードは無し。）
//$rowの添字（[ ]内）は、4-2で作成したカラムの名称に合わせる必要があります。
$sql = 'SELECT * FROM tbm5_1';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach ($results as $row){
    //$rowの中にはテーブルのカラム名が入る
    echo $row['id'].' ';
    echo '名前：' . '<span style="color: red;">' . $row['name'] . '</span>' .'  :';
    echo $row['post_date'].'<br>';

    //HTML 上では連続したスペースが 1 つにまとめられるため、
    //そのままでは効果がないことがある
    //その代わりに、HTML 上では&nbsp;（ノーブレークスペース）を使用すると、
    //連続したスペースがそのまま表示される。
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $row['comment'];
    
    echo '<br><br>';
    //<hr>は水平の横線を引くためのタグ
    }
?>