<?php

global $permission;

/**
 * Gets the root path of the project
 * Dapatkan alamat root projek
 *
 * @return string
 */
function getRootPath(){
    return realpath(__DIR__ . '/..');
}

/**
 * Gets the full path for the database file
 * Dapatkan alamat lengkap berkas database
 * 
 * @return string
 */
function getDatabasePath(){
    return getRootPath() . '/data/data.sqlite';
}

/**
 * Gets the DSN for the SQLite connection
 * Dapatkan DSN untuk sambungan SQLite
 *
 * @return string
 */
function getDsn(){
    return 'sqlite:' . getDatabasePath();
}

/**
 * Gets the PDO object for database access
 * Dapatkan objek PDO untuk akses kanal data
 * 
 * @return \PDO
 */
function getPDO(){
    $pdo = new PDO(getDSN());

    // Foreign key constraints need to be manually enabled in SQLite.
    $result = $pdo->query('PRAGMA foreign_keys = ON');
    if ($result === false){
        throw new Exception("Unable to turn on foreign key contraints");
    }
    return $pdo;
}

/**
 * Escapes HTML so it is safe to output
 *
 * @param string $html
 * @return string
 */
function htmlEscape($html){
    return htmlspecialchars($html, ENT_HTML5, 'UTF-8');
}

function convertSqlDate($sqlDate){
    /* @var $date DateTime */
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $sqlDate);
    return $date->format('d M Y, H:i');
}

function getSqlDateForNow(){
	return date('Y-m-d H:i:s');
}

/**
 * Gets a list of posts in reverse order
 * 
 * @param PDO $pdo
 * @return array
 */
function getAllPosts($pdo){
    $stmt = $pdo->query(
        'SELECT id, title, created_at, body,
        (SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count
        FROM post
        ORDER BY created_at DESC'
    );
    if ($stmt === false){
        throw new Exception('There was a problem running this query');
    }

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Mengamankan text, HTML berparagraf.
 * 
 * @param string $text
 * @return string
 */
function convertNewlinesToParagraphs($text){
	$escaped = htmlEscape($text);
	return '<p>' . str_replace("\n", "</p><p>", $escaped) . '</p>';
}

function redirectAndExit($script){
	//Get domain-relative URL (/whatever.php) out the folder.
	$relativeUrl = $_SERVER['PHP_SELF'];
	$urlFolder = substr($relativeUrl, 0, strrpos($relativeUrl, '/') + 1);
	
	//Redirect to the full URL (http://host/blog/script.php)
	$host = $_SERVER['HTTP_HOST'];
	$fullUrl = 'http://' . $host . $urlFolder . $script;
	header('Location: ' . $fullUrl);
	exit();
}

/**
 * Returns all the comments for the specified post
 *
 * @param PDO $pdo
 * @param integer $postId
 * @return array
 */
function getCommentsForPost(PDO $pdo, $postId){
    $sql = "
        SELECT
            id, name, text, created_at, website
        FROM
            comment
        WHERE
            post_id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array('post_id' => $postId, )
    );
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function tryLogin(PDO $pdo, $username, $password){
    $sql = "
        SELECT
            password
        FROM
            user
        WHERE
            username = :username 
            AND is_enabled = 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array('username' => $username, )
    );
    
    // Get the hash from this row, and use the third-party hashing library to check it
    $hash = $stmt->fetchColumn();
    $success = password_verify($password, $hash);
    
    return $success;
}
/**
 * Logs the user in
 *
 * For safety, we ask PHP to regenerate the cookie, so if a user logs onto a site that a cracker
 * has prepared for him/her (e.g. on a public computer) the cracker's copy of the cookie ID will be
 * useless.
 *
 * @param string $username
 */
function login($username){
    session_regenerate_id();
    $_SESSION['logged_in_username'] = $username;
}

/**
 * Logs the user out
 */
function logout()
{
    unset($_SESSION['logged_in_username']);
}
function getAuthUser()
{
    return isLoggedIn() ? $_SESSION['logged_in_username'] : null;
}

function isLoggedIn(){
    return isset($_SESSION['logged_in_username']);
}

/**
 * Looks up the user_id for current authenticated user
 * 
 * @param PDO $pdo
 */
function getAuthUserID(PDO $pdo){
    // Reply null if there is no logged-in user
    if(!isLoggedIn()){
        return null;
    }

    $sql = "SELECT id FROM user WHERE username = :username AND is_enabled = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
            'username' => getAuthUser()
        )
    );

    return $stmt->fetchColumn();
}

/**
 * Check one's permission to view the content of the page.
 * 
 * @return boolean $result
 */
function checkPermission(){
    // First, check if user is logged in.

    if (!isLoggedIn()){
        // If not logged in, check for permission session variable 
        // (why? because it's global and idk how to make a variable global)

        if (isset($_SESSION['permission'])){
            // Then, check the client request method.
            // POST method is used to create, edit, and post, and delete comment, while
            // GET is used to show them.

            if ($_POST){
                // If a user tries to access the create, edit, and delete post or delete comment,
                // don't let them.

                echo "You have no permission. \n";
                exit();
            } else if($_SERVER['REQUEST_METHOD'] == "GET"){
                // But if user tries to access limited pages, don't let them.
                $_SESSION['permission'] = false;
            }
        } else {
            // If user does not try to access limited pages, then let them see the page.
            $_SESSION['permission'] = true;
        }
    } else {
        // Or, if a user is logged in, then let them do anything.
        $_SESSION['permission'] = true;
    }
    return $_SESSION['permission']; 
}
?>