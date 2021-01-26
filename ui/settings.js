//
function ciniki_wineproduction_settings() {
    //
    // Panels
    //
    this.main = null;
    this.add = null;

    this.cb = null;
    this.toggleOptions = {'off':'Off', 'on':'On'};

//    this.init = function() {
        //
        // The main panel, which lists the options for production
        //
        this.main = new M.panel('Wine Production Settings',
            'ciniki_wineproduction_settings', 'main',
            'mc', 'medium', 'sectioned', 'ciniki.wineproduction.settings.main');
        this.main.sections = {
            'schedule':{'label':'Bottling Schedule', 'fields':{
                'bottling.schedule.start':{'label':'Start Time', 'size':'small', 'type':'text'},
                'bottling.schedule.end':{'label':'End Time', 'size':'small', 'type':'text'},
                'bottling.schedule.interval':{'label':'Interval', 'size':'small', 'hint':'15 or 30', 'type':'text'},
                'bottling.schedule.batchduration':{'label':'Batch Time', 'size':'small', 'hint':'30', 'type':'text'},
            }},
            'racking.autoschedule':{'label':'Racking Auto-Schedule', 'fields':{
                'racking.autoschedule.madeonsun':{'label':'Sun', 'type':'integer'},
                'racking.autoschedule.madeonmon':{'label':'Mon', 'type':'integer'},
                'racking.autoschedule.madeontue':{'label':'Tue', 'type':'integer'},
                'racking.autoschedule.madeonwed':{'label':'Wed', 'type':'integer'},
                'racking.autoschedule.madeonthu':{'label':'Thu', 'type':'integer'},
                'racking.autoschedule.madeonfri':{'label':'Fri', 'type':'integer'},
                'racking.autoschedule.madeonsat':{'label':'Sat', 'type':'integer'},
            }},
            'order.flags':{'label':'Order Options', 'type':'gridform', 'rows':12, 'cols':3,
                'header':['Flag Name','Background','Font'],
                'rowhistory':'yes', 'fields':[
                [   {'id':'order.flags.1.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.1.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.1.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.2.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.2.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.2.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.3.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.3.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.3.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.4.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.4.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.4.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.5.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.5.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.5.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.6.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.6.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.6.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.7.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.7.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.7.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.8.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.8.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.8.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.9.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.9.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.9.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.10.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.10.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.10.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.11.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.11.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.11.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'order.flags.12.name', 'label':'Name', 'type':'text'},
                    {'id':'order.flags.12.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'order.flags.12.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ]],
            },
            'bottling.flags':{'label':'Appointment Confirmation', 'type':'gridform', 'rows':9, 'cols':3,
                'header':['Flag Name','Background','Font'],
                'rowhistory':'yes', 'fields':[
                [   {'id':'bottling.flags.0.name', 'label':'Default', 'type':'text'},
                    {'id':'bottling.flags.0.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.0.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.1.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.1.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.1.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.2.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.2.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.2.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.3.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.3.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.3.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.4.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.4.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.4.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.5.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.5.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.5.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.6.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.6.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.6.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.7.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.7.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.7.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.flags.8.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.flags.8.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.flags.8.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ]],
            },
            'bottling.nocolour.flags':{'label':'Bottling Options', 'type':'gridform', 'rows':8, 'cols':1,
                'header':['Flag Name','Background','Font'],
                'rowhistory':'yes', 'fields':[
                [{'id':'bottling.nocolour.flags.1.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.2.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.3.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.4.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.5.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.6.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.7.name', 'label':'Name', 'type':'text'}],
                [{'id':'bottling.nocolour.flags.8.name', 'label':'Name', 'type':'text'}],
                ],
            },
            'bottling.status':{'label':'Bottling Status Options', 'type':'gridform', 'rows':4, 'cols':3,
                'header':['Text', 'Background','Font'],
                'labels':['Default', 'Reschedule', 'Rush', 'Ready'], 
                'rowhistory':'yes', 'fields':[
                [   
                    {'id':'bottling.status.0.name', 'label':'Default', 'type':'text'},
                    {'id':'bottling.status.0.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.status.0.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[ 
                    {'id':'bottling.status.1.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.status.1.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.status.1.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.status.2.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.status.2.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.status.2.fontcolour', 'label':'Font Colour', 'type':'colour'},
//              ],[
//                  {'id':'default', 'label':'Default', 'type':'label'},
//                  {'id':'bottling.status.3.name', 'label':'Name', 'type':'text'},
//                  {'id':'bottling.status.3.colour', 'label':'Background Colour', 'type':'colour'},
//                  {'id':'bottling.status.3.fontcolour', 'label':'Font Colour', 'type':'colour'},
//              ],[
//                  {'id':'default', 'label':'Default', 'type':'label'},
//                  {'id':'bottling.status.4.name', 'label':'Name', 'type':'text'},
//                  {'id':'bottling.status.4.colour', 'label':'Background Colour', 'type':'colour'},
//                  {'id':'bottling.status.4.fontcolour', 'label':'Font Colour', 'type':'colour'},
//              ],[
//                  {'id':'default', 'label':'', 'type':'label'},
//                  {'id':'bottling.status.5.name', 'label':'Name', 'type':'text'},
//                  {'id':'bottling.status.5.colour', 'label':'Background Colour', 'type':'colour'},
//                  {'id':'bottling.status.5.fontcolour', 'label':'Font Colour', 'type':'colour'},
//              ],[
//                  {'id':'default', 'label':'', 'type':'label'},
//                  {'id':'bottling.status.6.name', 'label':'Name', 'type':'text'},
//                  {'id':'bottling.status.6.colour', 'label':'Background Colour', 'type':'colour'},
//                  {'id':'bottling.status.6.fontcolour', 'label':'Font Colour', 'type':'colour'},
//              ],[
//                  {'id':'default', 'label':'', 'type':'label'},
//                  {'id':'bottling.status.7.name', 'label':'Name', 'type':'text'},
//                  {'id':'bottling.status.7.colour', 'label':'Background Colour', 'type':'colour'},
//                  {'id':'bottling.status.7.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ],[
                    {'id':'bottling.status.8.name', 'label':'Name', 'type':'text'},
                    {'id':'bottling.status.8.colour', 'label':'Background Colour', 'type':'colour'},
                    {'id':'bottling.status.8.fontcolour', 'label':'Font Colour', 'type':'colour'},
                ]],
            },
            'racking.autocolour':{'label':'Racking Auto-Colour', 'type':'gridform', 'rows':'3', 'cols':'7', 
                'active':function() { return M.modFlagSet('ciniki.wineproduction', 0x01); },
                'rowhistory':'yes',
                'sectioncolours':'racking.autocolour',
                'labels':['Week 1', 'Week 2', 'Week 3', 'Week 4'], 
                'compact_header':['S','M','T','W','T','F','S'],
                'header':['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                'fields':[
                    [
                    {'id':'racking.autocolour.week0sun','label':'Sunday','type':'colour'},
                    {'id':'racking.autocolour.week0mon','label':'Monday','type':'colour'},
                    {'id':'racking.autocolour.week0tue','label':'Tuesday','type':'colour'},
                    {'id':'racking.autocolour.week0wed','label':'Wednesday','type':'colour'},
                    {'id':'racking.autocolour.week0thu','label':'Thursday','type':'colour'},
                    {'id':'racking.autocolour.week0fri','label':'Friday','type':'colour'},
                    {'id':'racking.autocolour.week0sat','label':'Saturday','type':'colour'},
                    ],[
                    {'id':'racking.autocolour.week1sun','label':'Sunday','type':'colour'},
                    {'id':'racking.autocolour.week1mon','label':'Monday','type':'colour'},
                    {'id':'racking.autocolour.week1tue','label':'Tuesday','type':'colour'},
                    {'id':'racking.autocolour.week1wed','label':'Wednesday','type':'colour'},
                    {'id':'racking.autocolour.week1thu','label':'Thursday','type':'colour'},
                    {'id':'racking.autocolour.week1fri','label':'Friday','type':'colour'},
                    {'id':'racking.autocolour.week1sat','label':'Saturday','type':'colour'},
                    ],[
                    {'id':'racking.autocolour.week2sun','label':'Sunday','type':'colour'},
                    {'id':'racking.autocolour.week2mon','label':'Monday','type':'colour'},
                    {'id':'racking.autocolour.week2tue','label':'Tuesday','type':'colour'},
                    {'id':'racking.autocolour.week2wed','label':'Wednesday','type':'colour'},
                    {'id':'racking.autocolour.week2thu','label':'Thursday','type':'colour'},
                    {'id':'racking.autocolour.week2fri','label':'Friday','type':'colour'},
                    {'id':'racking.autocolour.week2sat','label':'Saturday','type':'colour'},
                    ],
//                  [
//                  {'id':'racking.autocolour.week3sun','label':'Sunday','type':'colour'},
//                  {'id':'racking.autocolour.week3mon','label':'Monday', 'type':'colour'},
//                  {'id':'racking.autocolour.week3tue','label':'Tuesday', 'type':'colour'},
//                  {'id':'racking.autocolour.week3wed','label':'Wednesday', 'type':'colour'},
//                  {'id':'racking.autocolour.week3thu','label':'Thursday', 'type':'colour'},
//                  {'id':'racking.autocolour.week3fri','label':'Friday', 'type':'colour'},
//                  {'id':'racking.autocolour.week3sat','label':'Saturday', 'type':'colour'},
//                  ]
                ]},
            'filtering.autocolour':{'label':'Filtering Auto-Colour', 'type':'gridform', 'rows':'7', 'cols':'7', 
                'active':function() { return M.modFlagSet('ciniki.wineproduction', 0x01); },
                'rowhistory':'yes',
                'labels':['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6', 'Week 7', 'Week 8', 'Week 9', 'Week 10', 'Week 11', 'Week 12', 'Week 13'], 
                'compact_header':['S','M','T','W','T','F','S'],
                'header':['Sun','Mon','Tue','Wed','Thu','Fri','Sat'],
                'fields':[
                    [
                    {'id':'filtering.autocolour.week0sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week0mon','label':'Monday','type':'colour'},
                    {'id':'filtering.autocolour.week0tue','label':'Tuesday','type':'colour'},
                    {'id':'filtering.autocolour.week0wed','label':'Wednesday','type':'colour'},
                    {'id':'filtering.autocolour.week0thu','label':'Thursday','type':'colour'},
                    {'id':'filtering.autocolour.week0fri','label':'Friday','type':'colour'},
                    {'id':'filtering.autocolour.week0sat','label':'Saturday','type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week1sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week1mon','label':'Monday','type':'colour'},
                    {'id':'filtering.autocolour.week1tue','label':'Tuesday','type':'colour'},
                    {'id':'filtering.autocolour.week1wed','label':'Wednesday','type':'colour'},
                    {'id':'filtering.autocolour.week1thu','label':'Thursday','type':'colour'},
                    {'id':'filtering.autocolour.week1fri','label':'Friday','type':'colour'},
                    {'id':'filtering.autocolour.week1sat','label':'Saturday','type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week2sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week2mon','label':'Monday','type':'colour'},
                    {'id':'filtering.autocolour.week2tue','label':'Tuesday','type':'colour'},
                    {'id':'filtering.autocolour.week2wed','label':'Wednesday','type':'colour'},
                    {'id':'filtering.autocolour.week2thu','label':'Thursday','type':'colour'},
                    {'id':'filtering.autocolour.week2fri','label':'Friday','type':'colour'},
                    {'id':'filtering.autocolour.week2sat','label':'Saturday','type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week3sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week3mon','label':'Monday', 'type':'colour'},
                    {'id':'filtering.autocolour.week3tue','label':'Tuesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week3wed','label':'Wednesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week3thu','label':'Thursday', 'type':'colour'},
                    {'id':'filtering.autocolour.week3fri','label':'Friday', 'type':'colour'},
                    {'id':'filtering.autocolour.week3sat','label':'Saturday', 'type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week4sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week4mon','label':'Monday', 'type':'colour'},
                    {'id':'filtering.autocolour.week4tue','label':'Tuesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week4wed','label':'Wednesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week4thu','label':'Thursday', 'type':'colour'},
                    {'id':'filtering.autocolour.week4fri','label':'Friday', 'type':'colour'},
                    {'id':'filtering.autocolour.week4sat','label':'Saturday', 'type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week5sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week5mon','label':'Monday', 'type':'colour'},
                    {'id':'filtering.autocolour.week5tue','label':'Tuesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week5wed','label':'Wednesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week5thu','label':'Thursday', 'type':'colour'},
                    {'id':'filtering.autocolour.week5fri','label':'Friday', 'type':'colour'},
                    {'id':'filtering.autocolour.week5sat','label':'Saturday', 'type':'colour'},
                    ],[
                    {'id':'filtering.autocolour.week6sun','label':'Sunday','type':'colour'},
                    {'id':'filtering.autocolour.week6mon','label':'Monday', 'type':'colour'},
                    {'id':'filtering.autocolour.week6tue','label':'Tuesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week6wed','label':'Wednesday', 'type':'colour'},
                    {'id':'filtering.autocolour.week6thu','label':'Thursday', 'type':'colour'},
                    {'id':'filtering.autocolour.week6fri','label':'Friday', 'type':'colour'},
                    {'id':'filtering.autocolour.week6sat','label':'Saturday', 'type':'colour'},
                    ],
                ]},
        };

        this.main.fieldValue = function(s, i, d) { 
            return this.data[i];
        };

        this.main.gridRowHistory = function(section, row) {
            // Get the fields from the row
            var fields = '';
            var comma = '';
            var labels = {};
            for(i in this.sections[section].fields[row]) {
                fields += comma + this.sections[section].fields[row][i].id;
                labels[this.sections[section].fields[row][i].id] = this.sections[section].fields[row][i].label;
                comma = ',';
            }
            var rsp = M.api.getJSON('ciniki.wineproduction.settingsHistory', 
                {'tnid':M.curTenantID, 'fields':fields});
            if( rsp.stat != 'ok' ) { 
                M.api.err(rsp);
                return false;
            }
            // 
            // Put human labels on the values
            //
            for(i in rsp.history) {
                var k = rsp.history[i].action.key;
                if( labels[k] != null ) {
                    rsp.history[i].action.label = labels[k] + ' - ';
                }
                // Setup a field_id attached to history so the interface knows which field to reset
                rsp.history[i].action.field_id = k;
            }

            return rsp;
        }

        //  
        // Callback for the field history
        //  
        this.main.fieldHistory = function(s, i) {
            var gridh = i.replace(/^(racking|filtering)\.autocolour_([0-3])$/, "$1,$2");

            if( gridh != i ) {
                var pieces = gridh.split(',');
                return this.gridRowHistory(pieces[0] + '.autocolour', pieces[1]);
            } else {
                var rsp = M.api.getJSON('ciniki.wineproduction.settingHistory', 
                    {'tnid':M.curTenantID, 'field':i});
            }
            if( rsp.stat != 'ok' ) { 
                M.api.err(rsp);
                return false;
            }   
            return rsp;
        };

        this.main.addButton('save', 'Save', 'M.ciniki_wineproduction_settings.saveSettings();');
        // this.main.addLeftButton('cancel', 'Cancel', 'M.ciniki_wineproduction_settings.showMain();');
        this.main.addClose('Cancel');
//    }

    this.purchaseorders = new M.panel('Purchase Order Settings',
        'ciniki_wineproduction_settings', 'purchaseorders',
        'mc', 'medium mediumaside', 'sectioned', 'ciniki.wineproduction.settings.purchaseorders');
    this.purchaseorders.sections = {
        'image':{'label':'Header Image', 'aside':'yes', 'fields':{
            'purchaseorder-header-image':{'label':'', 'type':'image_id', 'hidelabel':'yes', 'controls':'all', 'history':'no'},
            }},
        '_name_address':{'label':'Name & Address', 'fields':{
            'purchaseorder-name-address':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_bottom_msg':{'label':'Bottom Message', 'fields':{
            'purchaseorder-bottom-message':{'label':'', 'hidelabel':'yes', 'type':'textarea', 'size':'medium'},
            }},
        '_buttons':{'label':'', 'buttons':{
            'save':{'label':'Save', 'fn':'M.ciniki_wineproduction_settings.purchaseorders.save();'},
            }},
        };
    this.purchaseorders.fieldHistoryArgs = function(s, i) {
        return {'method':'ciniki.wineproduction.settingsHistory', 
            'args':{'tnid':M.curTenantID, 'setting':i}};
    }
    this.purchaseorders.fieldValue = function(s, i, d) {
        if( this.data[i] == null && d.default != null ) { return d.default; }
        return this.data[i];
    }
    this.purchaseorders.addDropImage = function(iid) {
        M.ciniki_wineproduction_settings.purchaseorders.setFieldValue('purchaseorder-header-image', iid);
        return true;
    }
    this.purchaseorders.deleteImage = function(fid) {
        this.setFieldValue(fid, 0);
        return true;
    }
    this.purchaseorders.open = function(cb) {
        M.api.getJSONCb('ciniki.wineproduction.settingsGet', {'tnid':M.curTenantID}, function(rsp) {
            if( rsp.stat != 'ok' ) {
                M.api.err(rsp);
                return false;
            }
            var p = M.ciniki_wineproduction_settings.purchaseorders;
            p.data = rsp.settings;
            p.refresh();
            p.show(cb);
        });
    }
    this.purchaseorders.save = function() {
        var c = this.serializeForm('no');
        if( c != '' ) {
            M.api.postJSONCb('ciniki.wineproduction.settingsUpdate', {'tnid':M.curTenantID}, 
                c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    }
                    M.ciniki_wineproduction_settings.purchaseorders.close();
                });
        } else {
            this.close();
        }
    }
    this.purchaseorders.addButton('save', 'Save', 'M.ciniki_wineproduction_settings.purchaseorders.save();');
    this.purchaseorders.addClose('Cancel');
    //
    // Arguments:
    // aG - The arguments to be parsed into args
    //
    this.start = function(cb, appPrefix, aG) {
        args = {};
        if( aG != null ) {
            args = eval(aG);
        }

        //
        // Create the app container if it doesn't exist, and clear it out
        // if it does exist.
        //
        var appContainer = M.createContainer(appPrefix, 'ciniki_wineproduction_settings', 'yes');
        if( appContainer == null ) {
            M.alert('App Error');
            return false;
        } 

        this.cb = cb;
        if( args.purchaseorders != null && args.purchaseorders == 'yes' ) {
            this.purchaseorders.open(cb);
        } else {
            this.showMain(cb);
        }
    }

    //
    // Grab the stats for the tenant from the database and present the list of orders.
    //
    this.showMain = function(cb) {
        var rsp = M.api.getJSONCb('ciniki.wineproduction.settingsGet', 
            {'tnid':M.curTenantID}, function(rsp) {
                if( rsp.stat != 'ok' ) {
                    M.api.err(rsp);
                    return false;
                }
                var p = M.ciniki_wineproduction_settings.main;
                p.data = rsp.settings;
                p.refresh();
                p.show(cb);
            });
    }

    this.saveSettings = function() {
        var c = this.main.serializeForm('no');
        if( c != '' ) {
            var rsp = M.api.postJSONCb('ciniki.wineproduction.settingsUpdate', 
                {'tnid':M.curTenantID}, c, function(rsp) {
                    if( rsp.stat != 'ok' ) {
                        M.api.err(rsp);
                        return false;
                    } 
                    M.ciniki_wineproduction_settings.main.close();
                });
        } else {
            M.ciniki_wineproduction_settings.main.close();
        }
    }
}
