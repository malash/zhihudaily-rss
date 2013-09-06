<?php
$file = 'data.xml';
if (!file_exists($file)) {
	header('HTTP/1.1 404 Not Found');
} else {
	header('Content-type: text/xml; charset=utf-8');
	@readfile($file);
}
?>