<?php
//
// Description
// -----------
// This function will return the array of rulesets available to the wineproduction module.
//
// Info
// ----
// Status: beta
//
// Arguments
// ---------
// ciniki:
// 
// Returns
// -------
//
function ciniki_wineproduction_getRulesets($ciniki) {

    //
    // business_group rules are OR'd together with customers rules
    //
    // - customers - 'any', (any customers of the business)
    // - customers - 'self', (the session user_id must be the same as requested user_id)
    //
    // *note* A function can only be allowed to customers, if there is no business_group rule.
    //

    return array(
        //
        // The default for nothing selected is to have access restricted to nobody
        //
        ''=>array('label'=>'Nobody',
            'description'=>'Nobody has access, no even owners.',
            'details'=>array(
                'owners'=>'no access.',
                'employees'=>'no access.',
                'customers'=>'no access.'
                ),
            'default'=>array(),
            'methods'=>array()
            ),

        //
        // For all methods, you must be in the group Bug Tracker.  Only need to specify
        // the default permissions, will automatically be applied to all methods.
        //
        'employees'=>array('label'=>'Employees', 
            'description'=>'This permission setting allows all owners and employees of the business to manage wineproduction',
            'details'=>array(
                'owners'=>'all tasks',
                'employees'=>'all tasks',
                'customers'=>'no access.'
                ),
            'default'=>array('permission_groups'=>array('ciniki.owners', 'ciniki.employees')),
            'methods'=>array()
            ),

        //
        // For all methods, you must be in the group Bug Tracker.  Only need to specify
        // the default permissions, will automatically be applied to all methods.
        //
        'group_restricted'=>array('label'=>'Group Restricted', 
            'description'=>'This permission setting allows only those employees in the Wine Production group',
            'details'=>array(
                'owners'=>'all tasks',
                'employees'=>'all tasks',
                'customers'=>'no access.'
                ),
            'default'=>array('permission_groups'=>array('ciniki.wineproduction')),
            'methods'=>array()
            ),
    );
}
?>
