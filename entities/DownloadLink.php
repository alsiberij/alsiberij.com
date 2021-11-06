<?php


/**
 * Класс для сущности "Ссылка для скачивания"
 */
class DownloadLink extends Entity {

    public const TOKEN_LENGTH = 64;
    public const TOKEN_CHARACTER_POOL = 'ij';
    public const TOKEN_LIFETIME = 60 * 60 * 24;

    protected string $token;
    protected DateTime $expiresIn;
    protected User $owner;
    protected Photo $item;
    protected string $bundle;


    /**
     * Метод для генерации токена, который будет однозначно определять пользователя, совершившего покупку, и товар,
     * который он купил.
     * @return string Токен ссылки для скачивания
     */
    public static function generateToken(): string {
        $pool = self::TOKEN_CHARACTER_POOL;
        $poolSize = strlen($pool);
        $token = '';
        for ($i = 0; $i < self::TOKEN_LENGTH; $i++) {
            $token .= $pool[rand(0, $poolSize- 1)];
        }
        return $token;
    }

    /**
     * Метод для проверки уникальности токена
     * @param string $token Токен
     * @return bool True, если токен уникален, иначе false
     */
    public static function isUniqueToken(string $token): bool {
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' WHERE token = :token';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token);
        $result->execute();
        if ($result->fetch(PDO::FETCH_ASSOC)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Метод для добавления ссылки для скачивания в БД
     * @param string $token Токен для ссылки
     * @param User $owner Объект пользователя (Покупателя)
     * @param Photo $item Объект приобретенного товара (Фотографии)
     * @param string $bundle Тип приобретенного набора
     * @return array Массив с возникшими ошибками
     */
    public static function create(string $token, User $owner, Photo $item, string $bundle): array {
        $errors = array();
        if (!in_array($bundle, array('JPG', 'RAW', 'JPGRAW'))) {
            $errors['Bundle'] = 'Invalid';
        }

        $priceForBundle = $item->getPrice();
        if ($bundle == 'RAW') {
            $priceForBundle = round($priceForBundle * 1.5, 0, PHP_ROUND_HALF_DOWN);
        } elseif ($bundle == 'JPGRAW') {
            $priceForBundle *= 2;
        }
        if ($owner->getBalance() < $priceForBundle) {
            $errors['Balance'] = 'NotEnoughMoney';
        }

        if (empty($errors)) {
            $owner->decreaseBalance($priceForBundle);
            $item->getAuthor()->increaseBalance($priceForBundle);
            $db = DB::getConnection();
            if (!self::isUniqueToken($token)) {
                $errors['Token'] = 'AlreadyExists';
                return $errors;
            }
            $query = 'INSERT INTO links VALUE (DEFAULT, :token, :expiresIn, :ownerID, :itemID, :bundle)';
            $result = $db->prepare($query);
            $result->bindParam(':token', $token);
            $expiresIn = (new DateTime())->setTimestamp(time() + self::TOKEN_LIFETIME)->format('Y-m-d H:i:s');
            $result->bindParam(':expiresIn', $expiresIn);
            $ownerID = $owner->getID();
            $result->bindParam(':ownerID', $ownerID, PDO::PARAM_INT);
            $itemID = $item->getID();
            $result->bindParam(':itemID', $itemID, PDO::PARAM_INT);
            $result->bindParam(':bundle', $bundle);
            if (!$result->execute()) {
                $owner->increaseBalance($priceForBundle);
                $item->getAuthor()->decreaseBalance($priceForBundle);
                $errors['Server'] = 'InternalError';
            }
        }
        return $errors;
    }

    /**
     * Реализованный метод класса Entity для нахождения конкретной ссылки в БД
     * @param int $ID Идентификатор ссылки
     * @return ?DownloadLink Объект ссылки, либо null
     */
    protected static function &getByID(int $ID): ?DownloadLink {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $owner = &User::newInstance($result['ownerID']);
            $photo = &Photo::newInstance($result['itemID']);
            try {
                $result = new DownloadLink($ID, $result['token'], new DateTime($result['expiresIn']), $owner, $photo, $result['bundle']);
            } catch (Exception $ex) {
                $result = new DownloadLink($ID, $result['token'], new DateTime(), $owner, $photo, $result['bundle']);
            }
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Метод для нахождения ссылки в БД по ее токену (Обеспечивается уникальностью токена)
     * @param string $token Токен
     * @return ?DownloadLink Объект ссылки, либо null
     */
    protected static function &getByToken(string $token): ?DownloadLink {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE token = :token';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $owner = &User::newInstance($result['ownerID']);
            $photo = &Photo::newInstance($result['itemID']);
            try {
                $result = new DownloadLink($result['ID'], $result['token'], new DateTime($result['expiresIn']), $owner, $photo, $result['bundle']);
            } catch (Exception $ex) {
                $result = new DownloadLink($result['ID'], $result['token'], new DateTime(), $owner, $photo, $result['bundle']);
            }
        } else {
            $result = null;
        }

        return $result;
    }

    public static function &newInstance(int $ID): ?DownloadLink {
        return parent::newInstance($ID);
    }

    public static function &newInstanceByToken(string $token): ?DownloadLink {
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' WHERE token = :token';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        $link = null;
        if ($result) {
            $link = &self::newInstance($result['ID']);
        }
        return $link;
    }

    public static function tableName(): string {
        return 'links';
    }

    /**
     * Метод для проверки, был ли уже куплен данный товар пользователем
     * @param int $itemID Идентификатор товара
     * @param string $bundle Выбранный пользователем набор
     * @param User $user Объект пользователя (Покупателя)
     * @return bool True, если пользователь уже купил данный набор, иначе false
     */
    public static function alreadyBoughtBy(int $itemID, string $bundle, User $user): bool {
        $db = DB::getConnection();
        $query = 'SELECT ID FROM links WHERE itemID = :itemID AND ownerID = :ownerID AND bundle = :bundle';
        $result = $db->prepare($query);
        $result->bindParam(':itemID', $itemID, PDO::PARAM_INT);
        $userID = $user->getID();
        $result->bindParam(':ownerID', $userID, PDO::PARAM_INT);
        $result->bindParam(':bundle', $bundle);
        $result->execute();
        if ($result->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else {
            return false;
        }
    }


    protected function __construct(int $ID, string $token, DateTime $expiresIn, User &$owner, Photo &$item, string $bundle) {
        parent::__construct($ID);
        $this->token = $token;
        $this->expiresIn = $expiresIn;
        $this->owner = &$owner;
        $this->item = &$item;
        $this->bundle = $bundle;
    }

    public function getToken(): string {
        return $this->token;
    }

    public function isExpired(): bool {
        $time = new DateTime();
        return $time->diff($this->expiresIn)->invert == 1;
    }

    public function getOwner(): User {
        return $this->owner;
    }

    public function getItem(): Photo {
        return $this->item;
    }

    public function getBundle(): string {
        return $this->bundle;
    }

    public function delete(): bool {
        $db = DB::getConnection();
        $query = 'DELETE FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $ID = $this->getID();
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        return $result->execute();
    }
}