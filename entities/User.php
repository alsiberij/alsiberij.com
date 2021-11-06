<?php


/**
 * Класс для сущности "Новость"
 */
class User extends Entity {

    public const NICKNAME_MIN_LENGTH = 3;
    public const NICKNAME_MAX_LENGTH = 16;

    public const EMAIL_PATTERN = '~^[a-z][\.a-z0-9]+@[a-z][a-z0-9]+\.[a-z][a-z0-9]+$~';
    public const EMAIL_MIN_LENGTH = 8;
    public const EMAIL_MAX_LENGTH = 32;

    public const PASSWORD_PATTERN = '~^\w*$~';
    public const PASSWORD_MIN_LENGTH = 6;
    public const PASSWORD_MAX_LENGTH = 32;

    public const ACTIVATION_TOKEN_PATTERN = '~^[\w]+$~';
    public const ACTIVATION_TOKEN_CHARACTER_POOL = 'ij';
    public const ACTIVATION_TOKEN_LENGTH = 32;

    public const LOCATION_PATTERN = '~^[, a-zA-ZаАбБвВгГдДеЕёЁжЖзЗиИйЙкКлЛмМнНоОпПрРсСтТуУфФхХцЦчЧшШщЩъЪыЫьЬэЭюЮяЯ]+$~';
    public const LOCATION_MIN_LENGTH = 3;
    public const LOCATION_MAX_LENGTH = 50;

    public const BIO_MAX_LENGTH = 256;

    public const AVATAR_MAX_SIZE = 5_000_000;


    protected string $activationToken;
    protected bool $isActivated;
    protected bool $isAdmin;
    protected bool $isModerator;
    protected bool $isPublicAccount;
    protected bool $isPublicEmail;
    protected string $nickname;
    protected string $email;
    protected string $password;
    protected string $avatar;
    protected int $age;
    protected string $dateOfBirth;
    protected string $location;
    protected string $bio;
    protected string $dateOfRegistration;
    protected int $balance;
    protected bool $isPublicBalance;
    protected int $downloads;
    protected int $comments;
    protected int $likes;
    protected array $likedContent;

    /**
     * Реализованный метод класса Entity для нахождения конкретной новости в БД
     * @param int $ID Идентификатор новости
     * @return ?User Объект новости
     */
    protected static function &getByID(int $ID): ?User  {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $result = new User($ID, $result['activationToken'], $result['isActivated'], $result['isAdmin'],
                $result['isModerator'], $result['isPublicAccount'], $result['isPublicEmail'],
                $result['nickname'], $result['email'], $result['password'], $result['avatar'], $result['dateOfBirth'],
                $result['location'], $result['bio'], $result['dateOfRegistration'], $result['balance'],
                $result['isPublicBalance'], $result['downloads'], $result['comments'], $result['likes'],
                $result['likedContent']);
        } else {
            $result = null;
        }
        return $result;
    }

    public static function &newInstance(int $ID): ?User {
        return parent::newInstance($ID);
    }

    /**
     * Метод для проверки существования записи в БД с данной электронной почтой
     * @param string $email Электронная почта
     * @return bool True, если пользователь с такой электронной почтой существует, иначе false
     */
    public static function emailExists(string $email): bool {
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.static::tableName().' WHERE email = :email';
        $result = $db->prepare($query);
        $result->bindParam(':email', $email);
        $result->execute();
        if ($result->fetch(PDO::FETCH_ASSOC)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Метод для активации аккаунта нового пользователя
     * @param string $token Токен активации
     * @return bool True, если активации была успешной, иначе false
     */
    public static function activateUser(string $token): bool {
        if (!self::validateToken($token)) {
            return false;
        }
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.static::tableName().' WHERE activationToken = :token';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token);
        $result->execute();
        $user = $result->fetch(PDO::FETCH_ASSOC);
        $success = false;
        if ($user) {
            $query = 'UPDATE '.static::tableName().' SET isActivated = 1, activationToken = \'-\' WHERE ID = :ID';
            $result = $db->prepare($query);
            $result->bindParam(':ID', $user['ID'], PDO::PARAM_INT);
            $success = $result->execute();
        }
        return $success;
    }

    /**
     * Метод для аутентификации пользователя
     * @param string $email Электронная почта
     * @param string $password Пароль
     * @return ?User Объект пользователя в случае успешной аутентификации, иначе null
     */
    public static function &authenticate(string $email, string $password): ?User {
        $user = null;
        if (self::validateEmailLength($email) && self::validatePasswordLength($password)) {
            $db = DB::getConnection();
            $query = 'SELECT ID FROM '.static::tableName().' WHERE email = :email AND password = :password';
            $result = $db->prepare($query);
            $result->bindParam(':email', $email);
            $result->bindParam(':password', $password);
            $result->execute();
            $result =  $result->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $user = &self::newInstance($result['ID']);
            }
        }
        return $user;
    }

    /**
     * Метод для добавления пользователя в БД
     * @param string $nickname Имя пользователя
     * @param string $email Электронная почта
     * @param string $password Пароль
     * @return array Массив с возникшими ошибками
     */
    public static function create(string $nickname, string $email, string $password): array {
        $errors = self::validateUser($nickname, $email, $password);
        if (!empty($errors)) {
            return $errors;
        }

        do {
            $token = self::generateActivationToken();
        } while (!self::isUniqueToken($token));

        $db = DB::getConnection();
        $query = 'INSERT INTO '.static::tableName().' VALUE 
                    (DEFAULT, :token, DEFAULT, DEFAULT, DEFAULT, DEFAULT,
                    :nickname, :email, DEFAULT, :password, DEFAULT, DEFAULT, DEFAULT,
                    \'\', CURRENT_TIMESTAMP, DEFAULT, DEFAULT, DEFAULT, DEFAULT, DEFAULT, \'{}\')';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token);
        $result->bindParam(':nickname', $nickname);
        $result->bindParam(':email', $email);
        $result->bindParam(':password', $password);
        if ($result->execute()) {
            $msg = "
                <!DOCTYPE html>
                <html lang='ru'>
                    Премногоуважаемый(ая) <b>$nickname</b>.<br>
                    Будьте добры, откройте ".WEB."account/activation?token=$token для завершения регистрации.
                    Добро пожаловать!
                </html>";
            $from = 'From: '.EMAIL.'\r\n';
            mail($email, 'Регистрация', $msg, $from);
        } else {
            $errors['server'] = 'InternalError';
        }
        return $errors;
    }

    /**
     * Валидатор данных для создания пользователя
     * @param string $nickname Имя пользователя
     * @param string $email Электронная почта
     * @param string $password Пароль
     * @return array Массив с возникшими ошибками
     */
    public static function validateUser(string $nickname, string $email, string $password): array {
        $errors = [];

        if (!self::validateNicknameLength($nickname)) {
            $errors['nicknameLength'] = 'Invalid';
        }

        if (!self::validateEmailLength($email)) {
            $errors['emailLength'] = 'Invalid';
        }
        if (!self::validateEmailPattern($email)) {
            $errors['emailPattern'] = 'Invalid';
        } elseif (self::emailExists($email)) {
            $errors['email'] = 'AlreadyExists';
        }

        if (!self::validatePasswordLength($password)) {
            $errors['passwordLength'] = 'Invalid';
        }
        if (!self::validatePasswordPattern($password)) {
            $errors['passwordPattern'] = 'Invalid';
        }
        return $errors;
    }
    private static function validateNicknameLength(string $nickname): bool {
        return mb_strlen($nickname) > self::NICKNAME_MIN_LENGTH && mb_strlen($nickname) < self::NICKNAME_MAX_LENGTH;
    }
    private static function validateEmailLength(string $email): bool {
        return mb_strlen($email) >= self::EMAIL_MIN_LENGTH && mb_strlen($email) <= self::EMAIL_MAX_LENGTH;
    }
    private static function validateEmailPattern(string $email): bool {
        return preg_match(self::EMAIL_PATTERN, $email);
    }
    private static function validatePasswordLength(string $password): bool {
        return mb_strlen($password) >= self::PASSWORD_MIN_LENGTH && mb_strlen($password) <= self::PASSWORD_MAX_LENGTH;
    }
    private static function validatePasswordPattern(string $password): bool {
        return preg_match(self::PASSWORD_PATTERN, $password);
    }
    public static function validateLocation(string $location): array {
        $errors = array();
        if (mb_strlen($location) < self::LOCATION_MIN_LENGTH || mb_strlen($location) > self::LOCATION_MAX_LENGTH) {
            $errors['LocationLength'] = 'Invalid';
        }
        if (!preg_match(self::LOCATION_PATTERN, $location)) {
            $errors['LocationPattern'] = 'Invalid';
        }
        return $errors;
    }
    public static function validateBio(string $bio): array {
        $errors = array();
        if (mb_strlen($bio) > self::BIO_MAX_LENGTH) {
            $errors['BioLength'] = 'Invalid';
        }
        return $errors;
    }
    public static function validateBirthday(string $birthday): array {
        $errors = array();
        $birthdayObj = (new DateTime())->setTimestamp(strtotime($birthday));
        if (!$birthdayObj) {
            $errors['BirthdayPattern'] = 'Invalid';
        } else {
            if ($birthdayObj->diff(new DateTime())->invert != 0) {
                $errors['BirthdayDate'] = 'FutureDate';
            } elseif ($birthdayObj->format('Y') < 1970) {
                $errors['BirthdayDate'] = 'TooOld';
            }
        }
        return $errors;
    }
    public static function validateAvatar(array $avatarFile): array {
        $errors = array();
        if (!empty($avatarFile)) {
            if ($avatarFile['type'] != 'image/jpeg') {
                $errors['AvatarFileExtension'] = 'JpgOnly';
            }
            if ($avatarFile['size'] > self::AVATAR_MAX_SIZE) {
                $errors['AvatarSize'] = 'FileIsTooBig';
            }
            if ($avatarFile['error']) {
                $errors['Avatar'] = $avatarFile['error'];
            }
        }
        return $errors;
    }
    private static function validateToken(string $token): bool {
        return mb_strlen($token) == self::ACTIVATION_TOKEN_LENGTH && preg_match(self::ACTIVATION_TOKEN_PATTERN, $token);
    }

    /**
     * Метод для генерации токена активации пользователя
     * @return string Токен активации пользователя
     */
    private static function generateActivationToken(): string {
        $pool = self::ACTIVATION_TOKEN_CHARACTER_POOL;
        $poolSize = strlen($pool);
        $token = '';
        for ($i = 0; $i < self::ACTIVATION_TOKEN_LENGTH; $i++) {
            $token .= $pool[rand(0, $poolSize- 1)];
        }
        return $token;
    }

    /**
     * Метод для проверки уникальности токена активации
     * @param string $token Токен активации
     * @return bool True, если токен уникален, иначе false
     */
    private static function isUniqueToken(string $token): bool {
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.static::tableName().' WHERE activationToken = :token';
        $result = $db->prepare($query);
        $result->bindParam(':token', $token);
        $result->execute();
        if ($result->fetch(PDO::FETCH_ASSOC)) {
            return false;
        } else {
            return true;
        }
    }


    protected function __construct(int $ID, string $activationToken, bool $isActivated, bool $isAdmin,
                                   bool $isModerator, bool $publicAccount, bool $publicEmail, string $nickname,
                                   string $email, string $password, string $avatar, ?string $dateOfBirth,
                                   string $location, string $bio, string $dateOfRegistration, int $balance,
                                   bool $isPublicBalance, int $downloads, int $comments, int $likes,
                                   string $likedContent) {
        parent::__construct($ID);
        $this->activationToken = $activationToken;
        $this->isActivated = $isActivated;
        $this->isAdmin = $isAdmin;
        $this->isModerator = $isModerator;
        $this->isPublicAccount = $publicAccount;
        $this->isPublicEmail = $publicEmail;
        $this->nickname = $nickname;
        $this->email = $email;
        $this->password = $password;
        $this->avatar = $avatar;
        try {
            $this->dateOfBirth = $dateOfBirth ? (new DateTime($dateOfBirth))->format('Y-m-d') : '';
            if ($dateOfBirth != '') {
                $this->age = (new DateTime($this->dateOfBirth))->diff(new DateTime())->y;
            } else {
                $this->age = -1;
            }
        } catch (Exception $ex) {
            $this->age = -1;
        }
        $this->location = $location;
        $this->bio = $bio;
        try {
            $this->dateOfRegistration = (new DateTime($dateOfRegistration))->format('d.m.y');
        } catch (Exception $ex) {
            $this->dateOfRegistration = new DateTime();
        }
        $this->balance = $balance;
        $this->isPublicBalance = $isPublicBalance;
        $this->downloads = $downloads;
        $this->comments = $comments;
        $this->likes = $likes;
        $this->likedContent = json_decode($likedContent, true);
    }

    public static function tableName(): string {
        return 'users';
    }

    public function getActivationToken(): string {
        return $this->activationToken;
    }

    public function isActivated(): bool {
        return $this->isActivated;
    }

    public function isAdmin(): bool {
        return $this->isAdmin;
    }

    public function setIsAdmin(bool $isAdmin): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isAdmin = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isAdmin, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isAdmin = $isAdmin;
        }
        return $success;
    }

    public function isModer(): bool {
        return $this->isModerator;
    }

    public function setIsModer(bool $isModerator): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isModerator = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isModerator, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isModerator = $isModerator;
        }
        return $success;
    }

    public function isPublicAccount(): bool {
        return $this->isPublicAccount;
    }

    public function setIsPublicAccount(bool $isPublicAccount): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isPublicAccount = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isPublicAccount, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isPublicAccount = $isPublicAccount;
        }
        return $success;
    }

    public function isPublicEmail(): bool {
        return $this->isPublicEmail;
    }

    public function setIsPublicEmail(bool $isPublicEmail): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isPublicEmail = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isPublicEmail, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isPublicEmail = $isPublicEmail;
        }
        return $success;
    }

    public function getNickname(): string {
        return $this->nickname;
    }

    public function setNickname(string $nickname): array {
        $errors = [];
        if (!self::validateNicknameLength($nickname)) {
            $errors['NicknameLength'] = 'Invalid';
        }

        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'UPDATE '.self::tableName().' SET nickname = :value WHERE ID = :ID';
            $result = $db->prepare($query);
            $result->bindParam(':value', $nickname);
            $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
            $success = $result->execute();
            if ($success) {
                $this->nickname = $nickname;
            } else {
                $errors['Nickname'] = 'DBInternalError';
            }
        }
        return $errors;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function getAvatar(): string {
        return $this->avatar.'?'.time();
    }

    public function getPureAvatar(): string {
        return $this->avatar;
    }

    public function updateAvatar(array $file = []): array {
        $errors = self::validateAvatar($file);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'UPDATE '.self::tableName().' SET avatar = :value WHERE ID = :ID';
            $result = $db->prepare($query);
            $newAvatar = empty($file) ? 'default.jpg' : $this->ID.'.jpg';
            $result->bindParam(':value', $newAvatar);
            $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
            $success = $result->execute();
            if ($success) {
                $this->avatar = $newAvatar;
            } else {
                $errors['Avatar'] = 'DBInternalError';
            }
        }
        return $errors;
    }

    public function getAge(): string {
        return $this->age != -1 ? strval($this->age) : '';
    }

    protected function setAge(string $dateOfBirth):bool  {
        try {
            if (!$dateOfBirth) {
                throw new Exception();
            }
            $this->age = (new DateTime($dateOfBirth))->diff(new DateTime())->y;
            return true;
        } catch (Exception $ex) {
            $this->age = -1;
            return false;
        }
    }

    public function getDateOfBirth(): string {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(string $dateOfBirth): array {
        $errors = self::validateBirthday($dateOfBirth);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'UPDATE ' . self::tableName() . ' SET dateOfBirth = :value WHERE ID = :ID';
            $result = $db->prepare($query);
            $result->bindParam(':value', $dateOfBirth);
            $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
            $success = $result->execute();
            if ($success) {
                $this->setAge($dateOfBirth);
                $this->dateOfBirth = $dateOfBirth;
            } else {
                $errors['Birthday'] = 'DBInternalError';
            }
        }
        return $errors;
    }

    public function deleteBirthday(): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET dateOfBirth = NULL WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->setAge('');
            $this->dateOfBirth = '';
        }
        return $success;
    }

    public function getLocation(): string {
        return $this->location;
    }

    public function setLocation(string $location): array {
        $errors = self::validateLocation($location);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'UPDATE '.self::tableName().' SET location = :value WHERE ID = :ID';
            $result = $db->prepare($query);
            $result->bindParam(':value', $location);
            $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
            $success = $result->execute();
            if ($success) {
                $this->location = $location;
            } else {
                $errors['Location'] = 'DBInternalError';
            }
        }
        return $errors;
    }

    public function getBio(): string {
        return $this->bio;
    }

    public function setBio(string $bio): array {
        $errors = self::validateBio($bio);
        if (empty($errors)) {
            $db = DB::getConnection();
            $query = 'UPDATE '.self::tableName().' SET bio = :value WHERE ID = :ID';
            $result = $db->prepare($query);
            $result->bindParam(':value', $bio);
            $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
            $success = $result->execute();
            if ($success) {
                $this->bio = $bio;
            } else {
                $errors['Bio'] = 'DBInternalError';
            }
        }
        return $errors;
    }

    public function getDateOfRegistration(): string {
        return $this->dateOfRegistration;
    }

    public function getBalance(): int {
        return $this->balance;
    }

    protected function setBalance(int $value): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET balance = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $value);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->balance = $value;
        }
         return $success;
    }

    public function increaseBalance(int $value): bool {
        return $value >= 0 && $this->setBalance($this->balance + $value);
    }

    public function decreaseBalance(int $value): bool {
        return $value >= 0 && $this->balance >= $value && $this->setBalance($this->balance - $value);
    }

    public function isPublicBalance(): bool {
        return $this->isPublicBalance;
    }

    public function setIsPublicBalance(bool $isPublicBalance): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET isPublicBalance = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $isPublicBalance, PDO::PARAM_BOOL);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->isPublicBalance = $isPublicBalance;
        }
        return $success;
    }

    public function getDownloads(): int {
        return $this->downloads;
    }

    protected function setDownloads(int $downloads): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET downloads = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $downloads);
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

    public function decreaseDownloads(): bool {
        return $this->setDownloads($this->downloads - 1);
    }

    public function getComments(): int {
        return $this->comments;
    }

    protected function setComments(int $comments): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET comments = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $comments);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->comments = $comments;
        }
        return $success;
    }

    public function increaseComments(): bool {
        return $this->setComments($this->comments + 1);
    }

    public function decreaseComments(): bool {
        return $this->setComments($this->comments - 1);
    }

    public function getLikes(): int {
        return $this->likes;
    }

    protected function setLikes(int $likes): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.self::tableName().' SET likes = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $likes);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->likes = $likes;
        }
        return $success;
    }

    public function getLikedContent(string $contentName = ''): array {
        return $contentName == '' ? $this->likedContent : ($this->likedContent[$contentName] ?? []);
    }

    protected function setLikedContent(string $likedContent): bool {
        $db = DB::getConnection();
        $query = 'UPDATE '.static::tableName().' SET likedContent = :value WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':value', $likedContent);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->likedContent = json_decode($likedContent, true);
        }
        return $success;
    }

    /**
     * Метод для обработки лайка от пользователя.
     * Увеличивает счетчик лайков и заносит ресурс идентификатор ресурса в массив
     * @param LikableEntity $resource Ресурс, который можно лайкнуть
     * @return bool True, в случае успеха, иначе false
     */
    public function like(LikableEntity &$resource): bool {
        $likedContent = $this->getLikedContent();
        $likes = $this->getLikes();

        $likedContentBackup = $likedContent;
        $likesBackup = $likes;

        $likedResources = $this->getLikedContent($resource->tableNameInst());

        if (!in_array($resource->getID(), $likedResources)) {
            $likes++;
            $likedResources[] = $resource->getID();
            $likedContent[$resource->tableNameInst()] = $likedResources;
        } else {
            $likes--;
            foreach ($likedResources as $key => $likedResource) {
                if ($resource->getID() == $likedResource) {
                    unset($likedResources[$key]);
                    break;
                }
            }
            $likedContent[$resource->tableNameInst()] = array_values($likedResources);
        }
        $likedContent = json_encode($likedContent);
        $likedContentBackup = json_encode($likedContentBackup);
        $success = $this->setLikedContent($likedContent) && $this->setLikes($likes);
        if ($success) {
            $success = $resource->doLike($this);
        }
        if (!$success) {
            $this->setLikes($likesBackup);
            $this->setLikedContent($likedContentBackup);
        }
        return $success;
    }

}