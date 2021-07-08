<?php


require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';



if (!function_exists('colorAdjustBrightness')) {
	/**
	 * @param string $hex Color in hex ('#AA1122' or 'AA1122' or '#a12' or 'a12')
	 * @param integer $steps Step/offset added to each color component. It should be between -255 and 255. Negative = darker, positive = lighter
	 * @return string                New color with format '#AA1122'
	 * @see colorAgressiveness()
	 */
	function colorAdjustBrightness($hex, $steps)
	{
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color = hexdec($color); // Convert to decimal
			$color = max(0, min(255, $color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
}


if (!function_exists('colorDarker')) {
	/**
	 * @param string $hex color in hex
	 * @param integer $percent 0 to 100
	 * @return string
	 */
	function colorDarker($hex, $percent)
	{
		$steps = intval(255 * $percent / 100) * -1;
		return colorAdjustBrightness($hex, $steps);
	}
}


if (!function_exists('colorLighten')) {
	/**
	 * @param string $hex color in hex
	 * @param integer $percent 0 to 100
	 * @return string
	 */
	function colorLighten($hex, $percent)
	{
		$steps = intval(255 * $percent / 100);
		return colorAdjustBrightness($hex, $steps);
	}
}


if (!function_exists('colorHexToRgb')) {
	/**
	 * @param string $hex color in hex
	 * @param float $alpha 0 to 1 to add alpha channel
	 * @param bool $returnArray true=return an array instead, false=return string
	 * @return string|array                String or array
	 */
	function colorHexToRgb($hex, $alpha = false, $returnArray = false)
	{
		$string = '';
		$hex = str_replace('#', '', $hex);
		$length = strlen($hex);
		$rgb = array();
		$rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
		$rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
		$rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
		if ($alpha !== false) {
			$rgb['a'] = floatval($alpha);
			$string = 'rgba(' . implode(',', $rgb) . ')';
		} else {
			$string = 'rgb(' . implode(',', $rgb) . ')';
		}

		if ($returnArray) {
			return $rgb;
		} else {
			return $string;
		}
	}
}
