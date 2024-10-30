<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 11/2/17
 * Time: 9:20 AM
 */

add_action( 'plugins_loaded', array( 'WPBDPOffers_shortcode_deals_display', 'init' ));

class WPBDPOffers_shortcode_deals_display{

    private $offers_field_id = 0;
    private $post_array = array();
    private $categories = array();
    private $deals_list = array();
    private $current_timestamp;

    public static function init(){
        $class = __CLASS__;
        new $class;
    }

    public function __construct(){

        add_shortcode( 'WPBDPOffers', array( $this , 'display_deals_page') );

    }

    public function display_deals_page(){

        $this->current_timestamp = strtotime(date('Y-m-d 00:00:00'));

        $this->set_offers_field_id();
        $this->set_posts();
        $this->set_deals();

        echo $this->get_main_html();

    }

    private function get_main_html(){

        $html_line = '';

        $html_line .= $this->add_filter();

        $html_line .= '<div id="tsl-bdoffers-masonry-grid">';
        $html_line .= '<div class="gutter-sizer"></div>';
        $html_line .= $this->get_grid_items();
        $html_line .= '</div>';

        return $html_line;
    }

    public function get_grid_items(){

        $html_line = '';

        foreach($this->deals_list as $deal ){
            $html_line .= $this->create_deal_html( $deal );
        }

        return $html_line;
    }

    private function add_filter(){

        if(sizeof($this->categories) == 0 ) return '';

        $html_line = '<div id="tsl-offers-display-filter" style="width:100%">';

        $html_line .= '<div style="float:right"><select id="tsl-grid-filter" >';
        $html_line .= '<option value="tsl_offers_all" >'.__('All Offers', 'business-directory-offers').'</option>';

        foreach($this->categories as $category ) {
            $html_line .= '<option value="'.$category['term_id'].'">'.$category['name'].'</option>';
        }
        $html_line .= '</select></div>';
        $html_line .= '</div><br><br>';

        return $html_line;

    }

    private function create_deal_html( $deal = array() ){

        $html_line = '<div class="tsl-grid-item '.$deal['categories_class'].'">';

        $html_line .= '<table class="tsl-bdoffer-grid-item-table" >';
        if(strlen($deal['deal_image'])>10) {
                $html_line .= '<tr>';
                $html_line .= '<td colspan="2"><a href="' . $deal['link'] . '" title="'.$deal['listing_title'].'"><img class="tsl-bdoffer-image" style="width:100%" src="' . $deal['deal_image'] . '"></a></td>';
                $html_line .= '</tr>';
        }else {
            if (strlen($deal['main_image']) > 10) {
                $html_line .= '<tr>';
                $html_line .= '<td colspan="2"><a href="' . $deal['link'] . '" title="'.$deal['listing_title'].'"><img class="tsl-bdoffer-image" style="width:100%" src="' . $deal['main_image'] . '"></a></td>';
                $html_line .= '</tr>';
            }
        }

        $html_line .= '<tr>';
        $html_line .= '<td colspan="2"><div class="tsl-bdoffer-go-to-listing"><a href="'.$deal['link'].'">'.$deal['listing_title'].'</a></div></td>';
        $html_line .= '</tr>';


        $html_line .= '<tr>';
        $html_line .= '<td colspan="2"><div class="tsl-bdoffer-title">'.$deal['title'].'</div></td>';
        $html_line .= '</tr>';

        $html_line .= '<tr>';
        $html_line .= '<td colspan="2"><div class="tsl-bdoffer-content">'.$deal['content'].'</div></td>';
        $html_line .= '</tr>';

        if($deal['always_visible'] != 1) {

            if($deal['starts'] > $this->current_timestamp) {
                $html_line .= '<tr>';
                $html_line .= '<td><div class="tsl-bdoffer-from">' . __('Starts', 'business-directory-offers') . ':</div></td><td><div class="tsl-bdoffer-from-value">' . $deal['starts_date'] . '</div></td>';
                $html_line .= '</tr>';
            }

            $html_line .= '<tr>';
            $html_line .= '<td><div class="tsl-bdoffer-end">' . __('Ends', 'business-directory-offers') . ':</div></td><td><div class="tsl-bdoffer-end-value">' . $deal['ends_date'] . '</div></td>';
            $html_line .= '</tr>';
        }

        if(strlen($deal['coupon_url'])>10) {
            $html_line .= '<tr>';
            $html_line .= '<td colspan="2"><div class="tsl-bdoffer-coupon"><a href="' . $deal['coupon_url'] . '" target="_blank">' . __('Coupon', 'business-directory-offers') . '</a></div></td>';
            $html_line .= '</tr>';
        }

        $html_line .= '</table>';

        $html_line .= '</div>';

        return $html_line;
    }

    private function set_posts(){

        $args = array(
            'numberposts' => 1000,
            'post_type' => 'wpbdp_listing',
            'post_status' => 'publish'
        );

        $this->post_array = get_posts($args);

    }
    private function set_offers_field_id(){

        if( ! class_exists('WPBDP_Listing')) return '';

        $all_fields = wpbdp_get_form_fields();

        for($f=0;$f<sizeof($all_fields);$f++){
            $this_obj = $all_fields[$f];
            if(strtolower($this_obj->get_shortname()) == 'offers'){
                $this->offers_field_id = $this_obj->get_id();
            }
        }
    }

    private function set_deals(){

        $this->deals_list = array();

        if($this->offers_field_id > 0 ){
            foreach( $this->post_array as $index => $post) {

                $post_item = array();
                $post_item['main_image'] = $this->get_image( $post );
                $post_item['listing_title'] = $post->post_title;
                $post_item['link'] = get_permalink($post->ID);

                $all_categories =  get_the_terms( $post->ID , WPBDP_CATEGORY_TAX ) ;

                foreach (wpbdp_get_form_fields(array('association' => 'meta')) as $field) {
                    if ($field->get_id() == $this->offers_field_id) {

                        $deals_dom_info = $field->value( $post->ID );

                        $dom = new DOMDocument;
                        $dom->loadHTML($deals_dom_info);

                        foreach ($dom->getElementsByTagName('span') as $tag) {

                            try {

                                $this_item = $post_item;

                                $this_item['title'] = ' ';
                                $this_item['deal_image'] = ' ';
                                $this_item['content'] = ' ';
                                $this_item['visible'] = ' ';
                                $this_item['starts'] = ' ';
                                $this_item['ends'] = ' ';
                                $this_item['always_visible'] = ' ';
                                $this_item['starts_date'] = substr($tag->getAttribute('data-start_date'),0,5);
                                $this_item['ends_date'] = substr($tag->getAttribute('data-end_date'),0,5);

                                if ($tag->getAttribute('data-title')) $this_item['title'] = $tag->getAttribute('data-title');
                                if(strlen($tag->nodeValue) > 0 ) $this_item['content'] = $tag->nodeValue;
                                if (strlen($tag->getAttribute('data-visible_date')) > 2) $this_item['visible'] = $this->convert_date($tag->getAttribute('data-visible_date'), $tag->getAttribute('data-date_format'));
                                if (strlen($tag->getAttribute('data-start_date')) > 2) $this_item['starts'] = $this->convert_date($tag->getAttribute('data-start_date'), $tag->getAttribute('data-date_format'));
                                if (strlen($tag->getAttribute('data-end_date')) > 2) $this_item['ends'] = $this->convert_date($tag->getAttribute('data-end_date'), $tag->getAttribute('data-date_format'));
                                if ($tag->getAttribute('data-always_visible')) $this_item['always_visible'] = $tag->getAttribute('data-always_visible');
                                if ($tag->getAttribute('data-voucher_image')) $this_item['coupon_url'] = $tag->getAttribute('data-voucher_image');
                                if ($tag->getAttribute('data-deal_image')) $this_item['deal_image'] = $tag->getAttribute('data-deal_image');

                                $this_item['categories_class'] = 'tsl-cat-all';

                                $class_array = array();

                                foreach($all_categories as $cat_index => $subcat){
                                    if(!in_array($subcat->term_id, $class_array)) {
                                        $this_item['categories_class'] .= ' tsl-cat-' . $subcat->term_id;
                                        $class_array[] = $subcat->term_id;
                                    }
                                    $add_item = true;
                                    foreach($this->categories as $main_cat) {
                                        if($main_cat['term_id'] == $subcat->term_id ) $add_item = false;

                                    }
                                    if($add_item) $this->categories[] = array('term_id' => $subcat->term_id, 'name' => $subcat->name);
                                }

                                if($this_item['ends'] >= $this->current_timestamp || $this_item['always_visible'] == 1 ) $this->deals_list[] = $this_item;

                            }catch (Exception $e){
                                //print_r($e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    }

    private function get_image( $post ){

        $this_image = wp_get_attachment_url( $post->ID );

         if (strlen($this_image) == 0) {
            if (strlen(get_post_thumbnail_id( $post->ID )) > 0 ) {
                $this_image = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ), 'thumbnail' );
            }
        }

        return $this_image;
    }

    private function convert_date($this_date , $format){

        $date_value = '';
        if(strlen($this_date) < 4 ) return '';

        if(strlen($format) < 4) $format = 'mm/dd/yy';

        try{

            switch($format){
                case 'dd/mm/yy':
                    $date_value = DateTime::createFromFormat("d/m/Y H:i:s", $this_date . '00:00:00');
                    break;
               case 'mm/dd/yy':
                    $date_value = DateTime::createFromFormat("m/d/Y H:i:s", $this_date . '00:00:00');
                    break;
               case 'd M, yy':
                    $date_value = DateTime::createFromFormat("d M, Y H:i:s", $this_date . '00:00:00');
                    break;
               case 'd MM, yy':
                    $date_value = DateTime::createFromFormat("d F, Y H:i:s", $this_date . '00:00:00');
                    break;
            }

            if($date_value) {
                return $date_value->format('U');
            }else{
                return '';
            }

        } catch ( exception $e ) {

            return ' ';
        }
    }

}