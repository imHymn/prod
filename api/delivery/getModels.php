<?php
require_once __DIR__ . '/../header.php';

use Model\DeliveryModel;
use Validation\DeliveryValidator;

if (isset($_GET['customer'])) {
    $customerName = $_GET['customer'];
    try {

        $validator = DeliveryValidator::validateCustomerName($customerName);
        if (!empty($validator)) {
            echo json_encode(['success' => false, 'errors' => $validator]);
            exit;
        };
        $model = new DeliveryModel($db);
        $sql = $model->selectDistinctModelName($customerName);

        echo json_encode($sql);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Customer parameter missing']);
}
