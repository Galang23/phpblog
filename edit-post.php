<?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
require_once 'lib/view-post.php';

session_start();

// If user is not authenticated, don't let them see this.
if (!isLoggedIn()){
    redirectAndExit('index.php');
}

// Empty defaults
$title = $body = '';

// Init database and get handle
$pdo = getPDO();

$postId = null;
if (isset($_GET['post_id'])){
    $post = getPostRow($pdo, $_GET['post_id']);
    if ($post){
        $postId = $_GET['post_id'];
        $title = $post['title'];
        $body = $post['body'];
    }
}

// Handle the form
$errors = array();
if ($_POST) {
    // Validate data
    $title = $_POST['post-title'];
    if (!$title){
        $errors[] = "The post must have a title";
    }

    $body = $_POST['post-body'];
    if (!$body) {
        $errors[] = "The post must have a content";
    }

    if (!$errors){
        $pdo = getPDO();

        // Decide editing or adding post
        if ($postId){
            editPost($pdo, $title, $body, $postId);
        } else {
            $userId = getAuthUserID($pdo);
            $postId = addPost($pdo, $title, $body, $userId);
    
            if ($postId === false){
                $errors[] = "Post operation failed";
            }
        }
    }

    if (!$errors){
        redirectAndExit('edit-post.php?post_id=' . $postId);
    }
}

// Check if create new post or edit existing one
if(isset($_GET['post_id'])){
    $pgTitle = "Edit Post";
}
else {
    $pgTitle = "New Post";
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>A blog application | <?php echo $pgTitle ?></title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/top-menu.php' ?>

        <h1><?php echo $pgTitle ?></h1>

        <?php if ($errors): ?>
            <div class="error box">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form method="POST" class="post-form user-form">
            <div>
                <label for="post-title">Title: </label>
                <input type="text" id="post-title" name="post-title" 
                 value="<?php echo htmlEscape($title) ?>"/>
            </div>
            <div>
                <label for="post-body">Body:</label>
                <textarea name="post-body" id="post-body" 
                cols="70" rows="12"><?php echo htmlEscape($body) ?></textarea>
            <div>
                <input type="submit" value="Save post">
                <a href="index.php">Cancel</a>
            </div>
        </form>
    </body>
</html>