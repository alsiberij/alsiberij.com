<?php


/**
 * Абстрактный класс представляющий контроллер
 */
abstract class Controller {

    /**
     * @var array Массив с параметрами маршрута
     */
    protected array $routeParams;

    /**
     * @var array Массив с параметрами GET-запроса
     */
    protected array $queryParams;

    /**
     * @var ?User Объект авторизованного пользователя
     */
    protected ?User $authorizedUser;


    /**
     * Конструктор класса
     * @param array $routeParams Массив с параметрами маршрута
     * @param array $queryParams Массив с параметрами GET-запроса
     */
    public function __construct(array $routeParams = [], array $queryParams = []) {
        $this->routeParams = $routeParams;
        $this->queryParams = $queryParams;
        if (isset($_SESSION['userID'])) {
            $this->authorizedUser = &User::newInstance($_SESSION['userID']);
        } else {
            $this->authorizedUser = null;
        }
    }

    /**
     * Метод для проверки, авторизован ли текущий пользователь
     * @return bool True, если авторизован, иначе false
     */
    public function isAuthorized(): bool {
        return $this->authorizedUser != null;
    }

    /**
     * Метод для получения объекта авторизованного пользователя
     * @return ?User Объект авторизованного пользователя, либо null
     */
    public function &getAuthorizedUser(): ?User {
        return $this->authorizedUser;
    }

    /**
     * Метод для получения информации о том, является ли авторизованный пользователь администратором
     * @return bool True, если авторизованный пользователь администратор, иначе false
     */
    public function adminPresence(): bool {
        return $this->isAuthorized() && $this->authorizedUser->isAdmin();
    }

    /**
     * Метод для получения информации о том, является ли авторизованный пользователь модератором
     * @return bool True, если авторизованный пользователь модератор, иначе false
     */
    public function moderPresence(): bool {
        return $this->isAuthorized() && $this->authorizedUser->isModer();
    }

    /**
     * Метод для получения массива параметров маршрута
     * @return array Массив с параметрами маршрута
     */
    public function getRouteParams(): array {
        return $this->routeParams;
    }

    /**
     * Метод для получения массива с параметрами GET-запроса
     * @return array Массив с параметрами GET-запроса
     */
    public function getQueryParams(): array {
        return $this->queryParams;
    }

    /**
     * Вспомогательный метод для преобразования массива параметров в query-строку
     * @param array $params Массив параметров
     * @return string Query-строка
     */
    public function toQueryString(array $params): string {
        $result = '?';
        foreach ($params as $paramKey => $paramValue) {
            $result .= $paramKey.'='.$paramValue.'&';
        }
        return trim($result, '&');
    }

    /**
     * Метод для обработки страниц
     * @param string $name Название
     * @return bool True, если не возникло ошибок, иначе false
     */
    public abstract function action(string $name): bool;
}