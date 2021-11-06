<?php


/**
 * Абстрактный класс для сущности со счетчиком просмотров, под которой можно оставить комментарий
 */
abstract class CommentableViewableEntity extends LikableEntity {

    /**
     * @var int Счетчик комментариев
     */
    protected int $comments;
    /**
     * @var int Счетчик просмотров
     */
    protected int $views;


    public function __construct(int $ID, int $comments, int $likes, string $likedBy, int $views) {
        parent::__construct($ID, $likes, $likedBy);
        $this->comments = $comments;
        $this->views = $views;
    }

    public final function getComments():int {
        return $this->comments;
    }

    protected final function setComments(int $comments):bool {
        $db = DB::getConnection();
        $query = "UPDATE {$this->tableNameInst()} SET comments = :value WHERE ID = :ID";
        $result = $db->prepare($query);
        $result->bindParam(':value', $comments, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->comments = $comments;
        }
        return $success;
    }

    public final function increaseComments(): bool {
        return $this->setComments($this->comments + 1);
    }
    public final function decreaseComments(): bool {
        return $this->setComments($this->comments - 1);
    }

    public final function getViews(): int {
        return $this->views;
    }

    protected final function setViews(int $views): bool {
        $db = DB::getConnection();
        $query = "UPDATE {$this->tableNameInst()} SET views = :value WHERE ID = :ID";
        $result = $db->prepare($query);
        $result->bindParam(':value', $views, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->views = $views;
            return true;
        } else {
            return false;
        }
    }

    protected final function increaseViews(): bool {
        return $this->setViews($this->views + 1);
    }

    /**
     * Метод для определения необходимости увеличить светчик просмотров.
     * Необходимость определяется наличием cookie с названием и идентификатором данной сущности.
     * Если идентификатор есть в cookie,
     * значит пользователь недавно просматривал ресурс и нет необходимости в увеличении счетчика
     * @return bool True, если счетчик был увеличен, иначе false
     */
    public final function updateViews(): bool {
        $needToIncreaseViews = true;
        if (!isset($_COOKIE['recentlyViewed-'.static::tableName()])) {
            $cookie = $this->ID;
        } else {
            $recentlyViewedResources = explode('&', $_COOKIE['recentlyViewed-'.static::tableName()]);
            if (in_array($this->ID, $recentlyViewedResources)) {
                $needToIncreaseViews = false;
            } else {
                $recentlyViewedResources[] = $this->ID;
            }
            $cookie = implode('&', $recentlyViewedResources);
        }
        setrawcookie('recentlyViewed-'.static::tableName(), $cookie, time() + VIEWLIFETIME, '/');
        if ($needToIncreaseViews) {
            return $this->increaseViews();
        }
        return false;
    }

}