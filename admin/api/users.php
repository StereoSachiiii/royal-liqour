<?php

declare(strict_types=1);

require_once __DIR__ . '/../controllers/UserController.php';

 

  header('Content-Type: application/json');

  $method = $_SERVER['REQUEST_METHOD'];
  $action = $_GET['action'] ?? $_POST['action'] ?? '';

  $userController = new UserController();

  try {
      switch ($method) {
          case 'POST':
              $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
              switch ($action) {
                  case 'register':
                      echo json_encode($userController->register($data));
                      break;
                  case 'login':
                      echo json_encode($userController->login($data));
                      break;
                  case 'updateProfile':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      echo json_encode($userController->updateProfile((int)$_SESSION['user_id'], $data));
                      break;
                  case 'anonymizeUser':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      echo json_encode($userController->anonymizeUser((int)$_SESSION['user_id']));
                      break;
                  case 'createAddress':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      echo json_encode($userController->createAddress((int)$_SESSION['user_id'], $data));
                      break;
                  case 'updateAddress':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      $addressId = (int)($data['address_id'] ?? 0);
                      if ($addressId <= 0) {
                          echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
                          exit;
                      }
                      echo json_encode($userController->updateAddress($addressId, $data));
                      break;
                  default:
                      echo json_encode(['success' => false, 'message' => 'Invalid action']);
                      break;
              }
              break;

          case 'GET':
              switch ($action) {
                  case 'getProfile':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      echo json_encode($userController->getProfile((int)$_SESSION['user_id']));
                      break;
                  case 'getAddresses':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      $addressType = $_GET['address_type'] ?? null;
                      echo json_encode($userController->getAddresses((int)$_SESSION['user_id'], $addressType));
                      break;
                  case 'getAllUsers':
                      $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
                      $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
                      echo json_encode($userController->getAllUsers($limit, $offset));
                      break;
                  case 'deleteAddress':
                      session_start();
                      if (!isset($_SESSION['user_id'])) {
                          echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                          exit;
                      }
                      $addressId = (int)($_GET['address_id'] ?? 0);
                      if ($addressId <= 0) {
                          echo json_encode(['success' => false, 'message' => 'Invalid address ID']);
                          exit;
                      }
                      echo json_encode($userController->deleteAddress($addressId));
                      break;
                  default:
                      echo json_encode(['success' => false, 'message' => 'Invalid action']);
                      break;
              }
              break;

          default:
              echo json_encode(['success' => false, 'message' => 'Invalid request method']);
              break;
      }
  } catch (Exception $e) {
      http_response_code(500);
      echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
  }

  ?>
  