<?php
// Simple test script to verify Swagger setup
echo "Testing Swagger Setup...\n\n";

// Check if api-docs.json exists
$apiDocsPath = __DIR__ . '/storage/api-docs/api-docs.json';
if (file_exists($apiDocsPath)) {
    echo "✓ API docs JSON file exists\n";
    $jsonContent = file_get_contents($apiDocsPath);
    $apiDocs = json_decode($jsonContent, true);
    
    if ($apiDocs) {
        echo "✓ JSON is valid\n";
        echo "✓ API Title: " . $apiDocs['info']['title'] . "\n";
        echo "✓ API Version: " . $apiDocs['info']['version'] . "\n";
        echo "✓ Total endpoints: " . count($apiDocs['paths']) . "\n";
        
        // Check if our new API logs endpoint is included
        if (isset($apiDocs['paths']['/admin/api-logs'])) {
            echo "✓ API logs endpoint is documented\n";
        } else {
            echo "⚠ API logs endpoint not found in documentation\n";
        }
    } else {
        echo "✗ JSON is invalid\n";
    }
} else {
    echo "✗ API docs JSON file not found\n";
}

// Check if swagger.html exists
$swaggerHtmlPath = __DIR__ . '/public/swagger.html';
if (file_exists($swaggerHtmlPath)) {
    echo "✓ Swagger HTML file exists\n";
} else {
    echo "✗ Swagger HTML file not found\n";
}

// Check if storage symlink exists
$storageSymlink = __DIR__ . '/public/storage';
if (is_link($storageSymlink)) {
    echo "✓ Storage symlink exists\n";
} else {
    echo "✗ Storage symlink not found\n";
}

echo "\nSwagger Documentation URLs:\n";
echo "- Static HTML: http://your-domain/swagger.html\n";
echo "- JSON API: http://your-domain/storage/api-docs/api-docs.json\n";
echo "\nSetup completed successfully!\n";
?>