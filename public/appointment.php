<?php
//
// Description
// -----------
// This function will return all the information for a bottling appointment.
//
// Arguments
// ---------
// api_key:
// auth_token:
// business_id:			The ID of the business to get the details for.
// appointment_id:		The ID of the bottling appointment to get.  The ID
// 						is a combination of unix timestamp formatted bottling
//						date and the ID of the customer.  
//
// Returns
// -------
//	<appointment id="" customer_name="" date="2012-02-08" time="00:00" 12hour="12:00" allday="yes" bottling_date="Feb 8, 2012 12:00 AM" duration="120" invoice_number="11111" wine_name="CC Rosso Grande, CC Merlot" colour="#caeeb6" bottling_flags="0">
//		<orders>
//			<order order_id="20" invoice_number="11111" wine_name="CC Rosso Grande" duration="60" order_date="Jan 2, 2012" start_date="Jan 2, 2012" racking_date="Jan 12, 2012" filtering_date="Feb 7, 2012" bottling_date="Feb 8, 2012 12:00 AM" status="40" bottling_status="Ready" colour="#caeeb6" />
//			<order order_id="21" invoice_number="11111" wine_name="CC Merlot" duration="60" order_date="Jan 2, 2012" start_date="Jan 2, 2012" racking_date="Jan 12, 2012" filtering_date="Feb 7, 2012" bottling_date="Feb 8, 2012 12:00 AM" status="40" bottling_status="Ready" colour="#caeeb6" />
//		</orders>
//		<followups>
//			<followup id="15" user_id="2" date_added="Feb 6, 2012 8:20 AM" age="2 days" content="Left message" user_display_name="Andrew" />
//			<followup id="19" user_id="2" date_added="Feb 7, 2012 8:20 AM" age="1 day" content="Called again, no answer" user_display_name="Andrew" />
//		</followups>
//	</appointment>
//
function ciniki_wineproduction_appointment($ciniki) {
	//
	// Find all the required and optional arguments
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
	$rc = ciniki_core_prepareArgs($ciniki, 'no', array(
		'business_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Business'), 
		'appointment_id'=>array('required'=>'yes', 'blank'=>'yes', 'name'=>'Appointment'), 
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	$args = $rc['args'];
	
	//
	// Check access to business_id as owner, or sys admin
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
	$rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.appointment');
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}

	//
	// Grab the settings for the business from the database
	//
	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
	$rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'business_id', $args['business_id'], 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
	$settings = $rc['settings'];

	//
	// FIXME: Add timezone information
	//
	date_default_timezone_set('America/Toronto');

	ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
	$datetime_format = ciniki_users_datetimeFormat($ciniki);

	$strsql = "SELECT ciniki_wineproductions.id AS order_id, ciniki_wineproductions.customer_id, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
		. "ciniki_customers.display_name AS customer_name, invoice_number, ciniki_products.name AS wine_name, "
		. "DATE_FORMAT(bottling_date, '%Y-%m-%d') As date, "
		. "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
		. "IF(STRCMP(DATE_FORMAT(bottling_date, '%H:%i'), '00:00'), 'no', 'yes') AS allday, "
		. "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
		. "UNIX_TIMESTAMP(bottling_date) as bottling_timestamp, bottling_duration AS duration, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') as bottling_date, "
		. "ciniki_wineproductions.bottling_flags, ciniki_wineproductions.bottling_nocolour_flags, "
		. "ciniki_wineproduction_settings.detail_value AS colour, "
		. "DATE_FORMAT(order_date, '%b %e, %Y') AS order_date, "
		. "DATE_FORMAT(start_date, '%b %e, %Y') AS start_date, "
		. "DATE_FORMAT(racking_date, '%b %e, %Y') AS racking_date, "
		. "DATE_FORMAT(filtering_date, '%b %e, %Y') AS filtering_date, "
		. "ciniki_wineproductions.status, IFNULL(s2.detail_value, '') AS bottling_status, "
		. "ciniki_wineproductions.bottling_notes "
		. "FROM ciniki_wineproductions "
		. "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_wineproduction_settings ON (ciniki_wineproductions.business_id = ciniki_wineproduction_settings.business_id "
			. "AND ciniki_wineproduction_settings.detail_key = CONCAT_WS('.', 'bottling.status', LOG2(ciniki_wineproductions.bottling_status)+1, 'colour')) "
		. "LEFT JOIN ciniki_wineproduction_settings s2 ON (ciniki_wineproductions.business_id = s2.business_id "
			. "AND s2.detail_key = CONCAT_WS('.', 'bottling.status', LOG2(ciniki_wineproductions.bottling_status)+1, 'name')) "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.status < 100 "
		. "";
	// Select all orders which have the same customer_id and bottling_date, specified as appointment_id
	$strsql .= "AND CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) = '" . ciniki_core_dbQuote($ciniki, $args['appointment_id']) . "' ";
	// Sort properly so querytree can understand results
	$strsql .= ""
		. "ORDER BY ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, wine_name, id "
		. "";

	ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryTree');
	$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.wineproduction', array(
		array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
			'fields'=>array('id', 'customer_id', 'customer_name', 
				'date', 'time', '12hour', 'allday', 'bottling_date', 'duration', 
				'invoice_number', 'wine_name', 'colour', 
				'bottling_flags', 'bottling_nocolour_flags', 'bottling_notes'), 
			'sums'=>array('duration'), 'countlists'=>array('wine_name')),
		array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 'fields'=>array('order_id', 'invoice_number', 'wine_name', 'duration',
			'order_date', 'start_date', 'racking_date', 'filtering_date', 'bottling_date', 'status', 'bottling_status', 'bottling_notes', 'colour')),
		));
	if( $rc['stat'] != 'ok' ) {
		return $rc;
	}
	if( !isset($rc['appointments']) ) {
		return $rc;
	}
	$appointments = $rc['appointments'];

	//
	// Lookup customer records
	//
	if( isset($appointments[0]['appointment']['customer_id']) 
		&& $appointments[0]['appointment']['customer_id'] > 0 ) {
		$appointments[0]['appointment']['customer'] = array();
		if( $appointments[0]['appointment']['customer_id'] > 0 ) {
			$strsql = "SELECT ciniki_customers.id, type, "
				. "ciniki_customers.display_name, "
				. "phone_home, phone_work, phone_fax, phone_cell, "
				. "ciniki_customers.company, "
				. "ciniki_customer_emails.email AS emails "
				. "FROM ciniki_customers "
				. "LEFT JOIN ciniki_customer_emails ON (ciniki_customers.id = ciniki_customer_emails.customer_id "
					. "AND ciniki_customer_emails.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
					. ") "
				. "WHERE ciniki_customers.id = '" . ciniki_core_dbQuote($ciniki, $appointments[0]['appointment']['customer_id']) . "' "
				. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
				. "";
			$rc = ciniki_core_dbHashQueryTree($ciniki, $strsql, 'ciniki.customers', array(
				array('container'=>'customers', 'fname'=>'id', 'name'=>'customer',
					'fields'=>array('id', 'type', 'display_name',
						'phone_home', 'phone_work', 'phone_cell', 'phone_fax', 'emails'),
					'lists'=>array('emails'),
					),
				));
			if( $rc['stat'] != 'ok' ) {
				return $rc;
			}
			if( isset($rc['customers']) && isset($rc['customers'][0]['customer']) ) {
				$appointments[0]['appointment']['customer'] = $rc['customers'][0]['customer'];
			}
		}
	}

	return array('stat'=>'ok', 'appointments'=>$appointments);
}
?>
