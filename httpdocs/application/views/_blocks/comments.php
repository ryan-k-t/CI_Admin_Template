
<section class="comments sidebar-item" data-slug="<?= $id; ?>|<?= $table; ?>">
	<header>
		<h3>Comments</h3>	
	</header>
	<main>
		<div class="form add-new">
			<button>Add New Comment</button>
		</div>

		<div class="form new-comment-panel" style="display: none;">
			<header>
				<label>Comment</label>
				<span class="close" aria-label="Close"><i class="fa fa-times"></i></span>
			</header>
			<textarea name="new_comment"></textarea>
			<button type="submit">Save Comment</button>
		</div>

		<div class="listing">

			<?
			$this->load->model('comments_model');
			$comments = $this->comments_model->with_table_key($table, $id);
			$this->load->view('comments/listing', array('comments'=> $comments));
			?>
		</div>
	</main>
</section>
