<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php echo JText::_('PLG_PAYSBUY_PREPAY_MSG');?>
<form method="post" action="<?php echo $vars->form_url; ?>">
    <input type="hidden" name="biz" value="<?php echo $vars->username;?>">
    <input type="hidden" name="inv" value="<?php echo $vars->inv;?>">
    <input type="hidden" name="itm" value="<?php echo $vars->itm;?>">
    <input type="hidden" name="amt" value="<?php echo $vars->amt;?>">
    <input type="hidden" name="currencyCode" value="<?php echo $vars->curr_type;?>">
    <input type="hidden" name="postURL" value="<?php echo $vars->resp_front_url;?>">
    <input type="hidden" name="reqURL" value="<?php echo $vars->resp_back_url;?>">
    <input type="submit" class="btn btn-primary button" value="<?php echo JText::_('PLG_PAYSBUY_PREPAY_BUTTON'); ?>" />
</form>