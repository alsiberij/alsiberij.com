<?php

/**
 * Контроллер /store
 */
class StoreController extends Controller {

    public function action(string $name): bool {
        switch ($name) {

            case 'Index': {
                $categoriesList = CategoryPhoto::getCategoriesList();
                $photosList = Photo::getPhotosList();
                $photosAmount = 0;
                foreach ($categoriesList as $category) {
                    if ($this->adminPresence() || $this->moderPresence()) {
                        $photosAmount += $category->getPhotos();
                    } else {
                        $photosAmount += $category->getPublicPhotos();
                    }
                }
                require(ROOT.'views/store/store.php');
                return true;
            }

            case 'IndexCategory': {
                $categoryName = $this->routeParams[0];
                $categoriesList = CategoryPhoto::getCategoriesList();
                $categoryID = 0;
                $photosAmount = 0;
                foreach ($categoriesList as $category) {
                    if ($category->getName() == $categoryName) {
                        $categoryID = $category->getID();
                    }
                    if ($this->adminPresence() || $this->moderPresence()) {
                        $photosAmount += $category->getPhotos();
                    } else {
                        $photosAmount += $category->getPublicPhotos();
                    }
                }

                if ($categoryID) {
                    $selectedCategory = &CategoryPhoto::newInstance($categoryID);
                    $photosList = Photo::getPhotosList($categoryID);
                    require(ROOT.'views/store/store.php');
                    return true;
                } else {
                    return false;
                }
            }

            case 'AddCategory': {
                if (!$this->isAuthorized()) {
                    header('Location: /account/login', true, 303);
                    return true;
                }
                if (!($this->adminPresence() || !$this->moderPresence())) {
                    header('Location: /store', true, 303);
                    return true;
                }

                if (isset($_POST['submitCategory'])) {
                    $newLocation = $this->postHandleCreateCategory();
                    header($newLocation, true, 303);
                    return true;
                }
                require(ROOT.'views/store/newCategory.php');
                return true;
            }

            case 'AddPhoto': {
                if (!$this->isAuthorized()) {
                    header('Location: /account/login', true, 303);
                    return true;
                }
                if (!($this->adminPresence() || !$this->moderPresence())) {
                    header('Location: /store', true, 303);
                    return true;
                }

                if (isset($_POST['submitNewPhoto'])) {
                    $newLocation = $this->postHandleAddPhoto();
                    header($newLocation, true, 303);
                    return true;
                }
                $categoriesList = CategoryPhoto::getCategoriesList();
                require(ROOT.'views/store/newPhoto.php');
                return true;
            }

            case 'ShowItem': {
                $categoryName = $this->routeParams[0];
                $photoID = $this->routeParams[1];
                $categoriesList = CategoryPhoto::getCategoriesList();

                $success = false;
                foreach ($categoriesList as $category) {
                    if ($category->getName() == $categoryName) {
                        $success = true;
                    }
                }

                if ($success) {
                    $photo = &Photo::newInstance($photoID);
                    if ($photo && $photo->getCategory()->getName() == $categoryName) {

                        if (isset($_POST['submitComment'])) {
                            $redirect = $this->postHandleCreateComment($photo);
                            header($redirect, true, 303);
                            return true;
                        }

                        $commentsList = CommentPhoto::getCommentsList($photo);
                        $photoIsLiked = false;
                        $likedComments = array();
                        if ($this->isAuthorized()) {
                            $photoIsLiked = in_array($photoID, $this->getAuthorizedUser()->getLikedContent(Photo::tableName()));
                            $likedComments = $this->getAuthorizedUser()->getLikedContent(CommentPhoto::tableName());
                        }

                        $photo->updateViews();
                        require(ROOT.'views/store/photo.php');
                    } else {
                        $success = false;
                    }
                }
                return $success;
            }

            case 'BuyItem': {
                if (!$this->isAuthorized()) {
                    header('Location: /account/login', true, 303);
                    return true;
                }

                $itemID = $this->getQueryParams()['item'] ?? '';
                $bundle = $this->getQueryParams()['bundle'] ?? '';

                $photo = &Photo::newInstance($itemID);
                if (!$photo) {
                    return false;
                }

                $bundlePrice = $photo->getPrice();
                if ($bundle == 'RAW') {
                    $bundlePrice = round($bundlePrice * 1.5, 0, PHP_ROUND_HALF_DOWN);
                } elseif ($bundle == 'JPGRAW') {
                    $bundlePrice = $bundlePrice * 2;
                } elseif ($bundle != 'JPG') {
                    return false;
                }

                require(ROOT.'views/store/cart.php');
                return true;
            }

            case 'DownloadItem': {
                if (!$this->isAuthorized()) {
                    require(ROOT.'views/templates/error/error2.php');
                    return true;
                }

                $token = $this->getQueryParams()['token'] ?? '';
                $link = &DownloadLink::newInstanceByToken($token);
                $success = true;
                if (!$link) {
                    $success = false;
                } elseif ($link->isExpired()) {
                    $link->delete();
                    $success = false;
                } elseif ($link->getOwner()->getID() != $this->getAuthorizedUser()->getID()) {
                    $success = false;
                }

                if (!$success) {
                    return false;
                }

                if (isset($_POST['submit'])) {
                    $this->postHandleDownload($link);
                }
                require(ROOT.'/views/store/download.php');
                return true;
            }

            default: return false;
        }
    }

    /**
     * Метод для обработки POST-запроса на создание новой категории фото
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleCreateCategory(): string {
        $categoryName = isset($_POST['categoryName']) ? strtolower($_POST['categoryName']) : '';
        $categoryNameRu = isset($_POST['categoryNameRu']) ? strtolower($_POST['categoryNameRu']) : '';
        $errors = CategoryPhoto::create($categoryName, $categoryNameRu);
        $newLocation = 'Location: /store';
        if (!empty($errors)) {
            $newLocation .= '/add/category'.$this->toQueryString($errors);
        }
        return $newLocation;
    }

    /**
     * Метод для обработки POST-запроса на скачивание фото
     * @param DownloadLink $link Объект ссылки для скачивания
     */
    protected function postHandleDownload(DownloadLink $link): void {
        $bundle = $_POST['bundle'];
        if ($bundle == 'JPG' && ($link->getBundle() == 'JPG' || $link->getBundle() == 'JPGRAW')) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            $itemID = $link->getItem()->getID();
            $uri = ROOT."/vault/$itemID/$itemID.jpg";
            header('Content-Disposition: attachment; filename='.basename($uri));
            header('Content-Length: ' . filesize($uri));
            header('Pragma: public');
            flush();
            readfile($uri, true);
        }
        if ($bundle == 'RAW' && ($link->getBundle() == 'RAW' || $link->getBundle() == 'JPGRAW')) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            $itemID = $link->getItem()->getID();
            $uri = ROOT."/vault/$itemID/$itemID.CR2";
            header('Content-Disposition: attachment; filename='.basename($uri));
            header('Content-Length: ' . filesize($uri));
            header('Pragma: public');
            flush();
            readfile($uri, true);
        }
    }

    /**
     * Метод для обработки POST-запроса на добавление фото
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleAddPhoto(): string {
        if (is_uploaded_file($_FILES['newPhotoRaw']['tmp_name']) &&
                is_uploaded_file($_FILES['newPhotoJpg']['tmp_name']) &&
                is_uploaded_file($_FILES['newPhotoJpgCompressed']['tmp_name']) ){
            $fileRaw = $_FILES['newPhotoRaw'];
            $fileJpg = $_FILES['newPhotoJpg'];
            $fileJpgCompressed = $_FILES['newPhotoJpgCompressed'];
            $category = $_POST['newPhotoCategory'] ?? 0;
            $price = isset($_POST['newPhotoPrice']) ? intval($_POST['newPhotoPrice']) : 0;
            $description = $_POST['newPhotoDescription'] ?? '';
            $privacy = isset($_POST['newPhotoPrivacy']) && $_POST['newPhotoPrivacy'] == true;
            $errors = Photo::create($this->getAuthorizedUser(), $fileRaw, $fileJpg, $fileJpgCompressed, $category, $price, $description, $privacy);
            $newLocation = 'Location: /store';
            if (!empty($errors)) {
                $newLocation .= '/add/photo'.$this->toQueryString($errors);
            }
            return $newLocation;
        }
        return 'Location: '.explode('?', $_SERVER['REQUEST_URI'])[0];
    }

    /**
     * Метод для обработки POST-запроса на создание комментария
     * @param Photo $photo Объект фотографии, под которой нужно оставить комментарий
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleCreateComment(Photo &$photo): string {
        $commentContent = $_POST['commentContent'] ?? '';
        $errors = CommentPhoto::create($this->getAuthorizedUser(), $photo, $commentContent);
        $newLocation = 'Location: '.explode('?', $_SERVER['REQUEST_URI'])[0];
        if (!empty($errors)) {
            $newLocation .= $this->toQueryString($errors);
        }
        return $newLocation;
    }

    /**
     * Метод для получения информации о том является ли пользователь автором фотографии и модератором
     * @param Photo $photo Объект новости
     * @return bool True, если пользователь и автор и модератор, иначе false
     */
    protected function isModerAndAuthorOf(Photo &$photo): bool {
        return $this->moderPresence() && $this->getAuthorizedUser()->getID() == $photo->getAuthor()->getID();
    }

    /**
     * Метод для получения информации о том, был ли уже куплен данный набор данным пользователем
     * @return bool True, если уже куплен, иначе false
     */
    protected function bundleAlreadyBought(): bool {
        $itemID = $this->getQueryParams()['item'];
        $bundle = $this->getQueryParams()['bundle'];
        return DownloadLink::alreadyBoughtBy($itemID, $bundle, $this->getAuthorizedUser());
    }
}