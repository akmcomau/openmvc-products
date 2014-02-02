<select name="<?php echo $element_name; ?>" class="form-control">
	<option value=""></option>
	<?php foreach ($categories as $value => $text) { ?>
		<option value="<?php echo $value; ?>" <?php if ($value == $selected) echo 'selected="selected"'; ?>><?php echo $text; ?></option>
	<?php } ?>
</select>
<?php echo $form->getHtmlErrorDiv($element_name); ?>