<?php
/**
 * Extension that protects intranet pages from accidentially being publicly accessible
 * Quite an 'expensive' protection, databasequery wise so only apply if needed
 *
 * @package intranet
 */
class Intranet_Controller_Extension extends Extension {

    public function onBeforeInit(){
		// If this page descends from IntranetPage (Hierarchy, not class),
		// check if we are logged in as a user who can access(view) the intranet, 
		// or has page management rights in the CMS (to include editors & admins by default)
		// 
		// We do this only on Controller::init, cause checking all parents from canView/Edit/etc 
		// makes the backend unusable
        if( $this->owner->belongsToIntranet()
				&& !Permission::check("INTRANET_ACCESS") 
				&& !Permission::check("CMS_ACCESS_CMSMain")) {
			Security::permissionFailure();
		}
	}
	
	/**
	 * Check if this page or any of its parents are of class/subclass IntranetPage
	 * 
	 * @return boolean
	 */
	public function belongsToIntranet(){
		// are we an IntranetPage?
		if(is_a( $this->owner, 'IntranetPage' )){ return true; }
		// else, check parents
		$parent = $this->owner->Parent();
		while($parent && $parent->exists()) {
			if(is_a( $parent, 'IntranetPage' )){ return true; }
		}
		// we've reached the root page without finding an intranetpage
		return false;
	}

}
