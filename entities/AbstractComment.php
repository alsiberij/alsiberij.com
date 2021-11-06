<?php

/**
 * Абстрактный класс для сущности "Комментарий"
 */
abstract class AbstractComment extends LikableEntity {

    public const CONTENT_MIN_LENGTH = 3;
    public const CONTENT_MAX_LENGTH = 1024;

    protected User $author;
    protected CommentableViewableEntity $resource;
    protected string $content;
    protected string $publicationTime;
    protected string $publicationDate;


    /**
     * Метод для добавления комментария в БД
     * @param User $author Объект пользователя (Автора)
     * @param CommentableViewableEntity $resource Объект ресурса, под которым оставлен комментарий
     * @param string $content Содержание комментария
     * @return array Массив возникших ошибок
     */
    public static function create(User &$author, CommentableViewableEntity &$resource, string $content): array {
        $errors = array();
        if (!self::validateComment($content)) {
            $errors['ContentLength'] = 'Invalid';
        } else {
            $db = DB::getConnection();
            $query = 'INSERT INTO '.static::tableName().' VALUE 
                    (DEFAULT, :authorID, :resourceID, :content, CURRENT_TIMESTAMP, DEFAULT, \'{"users":[]}\')';
            $result = $db->prepare($query);
            $authorID = $author->getID();
            $resourceID = $resource->getID();
            $result->bindParam(':authorID', $authorID, PDO::PARAM_INT);
            $result->bindParam(':resourceID', $resourceID, PDO::PARAM_INT);
            $result->bindParam(':content', $content);
            if (!$result->execute()) {
                $errors['Comment'] = 'DBInternalError';
            } else {
                $author->increaseComments();
                $resource->increaseComments();
            }
        }
        return $errors;
    }

    /**
     * Абстрактный метод для получения списка комментариев конкретного ресурса
     * @param CommentableViewableEntity $resource Идентификатор ресурса
     * @return AbstractComment[] Массив комментариев
     */
    public static abstract function getCommentsList(CommentableViewableEntity &$resource): array;

    /**
     * Метод для удаления всех инстанцированных комментариев
     * @return bool True, если все комментарии были успешно удалены, иначе false
     */
    public static function deleteAll(): bool {
        $success = true;
        $instancesOfComments = self::$instances[static::tableName()] ?? array();
        foreach ($instancesOfComments as &$commentInstance) {
            $commentInstance->getResource()->decreaseComments();
            $commentAuthor = &$commentInstance->getAuthor();
            $commentAuthor->decreaseComments();
            $commentLikedBy = $commentInstance->getLikedBy();
            foreach ($commentLikedBy as $likedUserID) {
                $likedUser = &User::newInstance($likedUserID);
                $likedUser->like($commentInstance);
            }
            $db = DB::getConnection();
            $query = 'DELETE FROM '.static::tableName().' WHERE referredResourceID = :ID';
            $result = $db->prepare($query);
            $resourceID = $commentInstance->getResource()->getID();
            $result->bindParam(':ID', $resourceID, PDO::PARAM_INT);
            $success = $success && $result->execute();
        }
        return $success;
    }

    /**
     * Валидатор комментария
     * @param string $content Содержание комментария
     * @return bool True, если данные прошли валидацию, иначе false
     */
    public static function validateComment(string $content): bool {
        return mb_strlen($content) >= self::CONTENT_MIN_LENGTH && mb_strlen($content) <= self::CONTENT_MAX_LENGTH;
    }


    protected function __construct(int    $ID, User &$author, CommentableViewableEntity &$resource, string $content,
                                   string $dateOfPublication, int $likes, string $likedBy) {
        parent::__construct($ID, $likes, $likedBy);
        $this->author = &$author;
        $this->resource = &$resource;
        $this->content = $content;
        try {
            $dateAndTime = new DateTime($dateOfPublication);
        } catch (Exception $ex) {
            $dateAndTime = new DateTime();
        }
        $this->publicationTime = $dateAndTime->format('H:i');
        $this->publicationDate = $dateAndTime->format('d.m.y');
    }

    public function &getAuthor(): User {
        return $this->author;
    }

    public function &getResource(): CommentableViewableEntity {
        return $this->resource;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.static::tableName().' SET content = :content WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':content', $content);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->content = $content;
        }
        return $success;
    }

    public function getPublicationTime(): string {
        return $this->publicationTime;
    }
    public function getPublicationDate(): string {
        return $this->publicationDate;
    }

}