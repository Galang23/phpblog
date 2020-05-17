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
    
    $sqls = aray(
        // Dekete comments first to remove foreign key objection
        "DELETE FROM comment WHERE post_id = :id",
        // Then delete the post itself
        "DELETE FROM post WHERE id = :id"
    );

    foreach ($sqls as $sql){
        $stmt = $pdo->prepare($sql);
        if ($stmt === false){
            throw new Exception('There was a problem preparing this query');
        }

        $result = $stmt->execute(array(
            'id' => $postId
        ));

        // Stop if something went wrong
        if ($result === false){
            break;
        }
    }

    return $result !== false;
}

function countComments(PDO $pdo){
    $sql = "SELECT COUNT(*) from comment";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false){
        throw new Exception("There was a problem preparing this query");
    }
    $result = $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>