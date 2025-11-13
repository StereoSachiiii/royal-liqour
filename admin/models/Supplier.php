<?PHP


class SupplierModel{

    private ?int $id;
    private ?string $name;
    private ?string $email;
    private ?int $phone;
    private ?int $address;
    private ?bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $deletedAt;


    /**
     * @param int|null $id
     * @param string|null $name
     * @param string|null $email
     * @param int|null $phone
     * @param string|null $address
     * @param bool|null $isActive
     * @param string|null $createdAt
     * @param string|null $updatedAt
     * @param string|null $deletedAt 
     *     */
    public function __construct(
        $id = null,
        $name = null,
        $email = null,
        $phone = null,
        $address = null,
        $isActive = null,
        $createdAt = null,
        $updatedAt = null,
        $deletedAt = null

    ){

        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->address = $address;
        $this->isActive = $address;
        $this->createdAt = $createdAt;;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;


    }

    public function getId():int|null{
        return $this->id;
    }
    public function getName():string|null{
        return $this->name;
    }

    public function getEmail():string|null{
        return $this->email;
    }

    public function getPhone():int|null{
        return $this->phone;
    }
    public function getAddress():string|null{
        return $this->address;
    }
    public function getIsActive():bool|null{
        return $this->isActive;
    }

    public function getCreatedAt():string|null{
        return $this->createdAt;
    }

    public function getUpdatedAt() : string|null {
        return $this->updatedAt;
    }

    public function toArray():array{
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "address" => $this->address,
            "is_active" => $this->isActive,
            "created_at" => $this->createdAt,
            "updated_at" => $this->updatedAt,
            "deleted_at" => $this->deletedAt,

        ];


    }







}




















?>