<?php
if(!$_P){exit;}

$channel     = (int)$_P['channel'];
$versionCode = (int)$_P['versionCode'];
$data = Version::getValue($channel);
CMD(200,$data);
