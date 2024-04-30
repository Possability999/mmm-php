<?php
// Define the base directory for texts and target files.
$textsDir = __DIR__ . '/texts';
$targetDir = __DIR__ ;

// Choose the language based on some criteria, or default to English.
$lang = 'en'; // This could be dynamically set based on environment variables or other parameters.

// Load the text data from the JSON file.
$textData = json_decode(file_get_contents($textsDir . "/$lang.json"), true);

// List of files to process. This could be dynamically generated or manually listed.
$filesToProcess = [
    $targetDir . '/test/index.php',
    $targetDir . '/link/index.php',
    $targetDir . '/marketingtext_echo.php',
];

// Process each file.
foreach ($filesToProcess as $file) {
    // Read the content of the file.
    $content = file_get_contents($file);
    
    // Replace each placeholder with the text from JSON.
    foreach ($textData as $key => $value) {
        $content = str_replace("{{" . $key . "}}", $value, $content);
    }
    
    // Write the modified content back to the file.
    file_put_contents($file, $content);
}

echo "Text embedding complete.\n";
?>
