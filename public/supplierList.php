<?php
//
// Description
// -----------
// This method will return the list of Suppliers for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Supplier for.
//
// Returns
// -------
//
function ciniki_wineproduction_supplierList($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.supplierList');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of suppliers
    //
    $strsql = "SELECT ciniki_wineproduction_suppliers.id, "
        . "ciniki_wineproduction_suppliers.name, "
        . "ciniki_wineproduction_suppliers.supplier_tnid "
        . "FROM ciniki_wineproduction_suppliers "
        . "WHERE ciniki_wineproduction_suppliers.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "";
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'suppliers', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'supplier_tnid')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['suppliers']) ) {
        $suppliers = $rc['suppliers'];
        $supplier_ids = array();
        foreach($suppliers as $iid => $supplier) {
            $supplier_ids[] = $supplier['id'];
        }
    } else {
        $suppliers = array();
        $supplier_ids = array();
    }

    return array('stat'=>'ok', 'suppliers'=>$suppliers, 'nplist'=>$supplier_ids);
}
?>
