<?php
/**
 * @var $errors string
 * @var $commentData array
 */
?>
<?php //Pemisah seksi halaman ?>
<hr />

<?php //Laporkan eror dalam bentuk titik ?>
<?php if ($errors): ?>
	<div style="border: 1px solid $ff6666; padding: 6px">
		<ul>
			<?php foreach($errors as $error): ?>
				<li><?php echo $error?></li>
			<?php endforeach ?>
		</ul>
	</div>
<?php endif ?>

<h3>Tambahkan Komentar</h3>

<form method="POST">
	<p>
		<label for="comment-name">
			Nama: 
		</label>
		<input type="text" id="comment-name" name="comment-name" 
		value="<?php echo htmlEscape($commentData['name']) ?>" />
	</p>
	<p>
		<label for="comment-website">
			Situs Web: 
		</label>
		<input type="text" id="comment-website" name="comment-website"
		value="<?php echo htmlEscape($commentData['website']) ?>" />
	</p>
	<p>
		<label for="comment-text">
			Komentar Anda: 
		</label>
		<input type="text" id="comment-text" name="comment-text" rows="8" cols="70"
		value="<?php echo htmlEscape($commentData['text']) ?>" />
	</p>
	
	<input type="submit" value="Kirim komentar" />
</form>
