<?php
//
// Description
// -----------
// This function will return a bottling schedule for a day
//
//
// Arguments
// ---------
// ciniki:
// tnid:         The ID of the tenant to get the appointments for.
// args:                The args passed from the API.
//
// Returns
// -------
//  <appointments>
//      <appointment module="ciniki.wineproduction" customer_name="" invoice_number="" wine_name="" />
//  </appointments>
//
function ciniki_wineproduction_hooks_appointments($ciniki, $tnid, $args) {
    //
    // Grab the settings for the tenant from the database
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbDetailsQuery');
    $rc =  ciniki_core_dbDetailsQuery($ciniki, 'ciniki_wineproduction_settings', 'tnid', $tnid, 'ciniki.wineproduction', 'settings', '');
    if( $rc['stat'] != 'ok' ) { 
        return $rc;
    }   
    $settings = $rc['settings'];

//  //
//  // FIXME: Add timezone information
//  //
//  date_default_timezone_set('America/Toronto');
    //
    // Load timezone info
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $tnid);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];

    if( isset($args['date']) && ($args['date'] == '' || $args['date'] == 'today') ) {
        $args['date'] = strftime("%Y-%m-%d");
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'datetimeFormat');
    $datetime_format = ciniki_users_datetimeFormat($ciniki, 'php');


    $strsql = "SELECT ciniki_wineproductions.id AS order_id, "
        . "CONCAT_WS('-', UNIX_TIMESTAMP(ciniki_wineproductions.bottling_date), ciniki_wineproductions.customer_id) AS id, "
        . "ciniki_customers.display_name AS customer_name, "
        . "invoice_number, "
        . "ciniki_wineproduction_products.name AS wine_name, "
        . "bottling_date AS start_date, "
        . "bottling_date AS start_ts, "
        . "bottling_date AS date, "
        . "bottling_date AS time, "
        . "bottling_date AS 12hour, "
//      . "DATE_FORMAT(bottling_date, '" . ciniki_core_dbQuote($ciniki, $datetime_format) . "') AS start_date, "
//      . "UNIX_TIMESTAMP(bottling_date) AS start_ts, "
//      . "DATE_FORMAT(bottling_date, '%Y-%m-%d') AS date, "
//      . "DATE_FORMAT(bottling_date, '%H:%i') AS time, "
//      . "DATE_FORMAT(bottling_date, '%l:%i') AS 12hour, "
        . "bottling_duration AS duration, "
        . "ciniki_wineproductions.bottling_flags, "
        . "ciniki_wineproductions.bottling_nocolour_flags, "
        . "ciniki_wineproductions.bottling_status, "
        . "ciniki_wineproductions.bottling_notes, "
        . "ciniki_wineproductions.status "
        . "FROM ciniki_wineproductions "
        . "JOIN ciniki_wineproduction_products ON ("
            . "ciniki_wineproductions.product_id = ciniki_wineproduction_products.id "
            . "AND ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "LEFT JOIN ciniki_customers ON ("
            . "ciniki_wineproductions.customer_id = ciniki_customers.id "
            . "AND ciniki_customers.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . ") "
        . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND ciniki_wineproductions.status < 100 "
        . "";
    if( isset($args['customer_id']) && $args['customer_id'] != '' ) {
        $strsql .= "AND ciniki_wineproductions.customer_id = '" . ciniki_core_dbQuote($ciniki, $args['customer_id']) . "' ";
    } elseif( isset($args['date']) && $args['date'] != '' ) {
        $strsql .= "AND DATE(bottling_date) = '" . ciniki_core_dbQuote($ciniki, $args['date']) . "' ";
    } elseif( isset($args['start_date']) && $args['start_date'] != '' 
        &&isset($args['end_date']) && $args['end_date'] != '' ) {
        $strsql .= "AND DATE(bottling_date) >= '" . ciniki_core_dbQuote($ciniki, $args['start_date']) . "' "
            . "AND DATE(bottling_date) <= '" . ciniki_core_dbQuote($ciniki, $args['end_date']) . "' ";
    } else {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.5', 'msg'=>'No constraints provided'));
    }

    if( isset($args['status']) && $args['status'] == 'unbottled' ) {
        $strsql .= "AND ciniki_wineproductions.status < 60 ";
    }

    if( isset($args['customer_id']) && $args['customer_id'] != '' ) {
        $strsql .= "ORDER BY ciniki_wineproductions.bottling_date DESC, ciniki_wineproductions.customer_id, wine_name, id ";
    } else {
        $strsql .= "ORDER BY ciniki_wineproductions.bottling_date, ciniki_wineproductions.customer_id, wine_name, id ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'appointments', 'fname'=>'id', 'name'=>'appointment', 
            'fields'=>array('id', 'start_date', 'start_ts', 'date', 'time', '12hour', 'duration', 'wine_name'),
            'utctotz'=>array('start_ts'=>array('timezone'=>$intl_timezone, 'format'=>'U'),
                'start_date'=>array('timezone'=>$intl_timezone, 'format'=>$datetime_format),
                'date'=>array('timezone'=>$intl_timezone, 'format'=>'Y-m-d'),
                'time'=>array('timezone'=>$intl_timezone, 'format'=>'H:i'),
                '12hour'=>array('timezone'=>$intl_timezone, 'format'=>'g:i')),
            'sums'=>array('duration'), 
//            'countlists'=>array('wine_name'),
            ),
        array('container'=>'orders', 'fname'=>'order_id', 'name'=>'order', 
            'fields'=>array('order_id', 'customer_name', 'invoice_number', 'wine_name', 'duration', 
                'status', 'bottling_flags', 'bottling_nocolour_flags', 'bottling_status', 'bottling_notes'),
            ),
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
    foreach($appointments as $aid => $appointment) {
        if( $appointment['time'] == '00:00' ) {
            $appointments[$aid]['allday'] = 'yes';
        }
        $appointments[$aid]['subject'] = $appointment['orders'][0]['customer_name'];
        if( count($appointment['orders']) > 1 ) {
            $appointments[$aid]['subject'] .= ' (' . count($appointment['orders']) . ')';
        }
//        $appointments[$aid]['subject'] .= ' - ' . preg_replace('/-\s*[A-Z]/', '', $appointment['orders'][0]['invoice_number']);
//        $appointments[$aid]['subject'] .= ' - ' . $appointment['wine_name'];
        $min_status = 255;
        $min_flags = 255;
        $min_order_status = 99;
        $bottling_notes = '';
        $appointments[$aid]['abbr_secondary_text'] = '';
        $appointments[$aid]['secondary_text'] = '';
        $appointments[$aid]['secondary_colour'] = '#ffffff';
        $appointments[$aid]['secondary_colour_text'] = '';
        $scomma = '';
        $bottling_nocolour_flags = 0;
        foreach($appointments[$aid]['orders'] as $onum => $order) {
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
            if( $order['bottling_nocolour_flags'] > 0 ) {
                $appointments[$aid]['secondary_colour_text'] = '*';
            }
            $bottling_nocolour_flags |= $order['bottling_nocolour_flags'];
            if( $onum == 0 ) {
                $appointments[$aid]['subject'] .= ' - ' . preg_replace('/-\s*[A-Z]/', '', $order['invoice_number']);
                $appointments[$aid]['subject'] .= ' - ' . $order['wine_name'];
            } elseif( $order['invoice_number'] != $appointments[$aid]['orders'][0]['invoice_number'] ) {
                $appointments[$aid]['subject'] .= ', ' . preg_replace('/-\s*[A-Z]/', '', $order['invoice_number']);
                $appointments[$aid]['subject'] .= ' - ' . $order['wine_name'];
            } else {
                $appointments[$aid]['subject'] .= ', ' . $order['wine_name'];
            }
        }
        for($i=1;$i<=8;$i++) {
            if( isset($settings["bottling.nocolour.flags.$i.name"]) && $settings["bottling.nocolour.flags.$i.name"] != '' 
                && ($bottling_nocolour_flags&pow(2, $i-1)) == pow(2,$i-1) ) {
                $appointments[$aid]['secondary_colour_text'] = 'm';
                $appointments[$aid]['secondary_text'] .= $scomma . $settings["bottling.nocolour.flags.$i.name"];
                $scomma = ', ';
            }
        }
        
        if( $min_status < 255 && isset($settings['bottling.status.' . (log($min_status, 2)+1) . '.name'])) {
            $appointments[$aid]['secondary_text'] .= $scomma . $settings['bottling.status.' . (log($min_status, 2)+1) . '.name'];
            $appointments[$aid]['colour'] = $settings['bottling.status.' . (log($min_status, 2)+1) . '.colour'];
            $scomma = ', ';
        }
        if( $min_flags < 255 && isset($settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name']) ) {
            $appointments[$aid]['secondary_text'] .= $scomma . $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.name'];
            $appointments[$aid]['secondary_colour'] = $settings['bottling.flags.' . (log($min_flags, 2)+1) . '.colour'];
        }
        if( $min_order_status == 60 ) {
            $appointments[$aid]['colour'] = '#e4d8f9';
        }
        if( $bottling_notes != '' ) {
            $appointments[$aid]['secondary_text'] .= $scomma . $bottling_notes;
        }
//      if( isset($appointments[$aid]['appointment']['bottling_status']) && $appointments[$aid]['appointment']['bottling_status'] != '' ) {
//          $appointments[$aid]['appointment']['subject'] .= ' ' . $appointment['appointment']['bottling_status'];
//      }

        //
        // Format items for uiCustomersData
        //
        if( $appointments[$aid]['start_ts'] == 0 ) {
            $appointments[$aid]['start_date_display'] = 'unscheduled';
            $appointments[$aid]['start_time_display'] = '';
        } elseif( isset($appointments[$aid]['allday']) && $appointments[$aid]['allday'] == 'yes' ) {
            $appointments[$aid]['start_date_display'] = preg_replace('/ [0-9]+:.*$/', '', $appointments[$aid]['start_date']);
            $appointments[$aid]['start_time_display'] = '';
        } else {
            $appointments[$aid]['start_date_display'] = preg_replace('/ [0-9]+:.*$/', '', $appointments[$aid]['start_date']);
            $appointments[$aid]['start_time_display'] = preg_replace('/.*[0-9]{4} /', '', $appointments[$aid]['start_date']);
        }
        if( !isset($appointments[$aid]['colour']) ) {
            $appointments[$aid]['colour'] = '#77ddff';
        }

        unset($appointments[$aid]['wine_name']);
        unset($appointments[$aid]['orders']);
        unset($appointments[$aid]['bottling_status']);
        $appointments[$aid]['calendar'] = 'Bottling Schedule';
        $appointments[$aid]['module'] = 'ciniki.wineproduction';
    }

    return array('stat'=>'ok', 'appointments'=>$appointments);;
}
?>
