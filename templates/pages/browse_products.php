<div class="<?php echo $page_class; ?>">
	 <h1><?php echo $text_browse_products; ?></h1>
	<?php if ($categories->getCategoryCount()) {
		?><h2><?php echo $main_heading; ?></h2><?php
		echo $categories->render();
	} ?>
	<?php if ($products->getProductCount()) {
		?><h2><?php echo $sub_heading; ?></h2>
		<?php if ($pagination->getMaxPage() > 1) { ?>
			<div class="pagination">
				<?php echo $pagination->getPageLinks(); ?>
			</div>
		<?php } ?>
		<div class="clearfix"></div>

		<?php echo $products->render(); ?>

		<?php if ($pagination->getMaxPage() > 1) { ?>
			<div class="pagination">
				<?php echo $pagination->getPageLinks(); ?>
			</div>
		<?php } ?>
		<div class="clearfix"></div>
		<?php
	}
	if ($products->getProductCount() == 0 && $categories->getCategoryCount() == 0) { ?>
		<h2><?php echo $this->language->get('no_products_found', [$group_name]); ?></h2>
	<?php } ?>
</div>
