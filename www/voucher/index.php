<?php
require_once '/var/www/vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader('../templates');
$twig = new \Twig\Environment($loader);

echo $twig->render('voucher.twig', [
    'title' => 'Hääd und Wolf OÜ - Innovative Email Security'
]);
?>

