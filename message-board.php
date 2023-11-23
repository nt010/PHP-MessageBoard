<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>message-board</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    //データベースへの接続
    $dsn = 'mysql:dbname=php_db;host=localhost';
    $user = 'root';
    $password = '';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    //データベース内にテーブルを作成
    $sql = "CREATE TABLE IF NOT EXISTS `message-board`"."(". "id INT AUTO_INCREMENT PRIMARY KEY,". "name char(252),". "comment TEXT,". "time TEXT,". "password TEXT". ");";
    $stmt = $pdo->query($sql);
    //新規投稿・編集登録用
    if (!empty($_POST["str1"])&&!empty($_POST["str2"])&&!empty($_POST["password"])) {
        $name = $_POST["str1"];
        $comment = $_POST["str2"];
        // タイムゾーンを設定
        date_default_timezone_set('Asia/Tokyo');
        $time = date('Y/m/d/H:i:s');
        $pw=$_POST["password"];
        //編集のサインがあった場合
        if(!empty($_POST["sign"])){
            $id=$_POST["sign"];
            $sql='UPDATE `message-board` SET name=:name,comment=:comment,time=:time,password=:password WHERE id=:id';
            $stmt=$pdo->prepare($sql);
            $stmt->bindValue(':name', $name, PDO::PARAM_STR);
            $stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
            $stmt->bindValue(':time', $time,PDO::PARAM_STR);
            $stmt->bindValue(':password',$pw,PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            //$resultにオブジェクトを格納
            $result=$stmt->fetchAll();
            //debug: print_r($result);
        }else if($name&&$comment){
            $sql = $pdo -> prepare("INSERT INTO `message-board` (name, comment, time, password) VALUES (:name, :comment, :time, :password)");
            $sql -> bindValue(':name', $name, PDO::PARAM_STR);
            $sql -> bindValue(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindValue(':time', $time, PDO::PARAM_STR);
            $sql -> bindValue(':password', $pw, PDO::PARAM_STR);
            $sql -> execute();  
        }
    }
    //削除用
    if(!empty($_POST["del"])){
        $deleteid=$_POST["del"];
        $dpass=$_POST["d-password"];
        
        //削除予定のidにあるデータを変数に格納
        $stmt=$pdo->prepare('SELECT * FROM `message-board` WHERE id=:id');
        $stmt->bindParam(':id',$deleteid,PDO::PARAM_INT);
        $stmt->execute();
        //$resultにオブジェクトを格納
        $result=$stmt->fetchAll();

        $signal=true;
        //削除番号が正しければ$tpassに正しいパスワードを格納
        if(!empty($result)){
            $tpass=$result[0][4];
        }else{
            $alert = "<script type='text/javascript'>alert('削除番号が間違っています！もう一度確認してください！');</script>";
            echo $alert;
            $signal=false;
        }
        //入力されたパスワードを比較し、正しければそのidのデータを削除
        if(!empty($tpass) && $tpass==$dpass){
            $sql='DELETE FROM `message-board` WHERE id=:id';
            $stmt=$pdo->prepare($sql);
            $stmt->bindParam(':id',$deleteid,PDO::PARAM_INT);
            $stmt->execute();
        }else if($signal==true){
            $alert = "<script type='text/javascript'>alert('パスワードが間違っています！もう一度確認してください！');</script>";
            echo $alert;
        }
        
    }
    //編集用
    if(!empty($_POST["edit"])){
        $editId=$_POST["edit"];
        $editPass=$_POST["e-password"];

        $stmt=$pdo->prepare('SELECT * FROM `message-board` WHERE id=:id');
        $stmt->bindValue(':id',$editId,PDO::PARAM_INT);
        //$stmt->bindValue(':password',$pw,PDO::PARAM_STR);
        //$stmt->bindValue(':name', $name, PDO::PARAM_STR);
        //$stmt->bindValue(':comment', $comment, PDO::PARAM_STR);
        $stmt->execute();
        //$resultにオブジェクトを格納
        $result=$stmt->fetchAll();
        print_r($result);
        if(!empty($result) && $result[0][4]==$editPass){
            $editname=$result[0][1];
            $editcomment=$result[0][2];
            $tpass=$result[0][4];
        }else{
            $alert = "<script type='text/javascript'>alert('編集番号かパスワードが間違っています！もう一度確認してください！');</script>";
            echo $alert;
        }
    }
    ?>

    <div class="input">
    <form action="" method="post">
        <h3 class="title">登録フォーム</h3>
        <div>名前</div><input type="text" name="str1" value="<?php if(isset($editname)){echo $editname;} ?>"><br>
        <div>コメント</div><input type="textarea" name="str2" value="<?php if(isset($editcomment)){echo $editcomment;} ?>"><br>
        <div>パスワード</div><input type="text" name="password"><br>
        <input type="submit" name="submit">
        <input type="hidden" name="sign" value="<?php if(!empty($editId)&&$editPass==$tpass){echo $editId;}?>"><br>
    </form>
    <form action="" method="post">
        <h3 class="title">削除</h3>
        <div>削除番号</div><input type="number" name="del"><br>
        <div>パスワード</div><input type="text" name="d-password"><br>
        <input type="submit" name="delete" value="削除">
    </form>
    <form action="" method="post">
        <h3 class="title">編集</h3>
        <div>編集対象番号</div><input type="text" name="edit"><br>
        <div>パスワード</div><input type="text" name="e-password"><br>
        <input type="submit" name="edition" value="編集"><br>
    </form>
    </div>
       
    <?php
    //表示用 
    $sql='SELECT * FROM `message-board`';
    $stmt=$pdo->query($sql);
    $results=$stmt->fetchAll();
    ?>
    <div class="table-container">
    <table border="5">
        <tr bgcolor="yellow">
            <th>id</th>
            <th>name</th>
            <th>comment</th>
            <th>time</th>
        </tr>
        <?php foreach($results as $row){?>
        <tr>
            <td><?php echo $row['id'];?></td>
            <td><?php echo $row['name'];?></td>
            <td><?php echo $row['comment'];?></td>
            <td><?php echo $row['time'];?></td>
        </tr>
        <?php }?>
    </table>
    </div>
</body>
</html>