<?php

define('INC_FROM_SCRIPT', 1);
require __DIR__ .'/../config.php';

$action = GETPOST('action', 'alpha');

if($action === 'getlogo')
{
    if (! empty($mysoc->logo_small) && is_readable($conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small))
    {
        $logoFile = $conf->mycompany->dir_output.'/logos/thumbs/'.$mysoc->logo_small;
    }
    elseif (! empty($mysoc->logo) && is_readable($conf->mycompany->dir_output.'/logos/'.$mysoc->logo))
    {
        $logoFile = $conf->mycompany->dir_output.'/logos/'.$mysoc->logo;
    }

    if(!empty($logoFile))
    {
        $type=dol_mimetype($logoFile);
        if ($type)
        {
            top_httphead($type);
            header('Content-Disposition: inline; filename="'.basename($logoFile).'"');
        }
        else
        {
            top_httphead('image/png');
            header('Content-Disposition: inline; filename="'.basename($logoFile).'"');
        }


        $fullpath_original_file_osencoded=dol_osencode($logoFile);

        readfile($fullpath_original_file_osencoded);
    }
}
