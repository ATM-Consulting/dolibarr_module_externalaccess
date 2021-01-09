<?php

require_once DOL_DOCUMENT_ROOT."/core/class/html.formticket.class.php";

class ExternalFormTicket extends FormTicket
{
	public function showFilesForm()
	{
		global $conf, $langs;

		$out = '';

		// Define list of attached files
		$listofpaths = array();
		$listofnames = array();
		$listofmimes = array();
		$keytoavoidconflict = empty($this->trackid) ? '' : '-'.$this->trackid; // this->trackid must be defined

		if (!empty($_SESSION["listofpaths".$keytoavoidconflict])) $listofpaths = explode(';', $_SESSION["listofpaths".$keytoavoidconflict]);
		if (!empty($_SESSION["listofnames".$keytoavoidconflict])) $listofnames = explode(';', $_SESSION["listofnames".$keytoavoidconflict]);
		if (!empty($_SESSION["listofmimes".$keytoavoidconflict])) $listofmimes = explode(';', $_SESSION["listofmimes".$keytoavoidconflict]);

		// Attached files
		if (!empty($this->withfile)) {
			$out .= '<input type="hidden" class="removedfilehidden" name="removedfile" value="">'."\n";
			$out .= '<script type="text/javascript" language="javascript">';
			$out .= 'jQuery(document).ready(function () {';
			$out .= '    jQuery(".removedfile").click(function() {';
			$out .= '        jQuery(".removedfilehidden").val(jQuery(this).val());';
			$out .= '    });';
			$out .= '})';
			$out .= '</script>'."\n";
			if (count($listofpaths)) {
				foreach ($listofpaths as $key => $val) {
					$out .= '<div id="attachfile_'.$key.'">';
					$out .= img_mime($listofnames[$key]).' '.$listofnames[$key]; // return font awesome
					if (!$this->withfilereadonly) {
						$out .= ' <button type="submit" value="'.($key + 1).'" class="removedfile reposition" id="removedfile_'.$key.'" name="removedfile_'.$key.'" /><i class="fa fa-trash"></i></button>';
					}
				}
			} else {
				$out .= $langs->trans("NoAttachedFiles").'<br>';
			}
			if ($this->withfile == 2) { // Can add other files
				$out .= '<input name="addedfile" type="file" class="fileToUpload btn" id="fileToUpload"/>';
				$out .= '<button type="submit" class="btn" id="add-comment-file" name="action" value="add-comment-file">'.$langs->trans("AddFileToComment").'</button>';
			}

		}
		return $out;
	}
}

