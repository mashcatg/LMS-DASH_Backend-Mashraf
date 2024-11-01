<?php
// Required headers for CORS and content type
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
session_start();
include 'db.php';

if (!isset($_SESSION['u_id'])) {
    echo json_encode(['error' => 'User not authenticated']);
    exit();
}

$u_id = $_SESSION['u_id'];

try {
    // Fetch services for the logged-in user
    $stmt = $conn->prepare("SELECT id, service_id, company_name AS company, sub_domain, status, created_at 
                            FROM services 
                            WHERE u_id = :u_id 
                            ORDER BY created_at DESC");
    $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalUnpaidInvoicesCount = 0;
    $totalUnpaidInvoicesAmount = 0;

    // Loop through each service to fetch invoices
    foreach ($services as $service) {
        $service_id = $service['service_id'];

        // Fetch invoices for each service
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE service_id = :service_id ORDER BY time DESC");
        $stmt->bindParam(':service_id', $service_id, PDO::PARAM_INT);
        $stmt->execute();
        $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Filter unpaid invoices and calculate total unpaid amount
        $unpaidInvoices = array_filter($invoices, function($invoice) {
            return strtolower($invoice['status']) !== 'paid';
        });
        $totalUnpaidInvoicesCount += count($unpaidInvoices);
        $totalUnpaidInvoicesAmount += array_reduce($unpaidInvoices, function($carry, $invoice) {
            return $carry + $invoice['amount'];
        }, 0);
    }

    // Fetch support tickets for the logged-in user
    $stmt = $conn->prepare("SELECT id, t_id, problem_statement, status 
                            FROM tickets 
                            WHERE u_id = :u_id 
                            ORDER BY time DESC");
    $stmt->bindParam(':u_id', $u_id, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare the response data
    $response = [
        'logged_in' => true,
        'redirect' => '/',
        'services' => $services,
        'tickets' => $tickets,
        'totalServices' => count($services),
        'totalTickets' => count($tickets),
        'unpaidInvoicesCount' => $totalUnpaidInvoicesCount,
        'unpaidInvoicesAmount' => $totalUnpaidInvoicesAmount,
        'u_id' => $u_id
    ];

    // Send the response as a single JSON object
    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Error fetching data: ' . $e->getMessage()]);
}
?>
