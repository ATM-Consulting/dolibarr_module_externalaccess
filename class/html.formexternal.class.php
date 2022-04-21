<?php
/* Copyright (C) 2021  John BOTELLA    <john.botella@atm-consulting.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */


/**
 * This class help you create external render
 */
class FormExternal
{

	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/** @var FormExternalItem[]  */
	public $items = array();

	/**
	 * @var int
	 */
	public $externalNotEmpty = 0;

	/** @var Translate */
	public $langs;

	/** @var Form */
	public $form;

	/** @var int */
	protected $maxItemRank;

	/**
	 * this is an html string display before output form
	 * @var string
	 */
	public $htmlBeforeOutputForm = '';

	/**
	 * this is an html string display after output form
	 * @var string
	 */
	public $htmlAfterOutputForm = '';

	/**
	 * this is an html string display on buttons zone
	 * @var string
	 */
	public $htmlOutputMoreButton = '';


	/**
	 *
	 * @var array
	 */
	public $formAttributes = array(
		'action' => '', // set in __construct
		'method' => 'POST'
	);
	/**
	 *
	 * @var array
	 */
	public $btAttributes = array();

	/**
	 * an list of hidden inputs used only in edit mode
	 * @var array
	 */
	public $formHiddenInputs = array();

	public $context;
	public $element;

	/**
	 * Constructor
	 *
	 * @param DoliDB $db          Database handler
	 * @param string $page_element     like ticket_card
	 * @param bool   $outputLangs if needed can use another lang
	 */
	public function __construct($db, $page_element, $outputLangs = false)
	{
		global $langs;
		$this->db = $db;
		$this->form = new Form($this->db);
		$this->context = Context::getInstance();
		$this->formAttributes['action'] = $this->context->getRootUrl($page_element);


		if ($outputLangs) {
			$this->langs = $outputLangs;
		} else {
			$this->langs = $langs;
		}
	}

	/**
	 * Generate an attributes string form an input array
	 *
	 * @param 	array 	$attributes 	an array of attributes keys and values,
	 * @return 	string					attribute string
	 */
	static public function generateAttributesStringFromArray($attributes)
	{
		$Aattr = array();
		if (is_array($attributes)) {
			foreach ($attributes as $attribute => $value) {
				if (is_array($value) || is_object($value)) {
					continue;
				}
				$Aattr[] = $attribute.'="'.dol_escape_htmltag($value).'"';
			}
		}

		return !empty($Aattr)?implode(' ', $Aattr):'';
	}

	/**
	 * print form
	 *
	 * @param bool   $editMode true will display output on edit mod
	 * @param string $element
	 * @return    string                html output
	 */
	public function generateOutput($editMode = false, $element = '')
	{
		global $hookmanager, $action, $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

		$this->element = $element;

		$parameters = array(
			'editMode' => $editMode
		);
		$this->addExtrafieldsItems();
		$reshook = $hookmanager->executeHooks('formExternalBeforeGenerateOutput', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if ($reshook > 0) {
			return $hookmanager->resPrint;
		} else {
			$out = '<!-- Start generateOutput from FormExternal class  -->';
			$out.= $this->htmlBeforeOutputForm;

			if ($editMode) {
				$out.= '<form ' . self::generateAttributesStringFromArray($this->formAttributes) . ' >';

				// generate hidden values from $this->formHiddenInputs
				if (!empty($this->formHiddenInputs) && is_array($this->formHiddenInputs)) {
					foreach ($this->formHiddenInputs as $hiddenKey => $hiddenValue) {
						$out.= '<input type="hidden" name="'.dol_escape_htmltag($hiddenKey).'" value="' . dol_escape_htmltag($hiddenValue) . '">';
					}
				}
			}
			//Conf aide
			$helpConfName = strtoupper($this->element).'_PUBLIC_TEXT_HELP_MESSAGE';
			if(! empty($conf->global->{$helpConfName})) {
				$out .= '<div class="'.$this->element.'-help-msg-wrap">'.$conf->global->{$helpConfName}.'</div>';
			}

			// generate output table
			$out .= $this->generateTableOutput($editMode);


			$reshook = $hookmanager->executeHooks('formExternalBeforeGenerateOutputButton', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) {
				setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			}

			if ($reshook > 0) {
				return $hookmanager->resPrint;
			}
			else if($editMode) {
				if(empty($this->btAttributes['action'])) $this->btAttributes['action'] = 'save';
				if(empty($this->btAttributes['text'])) $this->btAttributes['text'] = $this->langs->transnoentities('Save');
				$out .= '<div class="form-btn-action-container">';
				$out .= '<button type="submit" class="btn btn-success btn-strong pull-right" name="action" value="'.$this->btAttributes['action'].'" >'.$this->btAttributes['text'].'</button>';
				$out .= '
			</div>
	';
			}

			if ($editMode) {
				$out .= '</form>';
			}

			$out.= $this->htmlAfterOutputForm;

			return $out;
		}
	}

	/**
	 * prepare div form
	 *
	 * @param bool   $editMode true will display output on edit mod
	 * @return    string                html output
	 */
	public function generateTableOutput($editMode = false)
	{
		global $hookmanager, $action, $conf;
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

		$parameters = array(
			'editMode' => $editMode
		);
		$reshook = $hookmanager->executeHooks('formExternalBeforeGenerateTableOutput', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook < 0) {
			setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
		}

		if ($reshook > 0) {
			return $hookmanager->resPrint;
		} else {

			$out = '<div class="form-'.$this->element.'-message-container" >';

			// Sort items before render
			$this->sortingItems();

			foreach ($this->items as $item) {
				$out .= $this->generateLineOutput($item, $editMode);
			}

			$out .= '</div>';
			return $out;
		}
	}

	/**
	 * Function generating items for extrafields
	 * @return void
	 */
	public function addExtrafieldsItems() {
		global $conf;
		dol_include_once('core/class/extrafields.class.php');
		$e = new ExtraFields($this->db);
		$e->fetch_name_optionals_label($this->element);
		$confVal = 'EACCESS_CARD_ADDED_FIELD_'.strtoupper($this->element);
		$TExtraAddedField = unserialize($conf->global->{$confVal});
		if(!empty($TExtraAddedField)) {
			foreach($TExtraAddedField as $extra_field) {
				$extra_field = strtr($extra_field, array('EXTRAFIELD_' => ''));
				$label = $e->attributes[$this->element]['label'][$extra_field];
				$type = $e->attributes[$this->element]['type'][$extra_field];
				$size = $e->attributes[$this->element]['size'][$extra_field];
				$default = $e->attributes[$this->element]['default'][$extra_field];
				$computed = $e->attributes[$this->element]['computed'][$extra_field]; //TODO
				$unique = $e->attributes[$this->element]['unique'][$extra_field]; //TODO
				$required = $e->attributes[$this->element]['required'][$extra_field];
				$param = $e->attributes[$this->element]['param'][$extra_field];
				$perms = dol_eval($e->attributes[$this->element]['perms'][$extra_field], 1); //TODO
				$langfile = $e->attributes[$this->element]['langfile'][$extra_field]; //TODO
				$list = dol_eval($e->attributes[$this->element]['list'][$extra_field], 1);
				$totalizable = $e->attributes[$this->element]['totalizable'][$extra_field]; //TODO
				$help = $e->attributes[$this->element]['help'][$extra_field];
				$hidden = (empty($list) ? 1 : 0); //TODO
				if($hidden) continue;
				$item = $this->newItem('options_'.$extra_field);
				if(!empty($required)) $item->setAsRequired();
				$item->nameText = $label;
				if(!empty($help)) $item->helpText = $help;
				if(!empty($size) && in_array($type, array('varchar', 'text'))) $item->fieldAttr['maxlength'] = $size;
				if(!empty($default)) $item->fieldValue = $default;
				if($type == 'html') $item->setAsHtml();
				if($type == 'separate') $item->setAsSeparator();
				if($type == 'text') $item->setAsTextarea();
				if($type == 'int' || $type == 'double' || $type == 'price') {
					$item->fieldAttr['type'] = 'number';
					if($type == 'int') $item->fieldAttr['step'] = '1';
					else $item->fieldAttr['step'] = 'any';
				}
				if($type == 'date') $item->fieldAttr['type'] = 'date';
				if($type == 'datetime') $item->setAsDateTime();
				if($type == 'boolean') $item->setAsYesNo();
				if($type == 'password') $item->fieldAttr['type'] = 'password';
				if($type == 'select') $item->setAsSelect($param['options']);
				if($type == 'sellist') {
					$TOption = $this->prepareArrayFromTable($param);
					$item->setAsSelect($TOption);
				}
				if($type == 'radio') $item->setAsRadio($param['options']);
				if($type == 'checkbox') {
					$item->multiple = true;
					$item->setAsSelect($param['options']);
				}
				if($type == 'chkbxlst') {
					$item->multiple = true;
					$TOption = $this->prepareArrayMultipleFromTable($param);
					$item->setAsSelect($TOption);
				}
				if($type == 'link') {
					$TOption = $this->prepareArrayFromLink($param);
					$item->setAsSelect($TOption);
				}
			}
		}
	}

	/**
	 * Préparation du tableau pour le type lien
	 *
	 * @param 	array 		$param
	 * @return    void|null
	 */
	public function prepareArrayFromLink($param) {
		global $conf, $user, $hookmanager;
		$TOption = array();
		$param_list = array_keys($param['options']); // $param_list='ObjectName:classPath'

		$objecttmp = null;
		$InfoFieldList = explode(":", $param_list[0]);
		$classname = $InfoFieldList[0];
		$classpath = $InfoFieldList[1];
		$addcreatebuttonornot = empty($InfoFieldList[2]) ? 0 : $InfoFieldList[2];
		$filter = empty($InfoFieldList[3]) ? '' : $InfoFieldList[3];

		if(! empty($classpath)) {
			dol_include_once($classpath);

			if($classname && class_exists($classname)) {
				$objecttmp = new $classname($this->db);
				// Make some replacement
				$sharedentities = getEntity(strtolower($classname));
				$objecttmp->filter = str_replace(array('__ENTITY__', '__SHARED_ENTITIES__', '__USER_ID__'), array($conf->entity, $sharedentities, $user->id), $filter);
			}
		}
		if(! is_object($objecttmp)) {
			dol_syslog('Error bad setup of type for field '.$InfoFieldList, LOG_WARNING);

			return 'Error bad setup of type for field '.join(',', $InfoFieldList);
		}

		//var_dump($objecttmp->filter);
		$prefixforautocompletemode = $objecttmp->element;
		if($prefixforautocompletemode == 'societe') {
			$prefixforautocompletemode = 'company';
		}
		if($prefixforautocompletemode == 'product') {
			$prefixforautocompletemode = 'produit';
		}
		$confkeyforautocompletemode = strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT'; // For example COMPANY_USE_SEARCH_TO_SELECT

		$prefixforautocompletemode = $objecttmp->element;
		if($prefixforautocompletemode == 'societe') {
			$prefixforautocompletemode = 'company';
		}
		$confkeyforautocompletemode = strtoupper($prefixforautocompletemode).'_USE_SEARCH_TO_SELECT'; // For example COMPANY_USE_SEARCH_TO_SELECT

		if(! empty($objecttmp->fields)) {    // For object that declare it, it is better to use declared fields (like societe, contact, ...)
			$tmpfieldstoshow = '';
			foreach($objecttmp->fields as $key => $val) {
				if(! dol_eval($val['enabled'], 1, 1)) {
					continue;
				}
				if(! empty($val['showoncombobox'])) {
					$tmpfieldstoshow .= ($tmpfieldstoshow ? ',' : '').'t.'.$key;
				}
			}
			if($tmpfieldstoshow) {
				$fieldstoshow = $tmpfieldstoshow;
			}
		}
		else {
			// For backward compatibility
			$objecttmp->fields['ref'] = array('type' => 'varchar(30)', 'label' => 'Ref', 'showoncombobox' => 1);
		}

		if(empty($fieldstoshow)) {
			if(isset($objecttmp->fields['ref'])) {
				$fieldstoshow = 't.ref';
			}
			else {
				$this->langs->load("errors");
				$this->error = $this->langs->trans("ErrorNoFieldWithAttributeShowoncombobox");

				return $this->langs->trans('ErrorNoFieldWithAttributeShowoncombobox');
			}
		}

		// Search data
		$sql = "SELECT t.rowid, ".$fieldstoshow." FROM ".MAIN_DB_PREFIX.$objecttmp->table_element." as t";
		if(isset($objecttmp->ismultientitymanaged)) {
			if(! is_numeric($objecttmp->ismultientitymanaged)) {
				$tmparray = explode('@', $objecttmp->ismultientitymanaged);
				$sql .= " INNER JOIN ".MAIN_DB_PREFIX.$tmparray[1]." as parenttable ON parenttable.rowid = t.".$tmparray[0];
			}
			if($objecttmp->ismultientitymanaged === 'fk_soc@societe') {
				if(! $user->rights->societe->client->voir && ! $user->socid) {
					$sql .= ", ".MAIN_DB_PREFIX."societe_commerciaux as sc";
				}
			}
		}

		// Add where from hooks
		$parameters = array();
		$reshook = $hookmanager->executeHooks('selectForFormsListWhere', $parameters); // Note that $action and $object may have been modified by hook
		if(! empty($hookmanager->resPrint)) {
			$sql .= $hookmanager->resPrint;
		}
		else {
			$sql .= " WHERE 1=1";
			if(isset($objecttmp->ismultientitymanaged)) {
				if($objecttmp->ismultientitymanaged == 1) {
					$sql .= " AND t.entity IN (".getEntity($objecttmp->table_element).")";
				}
				if(! is_numeric($objecttmp->ismultientitymanaged)) {
					$sql .= " AND parenttable.entity = t.".$tmparray[0];
				}
				if($objecttmp->ismultientitymanaged == 1 && ! empty($user->socid)) {
					if($objecttmp->element == 'societe') {
						$sql .= " AND t.rowid = ".((int) $user->socid);
					}
					else {
						$sql .= " AND t.fk_soc = ".((int) $user->socid);
					}
				}
				if($objecttmp->ismultientitymanaged === 'fk_soc@societe') {
					if(! $user->rights->societe->client->voir && ! $user->socid) {
						$sql .= " AND t.rowid = sc.fk_soc AND sc.fk_user = ".((int) $user->id);
					}
				}
			}

			if($objecttmp->ismultientitymanaged == 'fk_soc@societe') {
				if(! $user->rights->societe->client->voir && ! $user->socid) {
					$sql .= " AND t.rowid = sc.fk_soc AND sc.fk_user = ".((int) $user->id);
				}
			}
			if($objecttmp->filter) {     // Syntax example "(t.ref:like:'SO-%') and (t.date_creation:<:'20160101')"
				$regexstring = '\(([^:\'\(\)]+:[^:\'\(\)]+:[^\(\)]+)\)';
				$sql .= " AND (".preg_replace_callback('/'.$regexstring.'/', 'Form::forgeCriteriaCallback', $objecttmp->filter).")";
			}
		}
		$sql .= $this->db->order($fieldstoshow, "ASC");

		// Build output string
		$resql = $this->db->query($sql);
		if($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			if($num) {
				while($i < $num) {
					$obj = $this->db->fetch_object($resql);
					$label = '';
					$tmparray = explode(',', $fieldstoshow);
					$oldvalueforshowoncombobox = 0;
					foreach($tmparray as $key => $val) {
						$val = preg_replace('/t\./', '', $val);
						$label .= (($label && $obj->$val) ? ($oldvalueforshowoncombobox != $objecttmp->fields[$val]['showoncombobox'] ? ' - ' : ' ') : '');
						$label .= $obj->$val;
						$oldvalueforshowoncombobox = $objecttmp->fields[$val]['showoncombobox'];
					}
					if(empty($outputmode)) {
						$TOption[$obj->rowid] = $label;
					}
					else {
						$TOption[$label] = $label;
					}

					$i++;
				}
			}
		}
		else {
			dol_print_error($this->db);
		}

		return $TOption;
	}

	/**
	 * prepare array from table type
	 *
	 * @param 	array 		$param
	 * @return 	void|null
	 */
	public function prepareArrayFromTable($param)
	{
		global $conf;
		$TOption = array();
		if(is_array($param['options'])) {
			$param_list = array_keys($param['options']);
			$InfoFieldList = explode(":", $param_list[0]);
			$parentName = '';
			$parentField = '';
			// 0 : tableName
			// 1 : label field name
			// 2 : key fields name (if differ of rowid)
			// 3 : key field parent (for dependent lists)
			// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
			// 5 : id category type
			// 6 : ids categories list separated by comma for category root
			$keyList = (empty($InfoFieldList[2]) ? 'rowid' : $InfoFieldList[2].' as rowid');

			if(count($InfoFieldList) > 4 && ! empty($InfoFieldList[4])) {
				if(strpos($InfoFieldList[4], 'extra.') !== false) {
					$keyList = 'main.'.$InfoFieldList[2].' as rowid';
				}
				else {
					$keyList = $InfoFieldList[2].' as rowid';
				}
			}
			if(count($InfoFieldList) > 3 && ! empty($InfoFieldList[3])) {
				list($parentName, $parentField) = explode('|', $InfoFieldList[3]);
				$keyList .= ', '.$parentField;
			}

			$filter_categorie = false;
			if(count($InfoFieldList) > 5) {
				if($InfoFieldList[0] == 'categorie') {
					$filter_categorie = true;
				}
			}

			if($filter_categorie === false) {
				$fields_label = explode('|', $InfoFieldList[1]);
				if(is_array($fields_label)) {
					$keyList .= ', ';
					$keyList .= implode(', ', $fields_label);
				}

				$sqlwhere = '';
				$sql = 'SELECT '.$keyList;
				$sql .= ' FROM '.MAIN_DB_PREFIX.$InfoFieldList[0];
				if(! empty($InfoFieldList[4])) {
					// can use curent entity filter
					if(strpos($InfoFieldList[4], '$ENTITY$') !== false) {
						$InfoFieldList[4] = str_replace('$ENTITY$', $conf->entity, $InfoFieldList[4]);
					}
					// can use SELECT request
					if(strpos($InfoFieldList[4], '$SEL$') !== false) {
						$InfoFieldList[4] = str_replace('$SEL$', 'SELECT', $InfoFieldList[4]);
					}

					// current object id can be use into filter
					if(strpos($InfoFieldList[4], '$ID$') !== false && ! empty($objectid)) {
						$InfoFieldList[4] = str_replace('$ID$', $objectid, $InfoFieldList[4]);
					}
					else {
						$InfoFieldList[4] = str_replace('$ID$', '0', $InfoFieldList[4]);
					}
					//We have to join on extrafield table
					if(strpos($InfoFieldList[4], 'extra') !== false) {
						$sql .= ' as main, '.MAIN_DB_PREFIX.$InfoFieldList[0].'_extrafields as extra';
						$sqlwhere .= ' WHERE extra.fk_object=main.'.$InfoFieldList[2].' AND '.$InfoFieldList[4];
					}
					else {
						$sqlwhere .= ' WHERE '.$InfoFieldList[4];
					}
				}
				else {
					$sqlwhere .= ' WHERE 1=1';
				}
				// Some tables may have field, some other not. For the moment we disable it.
				if(in_array($InfoFieldList[0], array('tablewithentity'))) {
					$sqlwhere .= ' AND entity = '.$conf->entity;
				}
				$sql .= $sqlwhere;
				//print $sql;

				$sql .= ' ORDER BY '.implode(', ', $fields_label);

				dol_syslog(get_class($this).'::showInputField type=sellist', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if($resql) {
					$num = $this->db->num_rows($resql);
					$i = 0;
					while($i < $num) {
						$labeltoshow = '';
						$obj = $this->db->fetch_object($resql);

						// Several field into label (eq table:code|libelle:rowid)
						$notrans = false;
						$fields_label = explode('|', $InfoFieldList[1]);
						if(is_array($fields_label) && count($fields_label) > 1) {
							$notrans = true;
							foreach($fields_label as $field_toshow) {
								$labeltoshow .= $obj->$field_toshow.' ';
							}
						}
						else {
							$labeltoshow = $obj->{$InfoFieldList[1]};
						}

						if(! $notrans) {
							$translabel = $this->langs->trans($obj->{$InfoFieldList[1]});
							$labeltoshow = $translabel;
						}
						if(empty($labeltoshow)) {
							$labeltoshow = '(not defined)';
						}

						if(! empty($InfoFieldList[3]) && $parentField) {
							$parent = $parentName.':'.$obj->{$parentField};
						}

						$TOption[$obj->rowid] = $labeltoshow;

						$i++;
					}
					$this->db->free($resql);
				}
				else {
					print 'Error in request '.$sql.' '.$this->db->lasterror().'. Check setup of extra parameters.<br>';
				}
			}
			else {
				require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
				$TOption = $this->form->select_all_categories(Categorie::$MAP_ID_TO_CODE[$InfoFieldList[5]], '', 'parent', 64, $InfoFieldList[6], 1, 1);
			}
		}

		return $TOption;
	}
	/**
	 * Preparation de tableau pour le type multiple issu d'une table
	 *
	 * @param 	array 		$param
	 * @return 	void|null
	 */
	public function prepareArrayMultipleFromTable($param)
	{
		global $conf;
		$TOption = array();
		if(is_array($param['options'])) {
			$param_list = array_keys($param['options']);
			$InfoFieldList = explode(":", $param_list[0]);
			$parentName = '';
			$parentField = '';
			// 0 : tableName
			// 1 : label field name
			// 2 : key fields name (if differ of rowid)
			// 3 : key field parent (for dependent lists)
			// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
			// 5 : id category type
			// 6 : ids categories list separated by comma for category root
			$keyList = (empty($InfoFieldList[2]) ? 'rowid' : $InfoFieldList[2].' as rowid');

			if(count($InfoFieldList) > 3 && ! empty($InfoFieldList[3])) {
				list ($parentName, $parentField) = explode('|', $InfoFieldList[3]);
				$keyList .= ', '.$parentField;
			}
			if(count($InfoFieldList) > 4 && ! empty($InfoFieldList[4])) {
				if(strpos($InfoFieldList[4], 'extra.') !== false) {
					$keyList = 'main.'.$InfoFieldList[2].' as rowid';
				}
				else {
					$keyList = $InfoFieldList[2].' as rowid';
				}
			}

			$filter_categorie = false;
			if(count($InfoFieldList) > 5) {
				if($InfoFieldList[0] == 'categorie') {
					$filter_categorie = true;
				}
			}

			if($filter_categorie === false) {
				$fields_label = explode('|', $InfoFieldList[1]);
				if(is_array($fields_label)) {
					$keyList .= ', ';
					$keyList .= implode(', ', $fields_label);
				}

				$sqlwhere = '';
				$sql = 'SELECT '.$keyList;
				$sql .= ' FROM '.MAIN_DB_PREFIX.$InfoFieldList[0];
				if(! empty($InfoFieldList[4])) {
					// can use SELECT request
					if(strpos($InfoFieldList[4], '$SEL$') !== false) {
						$InfoFieldList[4] = str_replace('$SEL$', 'SELECT', $InfoFieldList[4]);
					}

					// current object id can be use into filter
					if(strpos($InfoFieldList[4], '$ID$') !== false && ! empty($objectid)) {
						$InfoFieldList[4] = str_replace('$ID$', $objectid, $InfoFieldList[4]);
					}
					else if(preg_match("#^.*list.php$#", $_SERVER["PHP_SELF"])) {
						// Pattern for word=$ID$
						$word = '\b[a-zA-Z0-9-\.-_]+\b=\$ID\$';

						// Removing space arount =, ( and )
						$InfoFieldList[4] = preg_replace('# *(=|\(|\)) *#', '$1', $InfoFieldList[4]);

						$nbPreg = 1;
						// While we have parenthesis
						while($nbPreg != 0) {
							// Init des compteurs
							$nbPregRepl = $nbPregSel = 0;
							// On retire toutes les parenthèses sans = avant
							$InfoFieldList[4] = preg_replace('#([^=])(\([^)^(]*('.$word.')[^)^(]*\))#', '$1 $3 ', $InfoFieldList[4], -1, $nbPregRepl);
							// On retire les espaces autour des = et parenthèses
							$InfoFieldList[4] = preg_replace('# *(=|\(|\)) *#', '$1', $InfoFieldList[4]);
							// On retire toutes les parenthèses avec = avant
							$InfoFieldList[4] = preg_replace('#\b[a-zA-Z0-9-\.-_]+\b=\([^)^(]*('.$word.')[^)^(]*\)#', '$1 ', $InfoFieldList[4], -1, $nbPregSel);
							// On retire les espaces autour des = et parenthèses
							$InfoFieldList[4] = preg_replace('# *(=|\(|\)) *#', '$1', $InfoFieldList[4]);

							// Calcul du compteur général pour la boucle
							$nbPreg = $nbPregRepl + $nbPregSel;
						}

						// Si l'on a un AND ou un OR, avant ou après
						preg_match('#(AND|OR|) *('.$word.') *(AND|OR|)#', $InfoFieldList[4], $matchCondition);
						while(! empty($matchCondition[0])) {
							// If the two sides differ but are not empty
							if(! empty($matchCondition[1]) && ! empty($matchCondition[3]) && $matchCondition[1] != $matchCondition[3]) {
								// Nobody sain would do that without parentheses
								$InfoFieldList[4] = str_replace('$ID$', '0', $InfoFieldList[4]);
							}
							else {
								if(! empty($matchCondition[1])) {
									$boolCond = (($matchCondition[1] == "AND") ? ' AND TRUE ' : ' OR FALSE ');
									$InfoFieldList[4] = str_replace($matchCondition[0], $boolCond.$matchCondition[3], $InfoFieldList[4]);
								}
								else if(! empty($matchCondition[3])) {
									$boolCond = (($matchCondition[3] == "AND") ? ' TRUE AND ' : ' FALSE OR');
									$InfoFieldList[4] = str_replace($matchCondition[0], $boolCond, $InfoFieldList[4]);
								}
								else {
									$InfoFieldList[4] = " TRUE ";
								}
							}

							// Si l'on a un AND ou un OR, avant ou après
							preg_match('#(AND|OR|) *('.$word.') *(AND|OR|)#', $InfoFieldList[4], $matchCondition);
						}
					}
					else {
						$InfoFieldList[4] = str_replace('$ID$', '0', $InfoFieldList[4]);
					}

					// We have to join on extrafield table
					if(strpos($InfoFieldList[4], 'extra.') !== false) {
						$sql .= ' as main, '.MAIN_DB_PREFIX.$InfoFieldList[0].'_extrafields as extra';
						$sqlwhere .= ' WHERE extra.fk_object=main.'.$InfoFieldList[2].' AND '.$InfoFieldList[4];
					}
					else {
						$sqlwhere .= ' WHERE '.$InfoFieldList[4];
					}
				}
				else {
					$sqlwhere .= ' WHERE 1=1';
				}
				// Some tables may have field, some other not. For the moment we disable it.
				if(in_array($InfoFieldList[0], array('tablewithentity'))) {
					$sqlwhere .= ' AND entity = '.$conf->entity;
				}
				// $sql.=preg_replace('/^ AND /','',$sqlwhere);
				// print $sql;

				$sql .= $sqlwhere;
				dol_syslog(get_class($this).'::showInputField type=chkbxlst', LOG_DEBUG);
				$resql = $this->db->query($sql);
				if($resql) {
					$num = $this->db->num_rows($resql);
					$i = 0;

					$data = array();

					while($i < $num) {
						$labeltoshow = '';
						$obj = $this->db->fetch_object($resql);

						$notrans = false;
						// Several field into label (eq table:code|libelle:rowid)
						$fields_label = explode('|', $InfoFieldList[1]);
						if(is_array($fields_label)) {
							$notrans = true;
							foreach($fields_label as $field_toshow) {
								$labeltoshow .= $obj->$field_toshow.' ';
							}
						}
						else {
							$labeltoshow = $obj->{$InfoFieldList[1]};
						}
						$labeltoshow = dol_trunc($labeltoshow, 45);

						if(! $notrans) {
							$translabel = $this->langs->trans($obj->{$InfoFieldList[1]});
							if($translabel != $obj->{$InfoFieldList[1]}) {
								$labeltoshow = dol_trunc($translabel, 18);
							}
							else {
								$labeltoshow = dol_trunc($obj->{$InfoFieldList[1]}, 18);
							}
						}
						if(empty($labeltoshow)) {
							$labeltoshow = '(not defined)';
						}

						if(is_array($value_arr) && in_array($obj->rowid, $value_arr)) {
							$data[$obj->rowid] = $labeltoshow;
						}

						if(! empty($InfoFieldList[3]) && $parentField) {
							$parent = $parentName.':'.$obj->{$parentField};
						}

						$data[$obj->rowid] = $labeltoshow;

						$i++;
					}
					$this->db->free($resql);
					$TOption = $data;
				}
				else {
					print 'Error in request '.$sql.' '.$this->db->lasterror().'. Check setup of extra parameters.<br>';
				}
			}
			else {
				require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
				$data = $this->form->select_all_categories(Categorie::$MAP_ID_TO_CODE[$InfoFieldList[5]], '', 'parent', 64, $InfoFieldList[6], 1, 1);
				$TOption = $data;
			}
		}

		return $TOption;
	}
	/**
	 * save conf value from POST
	 *
	 * @param 	bool 		$noMessageInUpdate display event message on errors and success
	 * @return 	void|null
	 */
	public function saveConfFromPost($noMessageInUpdate = false)
	{
		if (empty($this->items)) {
			return null;
		}

		$this->db->begin();
		$error = 0;
		foreach ($this->items as $item) {
			$res = $item->setValueFromPost();
			if ($res > 0) {
				$item->saveConfValue();
			} elseif ($res < 0) {
				$error++;
				break;
			}
		}

		if (!$error) {
			$this->db->commit();
			if (empty($noMessageInUpdate)) {
				setEventMessages($this->langs->trans("SetupSaved"), null);
			}
		} else {
			$this->db->rollback();
			if (empty($noMessageInUpdate)) {
				setEventMessages($this->langs->trans("SetupNotSaved"), null, 'errors');
			}
		}
	}

	/**
	 * prepare div for a line
	 *
	 * @param 	FormExternalItem 	$item 		the external item
	 * @param 	bool 			$editMode 	Display as edit mod
	 * @return 	string 						the html output for an external item
	 */
	public function generateLineOutput($item, $editMode = false) {
		$out = '';
		if($item->enabled == 1) {
			if(empty($item->element)) $item->element = $this->element;
			$this->externalNotEmpty++;
			$out .= '<div class="form-group">';
			if($item->getType() == 'separator') $out .= '<hr style="max-width : 100%;">';
			else {
				$out .= '<label for="'.$this->element.'-'.$item->confKey.'">'.$item->getNameText().'</label>';
				$helpText = $item->getHelpText();
				if(! empty($helpText)) $item->fieldAttr['aria-describedby'] = $this->element.'-'.$item->confKey.'-help';

				if($editMode) {
					$out .= $item->generateInputField();
				}
				else {
					$out .= $item->generateOutputField();
				}
			}


			if (!empty($item->errors)) {
				// TODO : move set event message in a methode to be called by cards not by this class
				setEventMessages(null, $item->errors, 'errors');
			}


			if(!empty($helpText)) $out.= '<small id="'.$this->element.'-'.$item->confKey.'-help" class="form-text text-muted">'. $helpText.'</small>';
			$out.= '</div>';
		}

		return $out;
	}


	/**
	 * Method used to test  module builder convertion to this form usage
	 *
	 * @param array 	$params 	an array of arrays of params from old modulBuilder params
	 * @return null
	 */
	public function addItemsFromParamsArray($params)
	{
		if (!is_array($params) || empty($params)) { return false; }
		foreach ($params as $confKey => $param) {
			$this->addItemFromParams($confKey, $param); // todo manage error
		}
	}


	/**
	 * From old
	 * Method was used to test  module builder convertion to this form usage.
	 *
	 * @param 	string 	$confKey 	the conf name to store
	 * @param 	array 	$params 	an array of params from old modulBuilder params
	 * @return 	bool
	 */
	public function addItemFromParams($confKey, $params)
	{
		if (empty($confKey) || empty($params['type'])) { return false; }

		/*
		 * Exemple from old module builder external page
		 * 	// 'MYMODULE_MYPARAM1'=>array('type'=>'string', 'css'=>'minwidth500' ,'enabled'=>1),
			// 'MYMODULE_MYPARAM2'=>array('type'=>'textarea','enabled'=>1),
			//'MYMODULE_MYPARAM3'=>array('type'=>'category:'.Categorie::TYPE_CUSTOMER, 'enabled'=>1),
			//'MYMODULE_MYPARAM4'=>array('type'=>'emailtemplate:thirdparty', 'enabled'=>1),
			//'MYMODULE_MYPARAM5'=>array('type'=>'yesno', 'enabled'=>1),
			//'MYMODULE_MYPARAM5'=>array('type'=>'thirdparty_type', 'enabled'=>1),
			//'MYMODULE_MYPARAM6'=>array('type'=>'securekey', 'enabled'=>1),
			//'MYMODULE_MYPARAM7'=>array('type'=>'product', 'enabled'=>1),
		 */

		$item = new FormExternalItem($confKey);
		// need to be ignored from scrutinizer setTypeFromTypeString was created as deprecated to incite developper to use object oriented usage
		/** @scrutinizer ignore-deprecated */ $item->setTypeFromTypeString($params['type']);

		if (!empty($params['enabled'])) {
			$item->enabled = $params['enabled'];
		}

		if (!empty($params['css'])) {
			$item->cssClass = $params['css'];
		}

		$this->items[$item->confKey] = $item;

		return true;
	}

	/**
	 * Used to export param array for /core/actions_setmoduleoptions.inc.php template
	 * Method exists only for manage external convertion
	 *
	 * @return array $arrayofparameters for /core/actions_setmoduleoptions.inc.php
	 */
	public function exportItemsAsParamsArray()
	{
		$arrayofparameters = array();
		if(!empty($this->items)) {
			foreach($this->items as $item) {
				$arrayofparameters[$item->confKey] = array(
					'type' => $item->getType(),
					'enabled' => $item->enabled
				);
			}
		}

		return $arrayofparameters;
	}

	/**
	 * Reload for each item default conf
	 * note: this will override custom configuration
	 *
	 * @return bool
	 */
	public function reloadConfs()
	{

		if (!array($this->items)) { return false; }
		if(!empty($this->items)) {
			foreach($this->items as $item) {
				$item->reloadValueFromConf();
			}
		}

		return true;
	}


	/**
	 * Create a new item
	 * the tagret is useful with hooks : that allow externals modules to add external items on good place
	 *
	 * @param string	$confKey 				the conf key used in database
	 * @param string	$targetItemKey    		target item used to place the new item beside
	 * @param bool		$insertAfterTarget		insert before or after target item ?
	 * @return FormExternalItem the new external item created
	 */
	public function newItem($confKey, $targetItemKey = false, $insertAfterTarget = false)
	{
		$item = new FormExternalItem($confKey);

		// set item rank if not defined as last item
		if (empty($item->rank)) {
			$item->rank = $this->getCurentItemMaxRank() + 1;
			$this->setItemMaxRank($item->rank); // set new max rank if needed
		}

		// try to get rank from target column, this will override item->rank
		if (!empty($targetItemKey)) {
			if (isset($this->items[$targetItemKey])) {
				$targetItem = $this->items[$targetItemKey];
				$item->rank = $targetItem->rank; // $targetItem->rank will be increase after
				if ($targetItem->rank >= 0 && $insertAfterTarget) {
					$item->rank++;
				}
			}

			// calc new rank for each item to make place for new item
			if(!empty($this->items)) {
				foreach($this->items as $fItem) {
					if($item->rank <= $fItem->rank) {
						$fItem->rank = $fItem->rank + 1;
						$this->setItemMaxRank($fItem->rank); // set new max rank if needed
					}
				}
			}
		}

		$this->items[$item->confKey] = $item;
		return $this->items[$item->confKey];
	}

	/**
	 * Sort items according to rank
	 *
	 * @return bool
	 */
	public function sortingItems()
	{
		// Sorting
		return uasort($this->items, array($this, 'itemSort'));
	}

	/**
	 * get max item rank of item
	 *
	 * @param bool $cache To use cache or not
	 * @return int
	 */
	public function getCurentItemMaxRank($cache = true)
	{
		if (empty($this->items)) {
			return 0;
		}

		if ($cache && $this->maxItemRank > 0) {
			return $this->maxItemRank;
		}

		$this->maxItemRank = 0;
		if(!empty($this->items)) {
			foreach($this->items as $item) {
				$this->maxItemRank = max($this->maxItemRank, $item->rank);
			}
		}

		return $this->maxItemRank;
	}


	/**
	 * set new max rank if needed
	 *
	 * @param 	int 		$rank 	the item rank
	 * @return 	int|void			new max rank
	 */
	public function setItemMaxRank($rank)
	{
		$this->maxItemRank = max($this->maxItemRank, $rank);
	}


	/**
	 * get item position rank from item key
	 *
	 * @param	string		$itemKey    	the item key
	 * @return	int         				rank on success and -1 on error
	 */
	public function getLineRank($itemKey)
	{
		if (!isset($this->items[$itemKey]->rank)) {
			return -1;
		}
		return  $this->items[$itemKey]->rank;
	}


	/**
	 *  uasort callback function to Sort params items
	 *
	 *  @param	FormExternalItem	$a  formExternal item
	 *  @param	FormExternalItem	$b  formExternal item
	 *  @return	int					Return compare result
	 */
	public function itemSort(FormExternalItem $a, FormExternalItem $b)
	{
		if (empty($a->rank)) {
			$a->rank = 0;
		}
		if (empty($b->rank)) {
			$b->rank = 0;
		}
		if ($a->rank == $b->rank) {
			return 0;
		}
		return ($a->rank < $b->rank) ? -1 : 1;
	}
}

/**
 * This class help to create item for class formExternal
 */
class FormExternalItem
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/** @var Translate */
	public $langs;

	/** @var int */
	public $entity;

	/** @var Form */
	public $form;

	/** @var string $confKey the conf key used in database */
	public $confKey;

	/** @var string|false $nameText  */
	public $nameText = false;

	/** @var string $helpText  */
	public $helpText = '';

	/** @var string $fieldValue  */
	public $fieldValue;

	/** @var array $fieldAttr  fields attribute only for compatible fields like input text */
	public $fieldAttr;

	/** @var bool|string set this var to override field output will override $fieldInputOverride and $fieldOutputOverride too */
	public $fieldOverride = false;

	/** @var bool|string set this var to override field input */
	public $fieldInputOverride = false;

	/** @var bool|string set this var to override field output */
	public $fieldOutputOverride = false;

	/** @var int $rank  */
	public $rank = 0;

	/**
	 * @var string $errors
	 */
	public $errors = array();

	public $TOptions = array();

	/**
	 * TODO each type must have setAs{type} method to help configuration
	 *   And set var as protected when its done configuration must be done by method
	 *   this is important for retrocompatibility of futures versions
	 * @var string $type  'string', 'textarea', 'category:'.Categorie::TYPE_CUSTOMER', 'emailtemplate', 'thirdparty_type'
	 */
	protected $type = 'string';

	public $enabled = 1;
	public $required = false;
	public $multiple = false;

	public $cssClass = '';
	public $element = '';

	/**
	 * Constructor
	 *
	 * @param string $confKey the conf key used in database
	 */
	public function __construct($confKey)
	{
		global $langs, $db, $conf, $form;
		$this->db = $db;

		if (!empty($form) && is_object($form) && get_class($form) == 'Form') { // the form class has a cache inside so I am using it to optimize
			$this->form = $form;
		} else {
			$this->form = new Form($this->db);
		}

		$this->langs = $langs;
		$this->entity = $conf->entity;

		$this->confKey = $confKey;
		$this->fieldValue = $conf->global->{$this->confKey};
	}

	/**
	 * reload conf value from databases
	 * @return null
	 */
	public function reloadValueFromConf()
	{
		global $conf;
		$this->fieldValue = $conf->global->{$this->confKey};
	}


	/**
	 * Save const value based on htdocs/core/actions_setmoduleoptions.inc.php
	 *	@return     int         			-1 if KO, 1 if OK
	 */
	public function saveConfValue()
	{
		// Modify constant only if key was posted (avoid resetting key to the null value)
		if ($this->type != 'title') {
			$result = dolibarr_set_const($this->db, $this->confKey, $this->fieldValue, 'chaine', 0, '', $this->entity);
			if ($result < 0) {
				return -1;
			} else {
				return 1;
			}
		}
	}


	/**
	 * Save const value based on htdocs/core/actions_setmoduleoptions.inc.php
	 *	@return     int         			-1 if KO, 0  nothing to do , 1 if OK
	 */
	public function setValueFromPost()
	{
		// Modify constant only if key was posted (avoid resetting key to the null value)
		if ($this->type != 'title') {
			if (preg_match('/category:/', $this->type)) {
				if (GETPOST($this->confKey, 'int') == '-1') {
					$val_const = '';
				} else {
					$val_const = GETPOST($this->confKey, 'int');
				}
			} else {
				$val_const = GETPOST($this->confKey, 'alpha');
			}

			// TODO add value check with class validate
			$this->fieldValue = $val_const;

			return 1;
		}

		return 0;
	}

	/**
	 * Get help text or generate it
	 * @return int|string
	 */
	public function getHelpText()
	{
		if (!empty($this->helpText)) { return $this->helpText; }
		return (($this->langs->trans($this->confKey . 'Tooltip') != $this->confKey . 'Tooltip') ? $this->langs->trans($this->confKey . 'Tooltip') : '');
	}

	/**
	 * Get field name text or generate it
	 * @return false|int|string
	 */
	public function getNameText()
	{
		if (!empty($this->nameText)) { return $this->nameText; }
		return (($this->langs->trans($this->confKey) != $this->confKey) ? $this->langs->trans($this->confKey) : $this->langs->trans('MissingTranslationForConfKey', $this->confKey));
	}

	/**
	 * generate input field
	 *
	 * @return bool|string
	 */
	public function generateInputField()
	{
		global $conf;

		if (!empty($this->fieldOverride)) {
			return $this->fieldOverride;
		}

		if (!empty($this->fieldInputOverride)) {
			return $this->fieldInputOverride;
		}

		$this->fieldAttr['name'] = $this->confKey;
		$this->fieldAttr['id'] = $this->element.'-'.$this->confKey;
		$this->fieldAttr['value'] = $this->fieldValue;

		$out = '';

		if ($this->type == 'title') {
			$out.= $this->generateOutputField(); // title have no input
		} elseif ($this->type == 'textarea') {
			$out.= $this->generateInputFieldTextarea();
		} elseif ($this->type == 'datetime') {
			$out.= $this->generateInputFieldDateTime();
		} elseif ($this->type== 'html') {
			$out.= $this->generateInputFieldHtml();
		}elseif ($this->type== 'select') {
			$out.= $this->generateInputFieldSelect();
		}elseif ($this->type== 'radio') {
			$out.= $this->generateInputFieldRadio();
		} elseif ($this->type == 'yesno') {
			$out.= $this->form->selectyesno($this->confKey, $this->fieldValue, 1, false,  0,  0, 'form-control');
		} elseif (preg_match('/emailtemplate:/', $this->type)) {
			$out.= $this->generateInputFieldEmailTemplate();
		} elseif (preg_match('/category:/', $this->type)) {
			$out.=$this->generateInputFieldCategories();
		} elseif (preg_match('/thirdparty_type/', $this->type)) {
			require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
			$formcompany = new FormCompany($this->db);
			$out.= $formcompany->selectProspectCustomerType($this->fieldValue, $this->confKey);
		} elseif ($this->type == 'securekey') {
			$out.= $this->generateInputFieldSecureKey();
		} elseif ($this->type == 'product') {
			if (!empty($conf->product->enabled) || !empty($conf->service->enabled)) {
				$selected = (empty($this->fieldValue) ? '' : $this->fieldValue);
				$out.= $this->form->select_produits($selected, $this->confKey, '', 0, 0, 1, 2, '', 0, array(), 0, '1', 0, $this->cssClass, 0, '', null, 1);
			}
		} else {
			if (empty($this->fieldAttr['class'])) { $this->fieldAttr['class'] = 'form-control '.(empty($this->cssClass) ? '' : $this->cssClass); }

			$out.= '<input '.($this->required ? 'required' : '').' '.FormExternal::generateAttributesStringFromArray($this->fieldAttr).' />';
		}

		return $out;
	}

	/**
	 * generate input field for textarea
	 * @return string
	 */
	public function generateInputFieldDateTime()
	{
		if (empty($this->fieldAttr['class'])) { $this->fieldAttr['class'] = 'form-control '.(empty($this->cssClass) ? '' : $this->cssClass); }
		$out = '<input '.($this->required ? 'required' : '').' class="form-control" type="date" name="'.$this->confKey.'" id="'.$this->element.'-'.$this->confKey.'"/>';
		$out .= '<input '.($this->required ? 'required' : '').' class="form-control" type="time" name="'.$this->confKey.'-time" id="'.$this->element.'-'.$this->confKey.'-time" />';
		return $out;
	}
	/**
	 * generate input field for textarea
	 * @return string
	 */
	public function generateInputFieldTextarea()
	{
		$out = '<textarea '.($this->required ? 'required' : '').' class="form-control" name="'.$this->confKey.'" id="'.$this->element.'-'.$this->confKey.'"  rows="10" >' . "\n";
		$out.= dol_htmlentities($this->fieldValue);
		$out.= "</textarea>\n";
		return $out;
	}
	/**
	 * generate input field for select
	 * @return string
	 */
	public function generateInputFieldSelect()
	{
		$out = '<select '.($this->required ? 'required' : '').' id="'.$this->element.'-'.$this->confKey.'" name="'.$this->confKey.((! empty($this->multiple)) ? '[]' : '').'" class="selectpicker form-control"  data-live-search="true" '.((! empty($this->multiple)) ? 'multiple' : '').'>';
		if(!$this->multiple) {
			$out .= '<option ';
			$out .= ' id="'.$this->element.'-'.$this->confKey.'-0" ';
			$out .= ' value="0" ';
			$out .= '>&nbsp;</option>';
		}
		if(! empty($this->TOptions)) {
			foreach($this->TOptions as $k => $val) {
				$out .= '<option ';
				$out .= ' id="'.$this->element.'-'.$this->confKey.'-'.$k.'" ';
				$out .= ' value="'.dol_htmlentities($k, ENT_QUOTES).'" ';
				$out .= '>'.dol_htmlentities($val, ENT_QUOTES).'</option>';
			}
		}
		$out .= '</select>';

		return $out;
	}
	/**
	 * generate input field for select
	 * @return string
	 */
	public function generateInputFieldRadio()
	{
		$out = '';
		if(!empty($this->TOptions)) {
			foreach($this->TOptions as $keyopt => $val) {
				$out .= '<input '.($this->required ? 'required' : '').' class="form-control" type="radio" name="'.$this->confKey.'" id="'.$this->element.'-'.$this->confKey.'" ';
				$out .= ' value="'.$keyopt.'"';
				$out .= ' id="'.$this->element.'-'.$this->confKey.'-'.$keyopt.'"';
				$out .= '/><label for="'.$this->element.'-'.$this->confKey.'-'.$keyopt.'">'.$val.'</label><br>';
			}
		}

		return $out;
	}
//	/**
//	 * generate input field for number
//	 * @return string
//	 */
//	public function generateInputFieldNumber()
//	{
//		$out = '<textarea '.($this->required ? 'required' : '').' class="form-control" name="'.$this->confKey.'" id="'.$this->element.'-'.$this->confKey.'"  rows="10" >' . "\n";
//		$out.= dol_htmlentities($this->fieldValue);
//		$out.= "</textarea>\n";
//		return $out;
//	}

	/**
	 * generate input field for html
	 *
	 * @return string
	 */
	public function generateInputFieldHtml() {
		global $conf;

		$out = '<textarea '.($this->required ? 'required' : '').' class="form-control" name="'.$this->confKey.'" id="'.$this->element.'-'.$this->confKey.'"  rows="10" >'."\n";
		$out .= dol_htmlentities($this->fieldValue);
		$out .= "</textarea>\n";
		if(! empty($conf->global->FCKEDITOR_ENABLE_TICKET)) {
			$out .= '<script>CKEDITOR.replace("'.$this->confKey.'");</script>';
		}

		return $out;
	}

	/**
	 * generate input field for categories
	 * @return string
	 */
	public function generateInputFieldCategories()
	{
		global $conf;
		require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
		$formother = new FormOther($this->db);

		$tmp = explode(':', $this->type);
		$out= img_picto('', 'category', 'class="pictofixedwidth"');
		$out.= $formother->select_categories($tmp[1],  $this->fieldValue, $this->confKey, 0, $this->langs->trans('CustomersProspectsCategoriesShort'));
		return $out;
	}

	/**
	 * generate input field for email template selector
	 * @return string
	 */
	public function generateInputFieldEmailTemplate()
	{
		global $conf, $user;
		$out = '';
		if (preg_match('/emailtemplate:/', $this->type)) {
			include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
			$formmail = new FormMail($this->db);

			$tmp = explode(':', $this->type);
			$nboftemplates = $formmail->fetchAllEMailTemplate($tmp[1], $user, null, 1); // We set lang=null to get in priority record with no lang
			$arrayOfMessageName = array();
			if (is_array($formmail->lines_model)) {
				foreach ($formmail->lines_model as $modelMail) {
					$moreonlabel = '';
					if (!empty($arrayOfMessageName[$modelMail->label])) {
						$moreonlabel = ' <span class="opacitymedium">(' . $this->langs->trans("SeveralLangugeVariatFound") . ')</span>';
					}
					// The 'label' is the key that is unique if we exclude the language
					$arrayOfMessageName[$modelMail->id] = $this->langs->trans(preg_replace('/\(|\)/', '', $modelMail->label)) . $moreonlabel;
				}
			}
			$out .= $this->form->selectarray($this->confKey, $arrayOfMessageName, $this->fieldValue, 'None', 0, 0, '', 0, 0, 0, '', '', 1);
		}

		return $out;
	}


	/**
	 * generate input field for secure key
	 * @return string
	 */
	public function generateInputFieldSecureKey()
	{
		global $conf;
		$out = '<input required="required" type="text" class="flat" id="'.$this->confKey.'" name="'.$this->confKey.'" value="'.(GETPOST($this->confKey, 'alpha') ?GETPOST($this->confKey, 'alpha') : $this->fieldValue).'" size="40">';
		if (!empty($conf->use_javascript_ajax)) {
			$out.= '&nbsp;'.img_picto($this->langs->trans('Generate'), 'refresh', 'id="generate_token'.$this->confKey.'" class="linkobject"');
		}
		if (!empty($conf->use_javascript_ajax)) {
			$out .= "\n" . '<script type="text/javascript">';
			$out .= '$(document).ready(function () {
                        $("#generate_token' . $this->confKey . '").click(function() {
                	        $.get( "' . DOL_URL_ROOT . '/core/ajax/security.php", {
                		      action: \'getrandompassword\',
                		      generic: true
    				        },
    				        function(token) {
    					       $("#' . $this->confKey . '").val(token);
            				});
                         });
                    });';
			$out .= '</script>';
		}
		return $out;
	}

	/**
	 * get the type : used for old module builder external conf style conversion and tests
	 * because this two class will quickly evolve it's important to not set or get directly $this->type (will be protected) so this method exist
	 * to be sure we can manage evolution easily
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * set the type from string : used for old module builder external conf style conversion and tests
	 * because this two class will quickly evolve it's important to not set directly $this->type (will be protected) so this method exist
	 * to be sure we can manage evolution easily
	 * @param string $type possible values based on old module builder external : 'string', 'textarea', 'category:'.Categorie::TYPE_CUSTOMER', 'emailtemplate', 'thirdparty_type'
	 * @deprecated yes this setTypeFromTypeString came deprecated because it exists only for manage external convertion
	 * @return bool
	 */
	public function setTypeFromTypeString($type)
	{
		$this->type = $type;
		return true;
	}

	/**
	 * Add error
	 * @param array|string $errors the error text
	 * @return null
	 */
	public function setErrors($errors)
	{
		if (is_array($errors)) {
			if (!empty($errors)) {
				foreach ($errors as $error) {
					$this->setErrors($error);
				}
			}
		} elseif (!empty($errors)) {
			$this->errors[] = $errors;
		}
	}

	/**
	 * @return bool|string Generate the output html for this item
	 */
	public function generateOutputField()
	{
		global $conf, $user;

		if (!empty($this->fieldOverride)) {
			return $this->fieldOverride;
		}

		if (!empty($this->fieldOutputOverride)) {
			return $this->fieldOutputOverride;
		}

		$out = '';

		if ($this->type == 'title') {
			// nothing to do
		} elseif ($this->type == 'textarea') {
			$out.= dol_nl2br($this->fieldValue);
		} elseif ($this->type== 'html') {
			$out.=  $this->fieldValue;
		} elseif ($this->type == 'yesno') {
			$out.= ajax_constantonoff($this->confKey);
		} elseif (preg_match('/emailtemplate:/', $this->type)) {
			include_once DOL_DOCUMENT_ROOT . '/core/class/html.formmail.class.php';
			$formmail = new FormMail($this->db);

			$tmp = explode(':', $this->type);

			$template = $formmail->getEMailTemplate($this->db, $tmp[1], $user, $this->langs, $this->fieldValue);
			if ($template<0) {
				$this->setErrors($formmail->errors);
			}
			$out.= $this->langs->trans($template->label);
		} elseif (preg_match('/category:/', $this->type)) {
			$c = new Categorie($this->db);
			$result = $c->fetch($this->fieldValue);
			if ($result < 0) {
				$this->setErrors($c->errors);
			}
			$ways = $c->print_all_ways(' &gt;&gt; ', 'none', 0, 1); // $ways[0] = "ccc2 >> ccc2a >> ccc2a1" with html formated text
			$toprint = array();
			foreach ($ways as $way) {
				$toprint[] = '<li class="select2-search-choice-dolibarr noborderoncategories"' . ($c->color ? ' style="background: #' . $c->color . ';"' : ' style="background: #bbb"') . '>' . $way . '</li>';
			}
			$out.='<div class="select2-container-multi-dolibarr" style="width: 90%;"><ul class="select2-choices-dolibarr">' . implode(' ', $toprint) . '</ul></div>';
		} elseif (preg_match('/thirdparty_type/', $this->type)) {
			if ($this->fieldValue==2) {
				$out.= $this->langs->trans("Prospect");
			} elseif ($this->fieldValue==3) {
				$out.= $this->langs->trans("ProspectCustomer");
			} elseif ($this->fieldValue==1) {
				$out.= $this->langs->trans("Customer");
			} elseif ($this->fieldValue==0) {
				$out.= $this->langs->trans("NorProspectNorCustomer");
			}
		} elseif ($this->type == 'product') {
			$product = new Product($this->db);
			$resprod = $product->fetch($this->fieldValue);
			if ($resprod > 0) {
				$out.= $product->ref;
			} elseif ($resprod < 0) {
				$this->setErrors($product->errors);
			}
		} else {
			$out.= $this->fieldValue;
		}

		return $out;
	}


	/*
	 * METHODS FOR SETTING DISPLAY TYPE
	 */

	/**
	 * Set type of input as string
	 * @return self
	 */
	public function setAsString()
	{
		$this->type = 'string';
		return $this;
	}
	/**
	 * Set type of input as string
	 * @return self
	 */
	public function setAsDateTime()
	{
		$this->type = 'datetime';
		return $this;
	}

	/**
	 * Set type of input as string
	 *
	 * @param array $TOptions
	 * @return self
	 */
	public function setAsRadio($TOptions = array())
	{
		$this->TOptions = $TOptions;
		$this->type = 'radio';
		return $this;
	}
	/**
	 * Set type of input as string
	 *
	 * @param array $TOptions
	 * @return self
	 */
	public function setAsSelect($TOptions = array())
	{
		$this->TOptions = $TOptions;
		$this->type = 'select';
		return $this;
	}

	/**
	 * Set type of input as textarea
	 * @return self
	 */
	public function setAsTextarea()
	{
		$this->type = 'textarea';
		return $this;
	}

	/**
	 * Set type of input as html editor
	 * @return self
	 */
	public function setAsHtml()
	{
		$this->type = 'html';
		return $this;
	}

	/**
	 * Set type of input as html editor
	 * @return self
	 */
	public function setAsSeparator()
	{
		$this->type = 'separator';
		return $this;
	}
	/**
	 * Set type of input as html editor
	 * @return self
	 */
	public function setAsHelp()
	{
		$this->type = 'help';
		return $this;
	}
	/**
	 * Set input required
	 * @return self
	 */
	public function setAsRequired()
	{
		$this->required = true;
		return $this;
	}

	/**
	 * Set type of input as emailtemplate selector
	 * @param string $templateType email template type
	 * @return self
	 */
	public function setAsEmailTemplate($templateType)
	{
		$this->type = 'emailtemplate:'.$templateType;
		return $this;
	}

	/**
	 * Set type of input as thirdparty_type selector
	 * @return self
	 */
	public function setAsThirdpartyType()
	{
		$this->type = 'thirdparty_type';
		return $this;
	}

	/**
	 * Set type of input as Yes
	 * @return self
	 */
	public function setAsYesNo()
	{
		$this->type = 'yesno';
		return $this;
	}

	/**
	 * Set type of input as secure key
	 * @return self
	 */
	public function setAsSecureKey()
	{
		$this->type = 'securekey';
		return $this;
	}

	/**
	 * Set type of input as product
	 * @return self
	 */
	public function setAsProduct()
	{
		$this->type = 'product';
		return $this;
	}

	/**
	 * Set type of input as a category selector
	 * TODO add default value
	 * @param	int		$catType		Type of category ('customer', 'supplier', 'contact', 'product', 'member'). Old mode (0, 1, 2, ...) is deprecated.
	 * @return self
	 */
	public function setAsCategory($catType)
	{
		$this->type = 'category:'.$catType;
		return $this;
	}

	/**
	 * Set type of input as a simple title
	 * no data to store
	 * @return self
	 */
	public function setAsTitle()
	{
		$this->type = 'title';
		return $this;
	}
}
