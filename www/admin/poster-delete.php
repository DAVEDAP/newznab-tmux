<?php
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'smarty.php';


use app\models\MultigroupPosters;

$page = new AdminPage();

if (isset($_GET['id']))
{
	MultigroupPosters::remove(['id' => $_GET['id']]);
}

if (isset($_GET['from']))
	$referrer = $_GET['from'];
else
	$referrer = $_SERVER['HTTP_REFERER'];
header("Location: " . $referrer);