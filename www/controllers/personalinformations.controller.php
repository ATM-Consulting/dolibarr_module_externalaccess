<?php


class PersonalInformationsController extends Controller
{
	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		$this->accessNeedLoggedUser = true;
		$this->accessRight =  true; // personal information always available
		return parent::checkAccess();
	}


	/**
	 * action method is called before html output
	 * can be used to manage security and change context
	 *
	 * @param void
	 * @return void
	 */
	public function action(){
		global $langs, $user;
		$context = Context::getInstance();

		if(!$context->controllerInstance->checkAccess()) { return; }

		$context->title = $langs->trans('UserInfosDesc') ; //$user->firstname .' '. $user->lastname;
		$context->desc = $user->firstname .' '. $user->lastname;; //$langs->trans('UserInfosDesc');
		$context->meta_title = $user->firstname .' '. $user->lastname .' - '. $langs->trans('UserInfosDesc');
		//$context->doNotDisplayHeaderBar=1;// hide default header

		$hookRes = $this->hookDoAction();
		if(empty($hookRes)){

			if($context->action == 'save'){
				// TODO: need to check all send informations to prevent and verbose errors
				$user->firstname = GETPOST('firstname', 'none');
				$user->lastname = GETPOST('lastname', 'none');
				$user->address = GETPOST('address', 'none');
				$user->zip = GETPOST('addresszip', 'none');
				$user->town = GETPOST('town', 'none');
				$user->user_mobile = GETPOST('user_mobile', 'none');
				$user->office_phone = GETPOST('office_phone', 'none');
				$user->office_fax = GETPOST('office_fax', 'none');
				if(floatval(DOL_VERSION) > 4){
					$user->email = GETPOST('email', 'custom', 0, FILTER_SANITIZE_EMAIL);
				} else {
					$user->email = GETPOST('email', 'none');
				}

				if($user->update($user)>0)
				{
					header('Location: '.$context->getRootUrl('personalinformations').'&action=saved');
				}
				else {
					$context->action == 'saveError';
				}
			}
		}
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display(){
		global $conf, $user;
		$context = Context::getInstance();
		if(!$context->controllerInstance->checkAccess()) {  return $this->display404(); }

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();
		if(empty($hookRes)){
			$this->loadTemplate('userinfos');
		}
		$this->loadTemplate('footer');
	}
}
