<?php
function checkResponse($socket) {
    $response = fgets($socket, 1024);
    if (!preg_match('/^(2|3)/', $response)) {
        throw new Exception("Server rejected command: $response");
    }
    //echo "Server Response: " . $response;
    return $response;
}

function sendEmailViaLMTP($from, $to, $headers, $body) {
    $emailContent = $headers . "\r\n\r\n" . $body;

    $socket = fsockopen('127.0.0.1', 10027);
    if (!$socket) {
        throw new Exception("Unable to connect to LMTP server.");
    }

    try {
        checkResponse($socket);  // Read the server's greeting message
        fputs($socket, "EHLO localhost\r\n");
        checkResponse($socket);

        fputs($socket, "MAIL FROM:<$from>\r\n");
        checkResponse($socket);

        fputs($socket, "RCPT TO:<$to>\r\n");
        checkResponse($socket);

        fputs($socket, "DATA\r\n");
        checkResponse($socket);

        fputs($socket, $emailContent . "\r\n.\r\n");
        checkResponse($socket);

        fputs($socket, "QUIT\r\n");
        checkResponse($socket);
    } catch (Exception $e) {
        fclose($socket);
        throw $e;
    }

    fclose($socket);
 //   echo "Thank you! Email delivery to your recipient has resumed and data has been forwarded.\n";
}

// Hardcoded CID for demonstration
//$cid = 'bafybeihput6ytmunifvoytsshz27psgrf2dozre2pyehgifuoppvywgruq';

function fetchFromIPFSViaHTTPAPI($hash, $file) {
    $url = "http://localhost:5001/api/v0/cat?arg=bafybei" . urlencode($hash) . '/' . urlencode($file);
    $postData = '';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  //  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  //      'Content-Type: application/x-www-form-urlencoded'
  //  ));

    $output = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if ($output === false || $httpCode != 200) {
        throw new Exception("Failed to fetch data from BEAR via HTTP API: " . curl_error($ch) . ", HTTP code: " . $httpCode);
    }

    curl_close($ch);
    return $output;
}


// Function to get content from IPFS
//function fetchFromIPFS($hash, $file) {
//    $command = "/home/tonu/go/bin/ipfs cat {$hash}/{$file}";
//    $output = shell_exec($command);
//    if ($output === null) {
//        throw new Exception("Failed to fetch data from IPFS.");
//    }
//    return $output;
//}

function lmtp($cid,$reverse=0) {
    try {
        $headers = fetchFromIPFSViaHTTPAPI($cid, 'headers.txt');
        $body    = fetchFromIPFSViaHTTPAPI($cid, 'body.txt');
        if($reverse==0) {
            $to      = fetchFromIPFSViaHTTPAPI($cid, 'to.txt');
            $from    = fetchFromIPFSViaHTTPAPI($cid, 'from.txt');
        } else { // Echo service. From address becomes "to"
            $to      = fetchFromIPFSViaHTTPAPI($cid, 'from.txt');
            $from    = 'echo@xn--hd-viaa.com';
        }
        sendEmailViaLMTP($from, $to, $headers, $body);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
