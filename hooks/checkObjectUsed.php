<?php
//
// Description
// -----------
//
// Arguments
// ---------
//
// Returns
// -------
//
function ciniki_wineproduction_hooks_checkObjectUsed($ciniki, $tnid, $args) {

    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbCount');

    // Set the default to not used
    $used = 'no';
    $count = 0;
    $msg = '';

    if( $args['object'] == 'ciniki.customers.customer' ) {
        //
        // Check the wineproductions
        //
        $strsql = "SELECT 'items', COUNT(*) "
            . "FROM ciniki_wineproductions "
            . "WHERE customer_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.sapos', 'num');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['num']['items']) && $rc['num']['items'] > 0 ) {
            $used = 'yes';
            $count = $rc['num']['items'];
            $msg .= ($msg!=''?' ':'') . "There " . ($count==1?'is':'are') . " $count wine" . ($count==1?'':'s') . " in production for this customer.";
        }
    }

    if( $args['object'] == 'ciniki.products.product' ) {
        //
        // Check the wineproductions
        //
        $strsql = "SELECT 'items', COUNT(*) "
            . "FROM ciniki_wineproductions "
            . "WHERE product_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.sapos', 'num');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['num']['items']) && $rc['num']['items'] > 0 ) {
            $used = 'yes';
            $count = $rc['num']['items'];
            $msg .= ($msg!=''?' ':'') . "There " . ($count==1?'is':'are') . " $count wine" . ($count==1?'':'s') . " in production for this customer.";
        }
    }

    if( $args['object'] == 'ciniki.taxes.type' ) {
        //
        // Check the wineproductions
        //
        $strsql = "SELECT 'items', COUNT(*) "
            . "FROM ciniki_wineproduction_products "
            . "WHERE taxtype_id = '" . ciniki_core_dbQuote($ciniki, $args['object_id']) . "' "
            . "AND tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
            . "";
        $rc = ciniki_core_dbCount($ciniki, $strsql, 'ciniki.sapos', 'num');
        if( $rc['stat'] != 'ok' ) {
            return $rc;
        }
        if( isset($rc['num']['items']) && $rc['num']['items'] > 0 ) {
            $used = 'yes';
            $count = $rc['num']['items'];
            $msg .= ($msg!=''?' ':'') . "There " . ($count==1?'is':'are') . " $count product" . ($count==1?'':'s') . " for this tax type.";
        }
    }

    return array('stat'=>'ok', 'used'=>$used, 'count'=>$count, 'msg'=>$msg);
}
?>
