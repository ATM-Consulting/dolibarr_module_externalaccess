<?php if (! defined('ISLOADEDBYSTEELSHEET')) die('Must be call by steelsheet'); ?>
/* <style type="text/css" > */

/*
 * STATUS BADGES
 */
<?php


// text color
$textSuccess   = '#28a745';
$colorblind_deuteranopes_textSuccess = '#37de5d';
$textWarning   = '#a37c0d'; // See $badgeWarning
$textDanger    = '#9f4705'; // See $badgeDanger
$colorblind_deuteranopes_textWarning = $textWarning; // currently not tested with a color blind people so use default color


// Badges colors
$badgePrimary   = '#007bff';
$badgeSecondary = '#cccccc';
$badgeSuccess   = '#55a580';
$badgeWarning   = '#a37c0d'; // See $textDanger bc9526
$badgeDanger    = '#9f4705'; // See $textDanger
$badgeInfo      = '#aaaabb';
$badgeDark      = '#343a40';
$badgeLight     = '#f8f9fa';

// badge color ajustement for color blind
$colorblind_deuteranopes_badgeSuccess   = '#37de5d'; //! text color black
$colorblind_deuteranopes_badgeSuccess_textColor7 = '#000';
$colorblind_deuteranopes_badgeWarning   = '#e4e411';

/* default color for status : After a quick check, somme status can have oposite function according to objects
*  So this badges status uses default value according to theme eldy status img
*  TODO: use color definition vars above for define badges color status X -> exemple $badgeStatusValidate, $badgeStatusClosed, $badgeStatusActive ....
*/
$badgeStatus0 = '#cbd3d3';
$badgeStatus1 = '#bc9526';
$badgeStatus2 = '#e6f0f0';
$badgeStatus3 = '#bca52b';
$badgeStatus4 = '#55a580'; // Color ok
$badgeStatus5 = '#cad2d2';
$badgeStatus6 = '#cad2d2';
$badgeStatus7 = '#baa32b';
$badgeStatus8 = '#993013';
$badgeStatus9 = '#e7f0f0';

// status color ajustement for color blind
$colorblind_deuteranopes_badgeStatus4 = $colorblind_deuteranopes_badgeStatus7 = $colorblind_deuteranopes_badgeSuccess; //! text color black
$colorblind_deuteranopes_badgeStatus_textColor4 = $colorblind_deuteranopes_badgeStatus_textColor7 = '#000';
$colorblind_deuteranopes_badgeStatus1 = $colorblind_deuteranopes_badgeWarning;
$colorblind_deuteranopes_badgeStatus_textColor1 = '#000';

?>

.badge-status {
	font-size: 1em;
	padding: .19em .35em;			/* more than 0.19 generate a change into heigth of lines */
}

<?php

	for ($i = 0; $i <= 9; $i++) {
	/* Default Status */
	_createStatusBadgeCss($i, '', "STATUS".$i);

	// create status for accessibility
	_createStatusBadgeCss($i, 'colorblind_deuteranopes_', "COLORBLIND STATUS".$i, 'body[class*="colorblind-"] ');
}

/**
 * Create status badge
 *
 * @param string $statusName name of status
 * @param string $statusVarNamePrefix a prefix for var ${$statusVarNamePrefix.'badgeStatus'.$statusName}
 * @param string $commentLabel a comment label
 * @param string $cssPrefix a css prefix
 * @return void
 */
function _createStatusBadgeCss($statusName, $statusVarNamePrefix = '', $commentLabel = '', $cssPrefix = '')
{

	global ${$statusVarNamePrefix.'badgeStatus'.$statusName}, ${$statusVarNamePrefix.'badgeStatus_textColor'.$statusName};

	if (!empty(${$statusVarNamePrefix.'badgeStatus'.$statusName})) {
		print "\n/* ".strtoupper($commentLabel)." */\n";
		$thisBadgeBackgroundColor = $thisBadgeBorderColor = ${$statusVarNamePrefix.'badgeStatus'.$statusName};

		$TBadgeBorderOnly = array(0, 3, 5, 7);
		$thisBadgeTextColor = colorIsLight(${$statusVarNamePrefix.'badgeStatus'.$statusName}) ? '#212529' : '#ffffff';

		if (!empty(${$statusVarNamePrefix.'badgeStatus_textColor'.$statusName})) {
			$thisBadgeTextColor = ${$statusVarNamePrefix.'badgeStatus_textColor'.$statusName};
		}

		if (in_array($statusName, $TBadgeBorderOnly)) {
			$thisBadgeTextColor = '#212529';
			$thisBadgeBackgroundColor = "#fff";
		}

		if (in_array($statusName, array(0, 5, 9))) $thisBadgeTextColor = '#999999';
		if (in_array($statusName, array(6))) $thisBadgeTextColor = '#777777';

		print $cssPrefix.".badge-status".$statusName." {\n";
		print "        color: ".$thisBadgeTextColor." !important;\n";
		if (in_array($statusName, $TBadgeBorderOnly)) {
			print "        border-color: ".$thisBadgeBorderColor.";\n";
		}
		print "        background-color: ".$thisBadgeBackgroundColor.";\n";
		print "}\n";

		print $cssPrefix.".font-status".$statusName." {\n";
		print "        color: ".$thisBadgeBackgroundColor." !important;\n";
		print "}\n";

		print $cssPrefix.".badge-status".$statusName.".focus, ".$cssPrefix.".badge-status".$statusName.":focus {\n";
		print "    outline: 0;\n";
		print "    box-shadow: 0 0 0 0.2rem ".colorHexToRgb($thisBadgeBackgroundColor, 0.5).";\n";
		print "}\n";

		print $cssPrefix.".badge-status".$statusName.":focus, ".$cssPrefix.".badge-status".$statusName.":hover {\n";
		print "    color: ".$thisBadgeTextColor." !important;\n";
		//print "    background-color: " . colorDarker($thisBadgeBackgroundColor, 10) . ";\n";
		if (in_array($statusName, $TBadgeBorderOnly)) {
			print "        border-color: ".colorDarker($thisBadgeBorderColor, 10).";\n";
		}
		print "}\n";
	}
}
