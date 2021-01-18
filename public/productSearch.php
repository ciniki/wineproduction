<?php
//
// Description
// -----------
// This method searchs for a Products for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Product for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_productSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        'status'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Status'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
        
    //
    // Load maps
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'maps');
    $rc = ciniki_wineproduction_maps($ciniki);
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $maps = $rc['maps'];

    //
    // Get the list of products
    //
    $strsql = "SELECT ciniki_wineproduction_products.id, "
        . "ciniki_wineproduction_products.name, "
        . "ciniki_wineproduction_products.permalink, "
        . "ciniki_wineproduction_products.ptype, "
        . "ciniki_wineproduction_products.flags, "
        . "IF((ciniki_wineproduction_products.flags&0x01)=0x01, 'Visible', '') AS visible, "
        . "ciniki_wineproduction_products.status, "
        . "ciniki_wineproduction_products.status AS status_text, "
        . "ciniki_wineproduction_products.start_date, "
        . "ciniki_wineproduction_products.end_date, "
        . "ciniki_wineproduction_products.supplier_id, "
        . "ciniki_wineproduction_products.supplier_item_number, "
        . "ciniki_wineproduction_products.wine_type, "
        . "ciniki_wineproduction_products.kit_length, "
        . "ciniki_wineproduction_products.msrp, "
        . "ciniki_wineproduction_products.cost, "
        . "ciniki_wineproduction_products.unit_amount, "
        . "ciniki_wineproduction_products.unit_discount_amount, "
        . "ciniki_wineproduction_products.unit_discount_percentage, "
        . "ciniki_wineproduction_products.taxtype_id, "
        . "ciniki_wineproduction_products.inventory_current_num, "
        . "ciniki_wineproduction_products.description "
        . "FROM ciniki_wineproduction_products "
        . "WHERE ciniki_wineproduction_products.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' ";
    if( isset($args['status']) && $args['status'] == 'active' ) {
        $strsql .= "AND ciniki_wineproduction_products.status < 60 ";
    }
    $strsql .= "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "ORDER BY status, name ";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'products', 'fname'=>'id', 
            'fields'=>array('id', 'name', 'permalink', 'ptype', 'flags', 'visible', 'status', 'status_text',
                'start_date', 'end_date', 
                'supplier_id', 'supplier_item_number', 'wine_type', 'kit_length', 
                'msrp', 'cost', 'unit_amount', 'unit_discount_amount', 'unit_discount_percentage', 'taxtype_id', 
                'inventory_current_num', 'description'),
            'maps'=>array('status_text'=>$maps['product']['status']),
            ),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['products']) ) {
        $products = $rc['products'];
        $product_ids = array();
        foreach($products as $iid => $product) {
            $product_ids[] = $product['id'];
        }
    } else {
        $products = array();
        $product_ids = array();
    }

    return array('stat'=>'ok', 'products'=>$products, 'nplist'=>$product_ids);
}
?>
