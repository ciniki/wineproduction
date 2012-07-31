<?php
//
// Description
// -----------
// This function will return the list of colours available to the wine production module.
// In the future, these values can be pulled from the database.
//
// Info
// ----
// Status: 			beta
//
// Arguments
// ---------
// ciniki:
// business_id:		The ID of the business to get the settings for.
// 
// Returns
// -------
//
function ciniki_wineproduction__getSettings($ciniki, $business_id) {

	date_default_timezone_set('America/Toronto');

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	return ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'ciniki.wineproduction', 'settings', '');
}
