<?php
namespace OnPhpId\IndonesiaPostalCode;

use Slim\App;
require __DIR__ .'/../vendor/autoload.php';

return (function() : App {
        require __DIR__ .'/../app/Middleware.php';
        require __DIR__ .'/../app/Routes.php';
        return $this;
    })
    ->call(new App(require __DIR__ .'/../app/Container.php'))
    ->run();
