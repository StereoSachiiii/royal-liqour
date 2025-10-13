<?php


class UserModel {
    private $id;
    private $name;
    private $email;
    private $phone;
    private $passwordHash;
    private $profileImageUrl;
    private $isActive;
    private $isAdmin;
    private $isAnonymized;
    private $createdAt;
    private $updatedAt;
    private $deletedAt;
    private $anonymizedAt;
    private $lastLoginAt;

    // Constructor
    public function __construct(
        $id = null,
        $name = null,
        $email = null,
        $phone = null,
        $passwordHash = null,
        $profileImageUrl = null,
        $isActive = true,
        $isAdmin = false,
        $isAnonymized = false,
        $createdAt = null,
        $updatedAt = null,
        $deletedAt = null,
        $anonymizedAt = null,
        $lastLoginAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->passwordHash = $passwordHash;
        $this->profileImageUrl = $profileImageUrl;
        $this->isActive = $isActive;
        $this->isAdmin = $isAdmin;
        $this->isAnonymized = $isAnonymized;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->deletedAt = $deletedAt;
        $this->anonymizedAt = $anonymizedAt;
        $this->lastLoginAt = $lastLoginAt;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPhone() { return $this->phone; }
    public function getPasswordHash() { return $this->passwordHash; }
    public function getProfileImageUrl() { return $this->profileImageUrl; }
    public function isActive() { return $this->isActive; }
    public function isAdmin() { return $this->isAdmin; }
    public function isAnonymized() { return $this->isAnonymized; }
    public function getCreatedAt() { return $this->createdAt; }
    public function getUpdatedAt() { return $this->updatedAt; }
    public function getDeletedAt() { return $this->deletedAt; }
    public function getAnonymizedAt() { return $this->anonymizedAt; }
    public function getLastLoginAt() { return $this->lastLoginAt; }

    // Setters
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPhone($phone) { $this->phone = $phone; }
    public function setPasswordHash($passwordHash) { $this->passwordHash = $passwordHash; }
    public function setProfileImageUrl($profileImageUrl) { $this->profileImageUrl = $profileImageUrl; }
    public function setIsActive($isActive) { $this->isActive = $isActive; }
    public function setIsAdmin($isAdmin) { $this->isAdmin = $isAdmin; }
    public function setIsAnonymized($isAnonymized) { $this->isAnonymized = $isAnonymized; }
}