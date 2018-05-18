<?php
require_once 'lib/common.php';
// Work out the path to the database, so SQLite/PDO can connect

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
//execute takes an array of input paramters as a parameter 
// here id in the stmt querey is bound to 1 represented by :id
$result = $stmt->execute(
    array('id' => $postId, )
);
if ($result === false)
{
    throw new Exception('There was a problem running this query');
}
// Let's get a row to fill in the html page below
$row = $stmt->fetch(PDO::FETCH_ASSOC);

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
            <?php $bodyText = htmlEscape($row['body']) ?>
            <?php echo convertToPara($bodyText) ?>
        </p>

        <h3><?php echo countCommentsForPost($postId) ?> comments</h3>
        <?php foreach (getCommentsForPost($postId) as $comment): ?>
            <?php // For now, we'll use a horizontal rule-off to split it up a bit ?>
            <hr />
            <div class="comment">
                <div class="comment-meta">
                    Comment from
                    <?php echo htmlEscape($comment['name']) ?>
                    on
                    <?php echo convertSqlDate($comment['created_at']) ?>
                </div>
                <div class="comment-body">
                    <?php echo htmlEscape($comment['text']) ?>
                </div>
            </div>
        <?php endforeach ?>

    </body>
</html>
