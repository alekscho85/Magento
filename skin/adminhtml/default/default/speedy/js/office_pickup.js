/*
 Ajax.Responders.register({
 onComplete: function(transport) {
 if (transport.url.toString().search('card_validation') != -1) {
 order.loadShippingRates(true);
 }
 
 }
 })
 */

AdminOrder.addMethods({
    setShippingMethod: function(method) {

        var data = {};
        data['order[shipping_method]'] = method;
        data['speedy_exact_hour'] = $('speedy_exact_hour').getValue();
        data['speedy_exact_minutes'] = $('speedy_exact_minutes').getValue();

        var speedyExactTime = null;


        if (speedyExactTime) {
            data['speedyExactTime'] = speedyExactTime;
        }


        data['collect_shipping_rates'] = 1;
        this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);

    },
    switchPaymentMethod: function(method) {
        this.setPaymentMethod(method);
        this.isShippingMethodReseted = false;
        var data = {};
        data['order[payment_method]'] = method;
        if ($('speedy_exact_hour')) {
            data['speedy_exact_hour'] = $('speedy_exact_hour').getValue();
        }
        if ($('speedy_exact_minutes')) {
            data['speedy_exact_minutes'] = $('speedy_exact_minutes').getValue();
        }
        //if(data['speedy_exact_minutes'] && data['speedy_exact_hour']){
        data['collect_shipping_rates'] = 1;
        var selectedShippingMethod = $j('.shipment-methods input[type="radio"]:checked')

        if ($j(selectedShippingMethod).length) {
            data['order[shipping_method]'] = $j(selectedShippingMethod).val();
            data['shipping_method'] = $j(selectedShippingMethod).val();
            // data['collect_shipping_rates'] = 1;
        }
        //}
        this.loadArea(['card_validation', 'shipping_method', 'totals', 'billing_method'], true, data);
    }
    ,
    loadShippingRates: function(recalculate) {

        this.isShippingMethodReseted = false;

        var data = {};
        var speedyExactTime = null;
        var shippingMethod = null;


        var selectedShippingMethod = $j('.shipment-methods input[type="radio"]:checked')

        if ($j(selectedShippingMethod).length) {
            data['order[shipping_method]'] = $j(selectedShippingMethod).val();
            data['shipping_method'] = $j(selectedShippingMethod).val();

        }






        if ($('speedy_exact_hour')) {
            data['speedy_exact_hour'] = $('speedy_exact_hour').getValue();
        }
        if ($('speedy_exact_minutes')) {
            data['speedy_exact_minutes'] = $('speedy_exact_minutes').getValue();
        }


        if (data) {
            data['collect_shipping_rates'] = 1;


            this.loadArea(['shipping_method', 'totals', 'billing_method'], true, data);
        } else {


            this.loadArea(['shipping_method', 'totals', 'billing_method'], true, {collect_shipping_rates: 1});
        }

    },
    resetShippingMethod : function(data){
        data['reset_shipping'] = 1;
        this.isShippingMethodReseted = true;
        this.loadArea(['shipping_method', 'billing_method', 'shipping_address', 'totals', 'giftmessage', 'items'], true, data);
    },
    changeAddressField : function(event){
        var field = Event.element(event);
        var re = /[^\[]*\[([^\]]*)_address\]\[([^\]]*)\](\[(\d)\])?/;
        var matchRes = field.name.match(re);

        if (!matchRes) {
            return;
        }

        var type = matchRes[1];
        var name = matchRes[2];
        var data;

        if(this.isBillingField(field.id)){
            data = this.serializeData(this.billingAddressContainer)
        }
        else{
            data = this.serializeData(this.shippingAddressContainer)
        }
        data = data.toObject();

        if( (type == 'billing' && this.shippingAsBilling)
            || (type == 'shipping' && !this.shippingAsBilling) ) {
            data['reset_shipping'] = true;
        }

        data['order['+type+'_address][customer_address_id]'] = $('order-'+type+'_address_customer_address_id').value;

        if (data['reset_shipping']) {
            this.resetShippingMethod(data);
        }
        else {
            this.saveData(data);
            if (name == 'country_id' || name == 'customer_address_id') {
                this.loadArea(['shipping_method', 'billing_method', 'totals', 'items'], true, data);
            }
            // added for reloading of default sender and default recipient for giftmessages
            //this.loadArea(['giftmessage'], true, data);
        }
    }
    
});

$j('document').ready(function() {


    var currentMethodOptions = null;



    $j(document).on('change', 'input:radio[id*="speedyshippingmodule"]', function(evt) {

        var isExactHourAllowed = $j(this).nextAll('div.speedy_method_options').find('input:hidden.speedy_exacthour_allowed').length
        var isFreeMethod = $j(this).nextAll('input:hidden');

        if ($j(this).is(':checked') && isExactHourAllowed) {
            $j('input#speedy_exact_hour_enable').removeAttr('disabled')
            var finalSelector = $j(this).nextAll('div.speedy_method_options');
            var priceWithTax = $j(finalSelector).find('input:hidden.speedy_exacthour_allowed').val()
            var priceWithoutTax = $j(finalSelector).find('input:hidden.speedy_exacthour_withouttax').val()
            var price = '';
            if (!isFreeMethod.length) {

                if (includeTax) {
                    price = priceWithTax;
                }
                else if (excludeTax) {
                    price = priceWithoutTax;
                }
                else if (showBoth) {
                    price = priceWithoutTax + '/' + priceWithTax;
                }
            } else {
                price = '0.00';
            }
            price += ' '+Translator.translate('Leva');
            Translator.translate("extra charge")+' "'+Translator.translate("fixed hour")+'"'
            $j('p#fixed_price_view').show().text(Translator.translate("extra charge")+' "'+Translator.translate("fixed hour")+'"'+': ' + price)
        } else {

            $j('#speedy_admin_form input#speedy_exact_hour_enable').attr('disabled', 'disabled').removeAttr('checked');
            $j('#speedy_admin_form input:text').attr('disabled', 'disabled').val('')
            $j('p#fixed_price_view').text(Translator.translate("extra charge")+' "'+Translator.translate("fixed hour")+'"'+': ');

        }


    })



    $j(document).on('change', 'input#speedy_exact_hour_enable', function() {



        if ($j(this).is(':checked')) {
            //get the current selected service
            var service = $j('#co-shipping-method-form input:radio:checked')
            var isExactHourAllowed = $j(service).nextAll('div.speedy_method_options').find('input:hidden.speedy_exacthour_allowed').length
            var isFreeMethod = $j(service).nextAll('input:hidden');
            var finalSelector = $j(service).nextAll('div.speedy_method_options');
            var priceWithTax = $j(finalSelector).find('input:hidden.speedy_exacthour_allowed').val()
            var priceWithoutTax = $j(finalSelector).find('input:hidden.speedy_exacthour_withouttax').val()
            var price = '';
            if (!isFreeMethod.length) {
                if (includeTax) {
                    price = priceWithTax;
                }
                else if (excludeTax) {
                    price = priceWithoutTax;
                }
                else if (showBoth) {
                    price = priceWithoutTax + '/' + priceWithTax;
                }
            } else {
                price = '0.00';
            }
            price += ' '+Translator.translate('Leva');

            //$j('p#fixed_price_view').show().text('Добавка "фиксиран час:"'+price)
            $j('#speedy_admin_form input:text').removeAttr('disabled')
        } else {
            $j('#speedy_admin_form input:text').attr('disabled', 'disabled').val('')
            //$j('p#fixed_price_view').text('Добавка "фиксиран час:"')
        }
    })


})




