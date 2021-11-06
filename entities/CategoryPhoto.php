<?php


/**
 * Класс для сущности "Категория фотографии"
 */
class CategoryPhoto extends Entity {

    protected const NAME_PATTERN = '~^[a-z ]+$~';
    protected const RU_NAME_PATTERN = '~^[абвгдеёжзийклмнопрстуфхцчшщъыьэюя ]+$~';
    protected const NAME_MIN_LENGTH = 2;
    protected const NAME_MAX_LENGTH = 14;

    protected string $name;
    protected string $nameRu;
    protected int $photos;
    protected int $hiddenPhotos;
    protected int $downloads;

    /**
     * Реализованный метод класса Entity для нахождения конкретной категории в БД
     * @param int $ID Идентификатор категории
     * @return ?CategoryPhoto Объект категории, либо null
     */
    protected static function &getByID(int $ID): ?CategoryPhoto {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result = new CategoryPhoto($result['ID'], $result['name'], $result['nameRu'], $result['photos'], $result['hiddenPhotos'], $result['downloads']);
        } else {
            $result = null;
        }
        return $result;
    }

    public static function &newInstance(int $ID): CategoryPhoto {
        return parent::newInstance($ID);
    }

    /**
     * Метод для получения списка категорий фотографий
     * @return CategoryPhoto[] Список всех категорий
     */
    public static function getCategoriesList(): array {
        $categoriesList = array();
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' ORDER BY downloads DESC';
        $result = $db->query($query);
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $categoriesList[] = &self::newInstance($row['ID']);
        }
        return $categoriesList;
    }

    /**
     * Метод для добавления категории в БД
     * @param string $categoryName Имя категории
     * @param string $categoryNameRu Имя категории на русском
     * @return array Массив с возникшими ошибками
     */
    public static function create(string $categoryName, string $categoryNameRu): array {
        $errors = self::validateCategory($categoryName, $categoryNameRu);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'INSERT INTO '.self::tableName().' VALUE 
                    (DEFAULT, :name, :nameRu, DEFAULT, DEFAULT, DEFAULT)';
            $result = $db->prepare($query);
            $result->bindParam(':name', $categoryName);
            $result->bindParam(':nameRu', $categoryNameRu);
            if (!$result->execute()) {
                $errors['server'] = 'InternalError';
            }
        }
        return $errors;
    }

    public static function validateCategory(string $name, string $nameRu): array {
        return array_merge(self::validateNameLength($name, $nameRu), self::validateNamePattern($name, $nameRu));
    }
    public static function validateNamePattern(string $name, string $nameRu): array {
        $errors = [];
        if (!preg_match(self::NAME_PATTERN, $name) || !preg_match(self::RU_NAME_PATTERN, $nameRu)) {
            $errors['NamePattern'] = 'Invalid';
        }
        return $errors;
    }
    public static function validateNameLength($name, $nameRu): array {
        $errors = [];
        if (mb_strlen($name) < self::NAME_MIN_LENGTH || mb_strlen($name) > self::NAME_MAX_LENGTH ||
            mb_strlen($nameRu) < self::NAME_MIN_LENGTH || mb_strlen($nameRu) > self::NAME_MAX_LENGTH) {
            $errors['NameLength'] = 'Invalid';
        }
        return $errors;
    }


    protected function __construct(int $ID, string $name, string $nameRu, int $photos, int $hiddenPhotos, int $downloads) {
        parent::__construct($ID);
        $this->name = $name;
        $this->nameRu = $nameRu;
        $this->photos = $photos;
        $this->hiddenPhotos = $hiddenPhotos;
        $this->downloads = $downloads;
    }

    public static function tableName(): string {
        return 'categories_photo';
    }

    public function hasOnlyHiddenPhotos():bool {
        return $this->photos == $this->hiddenPhotos;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET name = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $name);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->name = $name;
        }
        return $success;
    }

    public function getNameRu(): string {
        return $this->nameRu;
    }

    public function setNameRu(string $nameRu): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET nameRu = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $nameRu);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->nameRu = $nameRu;
        }
        return $success;
    }

    public function getPhotos():int {
        return $this->photos;
    }

    public function getPublicPhotos():int {
        return $this->photos - $this->hiddenPhotos;
    }

    protected function setPhotos(int $photos):bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET photos = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $photos, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->photos = $photos;
        }
        return $success;
    }

    public function increasePhotos():bool {
        return $this->setPhotos($this->photos + 1);
    }
    public function decreasePhotos():bool {
        return $this->setPhotos($this->photos - 1);
    }

    public function getHiddenPhotos():int {
        return $this->hiddenPhotos;
    }

    protected function setHiddenPhotos(int $hiddenPhotos):bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET hiddenPhotos = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $hiddenPhotos, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->hiddenPhotos = $hiddenPhotos;
        }
        return $success;
    }

    public function increaseHiddenPhotos():bool {
        return $this->setHiddenPhotos($this->hiddenPhotos + 1);
    }

    public function decreaseHiddenPhotos():bool {
        return $this->setHiddenPhotos($this->hiddenPhotos - 1);
    }

    public function getDownloads(): int {
        return $this->downloads;
    }

    protected function setDownloads(int $downloads):bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET downloads = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $downloads, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->downloads = $downloads;
        }
        return $success;
    }

    public function increaseDownloads():bool {
        return $this->setDownloads($this->downloads + 1);
    }
}