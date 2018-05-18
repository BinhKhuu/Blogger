<?php
require_once './lib/common.php';
$username = isLoggedIn();
?>
<div class="menu-options">
	<?php if($username): ?>
	<a href="logout.php"><?php echo $username ?></a>
    <a href="edit-post.php">New post</a>
	<?php else: ?>
	<a href="login.php">Log in</a>
	<?php endif ?>
</div>
<a href="index.php">
    <h1>Blog title</h1>
</a>
<p>This paragraph summarises what the blog is about.</p>
