<?php
namespace UiCoreElements\Compatibility;
/**
 * Class WPML_UI_HighlightedText
 */
class WPML_UI_HighlightedText extends \WPML_Elementor_Module_With_Items {

	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'content';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		return array( 'text' );
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_title( $field ) {
		switch( $field ) {

			case 'text':
				return esc_html__( 'Highlighted Text', 'uicore-elements' );

			default:
				return '';
		}
	}

	/**
	 * @param string $field
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		switch( $field ) {
			case 'text':
				return 'LINE';

			default:
				return '';
		}
	}

}
