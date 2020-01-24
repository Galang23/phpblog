<?php
/**
 * Retrieves a single post
 *
 * @param PDO $pdo
 * @param integer $postId
 * @throws Exception
 */
function getPostRow(PDO $pdo, $postId){
    $stmt = $pdo->prepare(
        'SELECT
            title, created_at, body
        FROM
            post
        WHERE
            id = :id'
    );
    if ($stmt === false){
        throw new Exception('There was a problem preparing this query');
    }
    $result = $stmt->execute(
        array('id' => $postId, )
    );
    if ($result === false){
        throw new Exception('There was a problem running this query');
    }
    // Let's get a row 
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row;
}
/**
 * Tulis komentar untuk sebuah kiriman
 * 
 * @param PDO $pdo
 * @param integer $postId
 * @param array $commentData
 * @return array
 */
function addCommentToPost(PDO $pdo, $postId, array $commentData){
	$errors = array();
	
	//Validasi
	if (empty($commentData['name'])){
		$errors['name'] = "Nama diperlukan";
	}
	if (empty($commentData['text'])){
		$errors['text'] = "Komentar diperlukan";
	}
	
	// Kalau gak ada eror, simpan komentarnya
	if (!$errors) {
		try{
		$sql = "
		INSERT INTO comment (name, website, text, created_at post_id)
		VALUES(:name, :website, :text, :created_ad, :post_id)
		";
		
		$stmt = $pdo->prepare($sql);
		
		if ($stmt === false) {
			throw new Exception("Tidak dapat mempersiapkan kanal data
			untuk menyimpan komentar");
		}
		
		
		$result = $stmt->execute(
			array_merge(
				$commentData, 
				array('post_id' => $postId, 'created_at' => getSqlDateForNow(), )
			)
		);
		
		if ($result === false) {
			// @todo Render pesan dalam database untuk user
			$errorInfo = $stmt->errorInfo();
			if($errorInfo){
				$errors[] = $errorInfo[2];
			}
		}
		} catch (Exception $e){ echo "Error: " . $e->getMessage() . "\n"; }
	}
}
