<?php
require("../vendor/autoload.php");
$openapi = \OpenApi\scan('/media/sysadmin/Media2/amal/rest-api-project/app');
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();