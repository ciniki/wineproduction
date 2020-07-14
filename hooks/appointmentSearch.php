<?php
//
// Description
// -----------
// This function will return the appointment for a tenant
//
//
// Arguments
// ---------
// ciniki:
// tnid:         The ID of the tenant to get the details for.
// args:                The args passed through the API.
//
// Returns
// -------
//  <appointments>
//      <appointment calendar="Appointments" customer_name="" invoice_number="" wine_name="" />
//  </appointments>
//
function ciniki_wineproduction_hooks_appointmentSearch($ciniki, $tnid, $args) {
    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'tnid', $tnid, 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $settings = $rc['settings'];

    if( !isset($args['start_needle']) || $args['start_needle'] == '' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.4', 'msg'=>'No search specified'));
    }

    //
    // Load timezone info
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');

    $strsql = "SELECT ciniki_wineproductions.id AS order_id, "
        . "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
        . "ciniki_customers.display_name AS customer_name, "
        . "invoice_number, "
        . "ciniki_products.name AS wine_name, "
        //. "CONCAT_WS(' - ', CONCAT_WS(' ', first, last), IF(COUNT(invoice_number)>1, CONCAT('(',COUNT(invoice_number),')'), NULL), invoice_number, ciniki_products.name) AS subject, "
        . "bottling_date AS start_date, "
        . "bottling_date AS start_ts, "
        . "bottling_date AS date, "
        . "bottling_date AS time, "
        . "bottling_date AS 12hour, "
//      . "UNIX_TIMESTAMP(bottling_date) AS start_ts, "
//      . "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') AS start_date, "
//      . "DATE_FORMAT(bottling_date, '%Y-%m-%d') AS date, "
//      . "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
//      . "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
        . "'ciniki.wineproduction' AS module, "
        . "ciniki_wineproductions.bottling_flags, "
        . "ciniki_wineproductions.bottling_nocolour_flags, "
        . "ciniki_wineproductions.bottling_status, "
        . "ciniki_wineproductions.bottling_notes, "
        . "ciniki_wineproductions.status, "
        . "bottling_duration AS duration, '#aaddff' AS colour, 'ciniki.appointments' AS 'module' "
        . "FROM ciniki_wineproductions "
        . "JOIN ciniki_products ON (ciniki_wineproductions.product_id = ciniki_products.id "
            . "AND ciniki_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "') "
        . "LEFT JOIN ciniki_customers ON (ciniki_wineproductions.customer_id = ciniki_customers.id "
            . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "') "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
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
            . "OR ciniki_customers.first LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.last LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.last LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.company LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_customers.company LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_products.name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR ciniki_products.name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' ) "
            . "OR DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') LIKE '%" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
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
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']*5) . " "; // is_numeric verified
    }

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
            'fields'=>array('id', 'module', 'start_ts', 'start_date', 'date', 'time', '12hour', 'duration', 'wine_name'),
            'utctotz'=>array('start_ts'=>array('timezone'=>$intl_timezone, 'format'=>'U'),
                'start_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                'date'=>array('timezone'=>$intl_timezone, 'format'=>'Y-m-d'),
                'time'=>array('timezone'=>$intl_timezone, 'format'=>'H:i'),
                '12hour'=>array('timezone'=>$intl_timezone, 'format'=>'g:i')),
            'sums'=>array('duration'), 'countlists'=>array('wine_name'), 'limit'=>$args['limit']),
        array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 'fields'=>array('order_id', 'customer_name', 'invoice_number', 'wine_name', 'duration', 'status', 'bottling_flags', 'bottling_nocolour_flags', 'bottling_status', 'bottling_notes')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['appointments']) ) {
        $appointments = $rc['appointments'];
    } else {
        $appointments = array();
    }

    //
    // Create subject, and remote orders to flatten appointments
    //
    foreach($appointments as $anum => $appointment) {
        if( $appointment['time'] == '00:00' ) {
            $appointments[$anum]['allday'] = 'yes';
        }
        $appointments[$anum]['subject'] = $appointment['orders'][0]['customer_name'];
        if( count($appointment['orders']) > 1 ) {
            $appointments[$anum]['subject'] .= ' (' . count($appointment['orders']) . ')';
        }
        $appointments[$anum]['subject'] .= ' - ' . preg_replace('/-\s*[A-Z]/', '', $appointment['orders'][0]['invoice_number']);
        $appointments[$anum]['subject'] .= ' - ' . $appointment['wine_name'];
        $min_status = 255;
        $min_flags = 255;
        $min_order_status = 99;
        $bottling_notes = '';
        $appointments[$anum]['secondary_text'] = '';
        $appointments[$anum]['secondary_colour'] = '#ffffff';
        $scomma = '';
        $bottling_nocolour_flags = 0;
        foreach($appointments[$anum]['orders'] as $onum => $order) {
            if( $order['bottling_status'] < $min_status ) {
                $min_status = $order['bottling_status'];
            }
            if( $order['bottling_flags'] < $min_flags ) {
                $min_flags = $order['bottling_flags'];
            }
            if( $order['status'] < $min_order_status ) {
                $min_order_status = $order['status'];
            }
            if( $bottling_notes == '' ) {
                $bottling_notes = $order['bottling_notes'];
            }
            $bottling_nocolour_flags |= $order['bottling_nocolour_flags'];
        }
        for($i=1;$i<=8;$i++) {
            if( isset($settings["bottling.nocolour.flags.$i.name"]) && $settings["bottling.nocolour.flags.$i.name"] != '' 
                && ($order['bottling_nocolour_flags']&pow(2, $i-1)) == pow(2,$i-1) ) {
                $appointments[$anum]['secondary_colour_text'] = 'm';
                $appointments[$anum]['secondary_text'] .= $scomma . $settings["bottling.nocolour.flags.$i.name"];
                $scomma = ', ';
            }
        }
        
        if( $min_status < 255 && isset($settings['bottling.status.' . (log($min_status, 2)+1) . '.name'])) {
            $appointments[$anum]['secondary_text'] .= $scomma . $settings['bottling.status.' . (log($min_status, 2)+1) . '.name'];
            $appointments[$anum]['colour'] = $settings['bottling.status.' . (log($min_status, 2)+1) . '.colour'];
            $scomma = ', ';
        }
        if( $min_flags < 255 && isset($settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name']) ) {
            $appointments[$anum]['secondary_text'] .= $scomma . $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name'];
            $appointments[$anum]['secondary_colour'] = $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.colour'];
        }
        if( $min_order_status == 60 ) {
            $appointments[$anum]['colour'] = '#e4d8f9';
        }
        if( $bottling_notes != '' ) {
            $appointments[$anum]['secondary_text'] .= $scomma . $bottling_notes;
        }
        
        unset($appointments[$anum]['wine_name']);
        unset($appointments[$anum]['orders']);
        unset($appointments[$anum]['bottling_status']);
        $appointments[$anum]['calendar'] = 'Bottling Schedule';
        $appointments[$anum]['module'] = 'ciniki.wineproduction';
    }

    return array('stat'=>'ok', 'appointments'=>$appointments);;
}
?>
