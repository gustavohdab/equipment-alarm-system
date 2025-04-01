<?php
/**
 * Test Runner for Equipment Alarm System
 * 
 * This file runs all tests in the tests directory
 */

// Basic styling for the test runner
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Equipment Alarm System - Test Runner</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f6f9;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #3366cc;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .test-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .summary {
            margin-top: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>Equipment Alarm System - Test Runner</h1>";

// Function to find all test files
function findTestFiles($directory) {
    $testFiles = [];
    $files = scandir($directory);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'TestRunner.php') {
            continue;
        }
        
        $path = $directory . '/' . $file;
        
        if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $testFiles[] = $path;
        }
    }
    
    return $testFiles;
}

// Find all test files
$testFiles = findTestFiles(__DIR__);

if (empty($testFiles)) {
    echo "<div class='test-section'>
            <h2>No Test Files Found</h2>
            <p>No test files were found in the tests directory.</p>
          </div>";
} else {
    // Run each test file
    foreach ($testFiles as $testFile) {
        echo "<div class='test-section'>";
        echo "<h2>Running: " . basename($testFile) . "</h2>";
        
        // Include the test file
        ob_start();
        include($testFile);
        $result = ob_get_clean();
        
        echo $result;
        echo "</div>";
    }
}

// End of HTML
echo "        <div class='summary'>
            <p>Test run completed at: " . date('Y-m-d H:i:s') . "</p>
            <p><a href='../index.php'>Return to Application</a></p>
        </div>
    </div>
</body>
</html>";
?> 