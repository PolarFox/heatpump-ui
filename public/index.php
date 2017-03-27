<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Polaris\Heatpump;

require '../vendor/autoload.php';
\Polaris\Config::load('../config.json');
use \Slim\Views\Twig;
$app = new \Slim\App;

$container = $app->getContainer();
// Register component on container
$container['view'] = function ($container) {
  $view = new \Slim\Views\Twig('../views', [
    'cache' => false#'../../view_cache'
  ]);
  // Instantiate and add Slim specific extension
  $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
  $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

  return $view;
};
$app->get('/', function (Request $request, Response $response) {
    $sensor_data=['indoor_temp'=>20,'outdoor_temp'=>10];
    $name = $request->getAttribute('name');
    return $this->view->render($response, 'status.html',[
      'sensors'=>$sensor_data,
      'heatpump'=>\Polaris\Heatpump::getStatus()
    ]);
})->setName('status');

$app->get('/status.json', function (Request $request, Response $response) {
    $sensor_data=['indoor_temp'=>20,'outdoor_temp'=>10];
    return json_encode(\Polaris\Heatpump::getStatus());
})->setName('status');

$app->get('/set', function (Request $request, Response $response) {
  $sensor_data=['indoor_temp'=>20,'outdoor_temp'=>10];
  return $this->view->render($response, 'settings.html',[
    'sensors'=>$sensor_data,
    'heatpump'=>\Polaris\Heatpump::getStatus()
  ]);
})->setName('settings');

$app->post('/set/temperature', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  $temperature=(int)filter_var($data['temperature'],FILTER_SANITIZE_NUMBER_INT);
  \Polaris\Heatpump::setTemperature($temperature);
})->setName("setTemperature");

$app->post('/set/mode', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  $mode=filter_var($data['mode'],FILTER_SANITIZE_STRING);
  \Polaris\Heatpump::setMode($mode);
})->setName("setMode");

$app->post('/set/vane', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  $vane=filter_var($data['vane'],FILTER_SANITIZE_STRING);
  \Polaris\Heatpump::setVane($vane);
})->setName("setVane");

$app->post('/set/power', function (Request $request, Response $response) {
  $data = $request->getParsedBody();
  $mode=filter_var($data['power'],FILTER_SANITIZE_NUMBER_INT);
  if($mode==NULL) {
    throw new \Exception("Input not boolean.");
  }
  \Polaris\Heatpump::setPower($mode);
})->setName("setPower");

$app->get('/cron', function (Request $request, Response $response) {
  $avgprice=\Polaris\Fingrid::getSevenDayAveragePrice();
  $prices=\Polaris\Fingrid::getCurrentPrice();
  var_dump(array_filter($prices,function($var) {

    $stt=DateTime::createFromFormat("Y-m-d\TH+",$var['start_time']);
    die($stt->getTimeStamp());
    if(strptime("%Y-%m-%dT%H:%I:%SZ",$var['start_time'])<time()&&strptime("%Y-%m-%dT%H:%I:%SZ",$var['end_time'])>time()) { return true; }
    return false;
  }));
})->setName("runCron");

$app->run();
