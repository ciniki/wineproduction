<?php
//
// Description
// ===========
// This method will return all the information about an wine production order.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the wine production order is attached to.
// order_id:          The ID of the wine production order to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_orderGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'order_id'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Wine Production Order'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Make sure this module is activated, and
    // check permission to run this function for this tenant
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.orderGet');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Load tenant settings
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'tenants', 'private', 'intlSettings');
    $rc = ciniki_tenants_intlSettings($ciniki, $args['tnid']);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $intl_timezone = $rc['settings']['intl-default-timezone'];
    $intl_currency_fmt = numfmt_create($rc['settings']['intl-default-locale'], NumberFormatter::CURRENCY);
    $intl_currency = $rc['settings']['intl-default-currency'];

    ciniki_core_loadMethod($ciniki, 'ciniki', 'users', 'private', 'dateFormat');
    $date_format = ciniki_users_dateFormat($ciniki, 'php');

    //
    // Return default for new Wine Production Order
    //
    if( $args['order_id'] == 0 ) {
        $order = array('id'=>0,
            'parent_id'=>'0',
            'customer_id'=>'',
            'invoice_id'=>'0',
            'invoice_number'=>'',
            'batch_letter'=>'',
            'product_id'=>'',
            'wine_type'=>'',
            'kit_length'=>'',
            'status'=>'10',
            'rack_colour'=>'',
            'filter_colour'=>'',
            'location'=>'',
            'flags'=>'0',
            'order_flags'=>'0',
            'order_date'=>'',
            'start_date'=>'',
            'tsg_reading'=>'',
            'transferring_date'=>'',
            'transfer_date'=>'',
            'sg_reading'=>'',
            'racking_date'=>'',
            'rack_date'=>'',
            'filtering_date'=>'',
            'filter_date'=>'',
            'bottling_flags'=>'0',
            'bottling_nocolour_flags'=>'0',
            'bottling_duration'=>'0',
            'bottling_date'=>'',
            'bottling_status'=>'',
            'bottling_notes'=>'',
            'bottle_date'=>'',
            'notes'=>'',
            'batch_code'=>'',
        );
    }

    //
    // Get the details for an existing Wine Production Order
    //
    else {
        $strsql = "SELECT ciniki_wineproductions.id, "
            . "ciniki_wineproductions.parent_id, "
            . "ciniki_wineproductions.customer_id, "
            . "ciniki_wineproductions.invoice_id, "
            . "ciniki_wineproductions.invoice_number, "
            . "ciniki_wineproductions.batch_letter, "
            . "ciniki_wineproductions.product_id, "
            . "ciniki_wineproductions.wine_type, "
            . "ciniki_wineproductions.kit_length, "
            . "ciniki_wineproductions.status, "
            . "ciniki_wineproductions.rack_colour, "
            . "ciniki_wineproductions.filter_colour, "
            . "ciniki_wineproductions.location, "
            . "ciniki_wineproductions.flags, "
            . "ciniki_wineproductions.order_flags, "
            . "ciniki_wineproductions.order_date, "
            . "ciniki_wineproductions.start_date, "
            . "ciniki_wineproductions.tsg_reading, "
            . "ciniki_wineproductions.transferring_date, "
            . "ciniki_wineproductions.transfer_date, "
            . "ciniki_wineproductions.sg_reading, "
            . "ciniki_wineproductions.racking_date, "
            . "ciniki_wineproductions.rack_date, "
            . "ciniki_wineproductions.filtering_date, "
            . "ciniki_wineproductions.filter_date, "
            . "ciniki_wineproductions.bottling_flags, "
            . "ciniki_wineproductions.bottling_nocolour_flags, "
            . "ciniki_wineproductions.bottling_duration, "
            . "ciniki_wineproductions.bottling_date, "
            . "ciniki_wineproductions.bottling_status, "
            . "ciniki_wineproductions.bottling_notes, "
            . "ciniki_wineproductions.bottle_date, "
            . "ciniki_wineproductions.notes, "
            . "ciniki_wineproductions.batch_code "
            . "FROM ciniki_wineproductions "
            . "WHERE ciniki_wineproductions.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproductions.id = '" . ciniki_core_dbQuote($ciniki, $args['order_id']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'orders', 'fname'=>'id', 
                'fields'=>array('parent_id', 'customer_id', 'invoice_id', 'invoice_number', 'batch_letter', 'product_id', 'wine_type', 'kit_length', 'status', 'rack_colour', 'filter_colour', 'location', 'flags', 'order_flags', 'order_date', 'start_date', 'tsg_reading', 'transferring_date', 'transfer_date', 'sg_reading', 'racking_date', 'rack_date', 'filtering_date', 'filter_date', 'bottling_flags', 'bottling_nocolour_flags', 'bottling_duration', 'bottling_date', 'bottling_status', 'bottling_notes', 'bottle_date', 'notes', 'batch_code'),
                'utctotz'=>array('order_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'start_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'transferring_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'transfer_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'racking_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'rack_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'filtering_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'filter_date'=>array('timezone'=>'UTC', 'format'=>$date_format),
                    'bottle_date'=>array('timezone'=>'UTC', 'format'=>$date_format)),                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.265', 'msg'=>'Wine Production Order not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['orders'][0]) ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.266', 'msg'=>'Unable to find Wine Production Order'));
        }
        $order = $rc['orders'][0];
    }

    return array('stat'=>'ok', 'order'=>$order);
}
?>
