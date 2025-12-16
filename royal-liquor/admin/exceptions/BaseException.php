<?php 


abstract class BaseException extends Exception{

    protected int $statusCode = 500;
    protected array $context = [];

    public function __construct(
        string $message = "",
        array $context = [],
        int $code = 0,
        ?Throwable $previous = null
    ){
        $this->context = $context;
        parent::__construct($message,$code,$previous);
    }

    public function getStatusCode():int{
        return $this->statusCode;
    }

    public function getContext():array{
        return $this->context;
    }

    public function toArray():array{
        return [
            'error' => $this->getMessage(),
            'context' => $this->context
        ];
    }










}










?>