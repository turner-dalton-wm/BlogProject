<?php
require '../classes/Database.php';
require '../classes/Post.php';

$database = new Database();

function generateToken() {
    $date = date(DATE_RFC2822);
    $rand = rand();
    return sha1($date.$rand);
}

function getToken() {
    if (isset($_COOKIE['token'])) {
        return $_COOKIE['token'];
    }
}

if(isset($_POST['delete'])) {
    $database->query('DELETE FROM posts WHERE id = ?');
    $database->execute([$_POST['id']]);
    header('location: ../');
}

if(isset($_POST['update'])) {
    $database->query('UPDATE posts SET title = ?, body = ? WHERE id = ?');
    $database->execute([$_POST['title'], $_POST['body'], $_POST['id']]);
}

$url = $_GET['url'];
$database->query('SELECT * FROM posts WHERE url = ?');
$row = $database->fetch([$url]);
$post = new Post($row['id'], $row['title'], $row['body'], $row['date'], $row['url'], $row['users_id']);

?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-inverse" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <a class="navbar-brand" href="../">Blog</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li><a href="../">Home</a></li>
                <li><a href="../create/">Create</a></li>
                <li><a href="../login/">Login</a></li>
                <li><a href="../register/">Register</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">
    <div class="row">
        <div class="col-sm-6 col-sm-offset-3">
        <?php
        $database->query('SELECT * FROM users WHERE id = ? AND token = ? LIMIT 1');
        $rows = $database->fetchAll([$post->author->id, getToken()]);
        if(count($rows) > 0) {
            echo '<form method="post" action="">
                    <label>Post Title</label><br>
                    <input name="title" value="' . $post->title . '" style="width: 100%;"><br><br>
                    <label>Post Body</label><br>
                    <textarea name="body" style="width: 100%; height: 100px;">' . $post->body . '</textarea><br>
                    <input name="id" type="hidden" value="' . $post->id .'"><br>
                    <input name="update" type="submit" class="btn btn-primary" style="background-color:#000000;" value="Update">
                    <input name="delete" type="submit" class="btn btn-primary" style="background-color:#000000;" value="Delete">
                 </form>';
        }
        else {
            echo '<h3 style="display: inline;">' . $post->title .'</h3>';
            foreach($post->tags as $tag) {
                echo ' <span class="label label-danger">' . $tag->name . '</span>';
            }
            echo '<br>By ' . $post->author->firstname . ' ' .$post->author->lastname;
            echo '<br><br><p>' . $post->body . '</p>';
        }
        ?>
        </div>
    </div>
</div>
</body>
</html>
