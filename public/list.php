<?php
//
// Description
// -----------
// This function will return a list of orders
//
// Info
// ----
// Status: 			started
//
// Arguments
// ---------
// user_id: 		The user making the request
// 
// Returns
// -------
//
function ciniki_wineproduction_list($ciniki) {
    //  
    // Find all the required and optional arguments
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/prepareArgs.php');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'business_id'=>array('required'=>'yes', 'blank'=>'no', 'errmsg'=>'No business specified'), 
		'customer_id'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No customer specified'),
		'status_list'=>array('required'=>'no', 'type'=>'idlist', 'default'=>'', 'errmsg'=>'No status_list specified'),
		'status'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No status specified'),
		'before_start_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No start date specified'),
		'before_racking_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'after_racking_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'before_rack_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'before_filtering_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No filter date specified'),
		'after_filtering_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No filter date specified'),
		'before_filter_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No filter date specified'),
		'before_bottle_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No bottle date specified'),
		'order_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'started_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'racking_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No racking date specified'),
		'racked_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'filtering_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No filtering date specified'),
		'filtered_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No filtered date specified'),
		'bottled_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No bottled date specified'),
		'bottling_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No bottling date specified'),
		'before_bottling_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No bottling date specified'),
		'after_bottling_date'=>array('required'=>'no', 'type'=>'date', 'default'=>'', 'errmsg'=>'No bottling date specified'),
		'work_date'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No rack date specified'),
		'sorting'=>array('required'=>'no', 'default'=>'', 'errmsg'=>'No sorting order specified'),
        )); 
    if( $rc['stat'] != 'ok' ) { 
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'450', 'msg'=>'Unable to understand request', 'err'=>$rc['err']));
    }   
    $args = $rc['args'];
    
    //  
    // Make sure this module is activated, and
    // check permission to run this function for this business
    //  
    require_once($ciniki['config']['core']['modules_dir'] . '/wineproduction/private/checkAccess.php');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['business_id'], 'ciniki.wineproduction.list'); 
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   

	//
	// FIXME: Add timezone information from business settings
	//
	date_default_timezone_set('America/Toronto');
	$todays_date = strftime("%Y-%m-%d");

	require_once($ciniki['config']['core']['modules_dir'] . '/users/private/dateFormat.php');
	$date_format = ciniki_users_dateFormat($ciniki);

	// ARGS:
	// - status_list (10, 20, 40) or (10,20,30,40,50)
	// - status (10, 20, 30, 40, 50, 100)
	// - before_rack_date (2011/06/31 - search for any )
	// - before_filter_date
	// - before_bottle_date
	// - before_start_date
	//
	// eg:
	// status: 20, before_rack_date: 2011/06/28 
	//	  this will return all wines which have been started and have a rack date on or before jun 28, 2011,
	//    or all the wines that should be ready to rack
	//  
	// status: 10, before_start_date: 2011/05/30
	//    list all wines that have been ordered by not started
	//    EXCLUDE any wines that are to be started AFTER 2011/05/30
	//
	// status_list: 10,20,30,40
	//    list all wines that are not in a completed stage

    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuote.php');
    require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbQuoteIDs.php');

	$strsql = "SELECT ciniki_wineproductions.id, CONCAT_WS(' ', first, last) AS customer_name, invoice_number, "
		. "ciniki_products.name AS wine_name, wine_type, kit_length, ciniki_wineproductions.status, rack_colour, filter_colour, "
		. "order_flags, "
		. "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS order_date, "
		. "DATE_FORMAT(start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
		. "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS racking_date, "
		. "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS rack_date, "
		. "sg_reading, "
		. "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filtering_date, "
		. "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filter_date, "
		. "bottling_flags, "
		. "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottling_date, "
		. "bottling_status, "
		. "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottle_date, "
		. "DATE_FORMAT(IF(rack_date > 0, DATE_ADD(rack_date, INTERVAL (kit_length) DAY), DATE_ADD(start_date, INTERVAL kit_length WEEK)), '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS approx_filtering_date "
	//	. "DATE_FORMAT(IF(rack_date > 0, DATE_ADD(rack_date, INTERVAL (kit_length) DAY), DATE_ADD(start_date, INTERVAL (kit_length) WEEK)), '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS approx_date "
		. ", ciniki_wineproductions.notes, "
		. "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS appointment_id "
		. "FROM ciniki_wineproductions "
		. "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
			. "AND ciniki_customers.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "LEFT JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
			. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "') "
		. "WHERE ciniki_wineproductions.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "AND ciniki_wineproductions.product_id = ciniki_products.id "
		. "AND ciniki_products.business_id = '" . ciniki_core_dbQuote($ciniki, $args['business_id']) . "' "
		. "";
	if( isset($args['customer_id']) && $args['customer_id'] > 0 ) {
		$strsql .= "AND ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' ";
	}

	if( isset($args['status_list']) && is_array($args['status_list']) && count($args['status_list']) ) {
		$strsql .= "AND ciniki_wineproductions.status IN (" . ciniki_core_dbQuoteIDs($ciniki, $args['status_list']) . ") ";
	} elseif( isset($args['status']) && $args['status'] != '' ) {
		$strsql .= "AND ciniki_wineproductions.status = '" . ciniki_core_dbQuote($ciniki, $args['status']) . "' ";
	}

	if( isset($args['before_start_date']) && $args['before_start_date'] == 'today' ) {
		$strsql .= "AND start_date <= NOW() ";
	} else if( isset($args['before_start_date']) && $args['before_start_date'] != '' ) {
		$strsql .= "AND start_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_start_date']) . "' ";
	}
	if( isset($args['before_rack_date']) && $args['before_rack_date'] == 'today' ) {
		$strsql .= "AND rack_date <= NOW() ";
	} else if( isset($args['before_rack_date']) && $args['before_rack_date'] != '' ) {
		$strsql .= "AND rack_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_rack_date']) . "' ";
	}
	if( isset($args['before_racking_date']) && $args['before_racking_date'] == 'today' ) {
		$strsql .= "AND racking_date <= NOW() ";
	} elseif( isset($args['before_racking_date']) && $args['before_racking_date'] != '' ) {
		$strsql .= "AND racking_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_racking_date']) . "' ";
	} elseif( isset($args['after_racking_date']) && $args['after_racking_date'] == 'today' ) {
		$strsql .= "AND racking_date > NOW() ";
	} elseif( isset($args['after_racking_date']) && $args['after_racking_date'] != '' ) {
		$strsql .= "AND racking_date > '" . ciniki_core_dbQuote($ciniki, $args['after_racking_date']) . "' ";
	}
	if( isset($args['before_filter_date']) && $args['before_filter_date'] == 'today' ) {
		$strsql .= "AND filter_date <= NOW() ";
	} else if( isset($args['before_filter_date']) && $args['before_filter_date'] != '' ) {
		$strsql .= "AND filter_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_filter_date']) . "' ";
	}
	if( isset($args['before_filtering_date']) && $args['before_filtering_date'] == 'today' ) {
		$strsql .= "AND filtering_date <= NOW() ";
	} elseif( isset($args['before_filtering_date']) && $args['before_filtering_date'] != '' ) {
		$strsql .= "AND filtering_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_filtering_date']) . "' ";
	} elseif( isset($args['after_filtering_date']) && $args['after_filtering_date'] == 'today' ) {
		$strsql .= "AND filtering_date > NOW() ";
	} elseif( isset($args['after_filtering_date']) && $args['after_filtering_date'] != '' ) {
		$strsql .= "AND filtering_date > '" . ciniki_core_dbQuote($ciniki, $args['after_filtering_date']) . "' ";
	}
	// Bottling_date
	if( isset($args['before_bottling_date']) && $args['before_bottling_date'] == 'today' ) {
		$strsql .= "AND bottling_date <= NOW() ";
	} elseif( isset($args['before_bottling_date']) && $args['before_bottling_date'] != '' ) {
		$strsql .= "AND DATE(bottling_date) <= '" . ciniki_core_dbQuote($ciniki, $args['before_bottling_date']) . "' ";
	} elseif( isset($args['after_bottling_date']) && $args['after_bottling_date'] == 'today' ) {
		$strsql .= "AND bottling_date > NOW() ";
	} elseif( isset($args['after_bottling_date']) && $args['after_bottling_date'] != '' ) {
		$strsql .= "AND DATE(bottling_date) > '" . ciniki_core_dbQuote($ciniki, $args['after_bottling_date']) . "' ";
	}
	if( isset($args['before_bottle_date']) && $args['before_bottle_date'] == 'today' ) {
		$strsql .= "AND bottle_date <= NOW() ";
	} else if( isset($args['before_bottle_date']) && $args['before_bottle_date'] != '' ) {
		$strsql .= "AND bottle_date <= '" . ciniki_core_dbQuote($ciniki, $args['before_bottle_date']) . "' ";
	}

	if( isset($args['order_date']) && $args['order_date'] == 'today' ) {
		$strsql .= "AND order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['order_date']) && $args['order_date'] != '' ) {
		$strsql .= "AND order_date = '" . ciniki_core_dbQuote($ciniki, $args['order_date']) . "' ";
	}
	if( isset($args['started_date']) && $args['started_date'] == 'today' ) {
		$strsql .= "AND start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['started_date']) && $args['started_date'] != '' ) {
		$strsql .= "AND start_date = '" . ciniki_core_dbQuote($ciniki, $args['started_date']) . "' ";
	}
	if( isset($args['racking_date']) && $args['racking_date'] == 'today' ) {
		$strsql .= "AND racking_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['racking_date']) && $args['racking_date'] != '' ) {
		$strsql .= "AND racking_date = '" . ciniki_core_dbQuote($ciniki, $args['racking_date']) . "' ";
	}
	if( isset($args['racked_date']) && $args['racked_date'] == 'today' ) {
		$strsql .= "AND rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['racked_date']) && $args['racked_date'] != '' ) {
		$strsql .= "AND rack_date = '" . ciniki_core_dbQuote($ciniki, $args['racked_date']) . "' ";
	}
	if( isset($args['filtering_date']) && $args['filtering_date'] == 'today' ) {
		$strsql .= "AND filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['filtering_date']) && $args['filtering_date'] != '' ) {
		$strsql .= "AND filtering_date = '" . ciniki_core_dbQuote($ciniki, $args['filtering_date']) . "' ";
	}
	if( isset($args['filtered_date']) && $args['filtered_date'] == 'today' ) {
		$strsql .= "AND filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['filtered_date']) && $args['filtered_date'] != '' ) {
		$strsql .= "AND filter_date = '" . ciniki_core_dbQuote($ciniki, $args['filtered_date']) . "' ";
	}
	if( isset($args['bottled_date']) && $args['bottled_date'] == 'today' ) {
		$strsql .= "AND bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "' ";
	} else if( isset($args['bottled_date']) && $args['bottled_date'] != '' ) {
		$strsql .= "AND bottle_date = '" . ciniki_core_dbQuote($ciniki, $args['bottled_date']) . "' ";
	}

	if( isset($args['bottling_date']) ) {
		if( $args['bottling_date'] == 'late_wine' ) {
			$strsql .= "AND bottling_date > 0 AND (bottling_date < filtering_date "
				. "OR (filtering_date = 0 AND bottling_date < DATE_ADD(racking_date, INTERVAL (kit_length-2) WEEK)) "
				. "OR (racking_date = 0 AND bottling_date < DATE_ADD(start_date, INTERVAL kit_length WEEK)) "
				. "OR bottling_date < start_date) ";
		}
		elseif( $args['bottling_date'] == 'ctb' ) {
			$strsql .= "AND (TIME(bottling_date) = '00:00:00' OR bottling_date = '0000-00-00 00:00:00' ) "
				. "AND (filtering_date > 0 AND filtering_date < NOW()) "
				. " ";
		}
		elseif( $args['bottling_date'] != '' ) {
			$strsql .= "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $args['bottling_date']) . "' ";
		}
	}

	if( isset($args['schedule_date']) && $args['schedule_date'] == 'today' ) {
		$strsql .= "AND ((order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(racking_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR " 
			. "(filtering_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(bottling_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') ) ";
	} else if( isset($args['schedule_date']) && $args['schedule_date'] != '' ) {
		$strsql .= "AND ((order_date = '" . ciniki_core_dbQuote($ciniki, $args['schedule_date']) . "') OR "
			. "(start_date = '" . ciniki_core_dbQuote($ciniki, $args['schedule_date']) . "') OR "
			. "(racking_date = '" . ciniki_core_dbQuote($ciniki, $args['schedule_date']) . "') OR " 
			. "(filtering_date = '" . ciniki_core_dbQuote($ciniki, $args['schedule_date']) . "') OR "
			. "(bottling_date = '" . ciniki_core_dbQuote($ciniki, $args['schedule_date']) . "') ) ";
	}

	if( isset($args['work_date']) && $args['work_date'] == 'today' ) {
		$strsql .= "AND ((order_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(start_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(rack_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR " 
			. "(filter_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') OR "
			. "(bottle_date = '" . ciniki_core_dbQuote($ciniki, $todays_date) . "') ) ";
	} else if( isset($args['work_date']) && $args['work_date'] != '' ) {
		$strsql .= "AND ((order_date = '" . ciniki_core_dbQuote($ciniki, $args['work_date']) . "') OR "
			. "(start_date = '" . ciniki_core_dbQuote($ciniki, $args['work_date']) . "') OR "
			. "(rack_date = '" . ciniki_core_dbQuote($ciniki, $args['work_date']) . "') OR " 
			. "(filter_date = '" . ciniki_core_dbQuote($ciniki, $args['work_date']) . "') OR "
			. "(bottle_date = '" . ciniki_core_dbQuote($ciniki, $args['work_date']) . "') ) ";
	}

	if( $args['sorting'] == 'invoice_number' ) {
		$strsql .= "ORDER BY invoice_number, wine_type DESC ";
	} else if( $args['sorting'] == 'racking_date,invoice_number' ) {
		$strsql .= "ORDER BY ciniki_wineproductions.racking_date, ciniki_wineproductions.invoice_number ";
	} else if( $args['sorting'] == 'bottling_date' ) {
		$strsql .= "ORDER BY ciniki_wineproductions.bottling_date ";
	} else if( $args['sorting'] == 'appointments' ) {
		$strsql .= "ORDER BY ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id ";
	} else if( $args['status'] == '10' ) {
		$strsql .= "ORDER BY kit_length, wine_type DESC, order_date ASC ";
	} else if( $args['status'] == '20' ) {
		$strsql .= "ORDER BY kit_length, wine_type DESC, racking_date ASC ";
	} else if( $args['status'] == '30' ) {
		$strsql .= "ORDER BY kit_length, wine_type DESC, filtering_date ASC ";
	} else if( $args['status'] == '40' ) {
		$strsql .= "ORDER BY kit_length, wine_type DESC, filter_date ASC ";
	} else if( $args['customer_id'] > 0 ) {
		$strsql .= "ORDER BY status, order_date ASC ";
	} else {
		$strsql .= "ORDER BY ciniki_wineproductions.invoice_number DESC ";
	}

	// error_log($strsql);
	require_once($ciniki['config']['core']['modules_dir'] . '/core/private/dbRspQuery.php');
	$rc = ciniki_core_dbRspQuery($ciniki, $strsql, 'wineproduction', 'orders', 'order', array('stat'=>'ok', 'orders'=>array()));
	if( $rc != 'ok' ) {
		return $rc;
	}

	if( !isset($rc['orders']) ) {
		return array('stat'=>'fail', 'err'=>array('pkg'=>'ciniki', 'code'=>'361', 'msg'=>'Unable to find any orders'));
	}

	return $rc;
}
?>
