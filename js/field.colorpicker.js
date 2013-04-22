/**
 * ColorPickers
 */

CMB.addCallbackForInit( function() {

	// Colorpicker
	jQuery('input:text.cmb_colorpicker').wpColorPicker();

} );

CMB.addCallbackForClonedField( 'CMB_Color_Picker', function( newT ) {

	// Reinitialize colorpickers
    newT.find('.wp-color-result').remove();
	newT.find('input:text.cmb_colorpicker').wpColorPicker();

} );
