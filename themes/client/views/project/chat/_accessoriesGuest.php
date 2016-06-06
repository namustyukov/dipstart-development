<?php
	if (count($buttonTemplates))
	{
?>
		<div class="message-buttons-guest">
		<?php
			foreach ($buttonTemplates as $item) {
		?>
				<div class="message-buttons-guest-items">
					<button type="button" class="btn btn-primary btn-message-guest" id="<?=$item->name?>"><?=$item->title?></button>
					<div class="message-buttons-guest-text"><?=$item->text?></div>
				</div>
		<?php
			}
		?>
		</div>
<?php
	}
?>

<script type="text/javascript">
	$('.message-buttons-guest-items .btn-message-guest').click(function(){
		if (!$(this).parent().find('.message-buttons-guest-text').is(':visible'))
		{
			$('.message-buttons-guest-text').hide();
			$(this).parent().find('.message-buttons-guest-text').slideDown('fast');
		}
	});
</script>