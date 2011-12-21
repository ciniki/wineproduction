<?php
//
// Description
// -----------
// This function will return the appointment for a business
//
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
//	<appointments>
//		<appointment calendar="Appointments" customer_name="" invoice_number="" wine_name="" />
//	</appointments>
//
function ciniki_wineproduction__appointmentSearch($ciniki, $business_id, $args) {

	if( !isset($args['start_needle']) || $args['start_needle'] == '' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'499', 'msg'=>'No search specified'));
	}

    require_once($ciniki['config']['core']['modules_dir'] . '/users/private/datetimeFormat.php');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT ciniki_wineproductions.id AS wid, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
		. "CONCAT_WS(' - ', CONCAT_WS(' ', first, last), IF(COUNT(invoice_number)>1, CONCAT('(',COUNT(invoice_number),')'), NULL), invoice_number, ciniki_products.name) AS subject, "
		. "UNIX_TIMESTAMP(bottling_date) AS start_ts, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') AS start_date, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') AS date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
		. "'ciniki.wineproduction' AS module, "
		. "SUM(bottling_duration) AS duration "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		// Only search for active orders
		. "AND ciniki_wineproductions.status < 60 "
		. "";
	if( is_numeric($args['start_needle']) ) {
		$strsql .= "AND invoice_number LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "";
	} else {
		$strsql .= "AND ( ciniki_customers.first LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_customers.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
			. "OR ciniki_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' ) "
			. "";
	}
	$strsql .= "GROUP BY CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) ";
	$strsql .= "ORDER BY ABS(DATEDIFF(ciniki_wineproductions.bottling_date, NOW())), ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, ciniki_products.name, id ";
	
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";	// is_numeric verified
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'wineproduction', array(
		array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
			'fields'=>array('id', 'module', 'start_ts', 'start_date', 'date', 'time', '12hour', 'colour', 'duration', 'subject')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$appointments = $rc['appointments'];

	return array('stat'=>'ok', 'appointments'=>$appointments);;
}
?>
