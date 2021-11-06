<?php

/**
 * Контроллер /
 */
class BaseController extends Controller {


    public function action(string $name): bool {
        switch ($name) {

            case 'Index': {
                require(ROOT.'views/index/index.php');
                return true;
            }

            default: {
                return false;
            }

        }
    }
}