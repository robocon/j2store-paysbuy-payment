<?php defined('_JEXEC') or die('Restricted access'); ?>
<ul style="list-style:none;">
	<li>
		<b><?php echo JText::_("PLG_FORM_TITLE");?></b>
	</li>
	<li>
		<label for="r-paysbuy">
			<input type="radio" id="r-paysbuy" name="paytype" value="paysbuy"> <?php echo JText::_("PLG_FORM_PS");?>
		</label>
		
	</li>
	<li>
		<label for="r-cash">
			<input type="radio" id="r-cash" name="paytype" value="cash"> <?php echo JText::_("PLG_FORM_CASH");?>
		</label>
	</li>
	<li>
		<label for="r-credit">
			<input type="radio" id="r-credit" name="paytype" value="credit"> <?php echo JText::_("PLG_FORM_CC");?>
		</label>
	</li>
	<li>
		<label for="r-bank">
			<input type="radio" id="r-bank" name="paytype" value="bank"> <?php echo JText::_("PLG_FORM_IB");?>
		</label>
	</li>
</ul>
<!-- Description -->
<div class="note"> <?php echo JText::_('PLG_PAYSBUY_FORM'); ?> </div>