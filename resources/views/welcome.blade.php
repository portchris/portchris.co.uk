<?php 
$webroot = dirname(__FILE__) . '/../../../';
if (file_exists($webroot . 'dist/index.html')) {
	require_once($webroot . 'dist/index.html');
} else {
	require_once($webroot . 'src/index.html');
}
?>
