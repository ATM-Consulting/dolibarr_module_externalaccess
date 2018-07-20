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
	
	$out.= '
		<div class="modal fade" id="'.$htmlid.'" >
			<div class="modal-dialog">
				<div class="modal-content">
					<form action="'.$action.'" method="POST">
						<input type="hidden" name="action" value="'.$doAction.'" />
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
							<button type="button" class="btn btn-danger mr-auto" data-dismiss="modal">'.$langs->trans('Cancel').'</button>
							<button type="submit" class="btn btn-primary ">'.$langs->trans('Confirm').'</button>
						</div>
					</form>
				</div>
			</div>
		</div>';
	
	return $out;
}

function getEaNavbar($url_back='', $url_add='')
{
	global $langs;
	
	$out = '<nav class="navbar navbar-light justify-content-between mb-4 px-0">';
	
	$out.= '<a class="navbar-brand" href="'.(empty($url_back) ? '#" onclick="window.history.go(-1)' : $url_back).'"><i class="fa fa-chevron-left"></i> '.$langs->trans('EaBack').'</a>';
	
	if (!empty($url_add)) $out.= '<a class="btn btn-outline-primary my-2 my-sm-0" href="'.$url_add.'"><i class="fa fa-plus"></i></a>';
	
	$out.= '</nav>';
	
	return $out;
}
