<?php
	$category = $sf_request->getParameter("category");
?>

<div id="search">
	<form method="get" action="<?php echo url_for('@search')?>">
		<?php if(isset($categories)):?>
			<select name="category">
				<option value="" <?php if(!$category):?>selected="selected"<?php endif;?>>Everything</option>
			</select>
		<?php endif;?>

		<input type="text" name="query" value="<?php echo $sf_request->getParameter('query')?>"/>
		<input type="submit" name="submit" value="Search"/>
	</form>
</div>