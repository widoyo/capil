<?php

$username = "capil";
$password = "capil";
$dbname = "capildb";

$dsn = "pgsql:dbname=$dbname;host=localhost;user=$username;password=$password;";

require "vendor/autoload.php";
require "vendor/NotORM.php";


$pdo = new PDO( $dsn );
$db = new NotORM($pdo);

$app = new \Slim\Slim( array(
    'template.path' => '../templates',
    'view' => new \Slim\Views\Twig()
));


$app->get('/', function(  ) use( $app, $db ) {
    $penduduk_count = $db->penduduk->count("*");
    $samples_kode = array();
    foreach ($db->penduduk->select("kode")->limit(3) as $kodes) {
        $samples_kode[] = array(
            "kode" => $kodes["kode"]
        );
    }
    $dashboard = array(
        "penduduk_count" => $penduduk_count,
        "kodes" => $samples_kode
    );
    $app->render('index.html', $dashboard);
});

$app->get('/api/warga', function() use( $app, $db ) {
    $paramKode = $app->request->get('kode');
    $count = $db->penduduk->count("*");
    if ($paramKode) {
        $q = $db->penduduk()->select("*")->where("kode LIKE ?", "%$paramKode%");
    } else {
        $q = $db->penduduk()->select("*")->limit(5);
    }
    $wargas = array();
    foreach ( $q as $w ){
        $wargas[] = array(
            "kode" => $w['kode'],
            "nama" => $w['nama'],
            "tg_lahir" => $w['tg_lahir'],
            "kelamin" => $w['kelamin']
        );
    }

    $app->response()->header("Content-Type", "application/json");
    echo json_encode( array('warga' => $wargas) );
});

$app->get('/api/warga/sebaran', function() use( $app, $db ) {
    $count = $db->penduduk->count("*");
    $pria = $db->penduduk->where('kelamin', 'Laki-laki')->count();
    $wanita = $db->penduduk->where('kelamin', 'Perempuan')->count();
    $app->response()->header("Content-Type", "application/json");
    echo json_encode( array(
        'total' => $count,
        'pria' => $pria,
        'wanita' => $wanita
    ) );
});

$app->get('/warga', function() use( $app, $db ) {
    $paramKode = $app->request->get('kode');
    $warga = array();
    if ($paramKode) {
        $q = $db->penduduk->where("kode LIKE ?", "%$paramKode%");
    } else {
        $q = $db->penduduk->select("*")->limit(300);
    }
    foreach ($q as $person) {
        $warga[] = array(
            "id" => $person['id'],
            "kode" => $person['kode'],
            "nama" => $person['nama'],
            "tg_lahir" => $person['tg_lahir'],
            "kelamin" => $person['kelamin'],
            "rwrt" => $person['rwrt']
        );
    }
    $app->render('warga/index.html', array(
        "wargas" => $warga,
        "kode" => $paramKode
    ));
});

$app->run();
?>
