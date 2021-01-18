<?php
//
// Description
// ===========
// This method will return all the information about an product tag details.
//
// Arguments
// ---------
// api_key:
// auth_token:
// tnid:         The ID of the tenant the product tag details is attached to.
// detail_id:          The ID of the product tag details to get the details for.
//
// Returns
// -------
//
function ciniki_wineproduction_productTagDetailGet($ciniki) {
    //
    // Find all the required and optional arguments
    //
    ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'prepareArgs');
    $rc = ciniki_core_prepareArgs($ciniki, 'no', array(
        'tnid'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tenant'),
        'tag_type'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Tag Type'),
        'permalink'=>array('required'=>'yes', 'blank'=>'no', 'name'=>'Permalink'),
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
    $rc = ciniki_wineproduction_checkAccess($ciniki, $args['tnid'], 'ciniki.wineproduction.productTagDetailGet');
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
    // Return default for new Product Tag Details
    //
    if( $args['permalink'] == '' ) {
        $detail = array('id'=>0,
            'tag_type'=>'',
            'permalink'=>'',
            'name'=>'',
            'sequence'=>'',
            'display'=>'',
            'primary_image_id'=>'0',
            'synopsis'=>'',
            'description'=>'',
            'flags'=>'',
        );
    }

    //
    // Get the details for an existing Product Tag Details
    //
    else {
        $strsql = "SELECT ciniki_wineproduction_product_tagdetails.id, "
            . "ciniki_wineproduction_product_tagdetails.tag_type, "
            . "ciniki_wineproduction_product_tagdetails.permalink, "
            . "ciniki_wineproduction_product_tagdetails.name, "
            . "ciniki_wineproduction_product_tagdetails.sequence, "
            . "ciniki_wineproduction_product_tagdetails.display, "
            . "ciniki_wineproduction_product_tagdetails.primary_image_id, "
            . "ciniki_wineproduction_product_tagdetails.synopsis, "
            . "ciniki_wineproduction_product_tagdetails.description, "
            . "ciniki_wineproduction_product_tagdetails.flags "
            . "FROM ciniki_wineproduction_product_tagdetails "
            . "WHERE ciniki_wineproduction_product_tagdetails.tnid = '" . ciniki_core_dbQuote($ciniki, $args['tnid']) . "' "
            . "AND ciniki_wineproduction_product_tagdetails.tag_type = '" . ciniki_core_dbQuote($ciniki, $args['tag_type']) . "' "
            . "AND ciniki_wineproduction_product_tagdetails.permalink = '" . ciniki_core_dbQuote($ciniki, $args['permalink']) . "' "
            . "";
        ciniki_core_loadMethod($ciniki, 'ciniki', 'core', 'private', 'dbHashQueryArrayTree');
        $rc = ciniki_core_dbHashQueryArrayTree($ciniki, $strsql, 'ciniki.wineproduction', array(
            array('container'=>'details', 'fname'=>'id', 
                'fields'=>array('id', 'tag_type', 'permalink', 'name', 'sequence', 'display', 'primary_image_id', 'synopsis', 'description', 'flags'),
                ),
            ));
        if( $rc['stat'] != 'ok' ) {
            return array('stat'=>'fail', 'err'=>array('code'=>'ciniki.wineproduction.151', 'msg'=>'Product Tag Details not found', 'err'=>$rc['err']));
        }
        if( !isset($rc['details'][0]) ) {
            $detail = array('id'=>0,
                'tag_type'=>'',
                'permalink'=>'',
                'name'=>'',
                'sequence'=>'',
                'display'=>'',
                'primary_image_id'=>'0',
                'synopsis'=>'',
                'description'=>'',
                'flags'=>'',
            );
        } else {
            $detail = $rc['details'][0];
        }
    }

    return array('stat'=>'ok', 'detail'=>$detail);
}
?>
