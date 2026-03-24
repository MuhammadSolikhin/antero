<?php
session_start();
$_SESSION['user_id'] = 2; // pelatih id
$_SESSION['role'] = 'pelatih';
require 'students_list.php';
