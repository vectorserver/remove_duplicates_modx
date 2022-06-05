<?php
/* @global $modx*/
define('MODX_API_MODE', true);

require '../../../../index.php';


$modx->getService('error','error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_FATAL);

require_once MODX_CORE_PATH.'components/remove_duplicates_modx/model/remove_duplicates_modx.php';


$remove_duplicates_modx = new remove_duplicates_modx($modx);

$action = @$_REQUEST['action'];


$data = array();
if ($action == 'list') $data = $remove_duplicates_modx->getDuplicates();
if ($action == 'work_res') $data = $remove_duplicates_modx->updateResources();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);