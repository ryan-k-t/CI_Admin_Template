<?
if(!isset($pending)) 
{
	$pending = FALSE; 
}
/**
 * if the pending flag is set, the comments will just be an array of comment text and not saved records
 * 
 */
foreach($comments as $comment)
{
	if(!$pending)
	{
		$comment = new Comment($comment); 
	}
	?>
	<article>
		<header>
			<h5>
				<? if($pending){ ?>
					<i>This comment will save once the record has been created</i>
				<? } else { ?>
					<time pubdate datetime="<?= $comment->get_datetime_posted_formatted('c'); ?>"><?= $comment->get_datetime_posted_formatted("n/j/Y g:i:s A"); ?></time> | <?= $comment->user_admin->username; ?>
				<? } ?>
			</h5>
		</header>
		<main>
			<?= $pending ? $comment : $comment->comment; ?>
		</main>
	</article>
	<?
}
?>