
var shippingNomenclature;
var shippingSite;
var shippingQuarter;
var shippingStreet;
var shippingOffice;
var shippingBlok;

var isFirstPageLoad = 0;


$j(document).ready(function(evt){
    
            
    isShippingSameAsBilling = $j('#order-shipping_same_as_billing').is(':checked');
     $j(document).on('click','button#submit_order_top_button,button.save[id!="submit_order_top_button"]',function(evt) {
         evt.preventDefault();
            validateOrder();
           
        })
        
        
        function validateOrder(){
            
            var isHourValid = validateHour();
            
            if(!isHourValid){
                alert(Translator.translate("invalid_hour_warning"));
                return;
            }
            
            var isShippingSameAsBilling = $j('#order-shipping_same_as_billing').is(':checked');
            if(isShippingSameAsBilling){
            //hide previously shown errow if any
            var baseFieldSelector = 'order-billing_address_';
            }else{
                var baseFieldSelector = 'order-shipping_address_';
            }    
            $j('#advice-required-entry-order[has_valid_address]').hide();
            
                var quarter = $j('#'+baseFieldSelector + 'speedy_quarter_name').val();
                var quarter_id = $j('#'+baseFieldSelector + 'speedy_quarter_id').val();
                var street = $j('#'+baseFieldSelector + 'speedy_street_name').val();
                var street_id = $j('#'+baseFieldSelector + 'speedy_street_id').val();
                var number = $j('#'+baseFieldSelector + 'speedy_street_number').val();
                var blockNo = $j('#'+baseFieldSelector + 'speedy_block_number').val();
                var address_note = $j('#'+baseFieldSelector + 'speedy_address_note').val();

                var floor_no = $j('#'+baseFieldSelector + 'speedy_floor').val();
                var entrance = $j('#'+baseFieldSelector + 'speedy_entrance').val();
                var apartment = $j('#'+baseFieldSelector + 'speedy_apartment').val();

                var speedy_pick_from_office = $j('#'+baseFieldSelector + 'speedy_office_chooser').val();
                var speedy_office_name = $j('#'+baseFieldSelector + 'speedy_office_name').val();
                var speedy_office_id = $j('#'+baseFieldSelector + 'speedy_office_id').val();
                var country_id = $j('#'+baseFieldSelector + 'country_id').val();
                var state_id = $j('#'+baseFieldSelector + 'state_id').val();
                var city = $j('#'+baseFieldSelector + 'city').val();
                var speedy_site_id = $j('#'+baseFieldSelector + 'speedy_site_id').val();
                var speedy_country_id = $j('#'+baseFieldSelector + 'speedy_country_id').val();
                var postcode = $j('#'+baseFieldSelector + 'postcode').val();
                var address_1 = $j('#'+baseFieldSelector + 'street0').val();
                if ($j('#'+baseFieldSelector + 'street1').length != 0) {
                    var address_2 = $j('#'+baseFieldSelector + 'street1').val();
                } else {
                    var address_2 = '';
                }
                
                

                var isValid = 0;

                            $j('.cod_error_address').remove();

                            if (country_id != 'BG' && typeof validate_abroud_address != 'undefined') {
                                $j.ajax({
                                    url: validate_abroud_address,
                                    dataType: "json",
                                    async: false,
                                    data: {
                                        speedy_site_id: speedy_site_id,
                                        city: city,
                                        postcode: postcode,
                                        address_1: address_1,
                                        address_2: address_2,
                                        speedy_country_id: speedy_country_id,
                                        state_id: state_id,
                                    },
                                    success: function(addres_valid) {
                                        if (addres_valid === 1) {
                                            isValid = 1;
                                        }
                                    },
                                    error: function(addres_valid) {
                                        if (addres_valid !== 1 && addres_valid.responseText) {
                                            $j('#'+baseFieldSelector+'fields .content').append('<ul class="cod_error_address messages"><li class="error-msg">'+addres_valid.responseText+'</li></ul>');
                                        }
                                    }
                                });
                            } else if (quarter || (quarter_id !="0" && quarter_id !="")) {
                                if ((street && number || street_id && number) || blockNo || number) {
                                    isValid = 1;
                                }

                            } else if (speedy_pick_from_office && speedy_office_name && (speedy_office_id!="0" && speedy_office_id!="" )) {
                                isValid = 1;
                            } else if (street || (street_id!="0" && street_id!="")) {
                                if (number || blockNo) {
                                    isValid = 1;
                                }
                            }
                            else if (address_note && address_note.length > 4) {
                                isValid = 1;
                            }

                            if (isValid) {
                                 var streetAddress = '';
                                                            
                                if(quarter.length > 1){
                                   streetAddress += quarter; 
                                }
                                if(street.length > 1){
                                   streetAddress += street;
                                }
                                if(number.length > 1){
                                    streetAddress += number;
                                }
                                if(blockNo.length > 1){
                                    streetAddress += blockNo
                                }

                                if(entrance.length > 1){
                                   streetAddress += entrance   
                                }
                                if(floor_no.length > 1){
                                   streetAddress += floor_no;
                                }
                                if(apartment.length > 1){
                                   streetAddress += apartment;   
                                }

                                if(address_note.length > 1){
                                   streetAddress += address_note;   
                                }
      
                                order.submit();
                            }else{
                             alert(Translator.translate("Please enter a valid address")) 
                            }

        }

        function initShippingAddress(){
                shipping_quarter = $j('#order-shipping_address_speedy_quarter_name').val();
                shipping_quarter_id = $j('#order-shipping_address_speedy_quarter_id').val();
                shipping_street = $j('#order-shipping_address_speedy_street_name').val();
                shipping_street_id = $j('#order-shipping_address_speedy_street_id').val();
                shipping_number = $j('#order-shipping_address_speedy_street_number').val();
                shipping_blockNo = $j('#order-shipping_address_speedy_block_number').val();
                shipping_address_note = $j('#order-shipping_address_speedy_address_note').val();

                shipping_floor_no = $j('#order-shipping_address_speedy_floor').val();
                shipping_entrance = $j('#order-shipping_address_speedy_entrance').val();
                shipping_apartment = $j('#order-shipping_address_speedy_apartment').val();

                shipping_speedy_pick_from_office = $j('#order-shipping_address_speedy_office_chooser').val();
                shipping_speedy_office_name = $j('#order-shipping_address_speedy_office_name').val();
                shipping_speedy_office_id = $j('#order-shipping_address_speedy_office_id').val();
        }
        
        
        function validateHour(){
            var isValid = 1;
            var isEnabled = $j('#speedy_exact_hour_enable').is(':checked');
            var hour = parseInt($j('#speedy_exact_hour').val(), 10);
            var minutes = parseInt($j('#speedy_exact_minutes').val(), 10);
            
            
            if (isEnabled) {

                if (isNaN(hour) || isNaN(minutes)) {
                    isValid = 0;
                }
                
                if(hour.toString().length > 2 || minutes.toString().length > 2){
                  isValid = 0; 
                }

                if (hour !== false && !isNaN(hour) && minutes !== false && !isNaN(minutes)) {
                    if ((hour <= 17 && hour >= 10)) {


                        if (hour == 17 && minutes >= 31) {

                            isValid = 0;
                        }

                        if (hour == 10 && minutes <= 29) {
                            isValid = 0;
                        }


                        if (minutes <= 59 && minutes >= 0) {

                            isValid = 1;
                        }else{
                           isValid = 0; 
                        }
                    }else{
                        isValid = 0;
                    }
                }

            }
            
            return isValid;
        }
        
        
})

