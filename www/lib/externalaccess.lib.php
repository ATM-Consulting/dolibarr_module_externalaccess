<?php
/*
 * Copyright (C) 2018		Pierre-Henry Favre	<phf@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * \file /externalaccess/www/lib/externalaccess.lib.php
 * \ingroup externalaccess
 * \brief Fichier qui a pour but de regrouper les fonctions permettant de générer des bouts de HTML ou autre avec bootstrap
 */

/**
 * Permet de générer le rendu d'un modal bootstrap pour faire de la confirmation d'action
 *
 * @param string	$htmlid		Html id
 * @param string	$title		Titre du modal
 * @param string	$body		Contenu du modal (avec du html pour passer les inputs hidden)
 * @param string	$action		Valorise l'attribut "action" de l'élément "form"
 * @param string	$doAction	Valeur pour l'input name=action (il est explicité pour un usage plus intuitif)
 * @return string
 */
function getEaModalConfirm($htmlid, $title, $body, $action, $doAction)
{
	global $langs;

	$newToken = function_exists('newToken') ? newToken() : $_SESSION['newtoken'];

	$out= '
		<div class="modal fade" id="'.$htmlid.'" >
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="'.$action.'" method="POST">
						<input type="hidden" name="action" value="'.$doAction.'" />
						<input type="hidden" name="token" value="'.$newToken.'"/>
						<!-- Modal Header -->
						<div class="modal-header">
							<h4 class="modal-title">'.$title.'</h4>
							<button type="button" class="close" data-dismiss="modal">&times;</button>
						</div>

						<!-- Modal body -->
						<div class="modal-body">
							'.$body.'
						</div>

						<!-- Modal footer -->
						<div class="modal-footer">
							<button type="button" class="btn btn-secondary btn-strong mr-auto" data-dismiss="modal">'.$langs->trans('Cancel').'</button>
							<button type="submit" class="btn btn-primary btn-strong ">'.$langs->trans('Confirm').'</button>
						</div>
					</form>
				</div>
			</div>
		</div>';

	return $out;
}

/** generate a simple nav bar : note for dev, keep it generic
 * @param string $url_back
 * @param string $url_add
 * @param string $url_edit
 * @return string
 */
function getEaNavbar($url_back='', $url_add='', $url_edit='')
{
	global $langs;

	$out = '<nav class="navbar navbar-light justify-content-between mb-4 px-0">';

	if($url_back!==false) {
		$out .= '<a class="navbar-brand" href="' . (empty($url_back) ? '#" onclick="window.history.go(-1)' : $url_back) . '"><i class="fa fa-chevron-left"></i> ' . $langs->trans('EaBack') . '</a>';
	}
	if (!empty($url_add)) $out.= '<a class="btn btn-outline-primary my-2 my-sm-0" href="'.$url_add.'"><i class="fa fa-plus-circle"></i><span class="d-none d-sm-inline" > '.$langs->trans('New').'</span></a>';

	if (!empty($url_edit)) $out.= '<a class="btn btn-outline-primary my-2 my-sm-0" href="'.$url_edit.'"><i class="fa fa-edit"></i><span class="d-none d-sm-inline" > '.$langs->trans('Edit').'</span></a>';

	$out.= '</nav>';

	return $out;
}



/**
 *  Return a link to the user card (with optionally the picto)
 *    Use this->id,this->lastname, this->firstname
 *
 * @param User $user
 * @param int $withpictoimg Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto, -1=Include photo into link, -2=Only picto photo, -3=Only photo very small)
 * @param integer $notooltip 1=Disable tooltip on picto and name
 * @param int $maxlen Max length of visible user name
 * @param string $mode ''=Show firstname and lastname, 'firstname'=Show only firstname, 'firstelselast'=Show firstname or lastname if not defined, 'login'=Show login
 * @param string $morecss Add more css on link
 * @return    string                                String with URL
 */
function getUserName($user, $withpictoimg = 0,  $notooltip = 0, $maxlen = 24, $mode = '', $morecss = '')
{

	global $langs, $conf, $db;

	$result = '';
	$label = '';

	if (!empty($user->photo)) {
		$label .= '<div class="photointooltip">';
		$label .= showUserPhoto($user, 0, 60, 'photowithmargin photologintooltip'); // Force height to 60 so we total height of tooltip can be calculated and collision can be managed
		$label .= '</div><div style="clear: both;"></div>';
	}

//	if (!empty($user->socid))	// Add thirdparty for external users
//	{
//		$thirdpartystatic = new Societe($db);
//		$thirdpartystatic->fetch($user->socid);
//		$company = ' ('.$langs->trans("Company").': '.$thirdpartystatic->name.')';
//	}

	if ($withpictoimg) {
		$paddafterimage = '';
		if (abs($withpictoimg) == 1) $paddafterimage = 'style="margin-' . ($langs->trans("DIRECTION") == 'rtl' ? 'left' : 'right') . ': 3px;"';
		// Only picto
		if ($withpictoimg > 0) $picto = '<!-- picto user --><span class="nopadding userimg' . ($morecss ? ' ' . $morecss : '') . '">' . img_object('', 'user', $paddafterimage . ' ' . ($notooltip ? '' : 'class="classfortooltip"'), 0, 0, $notooltip ? 0 : 1) . '</span>';
		// Picto must be a photo
		else $picto = '<!-- picto photo user --><span class="nopadding userimg' . ($morecss ? ' ' . $morecss : '') . '"' . ($paddafterimage ? ' ' . $paddafterimage : '') . '>' . Form::showphoto('userphoto', $user, 0, 0, 0, 'userphoto' . ($withpictoimg == -3 ? 'small' : ''), 'mini', 0, 1) . '</span>';
		$result .= $picto;
	}

	if ($withpictoimg > -2 && $withpictoimg != 2) {
		if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) $result .= '<span class=" nopadding usertext' . ((!isset($user->statut) || $user->statut) ? '' : ' strikefordisabled') . ($morecss ? ' ' . $morecss : '') . '">';
		if ($mode == 'login') $result .= dol_trunc($user->login, $maxlen);
		else $result .= $user->getFullName($langs, '', ($mode == 'firstelselast' ? 3 : ($mode == 'firstname' ? 2 : -1)), $maxlen);
		if (empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) $result .= '</span>';
	}

	return $result;
}


/**
 *    	Return HTML code to output a photo
 *
 *     	@param  object		$user				Object containing data to retrieve file name
 * 		@param	int			$width				Width of photo
 * 		@param	int			$height				Height of photo (auto if 0)
 * 		@param	string		$cssclass			CSS name to use on img for photo
 * 	  	@return string    						HTML code to output photo
 */
function showUserPhoto($user, $width = 100, $height = 0, $cssclass = 'photowithmargin')
{
	global $conf;

	$email = $user->email;
	$ret = '';

	$context = Context::getInstance();


	$nophoto = $context->getRootUrl().'/img/avatar.png';
	if ($user->gender == 'man') $nophoto = $context->getRootUrl().'/img/user_man.png';
	if ($user->gender == 'woman') $nophoto = $context->getRootUrl().'/img/user_woman.png';

	if (!empty($conf->gravatar->enabled) && $email && 0)
	{
		/**
		 * @see https://gravatar.com/site/implement/images/php/
		 */
		global $dolibarr_main_url_root;
		$ret .= '<!-- Put link to gravatar -->';
		//$defaultimg=urlencode(dol_buildpath($nophoto,3));
		$defaultimg = 'mm';
		$ret .= '<img class="photo'.($cssclass ? ' '.$cssclass : '').'" alt="Gravatar avatar" title="'.$email.' Gravatar avatar" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="https://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s='.$width.'&d='.$defaultimg.'">'; // gravatar need md5 hash
	}
	else
	{
		$ret .= '<img class="photo'.($cssclass ? ' '.$cssclass : '').'" alt="No photo" '.($width ? ' width="'.$width.'"' : '').($height ? ' height="'.$height.'"' : '').' src="'.DOL_URL_ROOT.$nophoto.'">';
	}

	return $ret;
}

/**
 * @param       $searchProductId
 * @return false|stdClass
 */
function getProductImgFileInfos($searchProductId)
{
	global $db, $conf, $user;

	if(empty($searchProductId)){
		return false;
	}

	$photo = new stdClass();

	require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

	$searchProduct = new Product($db);
	$res = $searchProduct->fetch($searchProductId);
	if ($res > 0) {

		if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
		{
			$pdir[0] = get_exdir($searchProduct->id,2,0,0,$searchProduct,'product') . $searchProduct->id ."/photos/";
			$pdir[1] = get_exdir(0,0,0,0,$searchProduct,'product') . dol_sanitizeFileName($searchProduct->ref).'/';
		}
		else
		{
			$pdir[0] = get_exdir(0,0,0,0,$searchProduct,'product') . dol_sanitizeFileName($searchProduct->ref).'/'; // default
			$pdir[1] = get_exdir($searchProduct->id,2,0,0,$searchProduct,'product') . $searchProduct->id ."/photos/";	// alternative
		}

		$arephoto = false;
		foreach ($pdir as $midir)
		{
			if (! $arephoto)
			{
				$dir = $conf->product->dir_output.'/'.$midir;

				// on prend toujours la dernière photo uploader
				foreach ($searchProduct->liste_photos($dir, 0) as $key => $obj)
				{
					$photo->imageUrl = $photo->imageUrlThumb = $dir.$obj['photo'];
					if ($obj['photo_vignette']){
						$photo->imageUrlThumb = $dir.$obj['photo_vignette'];
					}

					$arephoto = true;
				}
			}
		}

		if(!empty($arephoto))
		{
			return $photo;
		}
	}
}

/**
 * @param       $searchProductId
 * @param mixed $size
 * @param @param mixed $size see getProductImgFileInfos
 */
function outputProductImg($searchProductId, $size = false)
{
	$photo = getProductImgFileInfos($searchProductId);

	// TODO : ajouter un droit permettant de voir les photos pour l'instant pour limiter l'access seul les utilisateurs identifiés peuvent voir les photos
	//$user->rights->externalaccess->see_product_img
//	if(empty($user->id)){ // bon l'interface n'a pas d'utilisateur...
//		$photo = false;
//	}

	// TODO : Taille d'image
	/* 	$size = false; // image std full size
		$size = 'thumb'; // use thumb image in Dolibarr
		$size = array(
			'h' => 200, // height in Pixels
			'w' => 200, // width in Pixels
			'crop' => auto|contain|cover,
					// contain 	: 	Un mot-clé qui redimensionne l'image afin qu'elle soit la plus grande possible et que l'image conserve ses proportions.
									L'image est contrainte dans le conteneur. Les zones éventuellement vide sont remplies avec la couleur d'arrière-plan (définie grâce à background-color).
					// cover	: 	Un mot-clé dont le comportement est opposé à celui de contain.
									L'image est redimensionnée pour être aussi grande que possible et pour conserver ses proportions.
									L'image couvre toute la largeur ou la hauteur du conteneur et les parties qui dépassent sont rognées si les proportions du conteneur sont différentes (il n'y aucun espace libre sur lequel on verrait la couleur d'arrière-plan).
					// auto		:	Un mot-clé qui redimensionne l'image d'arrière-plan afin que ses proportions soient conservées. contrairement a contain et cover , h et w deviennent des limites et non pas des valeurs fixes
			'proportion' => true|false,
			'background-color' => #FFFFFF, // couleur du fond de l'image lors d'un crop
		)

	NOTE 1 : 	Par expérience il faut prévoir un cache des images générées avec un moyen facile de le vider par une tâche cron par exemple
	NOTE 2 : 	Par expérience il est préférable de ne pas permettre à l'internaute de choisir le format d'image directement mais seulement une 'class' de format ex : large,medium,small,etc...
				la class étant la clé d'un tableau de l'ensemble des formats possibles.

	Une fois celà fait il nous sera possible de créer une vue catalogue en ligne ou autre
	 */


	if(!empty($photo))
	{
		$file = $photo->imageUrl;
		if($size === 'thumb'){
			$file = $photo->imageUrlThumb;
		}
		$type = mime_content_type($photo->imageUrl);
		header('Content-Type:'.$type);
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit();
	}
	else
	{
		$file = DOL_DOCUMENT_ROOT.'/public/theme/common/nophoto.png';
		$type = mime_content_type($file);
		header('Content-Type:'.$type);
		header('Content-Length: ' . filesize($file));
		readfile($file);
		exit();
	}
}


/**
 * @param string $fk_ecm
 * @param string $hashForShare
 * @param bool   $byPassSecurity
 * @param bool   $forceDownload
 */
function outputEcmFile($fk_ecm = '', $hashForShare = '', $byPassSecurity = false, $forceDownload = false)
{
	global $user, $db, $conf;

	include_once DOL_DOCUMENT_ROOT . '/ecm/class/ecmfiles.class.php';

	$ecm = new EcmFiles($db);
	if($ecm->fetch($fk_ecm, $ref = '', '', '', $hashForShare) > 0){

		$auth = true;

		if(!$byPassSecurity){
			if(empty($fk_ecm) && ($hashForShare !== $ecm->share || empty($ecm->share)) ){
				$auth = false;
			}

			if(!empty($fk_ecm)){
				$auth = false; // if $fk_ecm is provided so default state is not auth

				if(!empty($user->id)){ // to check if user is logged

					// Optional security
					$object = externalAccessObjectAutoLoad($ecm->src_object_type, $db);
					if($object->fetch(intval($ecm->src_object_id)) > 0){
						// TODO add a test for all document type to check is current user can download file
						if($object->element_type == 'ticket'){
							// TODO : check access
						}
					}
				}
			}
		}

		if(!$auth){
			http_response_code(401);
			// TODO include('401.php'); // provide your own HTML for the error page
			exit();
		}

		$file = DOL_DATA_ROOT . '/'.$ecm->filepath . '/' . $ecm->filename;
		if(file_exists($file))
		{
			$type = mime_content_type($file);

			header('Content-Type:'.$type);
			header('Content-Length: ' . filesize($file));

			if(!in_array($type, array()) && !$forceDownload){
				header('Content-Description: File Transfer');
				header('Cache-Control: must-revalidate');
				header('Content-Disposition: attachment; filename="'.$ecm->filename.'"');
			}

			readfile($file);
			exit();
		}
	}
	else{
		http_response_code(404);
		// TODO include('404.php'); // provide your own HTML for the error page
		exit();
	}
}

/**
 * @param        $searchProductId
 * @param string $format
 * @return string
 */
function getProductImgUrl($searchProductId, $format = 'thumb')
{
	$context = Context::getInstance();
	return $context->getRootUrl().'script/interface.php?action=productimg&p='.intval($searchProductId).'&f='.$format;
}


/**
 * return tags to use for js confirmation even on buttons or link
 * @param string $msg
 * @param string $url
 * @param string $title
 * @param string $confirmText
 * @param string $cancelTxt
 * @return string
 */
function getConfirmDialogsTags($msg, $url, $title = '', $confirmText='Confirm', $cancelTxt = 'Cancel')
{
	global $langs;

	$Tags = array(
		'data-confirm' => 1,
		'data-confirm-title' => !empty($title)?$langs->trans($title):'',
		'data-confirm-message' => $langs->trans($msg),
		'data-confirm-canceltxt' => $langs->trans($cancelTxt),
		'data-confirm-confirmtxt' => $langs->trans($confirmText),
		'data-confirm-url' => $url,
	);

	$TCompiledAttr = array();
	foreach ($Tags as $key => $value) {
		$TCompiledAttr[] = $key.'="'.dol_escape_htmltag($value).'"';
	}

	return !empty($TCompiledAttr) ?implode(' ', $TCompiledAttr) : '';
}



/**
 * Return an object
 *
 * @param string $objecttype Type of object ('invoice', 'order', 'expedition_bon', 'myobject@mymodule', ...)
 * @param DoliDB $db
 * @return int|object of $objecttype
 */
function externalAccessObjectAutoLoad($objecttype, &$db)
{
	global $conf, $langs;

	$ret = -1;
	$regs = array();

	// Parse $objecttype (ex: project_task)
	$module = $myobject = $objecttype;

	// If we ask an resource form external module (instead of default path)
	if (preg_match('/^([^@]+)@([^@]+)$/i', $objecttype, $regs)) {
		$myobject = $regs[1];
		$module = $regs[2];
	}


	if (preg_match('/^([^_]+)_([^_]+)/i', $objecttype, $regs))
	{
		$module = $regs[1];
		$myobject = $regs[2];
	}

	// Generic case for $classpath
	$classpath = $module.'/class';

	// Special cases, to work with non standard path
	if ($objecttype == 'facture' || $objecttype == 'invoice') {
		$classpath = 'compta/facture/class';
		$module='facture';
		$myobject='facture';
	}
	elseif ($objecttype == 'commande' || $objecttype == 'order') {
		$classpath = 'commande/class';
		$module='commande';
		$myobject='commande';
	}
	elseif ($objecttype == 'propal')  {
		$classpath = 'comm/propal/class';
	}
	elseif ($objecttype == 'supplier_proposal')  {
		$classpath = 'supplier_proposal/class';
	}
	elseif ($objecttype == 'shipping') {
		$classpath = 'expedition/class';
		$myobject = 'expedition';
		$module = 'expedition_bon';
	}
	elseif ($objecttype == 'delivery') {
	    $classpath = 'livraison/class';
        $myobject = 'livraison';
        $module = 'livraison_bon';
	    if (floatval(DOL_VERSION) >= 13.0) {
            $classpath = 'delivery/class';
            $myobject = 'delivery';
            $module = 'delivery_bon';
        }
	}
	elseif ($objecttype == 'contract') {
		$classpath = 'contrat/class';
		$module='contrat';
		$myobject='contrat';
	}
	elseif ($objecttype == 'member') {
		$classpath = 'adherents/class';
		$module='adherent';
		$myobject='adherent';
	}
	elseif ($objecttype == 'cabinetmed_cons') {
		$classpath = 'cabinetmed/class';
		$module='cabinetmed';
		$myobject='cabinetmedcons';
	}
	elseif ($objecttype == 'fichinter') {
		$classpath = 'fichinter/class';
		$module='ficheinter';
		$myobject='fichinter';
	}
	elseif ($objecttype == 'task') {
		$classpath = 'projet/class';
		$module='projet';
		$myobject='task';
	}
	elseif ($objecttype == 'stock') {
		$classpath = 'product/stock/class';
		$module='stock';
		$myobject='stock';
	}
	elseif ($objecttype == 'inventory') {
		$classpath = 'product/inventory/class';
		$module='stock';
		$myobject='inventory';
	}
	elseif ($objecttype == 'mo') {
		$classpath = 'mrp/class';
		$module='mrp';
		$myobject='mo';
	}

	// Generic case for $classfile and $classname
	$classfile = strtolower($myobject); $classname = ucfirst($myobject);
	//print "objecttype=".$objecttype." module=".$module." subelement=".$subelement." classfile=".$classfile." classname=".$classname;

	if ($objecttype == 'invoice_supplier') {
		$classfile = 'fournisseur.facture';
		$classname = 'FactureFournisseur';
		$classpath = 'fourn/class';
		$module = 'fournisseur';
	}
	elseif ($objecttype == 'order_supplier') {
		$classfile = 'fournisseur.commande';
		$classname = 'CommandeFournisseur';
		$classpath = 'fourn/class';
		$module = 'fournisseur';
	}
	elseif ($objecttype == 'stock') {
		$classpath = 'product/stock/class';
		$classfile = 'entrepot';
		$classname = 'Entrepot';
	}
	elseif ($objecttype == 'dolresource') {
		$classpath = 'resource/class';
		$classfile = 'dolresource';
		$classname = 'Dolresource';
		$module = 'resource';
	}


	if (!empty($conf->$module->enabled))
	{
		$res = dol_include_once('/'.$classpath.'/'.$classfile.'.class.php');
		if ($res)
		{
			if (class_exists($classname)) {
				return new $classname($db);
			}
		}
	}
	return $ret;
}
