<?php
require_once 'lib/common.php';
require_once 'lib/view-post.php';

// Dapatkan ID postingan
if (isset($_GET['post_id']))
{
    $postId = $_GET['post_id'];
}
else
{
	// Supaya variabel post ID selalu terdefinisi
    $postId = 0;
}

// Sambungkan ke database, jalankan query, tangani eror
$pdo = getPDO();
$row = getPostRow($pdo, $postId);

// Tangani pos yang tidak ada
if (!$row)
{
    redirectAndExit('index.php?not-found=1');
}

$errors = null;
if ($_POST)
{
    $commentData = array(
        'name' => $_POST['comment-name'],
        'website' => $_POST['comment-website'],
        'text' => $_POST['comment-text'],
    );
    $errors = addCommentToPost(
        $pdo,
        $postId,
        $commentData
    );

	// If there are no errors, redirect back to self and redisplay
	// Kalau tidak ada eror, redirek ke self dan tampilkan ulang
    if (!$errors)
    {
        redirectAndExit('view-post.php?post_id=' . $postId);
    }
}
else
{
    $commentData = array(
        'name' => '',
        'website' => '',
        'text' => '',
    );
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
        <?php // This is already escaped, so doesn't need further escaping ?>
		<?php // Sudah di-escape, tidak perlu escape lanjutan ?>
        <?php echo convertNewlinesToParagraphs($row['body']) ?>

        <h3><?php echo countCommentsForPost($postId) ?> comments</h3>

        <?php foreach (getCommentsForPost($postId) as $comment): ?>
            <?php // For now, we'll use a horizontal rule-off to split it up a bit ?>
			<?php // Sudah di-escape, tidak perlu escape lanjutan ?>
            <hr />
            <div class="comment">
                <div class="comment-meta">
                    Comment from
                    <?php echo htmlEscape($comment['name']) ?>
                    on
                    <?php echo convertSqlDate($comment['created_at']) ?>
                </div>
                <div class="comment-body">
                    <?php // This is already escaped ?>
					<?php // sudah di-escape ?>
                    <?php echo convertNewlinesToParagraphs($comment['text']) ?>
                </div>
            </div>
        <?php endforeach ?>

        <?php require 'templates/comment-form.php' ?>
    </body>
</html>
