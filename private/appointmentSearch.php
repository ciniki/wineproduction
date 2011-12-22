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
	//
	// Grab the settings for the business from the database
	//
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbDetailsQuery.php');
	$rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $business_id, 'wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$settings = $rc['settings'];

	if( !isset($args['start_needle']) || $args['start_needle'] == '' ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'499', 'msg'=>'No search specified'));
	}

    require_once($ciniki['config']['core']['modules_dir'] . '/users/private/datetimeFormat.php');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT ciniki_wineproductions.id AS order_id, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
		. "CONCAT_WS(' ', first, last) AS customer_name, "
		. "invoice_number, "
		. "ciniki_products.name AS wine_name, "
		//. "CONCAT_WS(' - ', CONCAT_WS(' ', first, last), IF(COUNT(invoice_number)>1, CONCAT('(',COUNT(invoice_number),')'), NULL), invoice_number, ciniki_products.name) AS subject, "
		. "UNIX_TIMESTAMP(bottling_date) AS start_ts, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') AS start_date, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') AS date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
		. "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
		. "'ciniki.wineproduction' AS module, "
		. "ciniki_wineproductions.bottling_flags, "
		. "ciniki_wineproductions.bottling_status, "
		. "ciniki_wineproductions.status, "
		. "bottling_duration AS duration, '#aaddff' AS colour, 'ciniki.appointments' AS 'module' "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $business_id) . "' "
		. "";
	if( isset($args['full']) && $args['full'] == 'yes' ) {
		// search for orders including bottled
		$strsql .= "AND ciniki_wineproductions.status <= 60 ";
	} else {
		// Only search for active orders
		$strsql .= "AND ciniki_wineproductions.status < 60 ";
	}
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
	//$strsql .= "GROUP BY CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) ";
	if( isset($args['date']) && $args['date'] != '' ) {
		$strsql .= "ORDER BY ABS(DATEDIFF(DATE(ciniki_wineproductions.bottling_date), DATE('" . ciniki_core_dbQuote($ciniki, $args['date']) . "'))), ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, ciniki_products.name, id ";
	} else {
		$strsql .= "ORDER BY ABS(DATEDIFF(DATE(ciniki_wineproductions.bottling_date), DATE(NOW()))), ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, ciniki_products.name, id ";
	}
	// $strsql .= "ORDER BY ABS(DATEDIFF(ciniki_wineproductions.bottling_date, NOW())), ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, ciniki_products.name, id ";

	// 
	// Have to increase the limit because there are several wines per order, we want the limit the orders, not the wines.
	//
	if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
		$strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']*5) . " ";	// is_numeric verified
	}

	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbHashQueryTree.php');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'wineproduction', array(
		array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
			'fields'=>array('id', 'module', 'start_ts', 'start_date', 'date', 'time', '12hour', 'duration', 'wine_name'),
				'sums'=>array('duration'), 'countlists'=>array('wine_name'), 'limit'=>$args['limit']),
		array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 'fields'=>array('order_id', 'customer_name', 'invoice_number', 'wine_name', 'duration', 'status', 'bottling_flags', 'bottling_status')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
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
