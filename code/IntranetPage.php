<?php
class IntranetPage extends Page implements PermissionProvider {

	public static $icon = 'intranet/images/intranet-icon.png';	

	private static $db = array(
	);
	
	private static $has_one = array(
	);
	
	public function getCMSFields() {
		
		$fields = parent::getCMSFields();

		$fields->removeByName(array('Summaryline', 'FeaturedDoc', 'FeaturedImage'));
						
		return $fields;
		
	}
	
	// Add a new permission 
    function providePermissions(){
		
		return array(
			'INTRANET_ACCESS' => array(
				'name' => _t(
					'Intranet.ACCESSPERMISSION',
					'Access the intranet'
				),
				'category' => _t(
					'Intranet.INTRANETPERMISSIONS',
					'Intranet permissions'
				),
				'help' => _t(
					'Intranet.ACCESSPERMISSION_HELP',
					'Allow the user to access the intranet'
				),
				'sort' => 100
			),
			// @TODO later: implement EDIT permissions, for example to edit/add pages wiki-style
//			'INTRANET_EDIT' => array(
//				'name' => _t(
//					'Intranet.EDITPERMISSION',
//					'Edit the intranet (via front-end, not yet implemented)'
//				),
//				'category' => _t(
//					'Intranet.INTRANETPERMISSIONS',
//					'Intranet permissions'
//				),
//				'help' => _t(
//					'Intranet.EDITPERMISSION_HELP',
//					'Allow the user to add/edit intranet pages'
//				),
//				'sort' => 100
//			)
		);
//        return array(
//			'INTRANET_ACCESS' => 'Access the intranet',
//			//'FRONTUSERS_ADD_MEMBERS' => 'A new permission',
//		);
		
    }
	
	public function onBeforeWrite() {
		// @TODO: make this more robust (not based on ParentID = 0)
		if(! $this->ParentID){
			// fixate urlsegment of root intranet page to /intranet
			$this->URLSegment = 'intranet';
			// make sure we require at least a logged in member to view 
			// (subpages of any type can still set their own access permissions)
			$this->CanViewType = 'LoggedInUsers';
		}
		parent::onBeforeWrite(); 
	}
	
	// Add group for intranet users & add permissions to existing groups
	public function requireDefaultRecords() {
        parent::requireDefaultRecords();

        // Add default intranet group for use with memberprofiles if no other group exists
        $intranet_group = Group::get()->filter("Code","intranet-users");
        if(!$intranet_group->exists()) {
            $intranet_group = new Group();
            $intranet_group->Code = 'intranet-users';
            $intranet_group->Title = "Intranet Users";
            $intranet_group->Sort = 1;
            $intranet_group->write();
			// allow intranet access to this group
            Permission::grant($intranet_group->ID, 'INTRANET_ACCESS');
            DB::alteration_message('Intranet users group created', 'created');
        }
		
	}
	
}
class IntranetPage_Controller extends Page_Controller {

	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	private static $allowed_actions = array (
	);

	public function init() {
		parent::init();

		// Check if we are logged in as a user who can access(view) the intranet, 
		// or has page management rights in the CMS (to include editors & admins by default)
        if(!Permission::check("INTRANET_ACCESS")
				&& !Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure();
		}
		
		
	}
	
	//
	
}