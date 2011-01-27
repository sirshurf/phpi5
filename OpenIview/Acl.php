<?php
/**
 * Standart ACL class, resieves parameters from INI file, acl node
 * 
 * @author sirshurf
 *
 */
class Openiview_Acl extends Zend_Acl {
	
	/**
	 * 
	 * @var Openiview_Acl
	 */
	public static $objIntance;
	
	public function __construct() {
		
		// Add roles to the Acl element
		$roles = Zend_Registry::get ( 'config' )->acl->roles->toArray ();
		
		foreach ( $roles as $key => $arrRole ) {
			
			if (! empty ( $arrRole ['parent'] )) {
				$this->addRole ( new Zend_Acl_Role ( $arrRole ['name'] ), $arrRole ['parent'] );
			} else {
				$this->addRole ( new Zend_Acl_Role ( $arrRole ['name'] ) );
			}
		}
		
		// Add roles to the Acl element
		$this->deny ();
		
		$arrResource = Zend_Registry::get ( 'config' )->acl->resource->toArray ();
		foreach ( $arrResource as $strResourceName => $arrResourceOptions ) {
			// Register new resource
			$this->add ( new Zend_Acl_Resource ( $strResourceName ) );
			
			// check if it's needed, we have a Deny All 7 rows up?
			$this->deny ( null, $strResourceName, null );
			
			foreach ( $arrResourceOptions as $strOptions => $arrRoles ) {
				
				$this->allow ( (('all' == $arrRoles [0]) ? null : $arrRoles), $strResourceName, (('all' == $strOptions) ? null : $strOptions) );
			
			}
		
		}
		
		Openiview_Acl::$objIntance = $this;
	}
	
	/**
	 * Checking that this user has role that can enter here
	 * 
	 * @param $resource
	 * @param $privilege
	 * @return bool
	 */
	public function checkPermissions($resource = null, $privilege = null, $intLabId = null, $intProjectId = null, $boolStricked = false) {
		if (Zend_Auth::getInstance ()->hasIdentity ()) {
			$session_role = new Zend_Session_Namespace ( 'role' );
			//			Zend_Debug::dump($session_role->role);
			if (! empty ( $session_role->role )) {
				$roles = $session_role->role;
			} else {
				$roles [] = ROLE_GUEST;
			}
		} else {
			$roles [] = ROLE_GUEST;
		}
		
		if ($resource || $privilege) {
			foreach ( $roles as $role ) {
				// determine using helper role and page resource/privilege				
				if ($this->isAllowed ( $role, $resource, $privilege )) {
					// The user Role is allowed, not check if he has SUB permissions
					if (! empty ( $session_role->role_by_role )) {
						if (isset ( $session_role->role_by_role [$role] )) {
							
							// Check if this user need to be checked for project
							if (Zend_Registry::get ( 'config' )->roles->$role->isProjectRelated) {
								$arrProjects = $session_role->projects;
								
								if (! empty ( $intProjectId )) {
									$boolInArray = in_array ( $intProjectId, $session_role->projects );
								} else {
									$boolInArray = false;
								}
							} else {
								$boolInArray = false;
							}
							
							if (! empty ( $intProjectId ) && ($boolInArray || $boolStricked)) {
								// Project permissions are requested.
								// Since teh user would have this permissions ONLY if/when he is a super of this lab,
								// Check for array is enouth
								if (! empty ( $boolInArray )) {
									return true;
								}
							} elseif (! empty ( $intLabId )) {
								// Lab Permissions are requested.
								if (self::isLabRole ( $role, $intLabId )) {
									return true;
								}
							} else {
								return true;
							}
						} else {
							return true;
						}
					} else {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Checks if the user has one of the Global permissions
	 * @return bool
	 */
	public static function hasGlobalPermission() {
		$session_role = new Zend_Session_Namespace ( 'role' );
		$arrRoles = $session_role->role;
		
		if (in_array ( ROLE_SYSADMIN, $arrRoles ) || in_array ( ROLE_MAINSUPER, $arrRoles ) || in_array ( ROLE_MAINACADSUP, $arrRoles )) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Checkes if the user IS student
	 * @return bool
	 */
	public static function isStudent() {
		$session_role = new Zend_Session_Namespace ( 'role' );
		$arrRoles = $session_role->role;
		
		if (in_array ( ROLE_STUDENT, $arrRoles )) {
			return true;
		}
		return false;
	}
	
	/**
	 * Check if the requested role is in the user's lab list
	 * 
	 * @param string $strRole
	 * @param int $intLabId
	 */
	public static function isLabRole($strRole, $intLabId) {
		
		$session_role = new Zend_Session_Namespace ( 'role' );
		$arrRoles = $session_role->role;
		$arrRolesByRole = $session_role->role_by_role;
		
		if (! empty ( $arrRolesByRole [$strRole] ) && in_array ( $intLabId, $arrRolesByRole [$strRole] )) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Functions checks that a requiered string is in a set of permissions, checks one only
	 * The result array is passed by reference
	 * 
	 * @param $strGroupDn
	 * @param &$result
	 * @param &$resultByLab
	 * @param &$resultByRole
	 */
	public static function getPermissions($strGroupDn, array &$result, array &$resultByLab, array &$resultByRole, array &$resultProjectByLab) {
		
		$arrDn = Openiview_Acl::prepareLabRolesList ();
		
		$objZendConfig = Zend_Registry::get ( "config" );
		
		// Check that the group is in designated OU
		if ($objZendConfig->dnformat->check == true && stripos ( $strGroupDn, $objZendConfig->dnformat->sufix ) === false) {
			return;
		}
		// Check global roles
		foreach ( $objZendConfig->dn as $strDnKey => $objDnValue ) {
			$strDn = Labadmin_Models_Users::getDn ( $objDnValue->cn );
			if (0 == strcasecmp ( $strGroupDn, $strDn )) {
				$result [] = $objDnValue->role;
				return;
			}
		}
		
		if (! empty ( $arrDn [$strGroupDn] )) {
			$result [] = $arrDn [$strGroupDn] ['role'];
			$resultByLab [$arrDn [$strGroupDn] ['lab']] [] = $arrDn [$strGroupDn] ['role'];
			$resultByRole [$arrDn [$strGroupDn] ['role']] [] = $arrDn [$strGroupDn] ['lab'];
			if (ROLE_SUPER == $arrDn [$strGroupDn] ['role']) {
				if (! isset ( $resultProjectByLab [$arrDn [$strGroupDn] ['lab']] )) {
					$resultProjectByLab [$arrDn [$strGroupDn] ['lab']] = array ();
				}
				$resultProjectByLab [$arrDn [$strGroupDn] ['lab']] = array_merge ( $resultProjectByLab [$arrDn [$strGroupDn] ['lab']], Labadmin_Models_Projects::getProjectsByUser ( $arrDn [$strGroupDn] ['lab'] ) );
			}
		}
		$result = array_unique ( $result );
		return;
	
	}
	
	public static function prepareLabRolesList() {
		// Get list of DN's for comparison
		$objLabs = new Labadmin_Models_Labs ();
		$objLabsRowSet = $objLabs->fetchAll ();
		
		$arrDn = array ();
		
		foreach ( $objLabsRowSet as $objLabsRow ) {
			// Get only active labs
			if (! $objLabsRow->is_active) {
				continue;
			}
			$strDn = Labadmin_Models_Users::getDn ( $objLabsRow->dn_eng );
			$arrDn [$strDn] ['role'] = ROLE_LABENG;
			$arrDn [$strDn] ['lab'] = $objLabsRow->id_labs;
			
			$strDn = Labadmin_Models_Users::getDn ( $objLabsRow->dn_cluster );
			$arrDn [$strDn] ['role'] = ROLE_LABCLUSTERCOORD;
			$arrDn [$strDn] ['lab'] = $objLabsRow->id_labs;
			
			$strDn = Labadmin_Models_Users::getDn ( $objLabsRow->dn_academic );
			$arrDn [$strDn] ['role'] = ROLE_LABACADSUP;
			$arrDn [$strDn] ['lab'] = $objLabsRow->id_labs;
			
			$strDn = Labadmin_Models_Users::getDn ( $objLabsRow->dn_super );
			$arrDn [$strDn] ['role'] = ROLE_SUPER;
			$arrDn [$strDn] ['lab'] = $objLabsRow->id_labs;
		
		}
		return $arrDn;
	
	}

}