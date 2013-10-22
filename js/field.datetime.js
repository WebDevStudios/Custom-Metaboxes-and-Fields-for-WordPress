
/**
 * Date & Time Fields
 */

CMB.addCallbackForClonedField( ['CMB_Date_Field', 'CMB_Time_Field', 'CMB_Date_Timestamp_Field', 'CMB_Datetime_Timestamp_Field' ], function( newT ) {

	// Reinitialize all the datepickers
	newT.find( '.cmb_datepicker' ).each(function () {
		jQuery(this).attr( 'id', '' ).removeClass( 'hasDatepicker' ).removeData( 'datepicker' ).unbind().datepicker();
	});

	// Reinitialize all the timepickers.
	newT.find('.cmb_timepicker' ).each(function () {
		jQuery(this).timePicker({
			startTime: "00:00",
			endTime: "23:30",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	});

} );

CMB.addCallbackForInit( function() {

	// Datepicker
	jQuery('.cmb_datepicker').each(function () {
		jQuery(this).datepicker();
	});
	
	// Wrap date picker in class to narrow the scope of jQuery UI CSS and prevent conflicts
	jQuery("#ui-datepicker-div").wrap('<div class="cmb_element" />');

	// Timepicker
	jQuery('.cmb_timepicker').each(function () {
		jQuery(this).timePicker({
			startTime: "00:00",
			endTime: "23:30",
			show24Hours: false,
			separator: ':',
			step: 30
		});
	} );

});