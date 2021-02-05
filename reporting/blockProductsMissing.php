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
// 
// Returns
// -------
//
function ciniki_wineproduction_reporting_blockProductsMissing(&$ciniki, $tnid, $args) {
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

    //
    // Get the list of products missing images, sysnopsis or descriptions.
    //
    $strsql = "SELECT products.id, "
        . "products.name, "
        . "products.status, "
        . "IF(products.primary_image_id=0, 'Missing', '') AS image, "
        . "products.synopsis, "
        . "products.description, "
        . "IF((products.webflags&0x01)=0x01, 'Visible', 'Hidden') AS visible "
        . "FROM ciniki_wineproduction_products AS products "
        . "WHERE products.tnid = '" . ciniki_core_dbQuote($ciniki, $tnid) . "' "
        . "AND products.status < 60 "
        . "AND ("
            . "products.primary_image_id = 0 "
            . "OR products.synopsis = '' "
            . "OR products.description = '' "
            . ") "
        . "ORDER BY name "
        . "";
    $rc = ciniki_core_dbHashQuery($ciniki, $strsql, 'ciniki.wineproduction', 'item');
    if( $rc['stat'] != 'ok' ) {
        return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.105', 'msg'=>'Unable to load item', 'err'=>$rc['err']));
    }
    $products = isset($rc['rows']) ? $rc['rows'] : array();
    
    if( count($products) > 0 ) {
        //
        // Create the report blocks
        //
        $chunk = array(
            'type'=>'table',
            'columns'=>array(
                array('label'=>'Product', 'pdfwidth'=>'30%', 'field'=>'name'),
                array('label'=>'Image', 'pdfwidth'=>'10%', 'field'=>'image'),
                array('label'=>'Synopsis', 'pdfwidth'=>'30%', 'field'=>'synopsis'),
                array('label'=>'Description', 'pdfwidth'=>'30%', 'field'=>'description'),
                ),
            'data'=>$products,
            'editApp'=>array('app'=>'ciniki.products.edit', 'args'=>array('product_id'=>'d.id')),
            'textlist'=>'',
            );
        foreach($products as $pid => $product) {
            //
            // Add emails to customer
            //
            $chunk['textlist'] .= sprintf("%40s \nImage: %40s\nSynopsis:\n%s\nDescription:\n%s\n", $product['name'], $product['image'], $product['synopsis'], $product['description']);
        }
        $chunks[] = $chunk;
    }
    else {
        $chunks[] = array('type'=>'message', 'content'=>'No active products missing information.');
    }
    
    return array('stat'=>'ok', 'chunks'=>$chunks);
}
?>
