<?php
	$hit = $doc->getDocument();
?>

<div class="search-result">
  <h3>
    <span class="search_cat">[<?php echo $hit->category;?>]</span>
    <?php echo link_to($doc->getDocument()->title,$hit->route);?>
    <span class="search_rank">(<?php echo floor($doc->score*100)?> %)</span>
  </h3>
  <?php if($doc->getDocument()->category == "Bilder"):?>
    <?php echo image_tag($doc->getDocument()->thumnail)?>
  <?php endif?>
  <p><?php echo truncate_text(html_entity_decode($hit->description), "400");?>
</div>
