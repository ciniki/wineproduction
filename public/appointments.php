<?php
//
// Description
// -----------
// This function will return the list of bottling appointments for a business
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// date:				The date to get the schedule for.
//
// Returns
// -------
//	<events>
//		<event customer_name="" invoice_number="" wine_name="" />
//	</events>
//
function ciniki_wineproduction_appointments($ciniki) {
	//
	// Find all the required and optional arguments
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'date'=>array('required'=>'no', 'default'=>'today', 'blank'=>'yes', 'errmsg'=>'No date specified'), 
		'startdate'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No start date specified'), 
		'enddate'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No end date specified'), 
		'appointment_id'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No appointment ID specified'), 
		'customer_id'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No customer ID specified'), 
		'status'=>array('required'=>'no', 'blank'=>'yes', 'errmsg'=>'No status specified'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];

	//
	// Check that date or customer_id is specified
	//
	if( !isset($args['date']) || !isset($args['customer_id']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'501', 'msg'=>'No customer or date specified'));
	}
	
	//
	// Check access to business_id as owner, or sys admin
	//
	require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
	$rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.appointments');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/appointments.php');
	return ciniki_wineproduction__appointments($ciniki, $args['business_id'], $args);
}
?>
