<?php


/**
 * Фабрика контроллеров
 */
class ControllerFactory {

    /**
     * Метод для получения объекта контроллера по его названию
     * @param string $controllerName Название контроллера
     * @param array $routeParams Массив с параметрами маршрута
     * @param array $queryParams Массив с параметрами GET-запроса
     * @return ?Controller Объект контроллера, если такой существует, иначе null
     */
    public static function newInstance(string $controllerName, array $routeParams, array $queryParams): ?Controller {
        switch ($controllerName) {
            case 'Api': return new ApiController($routeParams, $queryParams);
            case 'Base': return new BaseController($routeParams, $queryParams);
            case 'News': return new NewsController($routeParams, $queryParams);
            case 'Account' : return new AccountController($routeParams, $queryParams);
            case 'Store' : return new StoreController($routeParams, $queryParams);
            default: return null;
        }
    }
}