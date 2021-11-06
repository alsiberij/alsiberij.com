<?php

/**
 * Абстрактный класс для любой сущности, хранящейся в БД, у которой первичный ключ это числовой идентификатор
 */
abstract class Entity {

    /**
     * @var int Идентификатор сущности
     */
    protected int $ID;
    /**
     * @var array Массив сконструированных объектов
     */
    protected static array $instances = array();


    /**
     * Абстрактный метод для получения объекта сущности по идентификатору через обращение к БД
     * @param int $ID Идентификатор сущности
     * @return ?Entity Ссылка на объект сущности, либо null
     */
    protected static abstract function &getByID(int $ID): ?Entity;

    /**
     * Метод для проверки, сконструирован ли объект данной сущности
     * @param int $ID Идентификатор
     * @return ?Entity Ссылка на объект сущности, либо null
     */
    protected static final function &checkInstance(int $ID): ?Entity {
        $instance = null;
        if (!isset(self::$instances[static::tableName()])) {
            self::$instances[static::tableName()] = [];
        } else {
            foreach (self::$instances[static::tableName()] as &$checkedInstance) {
                if ($checkedInstance->getID() == $ID) {
                    return $checkedInstance;
                }
            }
        }
        return $instance;
    }

    /**
     * Метод-конструктор для получения новых объектов сущностей
     * @param int $ID Идентификатор сущности
     * @return ?Entity Ссылка на объект сущности, либо null
     */
    public static function &newInstance(int $ID): ?Entity {
        $instance = &self::checkInstance($ID);
        if ($instance == null) {
            $instance = &static::getByID($ID);
            if ($instance != null) {
                self::$instances[static::tableName()][] = $instance;
            }
        }
        return $instance;
    }

    public function __construct(int $ID) {
        $this->ID = $ID;
    }

    public final function getID(): int {
        return $this->ID;
    }

    /**
     * Статический абстрактный метод для связывания класса сущности с таблицей БД
     * @return string Имя таблицы для данной сущности
     */
    public static abstract function tableName(): string;

    /**
     * Абстрактный метод для связывания класса сущности с таблицей БД
     * @return string
     */
    public final function tableNameInst(): string {
        return static::tableName();
    }
}