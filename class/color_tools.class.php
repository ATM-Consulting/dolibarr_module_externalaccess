<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    class/color_tools.class.php
 * \ingroup gantt
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class ColorTools
 */
if(!class_exists('ColorTools'))
{

    class ColorTools
    {
    	
    	/**
    	 * Constructor
    	 */
    	public function __construct()
    	{
    	}
    
    	static function validate_color($color, $allow_white = false)
    	{
    		
    		if(!$allow_white && ($color === '#fff' || $color === '#ffffff') ) return false;
    		
    		if(preg_match('/^#[a-f0-9]{6}$/i', $color)) //hex color is valid
    		{
    			return true;
    		}
    		return false;
    	}
    	
    	static function imagehue(&$image, $angle) {
    		if($angle % 360 == 0) return;
    		$width = imagesx($image);
    		$height = imagesy($image);
    		
    		for($x = 0; $x < $width; $x++) {
    			for($y = 0; $y < $height; $y++) {
    				$rgb = imagecolorat($image, $x, $y);
    				$r = ($rgb >> 16) & 0xFF;
    				$g = ($rgb >> 8) & 0xFF;
    				$b = $rgb & 0xFF;
    				$alpha = ($rgb & 0x7F000000) >> 24;
    				list($h, $s, $l) = rgb2hsl($r, $g, $b);
    				$h += $angle / 360;
    				if($h > 1) $h--;
    				list($r, $g, $b) = hsl2rgb($h, $s, $l);
    				imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, $r, $g, $b, $alpha));
    			}
    		}
    	}
    	
    	static function adjustBrightness($hex, $steps) {
    		// Steps should be between -255 and 255. Negative = darker, positive = lighter
    		$steps = max(-255, min(255, $steps));
    		
    		$TData=array(); $TWS=array(); $TLink=array();
    		
    		// Normalize into a six character long hex string
    		$hex = str_replace('#', '', $hex);
    		if (strlen($hex) == 3) {
    			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
    		}
    		
    		// Split into three parts: R, G and B
    		$color_parts = str_split($hex, 2);
    		$return = '#';
    		
    		foreach ($color_parts as $color) {
    			$color   = hexdec($color); // Convert to decimal
    			$color   = max(0,min(255,$color + $steps)); // Adjust color
    			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    		}
    		
    		return $return;
    	}
    }
}