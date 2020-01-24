<?php
require_once 'lib/common.php';
require_once 'lib/view-post.php';

// Get the post ID
if (isset($_GET['post_id']))
{
    $postId = $_GET['post_id'];
}
else
{
    // So we always have a post ID var defined
    $postId = 0;
}

// Connect to the database, run a query, handle errors
$pdo = getPDO();
$row = getPostRow($pdo, $postId);

// Bila postingan tidak ada, tangani di sini
if(!$row){
	redirectAndExit('index.php?not-found=1');
}

$errors = null;
if($_POST){
	$commentData = array(
		'name' => $_POST['comment-name'],
		'website' => $_POST['comment-website'],
		'text' => $_POST['comment-text']
	);
	$errors = addCommentToPost($pdo, $postId, $commentData);

	//Kalau gak ada eror, reload halaman untuk menampilkan komentar
	if(!$errors){
		redirectAndExit('view-post.php?post_id=' . $postId);
	} else {
		$commmentData = array(
			'name' => '',
			'website' => '',
			'text' => ''
		);
	}
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>
            A blog application |
            <?php echo htmlEscape($row['title']) ?>
        </title>
        <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
    </head>
    <body>
        <?php require 'templates/title.php' ?>
        <h2>
            <?php echo htmlEscape($row['title']) ?>
        </h2>
        <div>
            <?php echo convertSqlDate($row['created_at']) ?>
        </div>
        <p>
            <?php echo convertNewlinesToParagraphs($row['body']) ?>
        </p>
		<h3> <?php echo countCommentsForPost($postId) ?> comments</h3>
		
		<?php foreach (getCommentsForPost($postId) as $comment): ?>
			<hr />
			<div class="comment">
				<div class="comment-meta">
					Comment from
					<?php echo htmlEscape($comment['name']) ?>
					on
					<?php echo convertSqlDate($comment['created_at']) ?>
				</div>
				<div class="comment-body">
					<?php echo convertNewlinesToParagraphs($comment['text']) ?>
				</div>
			</div>
		<?php endforeach ?>	
		<?php include 'templates/comment-form.php' ?>
    </body>
</html>
