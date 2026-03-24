<?php
$html = file_get_contents('http://localhost/prestasi/coach/test_modal_web.php');
file_put_contents('out.html', $html);
