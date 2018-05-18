 <?php
require_once 'lib/common.php';
require_once 'lib/edit-post.php';
require_once 'lib/view-post.php';

session_start();

// Don't let non-auth users see this screen
if (!isLoggedIn())
{
    redirectAndExit('index.php');
}

// Empty defaults
$title = $body = '';

// Init database and get handle
$pdo = getPDO();

$postId = null;
if (isset($_GET['post_id']))
{
    $post = getPostRow($pdo, $_GET['post_id']);
    if ($post)
    {
        //load title body and id for the html code below
        $postId = $_GET['post_id'];
        $title = $post['title'];
        $body = $post['body'];
    }
}

// Handle the post operation here
// when submit is pressed
$errors = array();
if ($_POST)
{
    // Validate these first
    $title = $_POST['post-title'];
    if (!$title)
    {
        $errors[] = 'The post must have a title';
    }
    $body = $_POST['post-body'];
    if (!$body)
    {
        $errors[] = 'The post must have a body';
    }

    if (!$errors)
    {

        $pdo = getPDO();
        // Decide if we are editing or adding

        //editing
        if ($postId)
        {
            //edit existing article
            editPost($pdo, $title, $body, $postId);
        }
        //adding
        else
        {
            //making new article
            $userId = getAuthUserId($pdo);
            $postId = addPost($pdo, $title, $body, $userId);

            if ($postId === false)
            {
                $errors[] = 'Post operation failed';
            }
        }
    }

    if (!$errors)
    {
        //parameter post_id is set to $post id so when redirect is called
        //_GET[post_id] will be set to somthing == true
        redirectAndExit('edit-post.php?post_id=' . $postId);
    }
}

?>
<!DOCTYPE html>
<html>
    <head>
        <title>A blog application | New post</title>
        <?php require 'templates/head.php' ?>
    </head>
    <body>
        <?php require 'templates/title.php' ?>

        <?php if ($errors): ?>
            <div class="error box">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error ?></li>
                    <?php endforeach ?>
                </ul>
            </div>
        <?php endif ?>

        <form method="post" class="post-form user-form">
            <div>
                <label for="post-title">Title:</label>
                <input
                    id="post-title"
                    name="post-title"
                    type="text"
                    value="<?php echo htmlEscape($title) ?>"
                />
            </div>
            <div>
                <label for="post-body">Body:</label>
                <textarea
                    id="post-body"
                    name="post-body"
                    rows="12"
                    cols="70"
                ><?php echo htmlEscape($body) ?></textarea>
            </div>
            <div>
                <input
                    type="submit"
                    value="Save post"
                />
            </div>
        </form>
    </body>
</html>