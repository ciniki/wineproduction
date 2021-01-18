<?php
//
// Description
// -----------
// Return the report of new wineproduction
//
// Arguments
// ---------
// ciniki:
// tnid:         The ID of the tenant to get the birthdays for.
// args:                The options for the query.
//
// Additional Arguments
// --------------------
// days:                The number of days past to look for new wineproduction.
// 
// Returns
// -------
//
function ciniki_wineproduction_reporting_blockWinesProcessed(&$ciniki, $tnid, $args) {
    //
    // Get the tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbQuoteIDs');

    $date_format = "M j, Y";
    $datetime_format = "M j, Y g:i A";

    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    $start_dt = new DateTime('now', new DateTimezone($intl_timezone));
    $today_date = clone $start_dt;
    $start_dt->setTime(23,59,59);
    $end_dt = clone $start_dt;
    if( isset($args['days']) && $args['days'] > 0 ) {
        $start_dt->sub(new DateInterval('P' . $args['days'] . 'D'));
    } else {
        $start_dt->sub(new DateInterval('P1D'));
    }
    $start_dt->setTimezone(new DateTimezone('UTC'));
    $end_dt->setTimezone(new DateTimezone('UTC'));

    //
    // Get the list of wine orders
    //
    $strsql = "SELECT ciniki_wineproductions.id, "
        . "ciniki_customers.display_name AS customer_name, "
        . "invoice_number, "
        . "ciniki_wineproduction_products.name AS wine_name, "
        . "wine_type, "
        . "kit_length, "
        . "ciniki_wineproductions.status, "
        . "ciniki_wineproductions.status AS status_text, "
        . "order_flags, "
        . "DATE_FORMAT(order_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS order_date, "
        . "DATE_FORMAT(ciniki_wineproductions.start_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS start_date, "
        . "DATE_FORMAT(racking_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS racking_date, "
        . "DATE_FORMAT(rack_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS rack_date, "
        . "sg_reading, "
        . "DATE_FORMAT(filtering_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filtering_date, "
        . "DATE_FORMAT(filter_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS filter_date, "
        . "bottling_flags, "
//      . "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottling_date, "
        . "bottling_date, "
        . "bottling_status, "
        . "DATE_FORMAT(bottle_date, '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS bottle_date, "
//        . "DATE_FORMAT(IF(rack_date > 0, DATE_ADD(rack_date, INTERVAL (kit_length) DAY), "
//        . "DATE_ADD(ciniki_wineproductions.start_date, INTERVAL kit_length WEEK)), '" . ciniki_core_dbQuote($ciniki, $date_format) . "') AS approx_filtering_date "
        . "ciniki_wineproductions.notes, "
        . "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS appointment_id "
        . "FROM ciniki_wineproductions "
        . "LEFT JOIN ciniki_customers ON ("
            . "ciniki_wineproductions.customer_id = ciniki_customers.id "
            . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_wineproduction_products ON ("
            . "ciniki_wineproductions.product_id = ciniki_wineproduction_products.id "
            . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.product_id = ciniki_wineproduction_products.id "
        . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "";
    if( isset($args['status']) && $args['status'] != '' && $args['status'] > 0 ) {
        $strsql .= "AND ciniki_wineproductions.status = '" . ciniki_core_dbQuote($ciniki, $args['status']) . "' ";
        if( $args['status'] == 10 ) {
            $strsql .= "AND ciniki_wineproductions.order_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' ";
        } elseif( $args['status'] == 20 ) {
            $strsql .= "AND ciniki_wineproductions.start_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' ";
        } elseif( $args['status'] == 30 ) {
            $strsql .= "AND ciniki_wineproductions.rack_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' ";
        } elseif( $args['status'] == 40 ) {
            $strsql .= "AND ciniki_wineproductions.filter_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' ";
        } elseif( $args['status'] == 60 ) {
            $strsql .= "AND ciniki_wineproductions.bottle_date = '" . ciniki_core_dbQuote($ciniki, $today_date->format('Y-m-d')) . "' ";
        }
    } else {
        $strsql .= "AND ciniki_wineproductions.last_updated >= '" . ciniki_core_dbQuote($ciniki, $start_dt->format('Y-m-d H:i:s')) . "' ";
    }
    $strsql .= "ORDER BY customer_name, wine_name, status "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'wines', 'fname'=>'id', 
            'fields'=>array('id', 'customer_name', 'invoice_number', 
                'wine_name', 'wine_type', 'kit_length', 'status', 'status_text',
                'order_flags', 'order_date', 'start_date', 'racking_date', 'rack_date',
                'sg_reading', 'filtering_date', 'filter_date', 'bottling_date', 'bottling_status', 'bottle_date', 
                'notes', 'appointment_id',
                ),
            'maps'=>array('status_text'=>$maps['wineproduction']['status']),
            'utctotz'=>array('bottling_date'=>array('timezone'=>$intl_timezone, 'format'=>'M j, Y')),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $wines = isset($rc['wines']) ? $rc['wines'] : array();

    if( count($wines) > 0 ) {
        //
        // Create the report blocks
        //
        $chunk = array(
            'type'=>'table',
            'columns'=>array(
                array('label'=>'Customer', 'pdfwidth'=>'40%', 'field'=>'customer_name'),
                array('label'=>'Wine', 'pdfwidth'=>'40%', 'field'=>'wine_name'),
                array('label'=>'Status', 'pdfwidth'=>'20%', 'field'=>'status_text'),
                ),
            'data'=>$wines,
//            'editApp'=>array('app'=>'ciniki.wineproduction.main', 'args'=>array('wineproduction_id'=>'d.id')),
            'textlist'=>'',
            );
        foreach($wines as $wid => $wine) {
            //
            // Add emails to customer
            //
            $chunk['textlist'] .= sprintf("%40s %40s %20s\n", $wine['customer_name'], $wine['wine_name'], $wine['status_text']);
        }
        $chunks[] = $chunk;
    }
    else {
        $chunks[] = array('type'=>'message', 'content'=>'No wines were processed today.');
    }
    
    return array('stat'=>'ok', 'chunks'=>$chunks);
}
?>
