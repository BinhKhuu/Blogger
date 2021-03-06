<?php
/**
 * Gets the root path of the project
 *
 * @return string
 */
function getRootPath()
{
	/* /opt/lampp/htdocs/blogger */
    return realpath(__DIR__ . '/..'); 
}
/**
 * Gets the full path for the database file
 *
 * @return string
 */
function getDatabasePath()
{
	/* opt/lampp/htdocs/blogger/data/data.sqlite */
    return getRootPath() . '/data/data.sqlite';
}
/**
 * Gets the DSN for the SQLite connection
 *
 * @return string
 */
function getDsn()
{
	/* sqlite:opt/lampp/htdocs/blogger/data/data.sqlite */
    return 'sqlite:' . getDatabasePath();
}
/**
 * Gets the PDO object for database acccess
 *
 * @return \PDO
 */
function getPDO()
{
    
    $pdo = new PDO(getDsn());
    // Foreign key constraints need to be enabled manually in SQLite
    $result = $pdo->query('PRAGMA foreign_keys = ON');
    if ($result === false)
    {
        throw new Exception('Could not turn on foreign key constraints');
    }
    return $pdo;
}



https://ilovephp.jondh.me.uk/
/**
 * Escapes HTML so it is safe to output
 *
 * @param string $html
 * @return string
 */
function htmlEscape($html)
{
    if($html == "")
    {
        return '';
    }
    return htmlspecialchars($html, ENT_HTML5, 'UTF-8');
}


function convertSqlDate($sqlDate)
{
    /* @var $date DateTime */
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $sqlDate);
    return $date->format('d M Y, H:i');
}

/** 
 *	Swap carriage returns for paragraph breaks
 *  interpreates \n as a new paragraph we are injecting html but its our code is its safe 
 * 	$bodyText is unsanitized assumes a sanitized string will be passed in
 * @param string $bodyText, unsanitized
 * @return String
 */
function convertToPara($bodyText)
{
	return str_replace("\n", "</p><p>", $bodyText);
}


/**
 * Returns the number of comments for the specified post
 * @param PDO $pdo
 * @param integer $postId
 * @return integer
 */
function countCommentsForPost(PDO $pdo, $postId)
{
    $sql = "
        SELECT
            COUNT(*) as c
        FROM
            comment
        WHERE
            post_id = :post_id
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array('post_id' => $postId, )
    );
    return (int) $stmt->fetchColumn();
}


/**
 * Returns all the comments for the specified post
 *
 * @param integer $postId
 * @return returns associative array
 */
function getCommentsForPost(PDO $pdo, $postId)
{
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


function redirectAndExit($script)
{
    // Get the domain-relative URL (e.g. /blog/whatever.php or /whatever.php) and work
    // out the folder (e.g. /blog/ or /).
    $relativeUrl = $_SERVER['PHP_SELF'];
    $urlFolder = substr($relativeUrl, 0, strrpos($relativeUrl, '/') + 1);
    // Redirect to the full URL (http://myhost/blog/script.php)
    $host = $_SERVER['HTTP_HOST'];
    $fullUrl = 'http://' . $host . $urlFolder . $script;
    header('Location: ' . $fullUrl);
    exit();
}


function getSqlDateForNow()
{
    return date('Y-m-d H:i:s');
}

function tryLogin(PDO $pdo, $username, $password)
{
    $sql = "
        SELECT
            password
        FROM
            user
        WHERE
            username = :username
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
function login($username)
{
    session_regenerate_id();

    $_SESSION['logged_in_username'] = $username;
}

/**
 * User logs out
 * Destory all session data and cookie data 
 * then redirect to main mage
 */
function logout()
{
    if (session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }
    //unset session variables
    if($_SESSION) 
    {
        $_SESSION = array();
        // kill session cookies
        if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
        }
        //destroy session
        session_destroy();
    }
    redirectAndExit('index.php');
}

/**
 * Converts unsafe text to safe, paragraphed, HTML
 * 
 * @param string $text
 * @return string
 */
function convertNewlinesToParagraphs($text)
{
    $escaped = htmlEscape($text);

    return '<p>' . str_replace("\n", "</p><p>", $escaped) . '</p>';
}

function isLoggedIn() {
    //check if session has started
    $username = false;
    if (session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }
    //check if user is logged in through session status
    //$username is false if not logged in
    if(isset($_SESSION['logged_in_username']))
    {
        $username = $_SESSION['logged_in_username'];
    }
    return $username;
}

function getAuthUser()
{
    if (session_status() == PHP_SESSION_NONE)
    {
        session_start();
    }
    return isLoggedIn() ? $_SESSION['logged_in_username'] : null;
}

/**
 * Looks up the user_id for the current auth user
 */
function getAuthUserId(PDO $pdo)
{
    // Reply with null if there is no logged-in user
    if (!isLoggedIn())
    {
        return null;
    }
    $sql = "
        SELECT
            id
        FROM
            user
        WHERE
            username = :username
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
            'username' => getAuthUser()
        )
    );
    return $stmt->fetchColumn();
}


/**
 * Gets a list of posts in reverse order
 *
 * @param PDO $pdo
 * @return array
 */
function getAllPost(PDO $pdo)
{
    $stmt = $pdo->query(
        'SELECT
           id, title, created_at, body,
            (SELECT COUNT(*) FROM comment WHERE comment.post_id = post.id) comment_count
        FROM
            post
        ORDER BY
            created_at DESC'
    );
    if ($stmt === false)
    {
        throw new Exception('There was a problem running this query');
    }
    //PDO::FETCH_ASSOC: returns an array indexed by column name as returned in your result set 
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
