<div class="top-menu">
	<div class="menu-options">
		<?php if (isLoggedIn()): ?>
			Hello <?php echo htmlEscape(getAuthUser()) ?>.
			<a href="edit-post.php">New Post</a>
			<a href="logout.php">Log out</a>
		<?php else: ?>
			<a href="login.php">Log in</a>
		<?php endif ?>
	</div>
</div>