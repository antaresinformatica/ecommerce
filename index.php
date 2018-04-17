<?php 
session_start();
require_once("vendor/autoload.php");
use \Slim\Slim;


$app = new Slim();
$app->config('debug', true);

require_once("functions.php");
// como este arquivo estava ficando muito grande, vamos dividir
// entao quando usa o comando require_once(nome), vai incluir neste arquivo
require_once("site.php"); // inclui esse arquivo 

require_once("admin.php");

require_once("admin-users.php");

require_once("admin-categories.php");

require_once("admin-products.php");

require_once("admin-orders.php");

$app->run();

?>

