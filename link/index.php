<?php

// Enable error logging to a file
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/error.log');

// Log all errors, except notices
error_reporting(E_ALL & ~E_NOTICE);

require_once '../vendor/autoload.php';
require_once '../myfuncs.php';
require_once '../lmtp.php';
require_once '../marketingtext_echo.php';

$loader = new \Twig\Loader\FilesystemLoader('/templates');
$twig = new \Twig\Environment($loader);

$pdo = new PDO('mysql:host=haad-db.cp8ms6gem1wy.us-east-1.rds.amazonaws.com;dbname=haad', 'admin', '6tr0n6p455w0rd', [
//$pdo = new PDO('mysql:host=localhost;dbname=postfixadmin', 'postfixadmin', '6tr0n6p455w0rd', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

if (!empty($_SERVER['QUERY_STRING'])) {
    $queryString = $_SERVER['QUERY_STRING'];
    $hash = explode('&', $queryString, 2)[0];

    // Extract only the valid base32 portion of the hash until the first invalid character
    preg_match('/^[a-z2-7]+/', $hash, $matches);
    $hash = $matches[0] ?? '';  // Use the first match or default to an empty string if no match

    // Check if the hash has already been processed for payment
    $checkPayment = $pdo->prepare("SELECT id FROM processed_payments WHERE hash = ?");
    $checkPayment->execute([$hash]);
    if ($checkPayment->fetchColumn()) {
        echo "This link has been redeemed.";
        exit;
    }

    $stmt = $pdo->prepare("SELECT rcpt, service_type FROM hashes WHERE hash = ?");
    $stmt->execute([$hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $rcpt = $row['rcpt'];
        $service_type = $row['service_type'];

        $directIP = $_SERVER['REMOTE_ADDR'];
        $proxyIP = getClientIP();

        // Log the visit to hash_visits table with IP information
        $stmt = $pdo->prepare("INSERT INTO hash_visits (hash, direct_ip, proxy_ip) VALUES (?, ?, ?)");
        $stmt->execute([$hash, $directIP, $proxyIP]);

        // Assuming $rcpt contains the email address like 'echo@xn--hd-viaa.com'
        list($localPart, $domain) = explode('@', $rcpt);
       // $unicodeDomain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        $unicodeDomain = idn_to_utf8($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46, $idna_info);

        if ($service_type == 2) {
             // Resume on click
             lmtp($hash);
            echo $twig->render('resumed.twig', [
            'title' => 'Hääd und Wolf OÜ - Innovative Email Security'
            ]);
            // echo "Thank you! Email delivery to " . htmlspecialchars($localPart . '@' . $unicodeDomain) . " has resumed.";
        } else if ($service_type == 5) {
             // ECHO service
             lmtp($hash,1);
            echo $twig->render('echo.twig', [
            'title' => 'Hääd und Wolf OÜ - Innovative Email Security'
            ]);
             //marketingtext_echo($localPart . '@' . $unicodeDomain);
        } elseif ($service_type == 3) {
            // Resume on payment
            $prices = [
                3 => 99,    // price in cents for service type 3
            ];

            $price = $prices[$service_type];

            \Stripe\Stripe::setApiKey('sk_test_51OrbqEP6NMhaC0NlgZFDksn4TOlJpaQCXLXiiBc592acPT73lalGqWlk1BcOrKxLiRXg8HYwr16jdQa25oc9pPv000e0l16rJe');

            try {
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'line_items' => [[
                        'price_data' => [
                            'currency' => 'usd',
                            'product_data' => [
                                'name' => "Fee for sending an email to ".$localPart.'@'.$unicodeDomain,
                            ],
                            'unit_amount' => $price,
                        ],
                        'quantity' => 1,
                    ]],
                    'metadata' => [
                        'hash' => $hash,
                        'service_name' => "Service Type" . $service_type,
                        'email' => $rcpt
                    ],
                    'mode' => 'payment',
                    'success_url' => 'https://lev.ee/paid/?hash=' . $hash . '&session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => 'https://lev.ee/pay/?canceled=true',
                ]);
                header('Location: ' . $session->url);
                exit;
            } catch (\Exception $e) {
                error_log('Error creating Stripe session: ' . $e->getMessage());
                echo "Failed to create payment session.";
            }
        } else {
            // Should never happen! User has valid link but it indicates service_type we do not know how to handle
            echo $twig->render('fatal.twig', [
            'title' => 'Hääd und Wolf OÜ - Innovative Email Security'
            ]);
            //echo "This service type is not supported.";
        }
    } else {
        //echo "Link has expired.";
        echo $twig->render('expired.twig', [
        'title' => 'Hääd und Wolf OÜ - Innovative Email Security'
        ]);
    }
} else {
    // Case when there is no QUERY_STRING. User simply wandered into our web 
    header("Location: https://hääd.com/");
    exit();
}
?>
