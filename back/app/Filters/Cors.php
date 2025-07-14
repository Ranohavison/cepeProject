<?php
namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class Cors implements FilterInterface {
    public function before(RequestInterface $request, $arguments = null) {
        $response = service('response');

        // Définition des headers CORS
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->setHeader('Access-Control-Allow-Credentials', 'true');

        // header('Access-Control-Allow-Origin : *');
        // header('Access-Control-Allow-Methods : GET, POST, OPTIONS, PUT, DELETE');
        // header('Access-Control-Allow-Headers : Content-Type, Authorization');
        // header('Access-Control-Allow-Credentials : true');

        // Gestion des requêtes préflight OPTIONS
        if ($request->getMethod() === 'options') {
            return $response->setStatusCode(200);
            // exit(0);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {
        return $response;
    }
}
?>
