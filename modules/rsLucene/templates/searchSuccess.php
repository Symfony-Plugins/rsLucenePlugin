<?php include_partial("search",array('categories'=>true));?>

<?php if(!isset($hits) || count($hits) == 0):?>
	<h2><?php echo "no results"?></h2>
<?php else : ?>	
	<?php use_helper("Text")?>
	<h2><?php echo count($hits)." "."Hit".(count($hits)>1 ? 's':'')?></h2>
	
	<?php foreach($hits as $doc):?>
		<?php include_partial("searchResult",array("doc"=>$doc));?>
	<?php endforeach;?>

<?php endif;?>