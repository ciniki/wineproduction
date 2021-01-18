<?php
//
// Description
// -----------
// This method searchs for a Product Tag Detailss for a tenant.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:        The ID of the tenant to get Product Tag Details for.
// start_needle:       The search string to search for.
// limit:              The maximum number of entries to return.
//
// Returns
// -------
//
function ciniki_wineproduction_productTagDetailSearch($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'start_needle'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Search String'),
        'limit'=>array('required'=>'no', 'blank'=>'yes', 'name'=>'Limit'),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    $args = $rc['args'];

    //
    // Check access to tnid as owner, or sys admin.
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'wineproduction', 'private', 'checkAccess');
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productTagDetailSearch');
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }

    //
    // Get the list of tags
    //
    $strsql = "SELECT ciniki_wineproduction_product_tagdetails.id, "
        . "ciniki_wineproduction_product_tagdetails.tag_type, "
        . "ciniki_wineproduction_product_tagdetails.permalink, "
        . "ciniki_wineproduction_product_tagdetails.name, "
        . "ciniki_wineproduction_product_tagdetails.sequence, "
        . "ciniki_wineproduction_product_tagdetails.display, "
        . "ciniki_wineproduction_product_tagdetails.flags "
        . "FROM ciniki_wineproduction_product_tagdetails "
        . "WHERE ciniki_wineproduction_product_tagdetails.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
        . "AND ("
            . "name LIKE '" . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
            . "OR name LIKE '% " . ciniki_core_dbQuote($ciniki, $args['start_needle']) . "%' "
        . ") "
        . "";
    if( isset($args['limit']) && is_numeric($args['limit']) && $args['limit'] > 0 ) {
        $strsql .= "LIMIT " . ciniki_core_dbQuote($ciniki, $args['limit']) . " ";
    } else {
        $strsql .= "LIMIT 25 ";
    }
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
    $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
        array('container'=>'tags', 'fname'=>'id', 
            'fields'=>array('id', 'tag_type', 'permalink', 'name', 'sequence', 'display', 'flags')),
        ));
    if( $rc['stat'] != 'ok' ) {
        return $rc;
    }
    if( isset($rc['tags']) ) {
        $tags = $rc['tags'];
        $tag_ids = array();
        foreach($tags as $iid => $tag) {
            $tag_ids[] = $tag['id'];
        }
    } else {
        $tags = array();
        $tag_ids = array();
    }

    return array('stat'=>'ok', 'tags'=>$tags, 'nplist'=>$tag_ids);
}
?>
