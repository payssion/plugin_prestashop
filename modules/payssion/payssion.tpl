{foreach from=$pm_enabled item=i}
    <div class="row">
	    <div class="col-xs-12 col-md-6">
	        <form id="payssion_{$i}_form" action="https://www.payssion.com/payment/create.html" method="post">
		        <input type="hidden" name="source" value="{$source}" />
		        <input type="hidden" name="api_key" value="{$api_key}" />
		        <input type="hidden" name="api_sig" value="{$api_sig[$i]}" />
		        <input type="hidden" name="payer_email" value="{$payer_email}" />
		        <input type="hidden" name="payer_name" value="{$payer_name}" />
		        <input type="hidden" id="pm_id" name="pm_id" value="{$i}" />
		        <input type="hidden" name="track_id" value="{$track_id}" />
		        <input type="hidden" name="description" value="{$description}" />
		        <input type="hidden" name="amount" value="{$amount}" />
		        <input type="hidden" name="currency" value="{$currency}" />
		        <input type="hidden" name="country" value="{$country}" />
		        <input type="hidden" name="language" value="{$language}" />
		        <input type="hidden" name="notify_url" value="{$notify_url}" />
		        <input type="hidden" name="success_url" value="{$success_url}" />
		        <input type="hidden" name="fail_url" value="{$fail_url}" />
				<p class="payment_module">
				<a id="payssion_pm_option" href="#" onclick="javascript:$('#payssion_{$i}_form').submit();">
				    <img src="{$module_dir}images/pm/{$i}.png" />&nbsp; Pay with {$pm_name[$i]} 
				    {if $pm_surcharge[$i]} 
				    {100 * $pm_surcharge[$i]}% Surcharge
				    {/if}
			    </a>
			    </p>
            </form>
	    </div>
    </div>
{/foreach}
