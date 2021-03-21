<?php 
require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ .'/../src/config.inc.php';

$stats = new \VirtManStats\VirtManStats($config);
$stats->connectNodes();
$data = $stats->gatherNodeData();
print_r($data);

