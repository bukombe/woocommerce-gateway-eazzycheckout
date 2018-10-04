(function ($) {
	$(document).ready(
		function () {
			var currency = "KES";
			var token = KanzuEazzyCheckout.token;
			var amount = KanzuEazzyCheckout.amount + '00';
			var orderRef = KanzuEazzyCheckout.orderRef;
			var merchantCode = KanzuEazzyCheckout.merchantCode;
			var outletCode = KanzuEazzyCheckout.outletCode;
			var description = KanzuEazzyCheckout.description;
			var website = KanzuEazzyCheckout.website;
			var custName = KanzuEazzyCheckout.custName;
			var ez1_callbackurl = KanzuEazzyCheckout.callbackUrl;
			var popupLogo = KanzuEazzyCheckout.siteLogo;
			var expiry = "2030-02-17T19:00:00";

			var gateway_form = $('#eazzycheckout-payment-form');
			gateway_form.append('token<input type="text" id="token" name="token" value="' + token + '">');
			gateway_form.append('amount<input type="text" id="amount" name="amount" value="' + amount + '">');
			gateway_form.append('ordedRef<input type="text" id="orderReference" name="orderReference" value="' + orderRef + '">');
			gateway_form.append('Merchant code<input type="text" id="merchantCode" name="merchantCode" value="' + merchantCode + '">');
			gateway_form.append('outlet code<input type="text" id="outletCode" name="outletCode" value="' + outletCode + '">');
			gateway_form.append('currency<input type="text" id="currency" name="currency" value="' + currency + '">');
			gateway_form.append('popupLogo<input type="text" id="popupLogo" name="popupLogo" value="' + popupLogo + '">');
			gateway_form.append('custName<input type="text" id="custName" name="custName" value="' + custName + '">');
			gateway_form.append('callback<input type="text" id="ez1_callbackurl" name="ez1_callbackurl" value="' + ez1_callbackurl + '">');
			gateway_form.append('expiry<input type="text" id="expiry" name="expiry" value="' + expiry + '">');
			gateway_form.append('website<input type="text" id="website" name="website" value="' + website + '">');
			gateway_form.append('description<input type="text" id="description" name="description" value="' + description + '">');
			gateway_form.append('Merchant<input type="text" id="merchant" name="merchant" value="Kasha">');
			gateway_form.append('<input type="submit" id="submit-cg" role="button" class="btn btn-primary col-md-4" value="Checkout">');

			// gateway_form.submit();

		}
	);
})(jQuery);
