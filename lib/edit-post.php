<?php
/**
 * Add post to blog.
 * 
 * @param PDO $pdo
 * @param string $title
 * @param string $body
 * @param integer $userId
 * @return PDO lastInsertId
 */
function addPost(PDO $pdo, $title, $body, $userId){
    // Prepare insert query
    $sql = "INSERT INTO post(title, body, user_id, created_at)
            VALUES(:title, :body, :user_id, :created_at)"; 
    $stmt = $pdo->prepare($sql);

    if ($stmt === false){
        throw new Exception("Could not prepare post insert query");
    }

    // Run the query with parameter
    $result = $stmt->execute(array(
        'title' => $title,
        'body' => $body,
        'user_id' => $userId,
        'created_at' => getSqlDateForNow(),
    ));
    if ($result === false){
        throw new Exception("Could not run post insert query");
    }

    return $pdo->lastInsertId();
}

/**
 * Edit post
 * @param PDO $pdo
 * @param string $title
 * @param string $body
 * @param integer $postId
 * @return true
 */
function editPost($pdo, $title, $body, $postId){
    // Prepare insert query
    $sql = "
    UPDATE post 
    SET title = :title, body = :body
    WHERE id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    if ($stmt === false){
        throw new Exception("Could not prepare post update query");
    }

    // Run with parameter
    $result = $stmt->execute(array(
        'title' => $title,
        'body' => $body,
        'post_id' => $postId
    ));
    if ($result === false){
        throw new Exception("Could not run post update query");
    }

    return true;
}
?>