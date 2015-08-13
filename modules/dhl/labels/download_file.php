<?php
$fileInfo = explode('/', $_GET['file']);
$file_name = $fileInfo[count($fileInfo)-1];
$id_order = $fileInfo[count($fileInfo)-2];

if(substr($file_name, -4, 4) == '.pdf' || substr($file_name, -4, 4) == '.xml'){
    header('location: '.$_GET['file']);
    die;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="'.basename($file_name).'"');
header('Content-Transfer-Encoding: Binary');

ob_clean();
flush(); 
readfile(realpath(dirname(__FILE__)).'/'.$id_order.'/'.$file_name);

exit;
?>