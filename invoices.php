<?php
header("Access-Control-Allow-Origin: http://lms.ennovat.com:3000");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");
header("Access-Control-Allow-Credentials: true");

// Temporarily display errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php'; 

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['logged_in' => false, 'redirect' => '/signin']);
    exit();
}
$u_id = $_SESSION['u_id']; 

try {
    // Step 1: Get service_id and company_name from the services table based on u_id
    $stmt = $conn->prepare("
        SELECT service_id, company_name 
        FROM services 
        WHERE u_id = :u_id
    ");
    $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare an array to store the fetched invoices
    $invoices = [];

    // Step 2: Loop through each service and get related invoices
    foreach ($services as $service) {
        $service_id = $service['service_id'];
        $company_name = $service['company_name'];

        // Fetch the invoices related to the current service
        $stmt = $conn->prepare("
            SELECT id, due_date, status, payment_date, amount, invoice_id 
            FROM invoices 
            WHERE service_id = :service_id 
            ORDER BY id DESC
        ");
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $stmt->execute();
        $serviceInvoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add company_name to each invoice and merge with the main invoices array
        foreach ($serviceInvoices as &$invoice) {
            $invoice['company_name'] = $company_name; // Add company name to each invoice
        }

        // Merge invoices into the main array
        $invoices = array_merge($invoices, $serviceInvoices);
    }

    // Count total invoices
    $totalinvoices = count($invoices);

    // Count paid invoices
    $paidinvoices = count(array_filter($invoices, function($invoice) {
        return strtolower($invoice['status']) === 'paid';
    }));
    $due_date = $invoice['due_date'];
    $totalDays = ($totalinvoices - 1) * 30;
    $next_due_date = new DateTime($due_date);
    $next_due_date->modify('+'.$totalDays.' days');
    $next_due = $next_due_date->format('m/d/Y');
    // Prepare the JSON response
    $response = [
        'invoices' => $invoices,
        'totalinvoices' => $totalinvoices,
        'paidinvoices' => $paidinvoices,
        'nextdue' => $next_due
    ];

    echo json_encode($response);
} catch (Exception $e) {
    // Catch any exceptions, including fatal errors
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}
?>
