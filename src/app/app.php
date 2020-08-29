<?php declare(strict_types = 1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$app_env = getenv('APP_ENV') ?? 'dev';

/**
 * Get Container
 */
$container = include_once __DIR__ . '/container.php';

/**
 * Create Slim Application
 */
AppFactory::setContainer($container);
$app = AppFactory::create();

/**
 * Get and Map Routes
 */
$routes = include_once __DIR__ . '/routes.php';
foreach ($routes as $pattern => [$methods, $callable]) {
    $app->map($methods, $pattern, $callable);
}

/**
 * Middleware
 */
/**
 * CORS Middleware
 */
$app->add(new App\Middleware\CORSMiddleware());

/**
 * Error Middleware
 * @param bool $displayErrorDetails -> Should be set to false in production
 * @param bool $logErrors -> Paramter is passed to the default ErrorHandler
 * @param bool $logErrorDetails -> Display error details in error log; replaceable by callable
 * 
 * Note: Add this middleware last
 */
$defaultHandler = function(
    \Psr\Http\Message\ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($app) {
    $payload = ['error' => true, 'message' => $exception->getMessage()];
    $status = $exception->getCode();
    $json = json_encode($payload);

    $response = $app->getResponseFactory()->createResponse();
    $response->getBody()->write($json);
    return $response->withHeader('Content-type', 'application/json')->withStatus($status);
};

$errorMiddleware = $app->addErrorMiddleware($app_env == 'dev', true, true);
$errorMiddleware->setDefaultErrorHandler($defaultHandler);

/**
 * Run Application
 */
$app->run();