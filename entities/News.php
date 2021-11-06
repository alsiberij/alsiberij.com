<?php


/**
 * Класс для сущности "Новость"
 */
class News extends CommentableViewableEntity {

    public static int $TITLE_MIN_LENGTH = 3;
    public static int $TITLE_MAX_LENGTH = 128;
    public static int $CONTENT_MIN_LENGTH = 3;
    public static int $CONTENT_MAX_LENGTH = 2048;

    protected bool $isPublic;
    protected bool $isImportant;
    protected string $title;
    protected User $author;
    protected string $content;
    protected string $publicationTime;
    protected string $publicationDate;


    /**
     * Реализованный метод класса Entity для нахождения конкретной новости в БД
     * @param int $ID Идентификатор новости
     * @return ?News Объект новости
     */
    protected static function &getByID(int $ID): News {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $author = &User::newInstance($result['authorID']);
            $result = new News($ID, $result['isShown'], $result['isImportant'], $result['title'], $author,
                        $result['content'], $result['dateOfPublication'], $result['comments'], $result['likes'],
                        $result['likedBy'], $result['views']);
        } else {
            $result = null;
        }
        return $result;
    }

    public static function &newInstance(int $ID): News {
        return parent::newInstance($ID);
    }

    /**
     * Метод для получения массива со всеми новостями
     * @return News[] Список всех новостей
     */
    public static function getNewsList(): array {
        $commentsList = array();
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' ORDER BY ID DESC';
        $result = $db->query($query);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $commentsList[] = &News::newInstance($row['ID']);
        }
        return $commentsList;
    }

    /**
     * Метод для добавления новости в БД
     * @param User $author Объект пользователя (Автора)
     * @param string $title Заголовок
     * @param string $content Содержание
     * @param bool $isImportant Важность
     * @param bool $isPublic Приватность
     * @return array Массив с возникшими ошибками
     */
    public static function create(User &$author, string $title, string $content, bool $isImportant, bool $isPublic): array {
        $errors = self::validate($title, $content);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'INSERT INTO '.static::tableName().' VALUE 
                    (DEFAULT, :isPublic, :isImportant, :title, :authorID, :content, CURRENT_TIMESTAMP, DEFAULT,
                     DEFAULT, \'{"users":[]}\', DEFAULT)';
            $result = $db->prepare($query);
            $authorID = $author->getID();
            $result->bindParam(':isPublic', $isPublic, PDO::PARAM_BOOL);
            $result->bindParam(':isImportant', $isImportant, PDO::PARAM_BOOL);
            $result->bindParam(':title', $title);
            $result->bindParam(':authorID', $authorID, PDO::PARAM_INT);
            $result->bindParam(':content', $content);
            if (!$result->execute()) {
                $errors['server'] = 'InternalError';
            }
        }
        return $errors;
    }

    /**
     * Валидатор длины заголовка
     * @param string $title Заголовок
     * @return bool True, если валидация успешна, иначе false
     */
    public static function validateTitleLength(string $title): bool {
        return mb_strlen($title) >= self::$TITLE_MIN_LENGTH && mb_strlen($title) <= self::$TITLE_MAX_LENGTH;
    }
    /**
     * Валидатор длины содержания
     * @param string $content Содержание
     * @return bool True, если валидация успешна, иначе false
     */
    public static function validateContentLength(string $content): bool {
        return mb_strlen($content) >= self::$CONTENT_MIN_LENGTH && mb_strlen($content) <= self::$CONTENT_MAX_LENGTH;
    }
    /**
     * Валидатор данных для создания новости
     * @param string $title Заголовок
     * @param string $content Содержание
     * @return array Массив с возникшими ошибками
     */
    public static function validate(string $title, string $content): array {
        $errors = array();
        if (!self::validateTitleLength($title)) {
            $errors['titleLength'] = 'Invalid';
        }
        if (!self::validateContentLength($content)) {
            $errors['contentLength'] = 'Invalid';
        }
        return $errors;
    }

    /**
     * Метод для удаления новости из БД (Также удаляются комментарии и лайки у пользователей)
     * @return bool True, если удаление успешно, иначе false
     */
    public function delete(): bool {
        $success = CommentNews::deleteAll();
        $likedBy = $this->getLikedBy();
        foreach ($likedBy as $likedUserID) {
            $user = &User::newInstance($likedUserID);
            $user->like($this);
        }

        $db = DB::getConnection();
        $query = "DELETE FROM {$this->tableNameInst()} WHERE ID = :ID";
        $result = $db->prepare($query);
        $newsID = $this->getID();
        $result->bindParam('ID', $newsID, PDO::PARAM_INT);
        return $success && $result->execute();
    }

    protected function __construct(int $ID, bool $isPublic, bool $isImportant, string $title, User &$author,
                                   string $content, string $dateOfPublication, int $comments, int $likes,
                                   string $likedBy, int $views) {
        parent::__construct($ID, $comments, $likes, $likedBy, $views);
        $this->isPublic = $isPublic;
        $this->isImportant = $isImportant;
        $this->title = $title;
        $this->author = &$author;
        $this->content = $content;
        try {
            $dateAndTime = new DateTime($dateOfPublication);
        } catch (Exception $ex) {
            $dateAndTime = new DateTime();
        }
        $this->publicationTime = $dateAndTime->format('H:i');
        $this->publicationDate = $dateAndTime->format('d.m.y');
    }

    public static function tableName(): string {
        return 'news';
    }

    public function isPublic(): bool {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isShown = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isPublic, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isPublic = $isPublic;
        }
        return $success;
    }

    public function isImportant(): bool {
        return $this->isImportant;
    }

    public function setIsImportant(bool $isImportant): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isImportant = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isImportant, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isImportant = $isImportant;
        }
        return $success;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function setTitle(string $title): bool {
        if (!self::validateTitleLength($title)) {
            return false;
        }
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET title = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isTitle);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->title = $title;
        }
        return $success;
    }

    public function &getAuthor(): User  {
        return $this->author;
    }

    public function getContent(): string {
        return $this->content;
    }

    public function setContent(string $content): bool {
        if (!self::validateContentLength($content)) {
            return false;
        }
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET content = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $content);
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