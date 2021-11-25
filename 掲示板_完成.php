<!DOCTYPE html>
<?php
// DB接続設定
$dsn = '';
$user = '';
$password = '';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

$default_name="";
$default_text="";
$default_num="0";

//編集したいコメントを取得しフォームに挿入する
function edit_func(){
    global $default_name;
    global $default_text;
    global $default_num;
    global $pdo;
    if(!empty($_POST["edit"])&&$_POST['edit_button']&&!empty($_POST['edit_pass'])){
        $edit_num = $_POST['edit'];
        $edit_pass = $_POST['edit_pass'];
        
        $sql_select = 'SELECT * FROM BBS WHERE id=:edit_num';
        $stmt = $pdo -> prepare($sql_select);
        $stmt -> bindParam(':edit_num',$edit_num,PDO::PARAM_INT);
        $stmt -> execute();
        $results = $stmt -> fetchALL();
        var_dump($results['0']['password']);
        if($results['0']['password']===$edit_pass){
            $default_name=$results['0']['name'];
            $default_text=$results['0']['comment'];
            $default_num=$edit_num;            
        }
        else{
            echo "パスワードが違います";
        }
    }
    
}
//投稿する時の処理
function submit_func(){
    global $pdo;
    if(isset($_POST["name"])&&$_POST["normal_pass"]&&isset($_POST["text"])&&$_POST['submit']&&$_POST['hidden']==="0"){
        $name=$_POST["name"];
        $comment=$_POST["text"];
        $date=date("Y-m-d h:i:s");
        $pass=$_POST["normal_pass"];
        $false=FALSE;
        
        $sql = $pdo -> prepare("INSERT INTO BBS (name,comment,password,date,is_delete) VALUES (:name, :comment, :password, :date, :is_delete)");
        $sql -> bindParam(':name',$name,PDO::PARAM_STR);
        $sql -> bindParam(':comment',$comment,PDO::PARAM_STR);
        $sql -> bindParam(':date',$date,PDO::PARAM_STR);
        $sql -> bindParam(':password',$pass,PDO::PARAM_STR);
        $sql -> bindParam(':is_delete',$false,PDO::PARAM_BOOL);
        $sql -> execute();
        }
    elseif($_POST['hidden']!==NULL&&$_POST['submit']){
        echo "<br>";
        $name=$_POST["name"];
        $text=$_POST["text"];
        $date=date("Y-m-d h:i:s");
        $edit_num = $_POST['hidden'];
        
        $sql_edit='UPDATE BBS SET name=:name, comment=:comment WHERE id=:id';
        $stmt= $pdo -> prepare($sql_edit);
        $stmt->bindParam(':name',$name,PDO::PARAM_STR);
        $stmt->bindParam(':comment',$text,PDO::PARAM_STR);
        $stmt->bindParam(':id',$edit_num,PDO::PARAM_INT);
        $stmt->execute();
    }
    else{
        echo "なにも入力されていません";
    }
}

//=====================================================================
//削除する時の処理
function delete_func(){
    global $pdo;
    if(!empty($_POST['num'])&&!empty($_POST['delete_pass'])&&$_POST['delete']){
        $true = TRUE;//削除するコメントにはTRUEを書く
        $delete = $_POST['num'];//削除する番号を取得
        $delete_pass = $_POST['delete_pass'];//削除するコメントのパスを取得
        
        $sql_select = 'SELECT * FROM BBS WHERE id=:delete';
        $stmt = $pdo -> prepare($sql_select);
        $stmt -> bindParam(':delete',$delete,PDO::PARAM_INT);
        $stmt -> execute();
        $results = $stmt -> fetchALL();
        var_dump($results['0']['password']);
        if ($delete_pass === $results['0']['password']){
            $sql_delete = 'UPDATE BBS SET is_delete = :true WHERE id=:delete';
            $stmt = $pdo -> prepare($sql_delete);
            $stmt -> bindParam(':true',$true,PDO::PARAM_BOOL);
            $stmt -> bindParam(':delete',$delete,PDO::PARAM_INT);
            $stmt -> execute();
        }
        else{
            echo "パスワードが違います";
        }
    }
}

//=================================================================
//表示する処理
function display_func(){
    global $pdo;
    $sql_select = 'SELECT*FROM BBS';
    $stmt = $pdo -> query($sql_select);
    $results = $stmt->fetchAll();
    foreach($results as $row){
        if($row['is_delete']==='0'){
            echo '<p class="comment">'.$row['id'].'<>';
            echo $row['name'].'<>';
            echo $row['comment'].',';
            echo $row['date'].',';
            echo $row['password'].',';
            echo $row['is_delete'].'</p><br>';
        }
        else{
            echo '<p class="comment">'.$row['id'].'<>';
            echo 'コメントは削除されました</p><br>';
        }
    }
    echo '<hr>';
}

edit_func();
?>
<html lang="ja">
    <head>
        <meta chraset="UTF-8">
        <title>mission3-1</title>
    </head>
    <body>
        <form action="" method="post">
            <?php
            echo '名前：<input type="text" name="name" placeholder="名前" class="form" value='.$default_name.'>';
            echo 'コメント：<input type="text" name="text" class="form" value='.$default_text.'>';
            echo '<input type="hidden" name="hidden" class="form" value='.$default_num.'>';
            ?>
            パスワード：<input type="text" name="normal_pass" class="form">
            <input type="submit" name="submit" class="form" value="送信">
            <br>
            削除：
            <input type="number" name="num" class="form">
            パスワード：<input type="text" name="delete_pass" class="form">
            <input type="submit" name="delete" class="form" value="削除">
            <br>
            編集：
            <input type="number" name="edit" class="form">
            パスワード：<input type="text" name="edit_pass" class="form">
            <input type="submit" name="edit_button" class="form" value="編集">
            <style>
                .form {
                    margin: 10px;
                    padding: 0.5em;
                    border: 1px solid #999;
                    box-sizing: border-box;
                    background: #f2f2f2;
                    
                }
                .comment {
                    margin: px;
                    font-size: 1.3rem;
                }
            </style>
        </form>
    </body>
</html>
<?php
submit_func();
delete_func();
display_func();
?>