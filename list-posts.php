<?php
require_once 'lib/common.php';
require_once 'lib/list-posts.php';

session_start();

// Tetapkan izin untuk melihat halaman ini
$_SESSION['permission'] = false;
if (!checkPermission()){
    redirectAndExit('index.php');
}

if ($_POST){
   $deleteResponse = $_POST['delete-post'];
   if ($deleteResponse){
       $keys = array_keys($deleteResponse);
       $deletePostId = $keys[0];
       if ($deletePostId){
           deletePost(getPDO(), $deletePostId);
           $_SESSION['doneDeletePost'] = true;
           redirectAndExit('list-posts.php');
       }
   }
}

// Connect to DB, run a query
$pdo = getPDO();
$posts = getAllPosts($pdo);
?>
<!DOCTYPE html>
<html>
    <head>
        <title>A blog application | Blog posts</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>
        <?php if ($_SESSION['doneDeletePost'] === true): ?>
            <h1>Done deleted post</h1>
        <?php endif; unset($_SESSION['doneDeletePost']); ?>
        <h1>Posts list</h1>
        <p>You have <?php echo count($posts) ?> posts.</p>
        <?php if (count($posts) > 0): ?>
        <form method="POST">
            <table id="post-list">
            <thead>
                <tr>
                    <th>Post ID</th>
                    <th>Post Title</th>
                    <th>Creation Date</th>
                    <th>Comments count</th>
                    <th colspan="2">Action</th>
                </tr>
            </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><?php echo $post['id']; ?></td>
                            <td><a href="view-post.php?post_id=<?php echo $post['id'];?>"><?php echo htmlEscape($post['title'])?></a></td>
                            <td><?php echo convertSqlDate($post['created_at']) ?></td>
                            <td><?php echo countCommentsForPost($pdo, $post['id']) ?></td>
                            <td><a href="edit-post.php?post_id=<?php echo $post['id'] ?>"><button type="button" name="edit post">Edit</button></a></td>
                            <td><input type="submit" name="delete-post[<?php echo $post['id'] ?>]" value="Delete"></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </form>
        <?php endif ?>
        <a href="edit-post.php">Create New Post</a>
    </body>
</html>