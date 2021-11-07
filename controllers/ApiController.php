<?php

/**
 * Контроллер /api
 */
class ApiController extends Controller {

    public function action(string $name): bool {
        switch ($name) {

            case 'Respond': {
                $method = $this->queryParams['method'] ?? '';
                ApiCallFactory::newInstance($method)->respond($this->getAuthorizedUser());
                return true;
            }

            default: {
                return false;
            }
        }
    }
}