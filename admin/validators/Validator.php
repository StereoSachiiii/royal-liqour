<?php


interface ValidatorInterface {
    public static function validateCreate(array $data): array;
    public static function validateUpdate(array $data): array;

}

?>