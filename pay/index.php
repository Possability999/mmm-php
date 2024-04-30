<?php
require_once '../../vendor/autoload.php';

$pdo = new PDO('mysql:host=localhost;dbname=postfixadmin', 'postfixadmin', '6tr0n6p455w0rd', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);


if (isset($_GET['hash'])) {
    $hash = $_GET['hash'];

    // First, check if the hash exists in the 'hashes' table
    $stmt = $pdo->prepare("SELECT rcpt FROM hashes WHERE hash = ?");
    $stmt->execute([$hash]);
    $rcpt = $stmt->fetchColumn();

    if ($rcpt) {
        // Log the visit to hash_visits table
        $stmt = $pdo->prepare("INSERT INTO hash_visits (hash) VALUES (?)");
        $stmt->execute([$hash]);

        echo "You about to order food for " . htmlspecialchars($rcpt) . ".";
    } else {
        echo "Link has expired.";
        exit;
        // Optional: Handle logic for unrecognized hashes or redirect to an error page
    }
} else {
    echo "No hash provided.";
    exit;
    // Handle cases where no hash is provided in the GET request
}

\Stripe\Stripe::setApiKey('sk_test_51OrbqEP6NMhaC0NlgZFDksn4TOlJpaQCXLXiiBc592acPT73lalGqWlk1BcOrKxLiRXg8HYwr16jdQa25oc9pPv000e0l16rJe');

// Check for hash in the query string
$hash = $_GET['hash'] ?? 'defaultHash';  // Default or fetched from GET

// Define services
$services = [
    'soup' => 99, // price in cents
    'pizza' => 999, // price in cents
    'pasta' => 9999, // price in cents
];

// Service selection and Stripe Checkout initiation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['service'], $services[$_POST['service']])) {
    $serviceName = $_POST['service'];
    $price = $services[$serviceName];

    try {
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $serviceName,
                        ],
                        'unit_amount' => $price,
                    ],
                    'quantity' => 1,
                ],
            ],
            'metadata' => [
                'hash' => $hash,
                'service_name' => $serviceName 
            ],
            'mode' => 'payment',
            'success_url' => '/paid/?hash=' . $hash . '&session_id={CHECKOUT_SESSION_ID}', // Ensure to include session_id
            'cancel_url' => '/pay/?canceled=true',
        ]);

        header('Location: ' . $session->url);
        exit;
    } catch (\Exception $e) {
        error_log('Error creating Stripe session: ' . $e->getMessage());
        echo "Failed to create payment session.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Service</title>
</head>
<body>
    <form action="/pay/?hash=<?= htmlspecialchars($hash) ?>" method="post">
        <select name="service">
            <?php foreach ($services as $key => $value): ?>
                <option value="<?= $key ?>"><?= $key ?> ($<?= number_format($value / 100, 2) ?>)</option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Pay Now</button>
    </form>
</body>
</html>
