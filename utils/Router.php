<?php

/**
 * Класс, выполняющий функции маршрутизатора
 */
class Router {

    /**
     * @var string[] Таблица маршрутов
     */
    private array $routes;

    /**
     * Метод для получения строки запроса без query-параметров и концевых "/"
     * @return string Запрашиваемый URI
     */
    private function getURI(): string {
        return explode('?', trim($_SERVER['REQUEST_URI'], '/'))[0];
    }

    public function __construct() {
        $this->routes = require(ROOT.'config/routes.php');
    }

    /**
     * Метод, реализующий функционал маршрутизатора
     */
    public function run(): void {
        $uri = $this->getURI();
        $success = false;
        foreach ($this->routes as $URIPattern => $path) {
            if (preg_match("~$URIPattern~", $uri)) {
                $routeSegments = explode(' -> ', preg_replace("~$URIPattern~", $path, $uri));
                $controllerName = array_shift($routeSegments);
                $actionName = array_shift($routeSegments);
                if (!empty($routeSegments)) {
                    $routeSegments = explode(' & ', $routeSegments[0]);
                }
                $controller = ControllerFactory::newInstance($controllerName, $routeSegments, $_GET);
                if ($controller && $controller->action($actionName)) {
                    $success = true;
                }
                break;
            }
        }
        if ($success == false) {
            http_response_code(404);
            require(ROOT.'views/templates/error/error.php');
        }
    }
}