<?php

use CodeIgniter\Router\RouteCollection;

/**
* @var RouteCollection $routes
*/
$routes->setAutoRoute(false);

// $routes->options('(:any)', '', ['filter' => 'cors']);
$routes->options('(:any)', 'ApiController::optionsTest', ['filter' => 'cors']);


$routes->get('/', 'Home::index');

$routes->group('api', function ($routes) {
    $routes->group('ecole', function($routes) {
        $routes->match(['get', 'post'], 'getAll', 'ApiController::getEcoles');
        $routes->match(['get', 'post'], 'create', 'ApiController::createEcole');
        $routes->delete('delete/(:segment)', 'ApiController::deleteEcole/$1');
        $routes->put('update/(:segment)', 'ApiController::updateEcole/$1');
    });
    $routes->group('eleve', function($routes) {
        $routes->match(['get', 'post'], 'getAll', 'ApiController::getEleves');
        $routes->match(['get', 'post'], 'create', 'ApiController::createEleve');
        $routes->delete('delete/(:segment)', 'ApiController::deleteEleve/$1');
        $routes->put('update/(:segment)', 'ApiController::updateEleve/$1');
    });
    $routes->group('matiere', function($routes) {
        $routes->match(['get', 'post'], 'getAll', 'ApiController::getMatieres');
        $routes->match(['get', 'post'], 'create', 'ApiController::createMatiere');
        $routes->delete('delete/(:segment)', 'ApiController::deleteMatiere/$1');
        $routes->put('update/(:segment)', 'ApiController::updateMatiere/$1');
    });
    $routes->group('note', function($routes) {
        $routes->match(['get', 'post'], 'getAll', 'ApiController::getNotes');
        $routes->match(['get', 'post'], 'create', 'ApiController::createNote');
        $routes->delete('delete/(:segment)/(:segment)', 'ApiController::deleteNote/$1/$2');
        $routes->put('update/(:segment)', 'ApiController::updateNote/$1');
    });
    $routes->match(['get', 'post'], 'result', 'ApiController::getResults');
    $routes->match(['get', 'post'], 'bulletin/(:segment)', 'ApiController::generatePdf/$1');
});

?>