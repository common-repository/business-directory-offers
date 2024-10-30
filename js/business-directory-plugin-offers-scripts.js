/**
 * Created by Tiny Screen Labs, LLC on 11/3/16.
 */

if (typeof tsl_business_directory_offers !== 'function') {

    var tsl_business_directory_offers = function () {

        var dialog;
        var cursorPosition = 0;
        var meta_editing_object = null;
        var current_file_type;

        this.load_special_deals_feature = function ( ) {

            moment.locale(tsl_business_lng.locale);

            //manage deal visibility from the from end
            var visibleOffers = false;
            var total_rows = jQuery('.tsl_business_offers').length;
            var current_row = 1;

            jQuery('.tsl_business_offers').each( function(){
                //hide expired or non-visible items
                // if current date is between show date and expiration date then display else hide item

                var startDt = jQuery(this).data("start_date");
                var endDt = jQuery(this).data("end_date");
                var visibleDt = jQuery(this).data("visible_date");
                var always_visible = jQuery(this).data("always_visible");
                var tsl_date_format = jQuery(this).data("date_format");
                var voucher_image = jQuery(this).data("voucher_image");
                var deal_image = jQuery(this).data("deal_image");

                if(tsl_date_format) {
                    if (tsl_date_format == 'dd/mm/yy') tsl_date_format = 'DD/MM/YYYY';
                    if (tsl_date_format == 'mm/dd/yy') tsl_date_format = 'MM/DD/YYYY';
                    if (tsl_date_format == 'd M, yy')   tsl_date_format = 'D MMM, YYYY';
                    if (tsl_date_format == 'd MM, yy')  tsl_date_format = 'D MMMM, YYYY';
                }

                if (!tsl_date_format) tsl_date_format = 'MM/DD/YYYY';

                if(startDt) startDt = moment(startDt, tsl_date_format , true).format();
                if(endDt) endDt = moment(endDt, tsl_date_format , true).format();
                if(visibleDt) visibleDt = moment(visibleDt, tsl_date_format , true).format();

                endDt = moment(endDt).add(23.99, 'hours');


                if( always_visible == 1 || ( ( new Date(startDt).getTime() <= new Date().getTime() || new Date(visibleDt).getTime() <= new Date().getTime() ) && new Date(endDt).getTime() >= new Date().getTime() ) ){

                    var html_line = '<div class="tsl_business_offers_fe_title" style="font-weight: bold;">' + jQuery(this).data('title') + "</div>";
                    if(!always_visible) html_line = html_line.concat('<span class="tsl_business_offers_fe_starts_label" >'+tsl_business_lng.starts+':&nbsp;</span><span class="tsl_business_offers_fe_starts_data">'+jQuery(this).data('start_date')+'</span>&nbsp;&nbsp;<span class="tsl_business_offers_fe_ends_title" >'+tsl_business_lng.ends+':&nbsp;</span><span class="tsl_business_offers_fe_ends_data" >'+jQuery(this).data('end_date')+'</span><br>');

                    var deal_info = tsl_business_directory_offers_handler.replace_voucher_with_link(jQuery(this).html() , voucher_image);

                    html_line = html_line.concat('<span class="tsl_business_offers_fe_content">'+ deal_info + '</span><br class="tsl_business_offers_fe_row_sep">');


                    if(total_rows > current_row){
                        html_line = html_line.concat('<hr class="tsl_business_offers_fe_hr">');
                    }else{
                        html_line = html_line.concat('<span class="tsl_business_offers_fe_end_of_data" style="margin-bottom:1.5em;">&nbsp;</span>');
                    }

                    jQuery(this).html(html_line);
                    jQuery(this).show();
                    visibleOffers = true;
                }else{
                    //remove following break <br>
                    jQuery(this).next('br').remove();
                    //remove this listing
                    jQuery(this).remove();
                }
                current_row++;

            })

            //check to see if there are other deals not managed by this plugin
            var offer_holder = jQuery('#tsl_business_offers_div').parent();
            var all_offers = offer_holder.html();
            offer_holder.find('#tsl_business_offers_div').remove();
            var remaining_offers = offer_holder.html();
            offer_holder.html(all_offers);

            if(remaining_offers) {
                if (remaining_offers.trim() != '') visibleOffers = true;
            }

            if(!visibleOffers){
                //hide special ofers tab
                offer_holder.parent().parent().hide();
            }

            this.load_offers_table();

        };

        this.load_offers_table = function(){

            meta_editing_object = jQuery('#wpbdp-field-' + tsl_field_id.id);

            cursorPosition = meta_editing_object.prop("selectionStart");
            jQuery('#tsl_business_offers_workarea').html(meta_editing_object.val());

            if( ! meta_editing_object ) return;

            //load current special offers
            var html_line = "<table style='width:100%'>";
            html_line = html_line.concat("<tr><td style='width:40%'><strong>"+tsl_business_lng.title+"</strong></td><td style='width:20%'><strong>"+tsl_business_lng.visible_date+"</strong></td><td style='width:20%'><strong>"+tsl_business_lng.start_date+"</strong></td><td style='width:20%'><strong>"+tsl_business_lng.end_date+"</strong></td><td style='display:none;'>&nbsp;</td><td style='display:none;'></td><td style='display:none;'>&nbsp;</td><td style='display:none;'>&nbsp;</td></tr>");
            html_line = html_line.concat("</table>");
            html_line = html_line.concat("<div id='tsl_business_offers_deals_list_div' style='max-height:20em;overflow-x:hidden;overflow-y: auto;'>");
            html_line = html_line.concat("<table style='width:100%' id='tsl_business_offers_deals_list'>");

            var line_number = 1;

                var list_items = jQuery('#tsl_business_offers_workarea').find('span');

                jQuery(list_items).each(function () {

                    if (jQuery(this).hasClass('tsl_business_offers')) {
                        var title = jQuery(this).data("title");
                        var start_date = jQuery(this).data("start_date");
                        var end_date = jQuery(this).data("end_date");
                        var visible_date = jQuery(this).data("visible_date");
                        var content = jQuery(this).text();
                        var always_visible = jQuery(this).data("always_visible");
                        var voucher_image = jQuery(this).data("voucher_image");
                        var voucher_thumbnail = jQuery(this).data("voucher_thumbnail");
                        var deal_image = jQuery(this).data("deal_image");
                        var deal_thumbnail = jQuery(this).data("deal_thumbnail");

                        if (!start_date) start_date = '';
                        if (!end_date) end_date = '';
                        if (!visible_date) visible_date = '';
                        if (!content) content = '';
                        if (!voucher_image) voucher_image = '';
                        if (!voucher_thumbnail) voucher_thumbnail = '';
                        if (!deal_image) deal_image = '';
                        if (!deal_thumbnail) deal_thumbnail = '';

                        if( always_visible) visible_date = tsl_business_lng.always;
                        if (!always_visible) always_visible = '';

                        //create table record
                        html_line = html_line.concat("<tr class='tsl_business_offers_row'><td style='width:40%;cursor: pointer;cursor:hand;'>" + '<a alt="'+tsl_business_lng.edit_item+'" onclick="tsl_business_directory_offers_handler.edit_deal(' + line_number + ');return false;" >' + title + "</a></td><td style='width:20%'>" + visible_date + "</td><td style='width:20%'>" + start_date + "</td><td style='width:20%'>" + end_date + "</td><td style='display:none;'>" + content + "</td><td style='display:none;'>" + always_visible + "</td><td style='display:none;'>" + voucher_image + "</td><td style='display:none;'>" + voucher_thumbnail + "</td><td style='display:none;'>" + deal_image + "</td><td style='display:none;'>" + deal_thumbnail + "</td></tr>");
                        line_number++;

                    }
                })


            html_line = html_line.concat("</table></div>");

            //html_line = html_line.concat("<div id='tsl_business_offers_editing_area' style='display: none;'></div>");

            jQuery('#business-directory-plugin-offers').html(html_line);
        }

        this.save_special_offers = function(){

            var tiny_mce_sep = '<br>';

            jQuery('#tsl_business_offers_workarea').html(meta_editing_object.val());

            jQuery('#tsl_business_offers_save_message').show();

            //create span elements
            var html_string = '';
            var date_array = [];
            var element_id = 1;

            jQuery('.tsl_business_offers_row').each(function(){
                var title = '';
                var visible_date = '';
                var start_date = '';
                var end_date = '';
                var content = '';
                var always_visible = '';
                var tsl_date_format = '';
                var voucher_thumbnail = '';
                var voucher_image = '';
                var deal_image = '';
                var deal_thumbnail = '';

                if(tsl_manager) tsl_date_format = tsl_manager;

                if(jQuery(this).find('td:first').text()) title = jQuery(this).find('td:first').text();
                if(jQuery(this).find('td:eq(1)').text()) visible_date = jQuery(this).find('td:eq(1)').text();
                if(jQuery(this).find('td:eq(2)').text()) start_date = jQuery(this).find('td:eq(2)').text();
                if(jQuery(this).find('td:eq(3)').text()) end_date = jQuery(this).find('td:eq(3)').text();
                if(jQuery(this).find('td:eq(4)').html()) content = jQuery(this).find('td:eq(4)').html();
                if(jQuery(this).find('td:eq(5)').text()) always_visible = jQuery(this).find('td:eq(5)').text();
                if(jQuery(this).find('td:eq(6)').text()) voucher_image = jQuery(this).find('td:eq(6)').text();
                if(jQuery(this).find('td:eq(7)').text()) voucher_thumbnail = jQuery(this).find('td:eq(7)').text();
                if(jQuery(this).find('td:eq(8)').text()) deal_image = jQuery(this).find('td:eq(8)').text();
                if(jQuery(this).find('td:eq(9)').text()) deal_thumbnail = jQuery(this).find('td:eq(9)').text();

               var html_line = '<span class="tsl_business_offers" data-voucher_thumbnail="'+voucher_thumbnail+'" data-voucher_image="'+voucher_image+'" data-deal_image="'+deal_image+'" data-deal_thumbnail="'+deal_thumbnail+'"  data-date_format="'+tsl_date_format+'" data-start_date="'+start_date+'" data-end_date="'+end_date+'" data-visible_date="'+visible_date+'" data-title="'+title+'" data-always_visible="'+always_visible+'">'+content+'</span>' + tiny_mce_sep;

                date_array[element_id] = {'this_date' : start_date , 'html_line' : html_line };
                element_id++;
            });


            date_array.sort(function(a,b){
              // Turn your strings into dates, and then subtract them
              // to get a value that is either negative, positive, or zero.
              return new Date(a.this_date) - new Date(b.this_date);
            });


            jQuery(date_array).each(function(index, value){
                if(value) {
                    html_string = html_string.concat(value.html_line);
                }
            });

            if(jQuery('#tsl_business_offers_workarea').find('#tsl_business_offers_div').length){

                jQuery('#tsl_business_offers_div').html(html_string);

                meta_editing_object.val(jQuery('#tsl_business_offers_workarea').html());

            }else{

                html_string = '<div id="tsl_business_offers_div">' + html_string + '</div>';

                var v = meta_editing_object.val();
                var textBefore = v.substring(0, cursorPosition);
                var textAfter = v.substring(cursorPosition, v.length);

                meta_editing_object.val(textBefore + html_string + textAfter);

            }


        };

        this.edit_deal = function(row_id){

            if(!jQuery('#tsl_dialog').length) jQuery('body').append('<div id="tsl_dialog" title="'+tsl_business_lng.manage_special_offers+'" style="display: none;"><div id="tsl_business_offers_editing_area" style="display: none;"></div></div>');

            var l10nButtons = {};

            if(row_id) {

                l10nButtons = {
                    "Save": {
                        text: tsl_business_lng.save,
                        click: function () {
                            tsl_business_directory_offers_handler.save_deal(row_id)
                        }
                    },
                    "Delete": {
                        text: tsl_business_lng.delete_item,
                        click: function () {
                            tsl_business_directory_offers_handler.delete_deal(row_id);
                        }
                    },
                    "Cancel": {
                        text: tsl_business_lng.cancel,
                        click: function () {
                            jQuery('#business-directory-plugin-add-button').show();
                            dialog.dialog('close');
                        }
                    },
                };
            }else{
                l10nButtons = {
                    "Save": {
                        text: tsl_business_lng.save,
                        click: function () {
                            tsl_business_directory_offers_handler.save_deal(row_id)
                        }
                    },
                    "Cancel": {
                        text: tsl_business_lng.cancel,
                        click: function () {
                            jQuery('#business-directory-plugin-add-button').show();
                            dialog.dialog('close');
                        }
                    },
                };
            }

            dialog = jQuery( "#tsl_dialog" ).dialog({
                height: 700,
                width: 700,
                modal: true,
                open: function (event, ui) {

                    //hide buttons
                    jQuery('.ui-dialog-titlebar-close').css('display','none', 'important');

                    var row_number = 1;
                    var html_line = '';
                    var title = '';
                    var visible_date = '';
                    var start_date = '';
                    var end_date = '';
                    var content = '';
                    var delete_button = '';
                    var always_visible = '0';
                    var text_disabled = '';
                    var voucher_image = '';
                    var voucher_thumbnail = '';
                    var deal_image = '';
                    var deal_thumbnail = '';

                    jQuery('#business-directory-plugin-add-button').hide();
                    jQuery('#tsl_business_offers_deals_list_div').css('max-height', '5em');

                    jQuery('.tsl_business_offers_row').each(function () {
                        if (row_id == row_number) {

                            title = jQuery(this).find('td:first').text();
                            visible_date = jQuery(this).find('td:eq(1)').text();
                            if (visible_date == 'Always') visible_date = '';
                            start_date = jQuery(this).find('td:eq(2)').text();
                            end_date = jQuery(this).find('td:eq(3)').text();
                            content = jQuery(this).find('td:eq(4)').html();
                            if (jQuery(this).find('td:eq(5)').text() == '1') {
                                always_visible = 'checked';
                                text_disabled = 'disabled';
                            }
                            voucher_image = jQuery(this).find('td:eq(6)').text();
                            voucher_thumbnail = jQuery(this).find('td:eq(7)').text();
                            deal_image = jQuery(this).find('td:eq(8)').text();
                            deal_thumbnail = jQuery(this).find('td:eq(9)').text();

                        }
                        row_number++;
                    });

                    html_line = html_line.concat('<hr style="margin-top:1em;margin-bottom:1em;">');
                    html_line = html_line.concat('<table style="width:100%"><tr><td><strong>' + tsl_business_lng.offer_title + '</strong></td><td><input style="width:100%" type="text" id="tsl_business_offers_edit_offer_title" value="' + title + '"></td></tr></table>');
                    html_line = html_line.concat('<table style="width:100%">');
                    html_line = html_line.concat('<tr><td><strong>' + tsl_business_lng.visible_date + '</strong></td><td><strong>' + tsl_business_lng.start_date + '</strong></td><td><strong>' + tsl_business_lng.end_date + '</strong></td></tr>');
                    html_line = html_line.concat('<tr>');
                    html_line = html_line.concat('<td><input type="text" id="tsl_business_offers_edit_visible_date" class="tsl_business_offers_datepicker" value="' + visible_date + '" ' + text_disabled + '></td>');
                    html_line = html_line.concat('<td><input type="text" id="tsl_business_offers_edit_start_date" class="tsl_business_offers_datepicker" value="' + start_date + '" ' + text_disabled + '></td>');
                    html_line = html_line.concat('<td><input type="text" id="tsl_business_offers_edit_end_date" class="tsl_business_offers_datepicker" value="' + end_date + '" ' + text_disabled + '></td>');
                    html_line = html_line.concat('</tr>');
                    html_line = html_line.concat('<tr>');
                    html_line = html_line.concat('<td colspan="3"><strong>' + tsl_business_lng.visible_always + '</strong>&nbsp;&nbsp;<input id="tsl_business_offers_edit_visible_always" type="checkbox" name="always_visible" value="1" ' + always_visible + '></td>');
                    html_line = html_line.concat('</tr>');
                    html_line = html_line.concat('</table>');
                    html_line = html_line.concat('<p><strong>' + tsl_business_lng.offer_description + '</strong></p><p><textarea rows="4" style="width:100%;" id="tsl_business_offers_edit_offer_content" >' + content + '</textarea><br><span class="business_offers_message_note" style="padding-left:0;padding-top;0;">' + tsl_business_lng.upload_instructions + '</span></p>');

                    html_line = html_line.concat('<table style="width:100%"><tr>'+tsl_business_directory_offers_handler.upload_form(deal_thumbnail , deal_image, voucher_thumbnail , voucher_image)+"</tr></table>");

                    jQuery('#tsl_business_offers_editing_area').html(html_line).show();
                    jQuery(".tsl_business_offers_datepicker").datepicker({
                        dateFormat: tsl_manager,
                        onSelect: function () {

                            if (this.id == 'tsl_business_offers_edit_visible_date') {
                                if (!jQuery('#tsl_business_offers_edit_start_date').val()) jQuery('#tsl_business_offers_edit_start_date').val(this.value);
                                if (!jQuery('#tsl_business_offers_edit_end_date').val()) jQuery('#tsl_business_offers_edit_end_date').val(this.value);
                            }
                            if (this.id == 'tsl_business_offers_edit_start_date') {
                                if (!jQuery('#tsl_business_offers_edit_visible_date').val()) jQuery('#tsl_business_offers_edit_visible_date').val(this.value);
                                if (!jQuery('#tsl_business_offers_edit_end_date').val()) jQuery('#tsl_business_offers_edit_end_date').val(this.value);
                            }
                        }
                    });

                    tsl_business_directory_offers_handler.load_image_handler();

                    jQuery('#tsl_business_offers_edit_visible_always').click(function () {
                        if (this.checked) {
                            jQuery('#tsl_business_offers_edit_visible_date').prop('disabled', true).val('');
                            jQuery('#tsl_business_offers_edit_start_date').prop('disabled', true).val('');
                            jQuery('#tsl_business_offers_edit_end_date').prop('disabled', true).val('');
                        } else {
                            jQuery('#tsl_business_offers_edit_visible_date').prop('disabled', false);
                            jQuery('#tsl_business_offers_edit_start_date').prop('disabled', false);
                            jQuery('#tsl_business_offers_edit_end_date').prop('disabled', false);
                        }
                    });
                },
                buttons: l10nButtons
            });
        };

        this.cancel = function(){
            jQuery('#tsl_business_offers_editing_area').hide();
            jQuery('#business-directory-plugin-add-button').show();
        };

        this.delete_deal = function(row_id) {

            if (row_id) {

                var row_number = 1;
                jQuery('.tsl_business_offers_row').each(function() {
                    if (row_id == row_number) {
                        jQuery(this).remove();
                    }
                    row_number++;
                })
            }

            jQuery('#tsl_business_offers_editing_area').hide();
            jQuery('#tsl_business_offers_deals_list_div').css('max-height' , '20em');

            tsl_business_directory_offers_handler.save_special_offers();
            jQuery('#business-directory-plugin-add-button').show();

            tsl_business_directory_offers_handler.reorder_offers();

            dialog.dialog( 'close' );

        };

        this.reorder_offers = function(){

            var html_line = '';
            var row_number = 1;

            jQuery('.tsl_business_offers_row').each(function(){
                var this_title = jQuery(this).find('td:first').text();
                jQuery(this).find('td:first').html('<a alt="'+tsl_business_lng.edit_item+'" style="cursor: pointer;cursor:hand;" onclick="tsl_business_directory_offers_handler.edit_deal('+row_number+');return false;" >'+this_title+'</a>');
                row_number++;
            })
        }

        this.save_deal = function(row_id){

            if(!jQuery('#tsl_business_offers_edit_offer_title').val().length){
                jQuery('#tsl_business_offers_edit_offer_title').css('border', 'solid 1px red');
                return;
            }

            jQuery('#tsl_business_offers_edit_offer_title').css('border', '');

            if(row_id){
                var row_number = 1;
                jQuery('.tsl_business_offers_row').each(function() {
                    if (row_id == row_number) {
                        jQuery(this).find('td:first').html('<a alt="'+tsl_business_lng.edit_item+'" style="cursor: pointer;cursor:hand;" onclick="tsl_business_directory_offers_handler.edit_deal('+row_number+');return false;" >'+jQuery('#tsl_business_offers_edit_offer_title').val()+'</a>');
                        jQuery(this).find('td:eq(1)').text(jQuery('#tsl_business_offers_edit_visible_date').val());
                        jQuery(this).find('td:eq(2)').text(jQuery('#tsl_business_offers_edit_start_date').val());
                        jQuery(this).find('td:eq(3)').text(jQuery('#tsl_business_offers_edit_end_date').val());
                        jQuery(this).find('td:eq(4)').text(jQuery('#tsl_business_offers_edit_offer_content').val());

                        if(jQuery('#tsl_business_offers_edit_visible_always').is(':checked')) {
                            jQuery(this).find('td:eq(5)').text('1');
                            jQuery(this).find('td:eq(1)').text('Always');
                        }else{
                            jQuery(this).find('td:eq(5)').text('0');
                        }
                        jQuery(this).find('td:eq(6)').text(jQuery('#tsl_image').text());
                        jQuery(this).find('td:eq(7)').text(jQuery('#tsl_thumbnail').text());

                        jQuery(this).find('td:eq(8)').text(jQuery('#tsl_deal_image').text());
                        jQuery(this).find('td:eq(9)').text(jQuery('#tsl_deal_thumbnail').text());

                    }
                    row_number++;
                });
            }else{
                //add new row
                var title = '';
                var visible_date = '';
                var start_date = '';
                var end_date = '';
                var content = '';
                var always_visible = '0';
                var voucher_image = '';
                var voucher_thumbnail = '';
                var deal_image = '';
                var deal_thumbnail = '';

                var line_number = jQuery('.tsl_business_offers_row').length + 1;
                var html_line = jQuery('#tsl_business_offers_deals_list').html();
                if(jQuery('#tsl_business_offers_edit_offer_title').val())  title = jQuery('#tsl_business_offers_edit_offer_title').val();
                if(jQuery('#tsl_business_offers_edit_visible_date').val()) visible_date = jQuery('#tsl_business_offers_edit_visible_date').val();
                if(jQuery('#tsl_business_offers_edit_start_date').val()) start_date = jQuery('#tsl_business_offers_edit_start_date').val();
                if(jQuery('#tsl_business_offers_edit_end_date').val()) end_date = jQuery('#tsl_business_offers_edit_end_date').val();
                if(jQuery('#tsl_business_offers_edit_offer_content').val()) content = jQuery('#tsl_business_offers_edit_offer_content').val();
                if(jQuery('#tsl_image').text()) voucher_image = jQuery('#tsl_image').text();
                if(jQuery('#tsl_thumbnail').text()) voucher_thumbnail = jQuery('#tsl_thumbnail').text();
                if(jQuery('#tsl_deal_image').text()) deal_image = jQuery('#tsl_deal_image').text();
                if(jQuery('#tsl_deal_thumbnail').text()) deal_thumbnail = jQuery('#tsl_deal_thumbnail').text();

                if(jQuery('#tsl_business_offers_edit_visible_always').is(':checked')) {
                    always_visible = '1';
                }

                html_line = html_line.concat("<tr class='tsl_business_offers_row'><td style='width:40%'>"+'<a alt="'+tsl_business_lng.edit_item+'" style="cursor: pointer;cursor:hand;" onclick="tsl_business_directory_offers_handler.edit_deal('+line_number+');return false;" >'+title+"</a></td><td style='width:20%'>"+visible_date+"</td><td style='width:20%'>"+start_date+"</td><td style='width:20%'>"+end_date+"</td><td style='display:none;'>"+content+"</td><td style='display:none;'>"+always_visible+"</td><td style='display:none;'>" + voucher_image + "</td><td style='display:none;'>" + voucher_thumbnail + "</td><td style='display:none;'>" + deal_image + "</td><td style='display:none;'>" + deal_thumbnail + "</td></tr>");
                jQuery('#tsl_business_offers_deals_list').html(html_line);

            }

            jQuery('#tsl_business_offers_editing_area').hide();
            jQuery('#tsl_business_offers_deals_list_div').css('max-height' , '20em');

            tsl_business_directory_offers_handler.save_special_offers();
            jQuery('#business-directory-plugin-add-button').show();

            dialog.dialog( 'close' );

        }

        this.upload_form = function(deal_thumbnail, deal_image, voucher_thumbnail , voucher_image){

            var image_info = '';
            var display_button = '';
            var deal_image_info = '';
            var deal_display_button = '';

            if(voucher_thumbnail){
                image_info = '<div class="plupload-thumbs" style="clear:inherit; margin-top:0; margin-left:5em; padding-top:10px; float:left; width:50%;">';
                image_info = image_info.concat('<div class="thumb">');
                image_info = image_info.concat('<div class="thumbi">');
                image_info = image_info.concat('<a onclick="tsl_business_directory_offers_handler.remove_voucher()" href="javascript:;" class="delete check">Remove</a>');
                image_info = image_info.concat('</div>');
                image_info = image_info.concat('<img style="margin-top:.5em" src="' + voucher_thumbnail + '" >');
                image_info = image_info.concat('</div>');
                image_info = image_info.concat('</div>');
                image_info = image_info.concat('<div id="tsl_thumbnail" style="display:none;">' + voucher_thumbnail + '</div>');
                image_info = image_info.concat('<div id="tsl_image" style="display:none;">' + voucher_image + '</div>    ');
                display_button ='style="display:none;"';
            }

            if(deal_thumbnail){
                deal_image_info = '<div class="plupload-thumbs" style="clear:inherit; margin-top:0; margin-left:5em; padding-top:10px; float:left; width:50%;">';
                deal_image_info = deal_image_info.concat('<div class="thumb">');
                deal_image_info = deal_image_info.concat('<div class="thumbi">');
                deal_image_info = deal_image_info.concat('<a onclick="tsl_business_directory_offers_handler.remove_deal_image()" href="javascript:;" class="delete check">Remove</a>');
                deal_image_info = deal_image_info.concat('</div>');
                deal_image_info = deal_image_info.concat('<img style="margin-top:.5em" src="' + deal_thumbnail + '" >');
                deal_image_info = deal_image_info.concat('</div>');
                deal_image_info = deal_image_info.concat('</div>');
                deal_image_info = deal_image_info.concat('<div id="tsl_deal_thumbnail" style="display:none;">' + deal_thumbnail + '</div>');
                deal_image_info = deal_image_info.concat('<div id="tsl_deal_image" style="display:none;">' + deal_image + '</div>    ');
                deal_display_button ='style="display:none;"';
            }

            var button = '<input type="button" style="margin-top:1em;margin-left:0;width:8em;text-align: center;" id="tsl_business_offers_open_image" class="button button-small" value="'+tsl_business_lng.select_file+'" >';
            var deal_button = '<input type="button" style="margin-top:1em;margin-left:0;width:8em;text-align: center;" id="tsl_business_offers_open_deal_image" class="button button-small" value="'+tsl_business_lng.select_file+'" >';

            var html_line = '<td style="vertical-align: top;">';
            html_line = html_line.concat('<table style="width:100%">');
            html_line = html_line.concat('<tr>');

            html_line = html_line.concat('<td style="width:50%" id="tsl-deal-image-column" valign="top">');
            html_line = html_line.concat('<div class="upload-form" >');
            html_line = html_line.concat('<span style="margin-bottom:1em"><strong>' + tsl_business_lng.deal_image + ' </strong>&nbsp;</span><br>');
            html_line = html_line.concat('<div class="tsl-deal-form-group" '+deal_display_button+'>');
            html_line = html_line.concat(deal_button + '<input type="file" id="tsl_files" name="tsl_voucher_file_upload[]" accept="image/*" class="tsl-files-data" style="display: none"/>');
            html_line = html_line.concat('</div></div><div id="tsl_deal_progress_div" class="tsl_progress" style="display:none;margin-top:1em;width:10em;height:2em;">Loading...</div><div style="margin-top:1em" id="tsl_deal_image_gallery">'+deal_image_info+'</div>');
            html_line = html_line.concat('</td>');

            html_line = html_line.concat('<td style="width:50%" id="tsl-voucher-image-column" valign="top">');
            html_line = html_line.concat('<div class="upload-form" >');
            html_line = html_line.concat('<span style="margin-bottom:1em"><strong>' + tsl_business_lng.voucher_image + ' </strong>&nbsp;</span><br>');
            html_line = html_line.concat('<div class="tsl-form-group" '+display_button+'>');
            html_line = html_line.concat(button + '<input type="file" id="tsl_files" name="tsl_voucher_file_upload[]" accept="image/*" class="tsl-files-data" style="display: none"/>');
            html_line = html_line.concat('</div></div><div id="tsl_progress_div" class="tsl_progress" style="display:none;margin-top:1em;width:10em;height:2em;">Loading...</div><div style="margin-top:1em" id="tsl_image_gallery">'+image_info+'</div>');
            html_line = html_line.concat('</td>');

            html_line = html_line.concat('</tr>');
            html_line = html_line.concat('</table>');
            html_line = html_line.concat('</td>');

            return html_line;
        }

        this.replace_voucher_with_link = function(html_line , image){

            var offer_info = html_line;

            if(image && html_line) {

                if (html_line.indexOf('[VOUCHER') > -1) {
                    var shortcode_start = html_line.indexOf('[VOUCHER') + 9;
                    var shortcode_length = html_line.substring(shortcode_start).indexOf(']') + shortcode_start;
                    var shortcode = html_line.substring(shortcode_start, shortcode_length);
                    var link = '<a style="text-decoration: underline;" href="' + image + '" target="_blank">' + shortcode + '</a>';
                    offer_info = html_line.replace('[VOUCHER ' + shortcode + ']', link);
                }
            }

            return offer_info;
        }

        this.remove_deal_image = function(){
            jQuery('#tsl_deal_image_gallery').html('');
            jQuery('.tsl-deal-form-group').show();
        }

        this.remove_voucher = function(){
            jQuery('#tsl_image_gallery').html('');
            jQuery('.tsl-form-group').show();
        }

        this.load_image_handler = function(){

            jQuery('span[title]').qtip({style: 'qtip-bootstrap'});

            jQuery('#tsl_business_offers_open_image').click(function(){
                jQuery('#tsl_files').click();
                current_file_type = 1;
            });

            jQuery('#tsl_business_offers_open_deal_image').click(function(){
                jQuery('#tsl_files').click();
                current_file_type = 2;
            });


            jQuery('body').on('change', '.tsl-files-data' , function (e) {
                e.preventDefault;

                jQuery('body').css('cursor','wait');

                var fd = new FormData();
                var files_data = jQuery('.upload-form .tsl-files-data');

                if(current_file_type == 2) {
                    jQuery('#tsl_deal_progress_div').show();
                    jQuery('.tsl-deal-form-group').hide();
                }else{
                    jQuery('#tsl_progress_div').show();
                    jQuery('.tsl-form-group').hide();
                }

                jQuery.each(files_data, function (i, obj) {
                    jQuery.each(obj.files, function (j, file) {
                        fd.append('tsl_voucher_file_upload[' + j + ']', file);
                    })
                });

                fd.append('action', 'tsl_business_directory_plugin_offers_upload_voucher');
                fd.append('image_type', current_file_type );

                jQuery.ajax({
                    type: 'POST',
                    url: tsl_ajax_url,
                    data: fd,
                    contentType: false,
                    processData: false,
                    dataType: "json",
                    success: function (response) {

                        if(current_file_type == 2){
                            jQuery('#tsl_deal_progress_div').hide();
                            jQuery('#tsl_deal_image_gallery').html(response['ul_con']);
                        }else{
                            jQuery('#tsl_progress_div').hide();
                            jQuery('#tsl_image_gallery').html(response['ul_con']);
                        }

                        jQuery('body').css('cursor','');
                    }
                });
            });
        }

        this.set_qtip_handler = function(){
            jQuery('#section-styling .qtip-container').click(function() {
                var style = jQuery(this).children().data('style');

                // Toggle active classes
                jQuery('#section-styling .qtip-container').removeClass('active');
                jQuery(this).addClass('active');

                // Set the global style
                if( this.parentNode.id === 'styling-builtin' ) {
                    globalStyle = [style].concat(globalStyle.split(' ').slice(1)).join(' ');
                }else{
                    globalStyle = style;
                }
            });
        }

        this.show_deals_page = function(){

            this.render_masonry();

            jQuery('#tsl-grid-filter').change(function(){

            if(jQuery('#tsl-grid-filter').val()=='tsl_offers_all') {
                jQuery('.tsl-cat-all').show();
            }else{
                jQuery('.tsl-cat-all').hide();
                jQuery('.tsl-cat-'+jQuery('#tsl-grid-filter').val()).show();
            }
            tsl_business_directory_offers_handler.render_masonry();

            })
        }

        this.render_masonry = function(){
            jQuery('#tsl-bdoffers-masonry-grid').masonry({
                columnWidth: '.tsl-grid-item',
                itemSelector: '.tsl-grid-item',
                gutter: 20
            });
        }
    }
}

jQuery(document).ready(function(){
    try {

        if(typeof tsl_field_id !== 'undefined') {
            if ( tsl_field_id.id > 0 ) jQuery('.wpbdp-form-field-id-' + tsl_field_id.id).hide();
        }

        tsl_business_directory_offers_handler = new tsl_business_directory_offers;

        if(typeof tsl_content !== 'undefined'){
            var html = decodeURIComponent(tsl_content);
            jQuery('#tsl_business_offers_div').html(html);
        }

        tsl_business_directory_offers_handler.load_special_deals_feature();

        var offers_grid = jQuery('#tsl-bdoffers-masonry-grid');
        if(offers_grid.length > 0 ){
            tsl_business_directory_offers_handler.show_deals_page();
        }

    }catch(err){
        console.log(err.message);
    }
});