<?php
require_once __DIR__. '/Validator.php';
require_once __DIR__. '/../repositories/UserRepository.php';
require_once __DIR__. '/../exceptions/ValidationException.php';
class UserValidator implements ValidatorInterface{

private static ?UserRepository $userRepository = null;

    public  function __construct() {
    if (self::$userRepository === null) {
        self::$userRepository = new UserRepository();
    }
    }

  

    /**
 * Validate user data for creation.
 *
 * @param array{name: string, email: string, phone?: string|null, password: string} $data
 *        - name: required, 1-100 characters
 *        - email: required, valid format, max 254 characters
 *        - phone: optional, string up to 15 digits, may start with +
 *        - password: required, minimum 6 characters
 *
 * @throws \InvalidArgumentException if any validation rule fails
 *
 * @return array{success: bool, message?: string} Returns success true if valid, or false with message for missing required fields
 */
    public static function validateCreate(
       array $data 
    ):array{

       if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
            throw new ValidationException(
                message: "Name, email, and password are required",
                context: [
                    'missing_fields' => array_keys(
                        array_filter(
                            ['name' => empty($data['name']), 'email' => empty($data['email']), 'password' => empty($data['password'])],
                            fn($v) => $v
                        )
                    )
                ]
            );
        }

        //name
        if (strlen($data['name']) < 5 || strlen($data['name']) > 100) {
            throw new ValidationException(message:"Invalid argument. name should be 1 to 100 characters long",
            context:["field" => "name" , "value" => $data['name']]);
        }

        //email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 254) {
            throw new ValidationException(message:"Invalid argument. email is invalid",
            context:["field" => "name" , "value" => $data['email']]);  
        }

        //phone
        if ($data['phone'] !== null && !preg_match('/^\+?\d{0,15}$/', $data['phone'])) {
            throw new ValidationException(message:"Invalid argument. phone number is invalid",
            context:["field" => "name" , "value" => $data['phone']]);           
        }

        //password
        if (strlen($data['password']) < 6) {
            throw new ValidationException(message:"Invalid argument. password is too short",
            context:["field" => "name" , "value" => $data['password']]);  
        }
        return ['success' => true];
    }

/**
 * Validate user data for update.
 *
 * @param array{
 *     name?: string|null,
 *     email?: string|null,
 *     phone?: string|null,
 *     password?: string|null,
 *     profileImageUrl?: string|null,
 *     userId: int
 * } $data
 *
 * @return array{success: bool}
 * @throws ValidationException
 */
public static function validateUpdate(array $data): array
{
    // Name
    if (isset($data['name']) && $data['name'] !== null) {
        $len = strlen($data['name']);
        if ($len < 1 || $len > 100) {
            throw new ValidationException(
                message: "Invalid argument: name must be 1-100 characters long",
                context: ['field' => 'name', 'value' => $data['name']]
            );
        }
    }

    // Email
    if (isset($data['email']) && $data['email'] !== null) {
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 254) {
            throw new ValidationException(
                message: "Invalid argument: email format is invalid",
                context: ['field' => 'email', 'value' => $data['email']]
            );
        }

        $existingUser = self::$userRepository->getUserById($data['userId'])?->getEmail();
        if ($existingUser !== $data['email'] && self::$userRepository->getUserByEmail($data['email'])) {
            throw new ValidationException(
                message: "Email already registered",
                context: ['field' => 'email', 'value' => $data['email']]
            );
        }
    }

    // Phone
    if (isset($data['phone']) && $data['phone'] !== null && !preg_match('/^\+?\d{0,15}$/', $data['phone'])) {
        throw new ValidationException(
            message: "Invalid argument: phone number format is invalid",
            context: ['field' => 'phone', 'value' => $data['phone']]
        );
    }

    // Password
    if (isset($data['password']) && $data['password'] !== null && strlen($data['password']) < 6) {
        throw new ValidationException(
            message: "Invalid argument: password must be at least 6 characters",
            context: ['field' => 'password', 'value' => $data['password']]
        );
    }

    // Profile Image URL
    if (isset($data['profileImageUrl']) && $data['profileImageUrl'] !== null && strlen($data['profileImageUrl']) > 500) {
        throw new ValidationException(
            message: "Invalid argument: profile image URL too long",
            context: ['field' => 'profileImageUrl', 'value' => $data['profileImageUrl']]
        );
    }

    return ['success' => true];
}

/**
 * Summary of loginValidate
 * @param array{email:string,password:string} $data
 * @return array{success:bool}
 * @throws ValidationException
 */
public static function loginValidate(array $data):array{

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 254) {
            throw new ValidationException(message:"Invalid argument. email is invalid",
            context:["field" => "name" , "value" => $data['email']]);  
        }

        if (strlen($data['password']) < 6) {
            throw new ValidationException(message:"Invalid argument. password is too short",
            context:["field" => "name" , "value" => $data['password']]);  
        }

        return [
            'success' => true,
        ];

}

/**
 * returns if a profileId is valid data type
 * Summary of validateProfileId
 * @param int $userId
 * @throws \ValidationException
 * @return array{success: bool}
 */
public static function validateProfileId(int $userId):array{
        if (isset($userId)&&is_int($userId)) {
            throw new ValidationException(message:"Invalid argument. userId is invalid",
            context:["field" => "name" , "value" => $userId]);  
        }    
        return [
            'success' => true,
        ];
}
















}















?>