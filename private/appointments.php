<?php
//
// Description
// -----------
// This function will return a bottling schedule for a day
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
//		<appointment module="ciniki.wineproduction" customer_name="" invoice_number="" wine_name="" />
//	</appointments>
//
function ciniki_wineproduction__appointments($ciniki, $business_id, $args) {

	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $business_id, 'wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$settings = $rc['settings'];

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');
	if( $args['date'] == '' || $args['date'] == 'today' ) {
		$args['date'] = strftime("%Y-%m-%d");
	}

	$strsql = "SELECT ciniki_wineproductions.id AS order_id, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
		. "CONCAT_WS(' ', first, last) AS customer_name, "
		. "invoice_number, "
		. "ciniki_products.name AS wine_name, "
		. "UNIX_TIMESTAMP(bottling_date) AS start_ts, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') AS date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
		. "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
		. "bottling_duration AS duration, "
		. "ciniki_wineproductions.bottling_flags, "
		. "ciniki_wineproductions.bottling_status, "
//		. "ciniki_wineproduction_settings.detail_value AS secondary_colour, "
//		. "s2.detail_value AS colour, "
//		. "s3.detail_value AS bottling_status, "
		. "ciniki_wineproductions.status "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
//		. "LEFT JOIN ciniki_wineproduction_settings ON (ciniki_wineproductions.business_id = ciniki_wineproduction_settings.business_id "
//			. "AND ciniki_wineproduction_settings.detail_key = CONCAT_WS('.', 'bottling.flags', LOG2(ciniki_wineproductions.bottling_flags)+1, 'colour')) "
//		. "LEFT JOIN ciniki_wineproduction_settings s2 ON (ciniki_wineproductions.business_id = s2.business_id "
//			. "AND s2.detail_key = CONCAT_WS('.', 'bottling.status', LOG2(ciniki_wineproductions.bottling_status)+1, 'colour')) "
//		. "LEFT JOIN ciniki_wineproduction_settings s3 ON (ciniki_wineproductions.business_id = s3.business_id "
//			. "AND s3.detail_key = CONCAT_WS('.', 'bottling.status', LOG2(ciniki_wineproductions.bottling_status)+1, 'name')) "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "AND ciniki_wineproductions.status < 100 "
		. "";
	if( isset($args['date']) && $args['date'] != '' ) {
		$strsql .= "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $args['date']) . "' ";
	} else {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'497', 'msg'=>'No constraints provided'));
	}
	$strsql .= ""
		. "ORDER BY ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, wine_name, id "
		. "";
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'wineproduction', array(
		array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
			'fields'=>array('id', 'start_ts', 'date', 'time', '12hour', 'duration', 'wine_name'),
			'sums'=>array('duration'), 'countlists'=>array('wine_name')),
		array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 'fields'=>array('order_id', 'customer_name', 'invoice_number', 'wine_name', 'duration', 'status', 'bottling_flags', 'bottling_status')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['appointments']) ) {
		return array('stat'=>'ok', 'appointments'=>array());
	}
	$appointments = $rc['appointments'];

	//
	// Create subject, and remote orders to flatten appointments
	//
	foreach($appointments as $anum => $appointment) {
		if( $appointment['appointment']['time'] == '00:00' ) {
			$appointments[$anum]['appointment']['allday'] = 'yes';
		}
		$appointments[$anum]['appointment']['subject'] = $appointment['appointment']['orders'][0]['order']['customer_name'];
		if( count($appointment['appointment']['orders']) > 1 ) {
			$appointments[$anum]['appointment']['subject'] .= ' (' . count($appointment['appointment']['orders']) . ')';
		}
		$appointments[$anum]['appointment']['subject'] .= ' - ' . preg_replace('/-\s*[A-Z]/', '', $appointment['appointment']['orders'][0]['order']['invoice_number']);
		$appointments[$anum]['appointment']['subject'] .= ' - ' . $appointment['appointment']['wine_name'];
		$min_status = 255;
		$min_flags = 255;
		foreach($appointments[$anum]['appointment']['orders'] as $onum => $order) {
			if( $order['order']['bottling_status'] < $min_status ) {
				$min_status = $order['order']['bottling_status'];
			}
			if( $order['order']['bottling_flags'] < $min_flags ) {
				$min_flags = $order['order']['bottling_flags'];
			}
		}
		
		$appointments[$anum]['appointment']['secondary_text'] = '';
		$appointments[$anum]['appointment']['secondary_colour'] = '#ffffff';
		$scomma = '';
		if( $min_status < 255 && isset($settings['bottling.status.' . (log($min_status, 2)+1) . '.name'])) {
			$appointments[$anum]['appointment']['secondary_text'] .= $settings['bottling.status.' . (log($min_status, 2)+1) . '.name'];
			$appointments[$anum]['appointment']['colour'] = $settings['bottling.status.' . (log($min_status, 2)+1) . '.colour'];
			$scomma = ', ';
		}
		if( $min_flags < 255 && isset($settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name']) ) {
			$appointments[$anum]['appointment']['secondary_text'] .= $scomma . $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name'];
			$appointments[$anum]['appointment']['secondary_colour'] = $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.colour'];
		}
//		if( isset($appointments[$anum]['appointment']['bottling_status']) && $appointments[$anum]['appointment']['bottling_status'] != '' ) {
//			$appointments[$anum]['appointment']['subject'] .= ' ' . $appointment['appointment']['bottling_status'];
//		}
		unset($appointments[$anum]['appointment']['wine_name']);
		unset($appointments[$anum]['appointment']['orders']);
		unset($appointments[$anum]['appointment']['bottling_status']);
		$appointments[$anum]['appointment']['calendar'] = 'Bottling Schedule';
		$appointments[$anum]['appointment']['module'] = 'ciniki.wineproduction';
	}

	return array('stat'=>'ok', 'appointments'=>$appointments);;
}
?>
