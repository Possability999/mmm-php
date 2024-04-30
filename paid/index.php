<?php
require_once '../../vendor/autoload.php';
\Stripe\Stripe::setApiKey('sk_test_51OrbqEP6NMhaC0NlgZFDksn4TOlJpaQCXLXiiBc592acPT73lalGqWlk1BcOrKxLiRXg8HYwr16jdQa25oc9pPv000e0l16rJe');

try {
    // Check if the session_id is present in the GET request from Stripe redirect
    if (!isset($_GET['session_id'])) {
        throw new Exception("Session ID not provided");
    }

    $pdo = new PDO('mysql:host=localhost;dbname=postfixadmin', 'postfixadmin', '6tr0n6p455w0rd', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    // Retrieve the session ID from the GET request
    $session_id = $_GET['session_id'];

    // Retrieve the Stripe session
    $session = \Stripe\Checkout\Session::retrieve($session_id);

    // Check if a payment with this session_id has already been processed
    $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM processed_payments WHERE session_id = ?");
    $checkStmt->execute([$session_id]);
    if ($checkStmt->fetchColumn() > 0) {
        echo "This hash has already been processed.<br>";
        exit;
    }

    // Retrieve the hash and service name stored in session metadata
    if (!isset($session->metadata->hash) || !isset($session->metadata->service_name)) {
        throw new Exception("Required metadata is missing");
    }
    $amount_paid = $session->amount_total; // Get total amount paid from the session
    $hash = $session->metadata->hash;
    $serviceName = $session->metadata->service_name;

    // Compare the hash from the session with the hash in the URL query for extra security
    if ($hash !== ($_GET['hash'] ?? '')) {
        throw new Exception("Hash mismatch");
    }


    $stmt = $pdo->prepare("INSERT INTO processed_payments (hash, money, service_name, session_id) VALUES (?, ?, ?, ?)");
+   $stmt->execute([$hash, $amount_paid, $serviceName, $session_id]);
 

    $stmt = $pdo->prepare("SELECT rcpt FROM hashes WHERE hash = ?");
    $stmt->execute([$hash]);
    $rcpt = $stmt->fetchColumn();
    list($localPart, $domain) = explode('@', $rcpt);
    $unicodeDomain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
    $rcpt = $localPart . '@' . $unicodeDomain;
    
    // All checks have passed, now confirm the payment and service information
    echo "Payment confirmed! Mail is resumed.<br>";
    //echo "Payment confirmed for hash: " . htmlspecialchars($hash) . "<br>";
    //echo "Service selected: " . htmlspecialchars($serviceName)."<br>";
    echo "<br>Recipient email: " . htmlspecialchars($rcpt)."<br>";
    echo "Amount paid: $" . number_format($amount_paid / 100, 2) . "<br>";

    // Here you can also perform additional actions such as:
    // - Updating database records
    // - Sending confirmation emails
    // - Logging transaction details
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle Stripe API-specific errors
    error_log('Stripe API Error: ' . $e->getMessage());
    echo "Stripe API processing error: " . htmlspecialchars($e->getMessage());
} catch (\Stripe\Exception\UnexpectedValueException $e) {
    // Handle errors due to unexpected data
    error_log('Unexpected Value Error: ' . $e->getMessage());
    echo "Unexpected data format error: " . htmlspecialchars($e->getMessage());
} catch (Exception $e) {
    // Handle general errors
    error_log('Error: ' . $e->getMessage());
    echo "Error: " . htmlspecialchars($e->getMessage());
}
?>
