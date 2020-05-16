<?php
/**
 * Tries to delete the specified post
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @return boolean Returns true on successful deletion
 * @throws Exception
 */
function deletePost(PDO $pdo, $postId){
    // To delete post, you need to also remove the comments.
    // TO DO so, you need to delete linked comments, then delete the post.
    
    // First, Delete all comments in the specified post.
    $sql_comment = "DELETE FROM comment WHERE post_id = :post_id";
    $stmt_comment = $pdo->prepare($sql_comment);
    if ($stmt_comment === false){
        throw new Exception("There was a proble preparing query to delete comments");
    }
    $result_comment = $stmt_comment->execute(array(
        'post_id' => $postId
    ));

    // Then delete the post.
    $sql_post = "DELETE FROM post WHERE id = :id";
    $stmt_post = $pdo->prepare($sql_post);
    if ($stmt_post === false){
        throw new Exception("There was a proble preparing this query");
    }
    $result_post = $stmt_post->execute(array(
        'id' => $postId
    ));

    // Check if everything is done without error
    if ($result_comment === true && $result_post === true){
        return $result === true;
    }
    // Return true
    return $result !== false;
}
?>