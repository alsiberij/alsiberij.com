<?php

/**
 * Класс для сущности "Фотография"
 */
class Photo extends CommentableViewableEntity {

    public const DESCRIPTION_MIN_LENGTH = 3;
    public const DESCRIPTION_MAX_LENGTH = 2048;

    public const RAW_FILE_MAX_SIZE = 50_000_000;
    public const JPG_FILE_MAX_SIZE = 50_000_000;
    public const COMPRESSED_JPG_FILE_MAX_SIZE = 1_000_000;


    protected CategoryPhoto $category;
    protected string $description;
    protected int $price;
    protected User $author;
    protected bool $isPublic;
    protected int $width;
    protected int $height;
    protected int $size;
    protected int $rawSize;
    protected string $publicationDate;
    protected string $publicationTime;
    protected int $downloads;


    /**
     * Реализованный метод класса Entity для нахождения конкретной фотографии в БД
     * @param int $ID Идентификатор фотографии
     * @return ?Photo Объект фотографии либо null
     */
    protected static function &getByID(int $ID): ?Photo {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.static::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $category = &CategoryPhoto::newInstance($result['categoryID']);
            $author = &User::newInstance($result['authorID']);
            $photo = new Photo($ID, $category, $result['price'], $result['description'], $author,
                $result['isShown'], $result['width'], $result['height'], $result['size'], $result['rawSize'], $result['dateOfPublication'],
                $result['comments'], $result['likes'], $result['likedBy'], $result['views'],
                $result['downloads']);
        } else {
            $photo = null;
        }
        return $photo;
    }

    public static function &newInstance(int $ID): Photo {
        return parent::newInstance($ID);
    }

    /**
     * Метод для получения списка фотографий
     * @param int $categoryID Идентификатор категории
     * @return Photo[] Список фотографий
     */
    public static function getPhotosList(int $categoryID = 0): array {
        $photosList = [];
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' WHERE (categoryID = :ID OR :ID = 0) ORDER BY ID DESC';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $categoryID, PDO::PARAM_INT);
        $result->execute();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $photosList[] = &self::newInstance($row['ID']);
        }
        return $photosList;
    }

    /**
     * Метод для добавления новости в БД
     * @param User $author Объект пользователя (Автора)
     * @param array $fileRaw Массив с данными о загруженном файле RAW
     * @param array $fileJpg Массив с данными о загруженном файле JPG
     * @param array $fileJpgCompressed Массив с данными о загруженном сжатом файле JPG
     * @param int $categoryID Идентификатор категории
     * @param int $price Цена
     * @param string $description Описание
     * @param bool $privacy Приватность
     * @return array Массив с возникшими ошибками
     */
    public static function create(User &$author, array $fileRaw, array $fileJpg, array $fileJpgCompressed,
                                  int $categoryID, int $price, string $description, bool $privacy): array {
        $errors = self::validatePhoto($fileRaw, $fileJpg, $fileJpgCompressed, $categoryID, $price, $description);
        if (empty($errors)) {
            $db = DB::getConnection();
            $result = $db->query('SELECT ID FROM '.self::tableName().' ORDER BY ID DESC LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $ID = $result['ID'] + 1;
            } else {
                $ID = 0;
            }
            if (!is_dir(ROOT."vault/$ID")) {
                mkdir(ROOT."vault/$ID");
            }
            if (!move_uploaded_file($fileRaw['tmp_name'], ROOT."vault/$ID/$ID.CR2")) {
                $errors['Server'] = 'InternalError';
                return $errors;
            }
            if (!move_uploaded_file($fileJpg['tmp_name'], ROOT."vault/$ID/$ID.jpg")) {
                $errors['Server'] = 'InternalError';
                return $errors;
            }
            if (!move_uploaded_file($fileJpgCompressed['tmp_name'], ROOT."data/store/$ID".'min.jpg')) {
                $errors['Server'] = 'InternalError';
                return $errors;
            }

            $query = 'INSERT INTO '.self::tableName().' VALUE
                        (DEFAULT, :category, :price, :description, :author, :privacy, :width, :height, :size, :rawSize,
                         CURRENT_TIMESTAMP, DEFAULT, DEFAULT, \'{"users":[]}\', DEFAULT, DEFAULT)';
            $result = $db->prepare($query);
            $result->bindParam(':category', $categoryID, PDO::PARAM_INT);
            $result->bindParam(':price', $price, PDO::PARAM_INT);
            $result->bindParam(':description', $description);
            $authorID = $author->getID();
            $result->bindParam(':author', $authorID, PDO::PARAM_INT);
            $result->bindParam(':privacy', $privacy, PDO::PARAM_BOOL);
            list($width, $height) = getimagesize(ROOT."vault/$ID/$ID.jpg");
            $result->bindParam(':width', $width, PDO::PARAM_INT);
            $result->bindParam(':height', $height, PDO::PARAM_INT);
            $result->bindParam(':size', $fileJpg['size'], PDO::PARAM_INT);
            $result->bindParam(':rawSize', $fileRaw['size'], PDO::PARAM_INT);
            if (!$result->execute()) {
                $errors['Server'] = 'InternalError';
            } else {
                $category = CategoryPhoto::newInstance($categoryID);
                $category->increasePhotos();
                if (!$privacy) {
                    $category->increaseHiddenPhotos();
                }
            }
        }
        return $errors;
    }

    /**
     * Валидатор данных для создания фотографии
     * @param array $fileRaw Массив с данными о загруженном файле RAW
     * @param array $fileJpg Массив с данными о загруженном файле JPG
     * @param array $fileJpgCompressed Массив с данными о загруженном сжатом файле JPG
     * @param int $categoryID Идентификатор категории
     * @param int $price Цена
     * @param string $description Описание
     * @return array Массив с возникшими ошибками
     */
    public static function validatePhoto(array $fileRaw, array $fileJpg, array $fileJpgCompressed, int $categoryID, int $price, string $description): array {
        return array_merge(self::validateRaw($fileRaw),
                                self::validateJpg($fileJpg),
                                self::validateJpgCompressed($fileJpgCompressed),
                                self::validateCategoryID($categoryID),
                                self::validatePrice($price),
                                self::validateDescription($description));
    }
    public static function validateRaw($fileRaw): array {
        $errors = [];
        if ($fileRaw['type'] != 'image/CR2') {
            $errors['RawType'] = 'CR2Only';
        }
        if ($fileRaw['size'] > self::RAW_FILE_MAX_SIZE) {
            $errors['RawSize'] = 'TooBig';
        }
        return $errors;
    }
    public static function validateJpg($fileJpg): array {
        $errors = [];
        if ($fileJpg['type'] != 'image/jpeg') {
            $errors['JpgType'] = 'JPGOnly';
        }
        if ($fileJpg['size'] > self::JPG_FILE_MAX_SIZE) {
            $errors['JpgSize'] = 'TooBig';
        }
        return $errors;
    }
    public static function validateJpgCompressed($fileJpgCompressed): array {
        $errors = [];
        if ($fileJpgCompressed['type'] != 'image/jpeg') {
            $errors['JpgCompressedType'] = 'JppOnly';
        }
        if ($fileJpgCompressed['size'] > self::COMPRESSED_JPG_FILE_MAX_SIZE) {
            $errors['JpgCompressedSize'] = 'TooBig';
        }
        return $errors;
    }
    public static function validateCategoryID($categoryID): array {
        $errors = [];
        $category = &CategoryPhoto::newInstance($categoryID);
        if (!$category) {
            $errors['CategoryID'] = 'Invalid';
        }
        return $errors;
    }
    public static function validatePrice(int $price): array {
        $errors = [];
        if ($price < 0) {
            $errors['PriceValue'] = 'NegativeValue';
        }
        return $errors;
    }
    public static function validateDescription(string $description): array {
        $errors = array();
        if (mb_strlen($description) < self::DESCRIPTION_MIN_LENGTH || mb_strlen($description) > self::DESCRIPTION_MAX_LENGTH) {
            $errors['DescriptionLength'] = 'Invalid';
        }
        return $errors;
    }


    protected function __construct(int $ID, CategoryPhoto &$category, int $price, string $description, User &$author,
                                   bool $isPublic, int $width, int $height, int $size, int $rawSize, string $dateOfPublication,
                                   int $comments, int $likes, string $likedBy, int $views, int $downloads) {
        parent::__construct($ID, $comments, $likes, $likedBy, $views);
        $this->category = &$category;
        $this->price = $price;
        $this->description = $description;
        $this->author = &$author;
        $this->isPublic = $isPublic;
        $this->width = $width;
        $this->height = $height;
        $this->size = $size;
        $this->rawSize = $rawSize;
        try {
            $dateAndTime = new DateTime($dateOfPublication);
        } catch (Exception $ex) {
            $dateAndTime = new DateTime();
        }
        $this->publicationDate = $dateAndTime->format('d.m.y');
        $this->publicationTime = $dateAndTime->format('H:i');
        $this->downloads = $downloads;
    }

    public static function tableName(): string {
        return 'photos';
    }

    public function &getCategory(): CategoryPhoto {
        return $this->category;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description):bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.static::tableName().' SET description = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':description', $description);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->description = $description;
        }
        return $success;
    }

    public function getPrice(): int {
        return $this->price;
    }

    public function getRawSize(): int {
        return $this->rawSize;
    }

    public function &getAuthor(): User {
        return $this->author;
    }

    public function isPublic(): bool {
        return $this->isPublic;
    }

    public function setIsPublic(bool $isPublic): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.static::tableName().' SET isShown = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isPublic, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isPublic = $isPublic;
        }
        return $success;
    }

    public function getWidth(): int {
        return $this->width;
    }

    public function getHeight(): int {
        return $this->height;
    }

    public function isHorizontal(): bool {
        return $this->width > $this->height;
    }

    public function getSize(): int {
        return $this->size;
    }

    public function getPublicationDate(): string {
        return $this->publicationDate;
    }
    public function getPublicationTime(): string {
        return $this->publicationTime;
    }

    public function getDownloads(): int {
        return $this->downloads;
    }

    protected function setDownloads(int $downloads): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.static::tableName().' SET downloads = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $downloads, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->downloads = $downloads;
        }
        return $success;
    }

    public function increaseDownloads(): bool {
        return $this->setDownloads($this->downloads + 1);
    }
}