
var SpeedyObj = Class.create();
SpeedyObj.prototype = {
    initialize: function(form, saveUrl) {
        this.form = form;
        if ($(this.form)) {
            $(this.form).observe('submit', function(event) {
                this.save();
                Event.stop(event);
            }.bind(this));
        }

        this.saveUrl = saveUrl;


        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this);
    },
    save: function() {
        $j('#speedyOverlay').show();
        var request = new Ajax.Request(
                this.saveUrl,
                {
                    method: 'post',
                    onComplete: this.onComplete,
                    onSuccess: this.onSave,
                    onFailure: checkout.ajaxFailure.bind(checkout),
                    parameters: Form.serialize(this.form)
                }
        );
    },
    resetLoadWaiting: function(transport) {
        $j('#speedyOverlay').hide();
        checkout.setLoadWaiting(false);
    },
    nextStep: function(transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }
        if (response.error) {
            if ((typeof response.message) == 'string') {
                alert(response.message);
            } else {
                if (window.shippingRegionUpdater) {
                    shippingRegionUpdater.update();
                }
                alert(response.message.join("\n"));
            }

            return false;
        }

        checkout.setStepResponse(response);
    }
}


var currentMethodOptions = null;
var isExactHourAllowed = false;
var isFreeMethod = false;


var excludeTax = null;

var includeTax = null;

var showBoth = null;

$j('document').ready(function() {





    $j('li#opc-shipping div.step-title').click(function(evt) {
        var isShippingSameAsBilling = $j('#shipping\\:same_as_billing').is(':checked');
        var isShippingOfficeChecked = $j('#shipping\\:speedy_office_chooser').is(':checked');
        var billingOfficeId = $j('#billing\\:speedy_office_id').val()
        if (isShippingSameAsBilling && !billingOfficeId) {
            $j('#shipping\\:speedy_office_chooser').removeAttr('checked');
            $j('#shipping\\:speedy_office_address').hide();
            $j('#shipping\\:speedy_street').val($j('#billing\\:speedy_street').val());
            $j('#shipping\\:speedy_normal_address').show();
        } else if (isShippingSameAsBilling && billingOfficeId) {
            $j('#shipping\\:speedy_office_chooser').prop('checked', true);
            $j('#shipping\\:speedy_street').val($j('#billing\\:speedy_street').val());
            $j('#shipping\\:speedy_normal_address').hide();
            $j('#shipping\\:speedy_office_address').show();
        }
    })

    $j('#shipping\\:same_as_billing').change(function(evt) {
        var isShippingSameAsBilling = $j('#shipping\\:same_as_billing').is(':checked');
        var isShippingOfficeChecked = $j('#shipping\\:speedy_office_chooser').is(':checked');
        var billingOfficeId = $j('#billing\\:speedy_office_id').val()
        if (isShippingSameAsBilling && !billingOfficeId) {
            $j('#shipping\\:speedy_office_chooser').removeAttr('checked');
            $j('#shipping\\:speedy_office_address').hide();
            $j('#shipping\\:speedy_street').val($j('#billing\\:speedy_street').val());
            $j('#shipping\\:speedy_normal_address').show();

        } else if (isShippingSameAsBilling && billingOfficeId) {
            $j('#shipping\\:speedy_office_chooser').prop('checked', true);
            $j('#shipping\\:speedy_street').val($j('#billing\\:speedy_street').val());
            $j('#shipping\\:speedy_normal_address').hide();
            $j('#shipping\\:speedy_office_address').show();
        }
    })







    var currentMethod = $j('#co-shipping-method-form input:radio:checked');
    if (currentMethod.length) {
        setActiveRadioButton(currentMethod);
    }

    $j(document).on('click', "li#opc-shipping_method div.step-title", function() {

    })

    $j(document).on('change', "input[type='radio']", function() {


    })


    function setActiveRadioButton(button) {
        isExactHourAllowed = $j(button).nextAll('div.speedy_method_options').find('input:hidden.speedy_exacthour_allowed').length
        isFreeMethod = $j(button).nextAll('input:hidden');
        if ($j(button).is(':checked') && isExactHourAllowed) {
            $j('#speedy_exact_picking_data input#speedy_exact_hour_enable').removeAttr('disabled')
            var finalSelector = $j(button).nextAll('div.speedy_method_options');
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
                    price += priceWithoutTax + ' '+Translator.translate('Leva')+' ('+Translator.translate('Incl. Tax') + priceWithTax;
                }
            } else {
                price = '0.00';
            }

            if (showBoth && !isFreeMethod.length) {
                price += ' '+Translator.translate('Leva') + ')'
            } else {
                price += ' '+Translator.translate('Leva');
            }

            $j('p#fixed_price_view').show().text(Translator.translate("extra charge")+'"'+Translator.translate("fixed hour")+'" ' + price)

        } else {

            $j('#speedy_exact_picking_data input#speedy_exact_hour_enable').attr('disabled', 'disabled').removeAttr('checked');
            $j('#speedy_exact_picking_data input:text').attr('disabled', 'disabled').val('')
            $j('p#fixed_price_view').text(Translator.translate("extra charge")+'"'+Translator.translate("fixed hour")+'"');
            isExactHourAllowed = false;

        }

    }





    $j(document).on('change', 'input:radio', function(evt) {

        setActiveRadioButton(this);

    })



    $j(document).on('change', '#speedy_exact_picking_data input#speedy_exact_hour_enable', function() {
        if ($j(this).is(':checked')) {
            //get the current selected service
            var service = $j('#co-shipping-method-form input:radio:checked')
            var finalSelector = $j(service).nextAll('div.speedy_method_options');

            var priceWithTax = $j(finalSelector).find('input:hidden.speedy_exacthour_allowed').val()

            var priceWithoutTax = $j(finalSelector).find('input:hidden.speedy_exacthour_withouttax').val()
            var price = '';

            if (!isFreeMethod.length) {
                if (includeTax) {
                    price = priceWithTax;
                }
                else if (excludeTax) {
                    price += priceWithoutTax;
                }
                else if (showBoth) {
                    price += priceWithoutTax + ' '+Translator.translate('Leva')+' ('+Translator.translate("Incl. Tax")+' ' + priceWithTax;
                }
            } else {
                price = '0.00';
            }
            if (showBoth && !isFreeMethod.length) {
                price += ' '+Translator.translate('Leva') + ')'
            } else {
                price += ' '+Translator.translate('Leva');
            }
            $j('p#fixed_price_view').show().text(Translator.translate("extra charge")+'"'+Translator.translate("fixed hour")+''+'" ' + price)
            $j('#speedy_exact_picking_data input:text').removeAttr('disabled')
        } else {
            $j('#speedy_exact_picking_data input:text').attr('disabled', 'disabled').val('')
            if (isExactHourAllowed) {
                //$j('p#fixed_price_view').text('Добавка "фиксиран час:"')
            }
        }
    })

})