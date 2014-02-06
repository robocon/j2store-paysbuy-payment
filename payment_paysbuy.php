<?php defined('_JEXEC') or die('Restricted access');
require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/library/plugins/payment.php');
require_once (JPATH_SITE.'/components/com_j2store/helpers/utilities.php');

class plgJ2StorePayment_paysbuy extends J2StorePaymentPlugin

{
    /**
	 * @var $_element  string  Should always correspond with the plugin's filename,
	 *                         forcing it to be unique
	 */
    var $_element    = 'payment_paysbuy';
    var $login_id    = '';
    var $tran_key    = '';
    var $_isLog      = false;

    public function plgJ2StorePayment_paysbuy(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage( '', JPATH_ADMINISTRATOR );
	}

    /**
     * Form in Confirm order page
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    public function _prePayment( $data )
    {
        // prepare the payment form
        $vars = new JObject();

        $params = JComponentHelper::getParams('com_j2store');
        $currency_code = $params->get('currency_code');
        $curr_type = "764";
        $lang = "t";

        // Convert to thai Baht before send to Paysbuy
        if($currency_code==="USD"){
            $xml = JFactory::getXML( 'http://www2.bot.or.th/RSS/fxrates/fxrate-usd.xml' );
            $pre_usd = (float)$xml->item->children('cb', true)->value;
            $data['orderpayment_amount'] = $data['orderpayment_amount']*$pre_usd;
        }

        $vars->form_url = $this->_getPaysbuyUrl()."?lang=$lang";
        $vars->username = $this->params->get('username','');
        $vars->inv = $data['orderpayment_id'];
        $vars->itm = $data['order_id'];
        $vars->amt = $data['orderpayment_amount'];
        $vars->curr_type = $curr_type;

        //Change to your URL
        $vars->resp_front_url = JURI::root()."index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=success_payment";
        $vars->resp_back_url = JURI::root()."index.php?option=com_j2store&view=checkout&task=confirmPayment&orderpayment_type=".$this->_element."&paction=success_payment";

        //lets check the values submitted
        $html = $this->_getLayout('prepayment', $vars);
        return $html;
    }

    /**
     * Processes the payment form
     * and returns HTML to be displayed to the user
     * generally with a success/failed message
     *
     * @param $data     array       form post data
     * @return string   HTML to display
     */
    public function _postPayment( $data )
    {
        // Process the payment
        $vars = new JObject();

        $app =JFactory::getApplication();
        $paction = JRequest::getVar( 'paction' );

        switch ($paction)
        {
            case "display_message":
                $session = JFactory::getSession();
                $session->set('j2store_cart', array());
                $vars->message = JText::_($this->params->get('onafterpayment', ''));
                $html = $this->_getLayout('message', $vars);
                $html .= $this->_displayArticle();

              break;
            case "success_payment":
                
                // Check User loged in
                $user = JFactory::getUser();
                if($user->id > 0){
                    $jinput = JFactory::getApplication()->input;
                    include("lib/nusoap.php");
                    $url = $this->_getPaysbuyUrl(true);
                    $client = new nusoap_client($url, true);

                    // Get this post from Paysbuy
                    $inv = $jinput->post->get('apCode', 0);
                    $params = array(
                        "psbID"=>$this->params->get('psbid',''),
                        "biz"=>$this->params->get('username',''),
                        "secureCode"=>$this->params->get('secureCode',''),
                        "invoice"=>$inv,
                        "flag"=>""
                    );

                    $result = $client->call(
                        'getTransactionByInvoiceCheckPost',
                        array('parameters' => $params),
                        'http://tempuri.org/',
                        'http://tempuri.org/getTransactionByInvoiceCheckPost',
                        false,
                        true
                    );

                    $result = $result["getTransactionByInvoiceCheckPostResult"];
                    $info = $result["getTransactionByInvoiceReturn"];

                    JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_j2store/tables');
                    $tOrder = JTable::getInstance('Orders', 'Table');
                    $tOrder->load($inv);
                    $order_id = intval($tOrder->id);

                    // Check $inv from Paysbuy and owner orderinfo
                    if($info['result']==="00" && !is_null($tOrder->id) && $tOrder->user_id==$user->id){
                        
                        // Update Status from Paysbuy
                        $tOrder->order_state = "Pending";
                        $tOrder->order_state_id = 4;
                        $tOrder->store();
                    }

                }

              break;
            
            default:
                $vars->message = JText::_( 'J2STORE_SAGEPAY_MESSAGE_INVALID_ACTION' );
                $html = $this->_getLayout('message', $vars);
              break;
        }

        return $html;
    }

    /**
     * Prepares variables and
     * Renders the form for collecting payment info
     *
     * @return unknown_type
     */
    public function _renderForm( $data )
    {
        $vars = new JObject();
        $html = $this->_getLayout('form', $vars);

        return $html;
    }

    /**
     * Verifies that all the required form fields are completed
     * if any fail verification, set
     * $object->error = true
     * $object->message .= '<li>x item failed verification</li>'
     *
     * @param $submitted_values     array   post data
     * @return unknown_type
     */
    public function _verifyForm( $submitted_values )
    {
        $object = new JObject();
        $object->error = false;
        $object->message = '';

        return $object;
    }

    public function _getPreDomain(){
        $sandbox = (int)$this->params->get('sandbox',1);
        return $sandbox===1 ? "http://demo" : "https://www" ;
    }

    public function _getPaysbuyUrl($api=false){
        $predomain = $this->_getPreDomain();
        if($api===false){
            return "$predomain.paysbuy.com/paynow.aspx";
        }else{
            return "$predomain.paysbuy.com/psb_ws/getTransaction.asmx?WSDL";
        }
    }
}