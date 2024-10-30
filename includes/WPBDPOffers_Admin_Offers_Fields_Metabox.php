<?php
/**
 * Created by PhpStorm.
 * User: fred
 * Date: 7/6/17
 * Time: 11:52 AM
 */


class WPBDPOffers_Admin_Offers_Fields_Metabox {

	private $listing = null;

	public function __construct( &$listing ) {

		$this->listing = $listing;

	}

	public function render() {

		$html_line =  '<div id="wpbdo-offers">';
		$html_line .= '<div class="business-directory-plugin-offers-holder">';

		$html_line .= '<table id="business_offers" style="width:100%">';
			$html_line .= '<tr>';
			$html_line .= '<td><div id="business-directory-plugin-offers">&nbsp;</div></td>';
			$html_line .= '</tr>';
			$html_line .= '<tr><td>&nbsp;</td></tr>';
			$html_line .= '<tr>';
			$html_line .= '<td><input id="business-directory-plugin-add-button" onclick="tsl_business_directory_offers_handler.edit_deal();" type="button" style="margin-top:1em;text-align: center;cursor: pointer;cursor:hand;" class="button button-small" value="'. __('Add Item', 'business-directory-plugin-offers') .'" ></td>';
			$html_line .= '</tr>';
			$html_line .= '<tr><td>&nbsp;</td></tr>';

		$html_line .= '</table>';

		$html_line .= '<div style="display: none;" id="tsl_business_offers_table">&nbsp;</div><div id="tsl_business_offers_workarea" style="display: none;">&nbsp;</div>';

		$html_line .= '<p><div id="tsl_business_offers_save_message" style="display: none;font-weight: bold;color: red;">'. __("Don't forget to update the page to save your changes!", 'business-directory-plugin-offers') .'</div></p>';
		$html_line .= '<p>'. __( 'Edit offers to be displayed. Offers become visible on the visible date and are hidden after the end date.', 'business-directory-plugin-offers' ) .'</p>';

		$html_line .= '</div>';

		$html_line .= '</div>';

		return $html_line;
	}

	public static function tsl_metabox_callback( $post ) {

		if( ! class_exists('WPBDP_Listing')) return '';

		$listing = WPBDP_Listing::get( $post->ID );

		if ( ! $listing ) {
			return '';
		}

		$instance = new self( $listing );

		echo $instance->render();
	}
}
