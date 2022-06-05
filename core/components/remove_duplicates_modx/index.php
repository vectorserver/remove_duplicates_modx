<?php

require_once MODX_CORE_PATH.'components/remove_duplicates_modx/model/remove_duplicates_modx.php';


$remove_duplicates_modx = new remove_duplicates_modx($modx);
return $remove_duplicates_modx->initialize('mgr');

