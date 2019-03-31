<?php
declare(strict_types=1);

namespace OnPhpId\IndonesiaPostalCode;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Http\Environment;
use Slim\Http\Uri;

/**
 * @var \Slim\App $this
 */
$this->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $uri = $request->getUri();
    /**
     * @var Environment $environment
     */
    $environment = $this['environment'];
    /* ------------------
     * FIX PATH FOR INDEX
     */
    $fileName   = $environment->get('SCRIPT_FILENAME');
    $scriptName = $environment->get('SCRIPT_NAME');
    $reqUri     = $environment->get('REQUEST_URI');
    if ($uri instanceof Uri && is_string($scriptName) && is_string($reqUri)) {
        // when it run on php -S
        if (is_string($fileName)
            && ($scriptName === '/' || $scriptName === $reqUri)
        ) {
            $environment['SCRIPT_NAME'] = '/'.basename($fileName);
            $environment['PHP_SELF'] = $environment['SCRIPT_NAME'];
            $request = $request->withUri($uri->createFromEnvironment($environment));
        } elseif ($environment->get('SCRIPT_NAME') === $uri->getBasePath()) {
            $environment['SCRIPT_NAME'] = dirname($environment->get('SCRIPT_NAME'));
            $request = $request->withUri($uri->createFromEnvironment($environment));
        }
    }

    // to json response
    $response = $response
        ->withHeader('Content-Type', 'application/json; charset-utf8')
        // add robots preventional
        ->withHeader('X-Robots-Tag', 'noindex, noydir, nofollow, noarchive,noodp');
    // override container request
    if (isset($this['request'])) {
        unset($this['request']);
        $this['request'] = $request;
    }

    // override container response
    if (isset($this['response'])) {
        unset($this['response']);
        $this['response'] = $response;
    }

    return $next(
        $request,
        $response
    );
});
