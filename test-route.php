<?php
// Test if the route is working
echo "Testing route registration...\n";

// Check if swagger JSON exists
$jsonPath = __DIR__ . '/storage/api-docs/api-docs.json';
if (file_exists($jsonPath)) {
    echo "✓ Swagger JSON exists\n";
} else {
    echo "✗ Swagger JSON missing\n";
}

// Check if view exists
$viewPath = __DIR__ . '/resources/views/swagger-ui.blade.php';
if (file_exists($viewPath)) {
    echo "✓ Swagger view exists\n";
} else {
    echo "✗ Swagger view missing\n";
}

// Test route accessibility
echo "\nTesting route: http://192.168.1.244:8000/api/documentation\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.244:8000/api/documentation');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    echo "✓ Route is accessible (HTTP 200)\n";
} elseif ($httpCode == 404) {
    echo "✗ Route not found (HTTP 404)\n";
} else {
    echo "✗ Route error (HTTP $httpCode)\n";
}

echo "\nResponse headers:\n";
echo $response;
?>