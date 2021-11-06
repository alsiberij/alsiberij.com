<?php

/**
 * Абстрактный класс для сущности, которой может быть поставлен лайк
 */
abstract class LikableEntity extends Entity {

    /**
     * @var int Количество лайков у сущности
     */
    protected int $likes;
    /**
     * @var int[] Массив с идентификаторами пользователей, лайкнувших данную сущность
     */
    protected array $likedBy;


    public function __construct(int $ID, int $likes, string $likedBy) {
        parent::__construct($ID);
        $this->likes = $likes;
        $this->likedBy = json_decode($likedBy, true)['users'];
    }

    public final function getLikes(): int {
        return $this->likes;
    }

    protected final function setLikes(int $likes): bool {
        $db = DB::getConnection();
        $query = "UPDATE {$this->tableNameInst()} SET likes = :value WHERE ID = :ID";
        $result = $db->prepare($query);
        $result->bindParam(':value', $likes, PDO::PARAM_INT);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->likes = $likes;
        }
        return $success;
    }

    public final function increaseLikes(): bool {
        return $this->setLikes($this->likes + 1);
    }

    public final function decreaseLikes(): bool {
        return $this->setLikes($this->likes - 1);
    }

    public final function getLikedBy(): array {
        return $this->likedBy;
    }

    protected final function setLikedBy(array $likedBy): bool {
        $db = DB::getConnection();
        $query = "UPDATE {$this->tableNameInst()} SET likedBy = :likedBy WHERE ID = :ID";
        $result = $db->prepare($query);
        $likedByJson = json_encode(['users' => $likedBy]);
        $result->bindParam(':likedBy', $likedByJson);
        $result->bindParam(':ID', $this->ID, PDO::PARAM_INT);
        $success = $result->execute();
        if ($success) {
            $this->likedBy = $likedBy;
        }
        return $success;
    }

    /**
     * Метод для обработки поступившего лайка от пользователя.
     * Увеличивает счетчик лайков и заносит идентификатор лайкнувшего пользователя в массив
     * @param User $user Объект пользователя
     * @return bool True, в случае успеха, иначе false
     */
    public final function doLike(User &$user): bool {
        $userID = $user->getID();

        $likes = $this->getLikes();
        $likedBy = $this->getLikedBy();
        $likesBackup = $likes;
        $likedByBackup = $likedBy;

        if (!in_array($userID, $likedBy)) {
            $likes++;
            $likedBy[] = $userID;
        } else {
            $likes--;
            foreach ($likedBy as $key => $likedUser) {
                if ($userID == $likedUser) {
                    unset($likedBy[$key]);
                    break;
                }
            }
            $likedBy = array_values($likedBy);
        }

        $success = $this->setLikes($likes) && $this->setLikedBy($likedBy);
        if (!$success) {
            $this->likes = $likesBackup;
            $this->likedBy = $likedByBackup;
        }
        return $success;
    }
}