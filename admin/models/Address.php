<?php 
class Address{

    private ?int $id;
    private ?int $userId;
    private ?string $addressType;
    private ?string $addressLineMain;
    private ?string $addressLineSecondary;
    private ?string $city;
    private ?string $state;
    private ?string $postalCode;
    private ?string $country;
    private ?bool $isDefault;
    private ?bool $isActive;
    private ?string $createdAt;
    private ?string $updatedAt;
    private ?string $deletedAt;

    /**
     * @param int|null    $id                   Address ID (primary key, auto-incremented).     
     * @param int|null    $userId               ID of the user associated with the address.
     * @param string|null $addressType          Type of address (e.g., 'shipping', 'billing').
     * @param string|null $addressLineMain      Main address line.
     * @param string|null $addressLineSecondary Secondary address line (optional).
     * @param string|null $city                 City of the address.
     * @param string|null $state                State or province of the address.
     * @param string|null $postalCode           Postal or ZIP code of the address.
     * @param string|null $country              Country of the address.
     * @param bool|null   $isDefault            Whether this is the default address for the user.
     * @param bool|null   $isActive             Whether the address is active.
     * @param string|null $createdAt            Timestamp when the address was created.
     * @param string|null $updatedAt            Timestamp when the address was last updated.
     * @param string|null $deletedAt            Timestamp when the address was deleted (nullable).
     */

    public function __construct(
        $id = null,
        $userId = null,
        $addressType = null,
        $addressLineMain = null,
        $addressLineSecondary = null,
        $city = null,
        $state = null,
        $postalCode = null,
        $country = null,
        $isDefault = null,
        $isActive = null,
        $createdAt = null,
        $updatedAt = null,
        $deletedAt = null
    ){
        $this->id = $id;
        $this->userId = $userId;
        $this->addressType = $addressType;
        $this->addressLineMain = $addressLineMain;
        $this->addressLineSecondary = $addressLineSecondary;
        $this->city = $city;
        $this->state = $state;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->isDefault = $isDefault;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
    }

    public function getId(): ?int {
        return $this->id;
    }
    public function getUserId(): ?int {
        return $this->userId;
    }
    public function getAddressType(): ?string {
        return $this->addressType;
    }
    public function getAddressLineMain(): ?string {
        return $this->addressLineMain;
    }
    public function getAddressLineSecondary(): ?string {
        return $this->addressLineSecondary;
    }
    public function getCity(): ?string {
        return $this->city;
    }
    public function getState(): ?string {
        return $this->state;
    }

    public function getPostalCode(): ?string {
        return $this->postalCode;
    }

    public function getCountry(): ?string {
        return $this->country;
    }
    public function getIsDefault(): ?bool {
        return $this->isDefault;
    }

    public function getIsActive(): ?bool {
        return $this->isActive;
    }
    public function getCreatedAt(): ?string {
        return $this->createdAt;
    }
    public function getUpdatedAt(): ?string {
        return $this->updatedAt;
    }
    public function getDeletedAt(): ?string {
        return $this->deletedAt;
    }

}





















?>