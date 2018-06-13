(function ($) {
	$(document).ready(
		function () {
			var options = {
				currency: "KES",
				token: KanzuEazzyCheckout.token,
				amount: KanzuEazzyCheckout.amount + '00',
				orderRef: KanzuEazzyCheckout.orderRef,
				merchantCode: KanzuEazzyCheckout.merchantCode,
				outletCode: KanzuEazzyCheckout.outletCode,
				description: KanzuEazzyCheckout.description,
				custName: KanzuEazzyCheckout.custName,
				ez1_callbackurl: KanzuEazzyCheckout.callbackUrl,
				popupLogo: KanzuEazzyCheckout.siteLogo,
				popupWebsite: KanzuEazzyCheckout.website,
				expiry: "2030-02-17T19:00:00",
			};

			EazzyCheckout.configure(options);
			EazzyCheckout.open();
		}
	);
})(jQuery);
