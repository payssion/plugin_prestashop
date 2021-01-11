<?php
/**
 * @since 1.5.0
 */
class PayssionValidationModuleFrontController extends ModuleFrontController
{
    const PAYSSION_API_KEY = "PAYSSION_API_KEY";
    const PAYSSION_SECRET_KEY = "PAYSSION_SECRET_KEY";
    const PAYSSION_PM_OPTIONS = "PAYSSION_PM_OPTIONS";
    const PAYSSION_PM_SURCHARGE = "PAYSSION_PM_SURCHARGE";
    const PAYSSION_PM_NAME = "PAYSSION_PM_NAME";
    const PAYSSION_PM_ENABLED = "PAYSSION_PM_ENABLED";
    
	/**
	 * @see FrontController::postProcess()
	 */
    public function postProcess()
    {
        die;
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        // Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'payssion')
            {
                $authorized = true;
                break;
            }
        }
        
        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        $apiKey = Configuration::get(self::PAYSSION_API_KEY);
        $secretKey = Configuration::get(self::PAYSSION_SECRET_KEY);
        if (!$apiKey || !$secretKey) {
            die($this->module->l('This payment method is not configured correctly.', 'validation'));
        }
        
        $pm_id = Tools::getValue('pm_id');
        $pmEnabled = Configuration::get(self::PAYSSION_PM_ENABLED);
        $pmEnabled = $pmEnabled ? explode('|', $pmEnabled) : array();
        if (in_array($pmEnabled, $pm_id)) {
            die($this->module->l('This payment method is not enabled.', 'validation')); 
        }
        
        /* Load objects */
        $address = new Address($cart->id_address_delivery);
        $countryObj = new Country($address->id_country, Configuration::get('PS_LANG_DEFAULT'));
        $customer = new Customer($cart->id_customer);
        $currency = new Currency($cart->id_currency);
        $lang = new Language((int)($cookie->id_lang));
        
        $reqParams = array();
        $reqParams['source'] = 'prestashop';
        
        /* About the merchant */
        $reqParams['api_key'] = $apiKey;
        
        /* About the customer */
        $reqParams['payer_email'] = $customer->email;
        $reqParams['payer_name'] = $address->firstname . $address->lastname;
        $reqParams['country'] = isset($this->_country[strtoupper($countryObj->iso_code)]) ? $this->_country[strtoupper($countryObj->iso_code)] : '';
        $reqParams['language'] = strtoupper($lang->iso_code);
        
        /* About the cart */
        $reqParams['track_id'] = $cart->id;
        $reqParams['currency'] = $currency->iso_code;
        $reqParams['amount'] = number_format($cart->getOrderTotal(), 2, '.', '');
        $reqParams['pm_id'] = $pm_id;
        $reqParams['description'] = Configuration::get('PS_SHOP_NAME');
        
        /* URLs */
        $baseUrl = (Configuration::get('PS_SSL_ENABLED') ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].__PS_BASE_URI__;
        $reqParams['success_url'] = $baseUrl .'index.php?controller=order-confirmation?id_cart='.$cart->id.'&id_module='.(int)($this->id).'&key='.$customer->secure_key;
        $reqParams['fail_url'] = $baseUrl;
        $reqParams['notify_url'] = $baseUrl .'modules/'.$this->name.'/notify.php';
        
        $reqParams['api_sig'] = $this->generateSignature($reqParams, $pm_id, $secretKey);;
        
        
        /* Display the MoneyBookers iframe */
        return $this->display(__FILE__, 'payssion.tpl');
    }
    
    private function generateSignature(&$req, $pm_id, $secretKey)
    {
        $arr = array($req['api_key'], $pm_id, $req['amount'], $req['currency'],
            $req['track_id'], '', $secretKey);
        $msg = implode('|', $arr);
        return md5($msg);
    }
}
