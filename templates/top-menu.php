<?php
require_once './lib/common.php';
$username = isLoggedIn();
?>
<div class="menu-options">
    <?php if($username): ?>
        <a href="index.php">Home</a>
        |
        <a href="logout.php"><?php echo $username ?></a>
        |
        <a href="edit-post.php">New post</a>
        |       
        <a href="list-post.php">All posts</a>
    <?php else: ?>
        <a href="login.php">Log in</a>
    <?php endif ?>
</div>