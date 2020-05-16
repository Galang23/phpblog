<?php
/**
 * Called to handle comment form. Redirect upon success.
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param array $commentData
 * @return array Errors array
 */
function handleAddComment(PDO $pdo, $postId, array $commentData){
    $errors = addCommentToPost($pdo, $postId, $commentData);

    // If no error, redirect back to self and redisplay
    if (!$errors){
        redirectAndExit("view-post.php?post_id=" . $postId);
    }

    return $errors;
}

/**
 * Called to handle the delete comment form, redirect afterwards.
 *
 * The $deleteReponse array is expected to be in the form:
 * 
 *      Array ( [6] => Delete )
 * 
 * which comes directly from input elements of this form:
 * 
 *      name="delete-comment[6]"
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param array $deleteReponse
 */
function handleDeleteComment(PDO $pdo, $postId, array $deleteResponse){
    if (isLoggedIn()){
        $keys = array_keys($deleteResponse);
        $deleteCommentId = $keys[0];
        if ($deleteCommentId){
            deleteComment($pdo, $postId, $deleteCommentId);
        }
        
        redirectAndExit('view-post.php?post_id=' . $postId);
    }
}

/**
 * Delete the specified comment on the specified post by authenticated user
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param integer $commentId
 * @return boolean True if the command executed without error.
 */
function deleteComment(PDO $pdo, $postId, $commentId){
    // The comment id on its own would suffice, but post_id is a nice extra safety check.
    $sql = "
        DELETE FROM comment
        WHERE post_id = :post_id AND id = :comment_id
    ";
    $stmt = $pdo->prepare($sql);
    if($stmt === true){
        throw new Exception("There is a problem preparing SQL squery");
    }

    $result = $stmt->execute(array(
        'post_id' => $postId,
        'comment_id' => $commentId
    ));

    return $result !== false;
}

/**
 * Ambil satu postingan
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @throws Exception
 */
function getPostRow(PDO $pdo, $postId)
{
    $stmt = $pdo->prepare(
        'SELECT
            title, created_at, body
        FROM
            post
        WHERE
            id = :id'
    );
    if ($stmt === false)
    {
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(
        array('id' => $postId, )
    );
    if ($result === false)
    {
        throw new Exception('There was a problem running this query');    
    }

    // Let's get a row
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    return $row;
}

/**
 * Tulis komentar ke postingan bersangkutan
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param array $commentData
 * @return array
 */
function addCommentToPost(PDO $pdo, $postId, array $commentData)
{
    $errors = array();

    // Do some validation
    if (empty($commentData['name']))
    {
        $errors['name'] = 'A name is required';
    }
    if (empty($commentData['text']))
    {
        $errors['text'] = 'A comment is required';
    }

    // If we are error free, try writing the comment
    if (!$errors)
    {
        $sql = "
            INSERT INTO
                comment
            (name, website, text, created_at, post_id)
            VALUES(:name, :website, :text, :created_at, :post_id)
        ";
        $stmt = $pdo->prepare($sql);
        if ($stmt === false)
        {
            throw new Exception('Cannot prepare statement to insert comment');
        }

        $createdTimestamp = date('Y-m-d H:m:s');

        $result = $stmt->execute(
            array_merge(
                $commentData,
                array('post_id' => $postId, 'created_at' => $createdTimestamp, )
            )
        );

        if ($result === false)
        {
            // @todo This renders a database-level message to the user, fix this
            $errorInfo = $stmt->errorInfo();
            if ($errorInfo)
            {
                $errors[] = $errorInfo[2];
            }
        }
    }

    return $errors;
} 