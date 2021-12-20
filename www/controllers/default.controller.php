<?php


class DefaultController extends Controller
{
	/**
	 * check current access to controller
	 *
	 * @param void
	 * @return  bool
	 */
	public function checkAccess() {
		$this->accessRight = true;
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
		global $langs;
		$context = Context::getInstance();

		$hookRes = $this->hookDoAction();
		if(empty($hookRes)){
			$context->title = $langs->trans('Welcome');
			$context->desc = $langs->trans('WelcomeDesc');
//			$context->topMenu->shrink = 1; // no transparency menu
			$context->doNotDisplayHeaderBar=1;// hide default header
		}
	}


	/**
	 *
	 * @param void
	 * @return void
	 */
	public function display(){
		global $conf;
		$context = Context::getInstance();

		$this->loadTemplate('header');

		$hookRes = $this->hookPrintPageView();

		if(empty($hookRes)){
			if(!empty($conf->global->EACCESS_NO_FULL_HEADBAR_FOR_HOME)){
				$this->loadTemplate('headbar');
			} else {
				$this->loadTemplate('headbar_full');
			}
			$this->loadTemplate('services');
		}

		$this->loadTemplate('footer');
	}
}
