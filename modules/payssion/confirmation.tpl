{if $state == 'completed'}
	<p>{l s='Your order on' mod='payssion'} <span class="bold">{$shop_name}</span> {l s='is complete.' mod='payssion'}
		<br /><br /><span class="bold">{l s='Your order will be shipped as soon as possible.' mod='payssion'}</span>
		<br /><br />{l s='For any questions or for further information, please contact our' mod='payssion'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payssion'}</a>.
	</p>
{else}
	{if $state == 'pending'}
		<p>{l s='Your order on' mod='payssion'} <span class="bold">{$shop_name}</span> {l s='is pending.' mod='payssion'}
			<br /><br /><span class="bold">{l s='Your order will be shipped as soon as we receive your bankwire.' mod='payssion'}</span>
			<br /><br />{l s='For any questions or for further information, please contact our' mod='payssion'} <a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payssion'}</a>.
		</p>
	{else}
		<p class="warning">
			{l s='Oops!!! Your payment has not been completed. If you have completed the payment successfully, you can contact our' mod='payssion'} 
			<a href="{$link->getPageLink('contact', true)}">{l s='customer support' mod='payssion'}</a>.
		</p>
	{/if}
{/if}