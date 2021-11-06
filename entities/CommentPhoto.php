<?php


/**
 * Класс для сущности "Комментарий фото"
 */
class CommentPhoto extends AbstractComment {

    public static function getCommentsList(CommentableViewableEntity &$resource): array {
        $commentsList = array();
        $db = DB::getConnection();
        $query = 'SELECT ID FROM '.self::tableName().' WHERE referredResourceID = :ID';
        $result = $db->prepare($query);
        $resourceID = $resource->getID();
        $result->bindParam(':ID', $resourceID, PDO::PARAM_INT);
        $result->execute();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $commentsList[] = &CommentPhoto::newInstance($row['ID']);
        }
        return $commentsList;
    }

    protected static function &getByID($ID): CommentPhoto {
        $db = DB::getConnection();
        $query = 'SELECT * FROM '.self::tableName().' WHERE ID = :ID';
        $result = $db->prepare($query);
        $result->bindParam(':ID', $ID, PDO::PARAM_INT);
        $result->execute();
        $result = $result->fetch(PDO::FETCH_ASSOC);
        if ($result) {
            $author = &User::newInstance($result['authorID']);
            $resource = &Photo::newInstance($result['referredResourceID']);
            $result = new CommentPhoto($ID, $author, $resource, $result['content'], $result['dateOfPublication'],
                        $result['likes'], $result['likedBy']);
        } else {
            $result = null;
        }

        return $result;
    }
    public static function &newInstance(int $ID): CommentPhoto {
        return parent::newInstance($ID);
    }

    public static function tableName(): string {
        return 'comments_photo';
    }
}