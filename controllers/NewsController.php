<?php


/**
 * Контроллер /news
 */
class NewsController extends Controller {

    public function action(string $name): bool {
        switch ($name) {

            case 'Index': {
                $newsList = News::getNewsList();
                require(ROOT.'views/news/news.php');
                return true;
            }

            case 'ShowNews': {
                $newsID = $this->getRouteParams()[0];
                $news = &News::newInstance($newsID);

                if ($news && ($news->isPublic() || $this->adminPresence() || $this->isModerAndAuthorOf($news))) {

                    if ($this->isAuthorized() && isset($_POST['submitComment'])) {
                        $redirect = $this->postHandleCreateComment($news);
                        header($redirect, true, 303);
                        return true;
                    }

                    $commentsList = CommentNews::getCommentsList($news);
                    $newsIsLiked = false;
                    if ($this->isAuthorized()) {
                        $newsIsLiked = in_array($this->getAuthorizedUser()->getID(), $news->getLikedBy());
                        $likedComments = $this->getAuthorizedUser()->getLikedContent(CommentNews::tableName());
                    }

                    $news->updateViews();

                    require(ROOT.'views/news/single.php');
                    return true;
                } else {
                    return false;
                }
            }

            case 'Add': {
                if (!$this->isAuthorized()) {
                    header('Location: /account/login', true, 303);
                    return true;
                }
                if (!($this->adminPresence() || $this->moderPresence())) {
                    header('Location: /news', true, 303);
                    return true;
                }
                if (isset($_POST['submitNews'])) {
                    $newLocation = $this->postHandleCreateNews();
                    header($newLocation, true, 303);
                    return true;
                }
                require(ROOT.'views/news/post.php');
                return true;
            }

            default: {
                return false;
            }
        }
    }

    /**
     * Метод для публикации комментариев
     * @param News $news Объект новости под которой нужно оставить комментарий
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleCreateComment(News &$news): string {
        $commentContent = $_POST['commentContent'] ?? '';
        $errors = CommentNews::create($this->getAuthorizedUser(), $news, $commentContent);
        $newLocation = 'Location: '.explode('?', $_SERVER['REQUEST_URI'])[0];
        if (!empty($errors)) {
            $newLocation .= $this->toQueryString($errors);
        }
        return $newLocation;
    }

    /**
     * Метод для публикации новостей
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleCreateNews(): string {
        $newsTitle = $_POST['newsTitle'] ?? '';
        $newsContent = $_POST['newsContent'] ?? '';
        $newsIsPublic = isset($_POST['newsIsPublic']) && $_POST['newsIsPublic'] == true;
        $newsIsImportant = isset($_POST['newsIsImportant']) && $_POST['newsIsImportant'] == true;
        $errors = News::create($this->getAuthorizedUser(), $newsTitle, $newsContent, $newsIsImportant, $newsIsPublic);
        $newLocation = 'Location: ';
        if (empty($errors)) {
            $newLocation .= '/news';
        } else {
            $newLocation .= explode('?', $_SERVER['REQUEST_URI'])[0].$this->toQueryString($errors);
        }
        return $newLocation;
    }

    /**
     * Метод для получения информации о том является ли пользователь автором записи и модератором
     * @param News $news Объект новости
     * @return bool True, если пользователь и автор и модератор, иначе false
     */
    protected function isModerAndAuthorOf(News &$news): bool {
        return $this->moderPresence() && $this->getAuthorizedUser()->getID() == $news->getAuthor()->getID();
    }
}