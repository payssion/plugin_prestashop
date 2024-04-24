<?php

class Payssion extends PaymentModule
{
	const PAYSSION_API_KEY = "PAYSSION_API_KEY";
	const PAYSSION_SECRET_KEY = "PAYSSION_SECRET_KEY";
	const PAYSSION_PM_OPTIONS = "PAYSSION_PM_OPTIONS";
	const PAYSSION_PM_SURCHARGE = "PAYSSION_PM_SURCHARGE";
	const PAYSSION_PM_NAME = "PAYSSION_PM_NAME";
	const PAYSSION_PM_ENABLED = "PAYSSION_PM_ENABLED";
	/**
	 * waiting status
	 *
	 * @var array
	 */
	private $os_statuses = array(
			'PS_OS_PAYSSION' => 'Awaiting payment',
	);
	
	public function __construct()
	{
		$this->name = 'payssion';
		$this->tab = 'payments_gateways';
		$this->version = '1.0.1';
		$this->author = 'PAYSSION';

		parent::__construct();

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Payssion');
		$this->description = $this->l('Accepts payments via Payssion.');
		$this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');

		if (_PS_VERSION_ < '1.5') {
		    require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');
		}
	}
	
	public function install()
	{
	    if (_PS_VERSION_ > '1.7')
	    {
	        if (!parent::install()
	            OR !Configuration::updateValue(self::PAYSSION_API_KEY, '')
	            OR !Configuration::updateValue(self::PAYSSION_SECRET_KEY, '')
	            OR !Configuration::updateValue(self::PAYSSION_PM_OPTIONS,
	                'bitcoin|onecard|paysafecard|giropay_de|eps_at|bancontact_be|ideal_nl|mybank|multibanco_pt|paybybankapp_gb|trustly|payu_cz|dotpay_pl|sofort|polipayment|webmoney|qiwi|yoomoney_ru|sberbank_ru|card_ru|neosurf|boleto_br|bancodobrasil_br|bradesco_br|caixa_br|itau_br|elo_br|hipercard_br|visa_br|mastercard_br|dinersclub_br|americanexpress_br|banamex_mx|bancomer_mx|oxxo_mx|santander_mx|redpagos_uy|bancochile_cl|redcompra_cl|wallet_in|ebanking_in|banktransfer_in|maybank2u_my|dragonpay_ph|alipay_cn|tenpay_cn|unionpay_cn')
	            OR !Configuration::updateValue(self::PAYSSION_PM_SURCHARGE, '0|0|0|0|0|0')
	            OR !Configuration::updateValue(self::PAYSSION_PM_NAME,
	                'Bitcoin|OneCard|Paysafecard|Giropay|EPS|Bancontact|iDeal|Mybank|Multibanco|Pay By Bank app|Trustly|PayU|Dotpay|SOFORT|POLi|WebMoney|Qiwi|YooMoney|SberPay|Russian Card|Neosurf|Boleto|Banco do Brasil|Bradesco|Caixa Brazil|Itau|Elo Brazil|Hipercard Brazil|Visa Brazil|Mastercard Brazil|Dinersclub Brazil|American Express Brazil|Banamex|Bancomer(BBVA)|Oxxo|Santander Mexico|Redpagos|Banco de Chile|RedCompra|India Wallets|India Netbanking|Indian Bank Transfer|Maybank2u|Dragonpay|Alipay|Tenpay|Unionpay')
	            OR !Configuration::updateValue(self::PAYSSION_PM_ENABLED, '')
	            OR !$this->registerHook('paymentOptions')
	            OR !$this->registerHook('paymentReturn'))
	            return false;
	    } else {
	        if (!parent::install()
	            OR !Configuration::updateValue(self::PAYSSION_API_KEY, '')
	            OR !Configuration::updateValue(self::PAYSSION_SECRET_KEY, '')
	            OR !Configuration::updateValue(self::PAYSSION_PM_OPTIONS,
	                'bitcoin|onecard|paysafecard|giropay_de|eps_at|bancontact_be|ideal_nl|mybank|multibanco_pt|paybybankapp_gb|trustly|payu_cz|dotpay_pl|sofort|polipayment|webmoney|qiwi|yoomoney_ru|sberbank_ru|card_ru|neosurf|boleto_br|bancodobrasil_br|bradesco_br|caixa_br|itau_br|elo_br|hipercard_br|visa_br|mastercard_br|dinersclub_br|americanexpress_br|banamex_mx|bancomer_mx|oxxo_mx|santander_mx|redpagos_uy|bancochile_cl|redcompra_cl|wallet_in|ebanking_in|banktransfer_in|maybank2u_my|dragonpay_ph|alipay_cn|tenpay_cn|unionpay_cn')
	            OR !Configuration::updateValue(self::PAYSSION_PM_SURCHARGE, '0|0|0|0|0|0')
	            OR !Configuration::updateValue(self::PAYSSION_PM_NAME,
	                'Bitcoin|OneCard|Paysafecard|Giropay|EPS|Bancontact|iDeal|Mybank|Multibanco|Pay By Bank app|Trustly|PayU|Dotpay|SOFORT|POLi|WebMoney|Qiwi|YooMoney|SberPay|Russian Card|Neosurf|Boleto|Banco do Brasil|Bradesco|Caixa Brazil|Itau|Elo Brazil|Hipercard Brazil|Visa Brazil|Mastercard Brazil|Dinersclub Brazil|American Express Brazil|Banamex|Bancomer(BBVA)|Oxxo|Santander Mexico|Redpagos|Banco de Chile|RedCompra|India Wallets|India Netbanking|Indian Bank Transfer|Maybank2u|Dragonpay|Alipay|Tenpay|Unionpay')
	            OR !Configuration::updateValue(self::PAYSSION_PM_ENABLED, '')
	            OR !$this->registerHook('payment')
	            OR !$this->registerHook('paymentReturn'))
	            return false;
	    }

		return true;
	}
	
	/**
	 * update payment status
	 *
	 * @return bool
	 */
	public function updatePaymentStatusConfiguration()
	{
		$statuses = array();
		$statuses["PS_OS_PAYSSION_PENDING"] = "Awaiting payment";
		//waiting payment status creation
		$this->updatePaymentStatus($statuses, '#4169E1', '', false, false, '', false);
		return true;
	}
	
	/**
	 * update new order statuses
	 *
	 * @param $array
	 * @param $color
	 * @param $template
	 * @param $invoice
	 * @param $send_email
	 * @param $paid
	 * @param $logable
	 */
	public function updatePaymentStatus($array, $color, $template, $invoice, $send_email, $paid, $logable)
	{
		foreach ($array as $key => $value)
		{
			$ow_status = Configuration::get($key);
			if ($ow_status === false)
			{
				$order_state = new OrderState();
				//$order_state->id_order_state = (int)$key;
			}
			else
				$order_state = new OrderState((int)$ow_status);
	
			$langs = Language::getLanguages();
	
			foreach ($langs as $lang)
				$order_state->name[$lang['id_lang']] = utf8_encode(html_entity_decode($value));
	
			$order_state->invoice = $invoice;
			$order_state->send_email = $send_email;
	
			if ($template != '')
				$order_state->template = $template;
	
			if ($paid != '')
				$order_state->paid = $paid;
	
			$order_state->logable = $logable;
			$order_state->color = $color;
			$order_state->save();
	
			Configuration::updateValue($key, (int)$order_state->id);
	
			//copy(dirname(__FILE__).'/img/statuses/'.$key.'.gif', dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif');
		}
	}
	
	public function uninstall()
	{
		if (!Configuration::deleteByName(self::PAYSSION_API_KEY)
				OR !Configuration::deleteByName(self::PAYSSION_SECRET_KEY)
				OR !Configuration::deleteByName(self::PAYSSION_PM_OPTIONS)
				OR !Configuration::deleteByName(self::PAYSSION_PM_SURCHARGE)
				OR !Configuration::deleteByName(self::PAYSSION_PM_NAME)
				OR !Configuration::deleteByName(self::PAYSSION_PM_ENABLED)
				OR !parent::uninstall())
			return false;
		return true;
	}
	
	public function getContent()
	{
		if (isset($_POST['submitPayssion'])) {
			$errors = array();
			$apiKey = Tools::getValue('api_key');
			if (!$apiKey) {
				$errors[] = $this->l('API Key required.');
			} else {
				Configuration::updateValue(self::PAYSSION_API_KEY, $apiKey);
			}
			
			$secretKey = Tools::getValue('secret_key');
			if (!$secretKey) {
				$errors[] = $this->l('Secret Key is required.');
			} else {
				Configuration::updateValue(self::PAYSSION_SECRET_KEY, $secretKey);
			}
			
			//Configuration::updateValue(self::PAYSSION_API_KEY, $_POST['api_key']);
			//Configuration::updateValue(self::PAYSSION_SECRET_KEY, $_POST['secret_key']);
			
			$enabled = '';
			foreach ($_POST AS $key => $value)
			{
				if (strstr($key, 'pm_option_'))
				{
					if ($enabled) {
						$enabled .= '|';
					}
					$enabled .= substr($key, 10);
				}
			}
			if (!$enabled) {
				$errors[] = $this->l('You must slelect at least one payment method.');
			} else {
				Configuration::updateValue(self::PAYSSION_PM_ENABLED, $enabled);
				$this->updatePaymentStatusConfiguration();
			}
			
			foreach ($errors as $err)
				$this->_html .= $this->displayError($err);
			
			$this->displayConf();
		}
		
		$this->displayPayssion();
		$this->displayFormSettings();
		return $this->_html;
	}
	
	private function displayConf()
	{
		$this->_html .= '
		<div class="conf confirm">
		<img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />
		'.$this->l('Settings updated').'
		</div>';
	}
	
	private function displayPayssion()
	{
		$this->_html .= '
		<img src="../modules/payssion/payssion.png" style="float:left; margin-right:15px;" /><br /><br />
		<b>'.$this->l('This module allows you to accept alternative payment methods via Payssion.').'</b><br /><br />
		<div style="clear:both;">&nbsp;</div>';
	}
	
	private function displayFormSettings()
	{
		$apiKey = Tools::getValue('api_key', Configuration::get(self::PAYSSION_API_KEY));
		$secretKey = Tools::getValue('secret_key', Configuration::get(self::PAYSSION_SECRET_KEY));
		//$apiKey = isset($_POST['api_key']) ? $_POST['api_key'] : Configuration::get(self::PAYSSION_API_KEY);
		//$secretKey = isset($_POST['secret_key']) ? $_POST['secret_key'] : Configuration::get(self::PAYSSION_SECRET_KEY);
		
		$pmOptions = Configuration::get(self::PAYSSION_PM_OPTIONS);
		$pmOptions = $pmOptions ? explode('|', $pmOptions) : array();
		$pmEnabled = Configuration::get(self::PAYSSION_PM_ENABLED);
		$pmEnabled = $pmEnabled ? explode('|', $pmEnabled) : array();
		
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
		<fieldset>
		<legend><img src="../img/admin/contact.gif" />'.$this->l('Settings').'</legend>
		<label>API Key:</label>
		<div class="margin-form"><input type="text" size="32" name="api_key" id="api_key" value="'.htmlentities($apiKey, ENT_COMPAT, 'UTF-8').'" /> </div>
		<label>Secret Key:</label>
		<div class="margin-form"><input type="text" size="32" name="secret_key" id="secret_key" value="'.htmlentities($secretKey, ENT_COMPAT, 'UTF-8').'" /> </div>';

		$this->_html .= '<hr size="1" noshade />
				<p>Click the local payment methods that you would like to enable</p>';

				for ($i = 0; $i < count($pmOptions); $i++) {
					if (0 == $i % 3) {
						
						if ($i > 0) {
							$this->_html .= '</div>';
						}
						$this->_html .= '<div style="width: 200px; float: left; margin-right: 25px; line-height: 75px;">';
							
					}
					
					$option = $pmOptions[$i];
					$this->_html .= '<input type="checkbox" name="pm_option_'. $option .'" value="1"'.
					                (in_array($option, $pmEnabled) ? ' checked="checked"' : '').
					                ' /> <img src="'.__PS_BASE_URI__.'modules/payssion/images/pm/'.$option.'.png" alt="" style="vertical-align: middle;max-width:100px;" /><br />';
				}

		$this->_html .= '<br /><div style="text-align:center;">
					<input class="button" type="submit" name="submitPayssion" value="'.$this->l('Save settings').'" />
				</div>
		</fieldset>
		</form>';
	}
	
	public function hookPaymentOptions($params)
	{
	    if (!$this->active) {
	        return;
	    }
	    
	    $apiKey = Configuration::get(self::PAYSSION_API_KEY);
	    $secretKey = Configuration::get(self::PAYSSION_SECRET_KEY);
	    if (empty($apiKey))
	        return $this->l('Payssion error: (undefined api key)');
	        if (empty($secretKey))
	            return $this->l('Payssion error: (undefined secret key)');
	            
	    global $smarty;
	    global $cookie;
	    
	    /* Load objects */
	    $address = new Address((int)($params['cart']->id_address_invoice ?: $params['cart']->id_address_delivery));
	    $countryObj = new Country((int)($address->id_country), Configuration::get('PS_LANG_DEFAULT'));
	    $customer = new Customer((int)($params['cart']->id_customer));
	    $currency = new Currency((int)($params['cart']->id_currency));
	    $lang = new Language((int)($cookie->id_lang));
	    
	    $reqParams = array();
	    $reqParams['source'] = 'prestashop';
	    
	    /* About the merchant */
	    $reqParams['api_key'] = $apiKey;
	    
	    /* About the customer */
	    $reqParams['payer_email'] = $customer->email;
	    $reqParams['payer_name'] = $address->firstname . ' ' . $address->lastname;
	    $reqParams['country'] = $countryObj->iso_code;
	    $reqParams['language'] = strtoupper($lang->iso_code);
	    
	    /* About the cart */
	    $reqParams['track_id'] = $params['cart']->id;
	    $reqParams['currency'] = $currency->iso_code;
	    $reqParams['amount'] = number_format($params['cart']->getOrderTotal(), 2, '.', '');
	    //$reqParams['pm_id'] = '';
	    $reqParams['description'] = Configuration::get('PS_SHOP_NAME');
	    
	    /* URLs */
	    $baseUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
	    $reqParams['success_url'] = $baseUrl .'index.php?controller=order-confirmation?id_cart='.(int)($params['cart']->id).'&id_module='.(int)($this->id).'&key='.$customer->secure_key;
	    $reqParams['fail_url'] = $baseUrl . 'index.php?controller=order&step=3';
	    $reqParams['notify_url'] = $baseUrl .'modules/'.$this->name.'/notify.php';
	    
	    $pmEnabled = Configuration::get(self::PAYSSION_PM_ENABLED);
	    $pmEnabled = $pmEnabled ? explode('|', $pmEnabled) : array();
	    //$reqParams['pm_enabled'] = $pmEnabled;
	    
	    $pmOptions = Configuration::get(self::PAYSSION_PM_OPTIONS);
	    $pmOptions = $pmOptions ? explode('|', $pmOptions) : array();
	    $surcharge = Configuration::get(self::PAYSSION_PM_SURCHARGE);
	    $surcharge = $surcharge ? explode('|', $surcharge) : array();
	    $pmName = Configuration::get(self::PAYSSION_PM_NAME);
	    $pmName = $pmName ? explode('|', $pmName) : array();
	    
	    $inputs = [];
	    foreach ($reqParams as $request_key => $request_value) {
	        $inputs[$request_key] = [
	            'name' => $request_key,
	            'type' => 'hidden',
	            'value' => $request_value,
	        ];
	    }
	    $payment_options =[];
	    for ($i = 0; $i < count($pmOptions); $i++) { 
	        $pm_id = $pmOptions[$i];
	        if (!in_array($pm_id, $pmEnabled) || !$this->isAvailable($pm_id, $countryObj->iso_code)) {
	            continue;
	        }
	        
	        if (_PS_VERSION_ > '1.7')
	        {
	            $externalOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
	        } else {
	            $externalOption = new PaymentOption();
	        }
	        $externalOption->setCallToActionText($pmName[$i])
	        ->setAction('https://www.payssion.com/payment/create.html')
	        ->setInputs(array_merge($inputs, [
	            'pm_id' => [
	                'name' => 'pm_id',
	                'type' => 'hidden',
	                'value' => $pm_id,
	            ], 
	            'api_sig' => [
	                'name' => 'api_sig',
	                'type' => 'hidden',
	                'value' => $this->generateSignature($reqParams, $pm_id, $secretKey),
	            ],
	        ]))
	        //->setAdditionalInformation($this->context->smarty->fetch('module:paymentexample/views/templates/front/payment_infos.tpl'))
	        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_. $this->name. '/images/pm/' . $pm_id . '.png'));
	        
	        $payment_options[] = $externalOption;
	    }
	    
	    return $payment_options;
	}
	
	protected function isAvailable($pm_id, $country) {
	    $country_list = null;
	    if ($country) {
	        $suffix = $this->getPMSuffix($pm_id);
	        if (!$suffix) {
	            if ('sofort' == $pm_id) {
	                $country_list = ['AT', 'BE', 'DE', 'IT', 'NL', 'ES' ,'CH'];
	            } elseif ('mybank' == $pm_id) {
	                $country_list = ['IT'];
	            } elseif ('trustly' == $pm_id) {
	                $country_list = ['AT', 'BE','CZ','DE','DK','EE','ES','FI','LT','LV','NL','NO','PL','SE','SK','GB'];
	            }
	        } else if ($suffix) {
	            $country_list = [strtoupper($suffix)];
	        }
	    }
	    
	    return $country_list ? in_array($country, $country_list) : true;
	}
	
	protected function getPMSuffix($pm_id) {
	    $pos = strpos($pm_id, '_');
	    return $pos ? substr($pm_id, $pos + 1) : null;
	}
	
	public function hookPayment($params)
	{
		$apiKey = Configuration::get(self::PAYSSION_API_KEY);
		$secretKey = Configuration::get(self::PAYSSION_SECRET_KEY);
		if (empty($apiKey))
			return $this->l('Payssion error: (undefined api key)');
		if (empty($secretKey))
			return $this->l('Payssion error: (undefined secret key)');
		
		global $smarty;
		global $cookie;
		
		/* Load objects */
		$address = new Address((int)($params['cart']->id_address_delivery));
		$countryObj = new Country((int)($address->id_country), Configuration::get('PS_LANG_DEFAULT'));
		$customer = new Customer((int)($params['cart']->id_customer));
		$currency = new Currency((int)($params['cart']->id_currency));
		$lang = new Language((int)($cookie->id_lang));
		
		$reqParams = array();
		$reqParams['source'] = 'prestashop';
		
		/* About the merchant */
		$reqParams['api_key'] = $apiKey;

		/* About the customer */
		$reqParams['payer_email'] = $customer->email;
		$reqParams['payer_name'] = $address->firstname . ' ' . $address->lastname;
		$reqParams['country'] = strtoupper($countryObj->iso_code);
		$reqParams['language'] = strtoupper($lang->iso_code);
		
		/* About the cart */
		$reqParams['track_id'] = $params['cart']->id;
		$reqParams['currency'] = $currency->iso_code;
		$reqParams['amount'] = number_format($params['cart']->getOrderTotal(), 2, '.', '');
		$reqParams['pm_id'] = '';
		$reqParams['description'] = Configuration::get('PS_SHOP_NAME');
		
		/* URLs */
		$baseUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
		$reqParams['success_url'] = $baseUrl .'index.php?controller=order-confirmation?id_cart='.(int)($params['cart']->id).'&id_module='.(int)($this->id).'&key='.$customer->secure_key;
		$reqParams['fail_url'] = $baseUrl . 'quick-order';
		$reqParams['notify_url'] = $baseUrl .'modules/'.$this->name.'/notify.php';

		$pmEnabled = Configuration::get(self::PAYSSION_PM_ENABLED);
		$pmEnabled = $pmEnabled ? explode('|', $pmEnabled) : array();
		$pm_available = [];
		if (!empty($pmEnabled)) {
		    foreach ($pmEnabled as $pm) {
		        if ($this->isAvailable($pm, $countryObj->iso_code)) {
		            $pm_available[] = $pm;
		        }
		    }
		}

		$reqParams['pm_enabled'] = $pm_available;
		
		$pmOptions = Configuration::get(self::PAYSSION_PM_OPTIONS);
		$pmOptions = $pmOptions ? explode('|', $pmOptions) : array();
		$surcharge = Configuration::get(self::PAYSSION_PM_SURCHARGE);
		$surcharge = $surcharge ? explode('|', $surcharge) : array();
		$pmName = Configuration::get(self::PAYSSION_PM_NAME);
		$pmName = $pmName ? explode('|', $pmName) : array();
		$apiSig = array();
		for ($i = 0; $i < count($pmOptions); $i++) {
			if (isset($surcharge[$i])) {
				$surcharge[$pmOptions[$i]] = $surcharge[$i];
			} else {
				$surcharge[$pmOptions[$i]] = 0;
			}
			
			$pmName[$pmOptions[$i]] = $pmName[$i];
			$apiSig[$pmOptions[$i]] = $this->generateSignature($reqParams, $pmOptions[$i], $secretKey);
		}
		
		$reqParams['pm_name'] = $pmName;
		$reqParams['pm_surcharge'] = $surcharge;
		$reqParams['api_sig'] = $apiSig;
		
		/* Assign settings to Smarty template */
		$smarty->assign($reqParams);
		
		/* Display the payssion iframe */
		return $this->display(__FILE__, 'payssion.tpl');
	}
	
	public function hookPaymentReturn($params)
	{
		if (!$this->active)
			return ;
	
		global $smarty;
		$smarty->assign('state', $_GET['state']);
		
		return $this->display(__FILE__, 'confirmation.tpl');
	}
	
	public function isValidNotify() {
		$apiKey = Configuration::get(self::PAYSSION_API_KEY);
		$secretKey = Configuration::get(self::PAYSSION_SECRET_KEY);
		
		// Assign payment notification values to local variables
		$pm_id = $_POST['pm_id'];
		$amount = $_POST['amount'];
		$currency = $_POST['currency'];
		$track_id = $_POST['track_id'];
		$sub_track_id = $_POST['sub_track_id'];
		$state = $_POST['state'];
		
		$check_array = array(
				$apiKey,
				$pm_id,
				$amount,
				$currency,
				$track_id,
				$sub_track_id,
				$state,
				$secretKey
		);
		$check_msg = implode('|', $check_array);
		echo "check_msg=$check_msg";
		$check_sig = md5($check_msg);
		$notify_sig = $_POST['notify_sig'];
		return ($notify_sig == $check_sig);
	}
	
	private function generateSignature(&$req, $pm_id, $secretKey)
	{
		$arr = array($req['api_key'], $pm_id, $req['amount'], $req['currency'], 
				$req['track_id'], '', $secretKey);
		$msg = implode('|', $arr);
		return md5($msg);
	}
	
	public function handleNotify() {
		if ($this->isValidNotify()) {
			$state = $_POST['state'];
			$pm_id = $_POST['pm_id'];
			$pm_name = $this->displayName . '_' . $pm_id;
			$cart_id = (int)($_POST['track_id']);
			$order_amount = (float)($_POST['amount']);
			$paid = (float)($_POST['amount']);
			$currency = $_POST['currency'];
			$trans_id = $_POST['transaction_id'];
			
			if (in_array($state, ['paid_more', 'paid_partial', 'completed', 'awaiting_confirm'])) {
			    $this->updateOrder($cart_id, $paid, $currency, $pm_name, $pm_id, $state, $paid, $trans_id);
			} else {
			    echo "ignore state:$state";
			}
			
		} else {
			echo "failed to check api_sig";
		}
	}
	
	protected function updateOrder($cart_id, $amount_paid, $order_currency, $pm_name, $pm_id, $payment_state, $paid, $payssion_id) {
		global $cart;
		$cart = new Cart((int)$cart_id);
		
		$errors = array();
		if (!Validate::isLoadedObject($cart))
			$errors[] = $this->l('Invalid Cart ID');
		else
		{
			$currency = new Currency((int)Currency::getIdByIsoCode($order_currency));
				
			if (!Validate::isLoadedObject($currency) || $currency->id != $cart->id_currency)
				$errors[] = $this->l('Invalid Currency ID').' '.($currency->id.'|'.$cart->id_currency);
			else
			{
				if (false/*$order_amount != $cart->getOrderTotal()*/)
					$errors[] = $this->l('Invalid Amount paid' . $cart->getOrderTotal(true));
				else
				{
					$order_status = null;
					switch ($payment_state) {
					    case 'paid_more':
					    case 'paid_partial':
						case 'completed':
							$order_status = (int)Configuration::get('PS_OS_PAYMENT');
							break;
						case 'awaiting_confirm':
							$order_status = (int)Configuration::get("PS_OS_PAYSSION_PENDING");
							break;
						default:
							break;

					}
		
					/* If the order already exists, it may be an update sent by Payssion - we need to update the order status */
					if ($cart->OrderExists())
					{
						echo "OrderExists";
						$order = new Order((int)Order::getOrderByCartId($cart->id));
						$new_history = new OrderHistory();
						$new_history->id_order = (int)$order->id;
						if(_PS_VERSION_ < '1.5')
						{
						    $new_history->changeIdOrderState((int)$order_status, (int)$order->id);
						} else {
						    $new_history->changeIdOrderState((int)$order_status, $order, true);
						}
						$new_history->addWithemail(true);
						
					}
		
					/* it is a new order that we need to create in the database */
					else
					{
						echo "validateOrder";
						echo "order_status=$order_status;";
						$customer = new Customer($cart->id_customer);
						
						$this->validateOrder((int)$cart->id, (int)$order_status, $amount_paid, $pm_name, null,
						    array('transaction_id' => $payssion_id), null, false, $customer->secure_key);
					}
				}
			}
		}
		
		d($errors);
	}
}
