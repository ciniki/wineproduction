<?php
//
// Description
// -----------
// This method will return the wineproduction settings for a business.
//
// Info
// ----
// Status: 			started
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:		The ID of the business to get the settings for.
// 
// Returns
// -------
//
function ciniki_wineproduction_getSettings($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
        )); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.getSettings'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	
//  require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/getColours.php');
//	$colours = ciniki_wineproduction__getColours($ciniki, $args['business_id']);

	//
	// Get the current time in the users format
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	date_default_timezone_set('America/Toronto');
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQuery.php');
	$strsql = "SELECT DATE_FORMAT(FROM_UNIXTIME('" . time() . "'), '" . ciniki_core_dbQuote($ciniki, $date_format) . "') as formatted_date ";
	$rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.core', 'date');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$formatted_date = '';
	if( isset($rc['date']['formatted_date']) ) {
		$formatted_date = $rc['date']['formatted_date'];
	}

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc = ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'ciniki.wineproduction', 'settings', '');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	$rc['date_today'] = $formatted_date;

	//
	// Return the response, including colour arrays and todays date
	//
	return $rc;
}
?>
