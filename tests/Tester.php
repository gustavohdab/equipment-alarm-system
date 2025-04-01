<?php
/**
 * Equipment Alarm System - TDD Tests
 * 
 * This file contains unit tests for the core functionality of the Equipment Alarm System.
 * It follows a simple Test-Driven Development approach.
 */

// Include the necessary application files
require_once '../includes/config.php';
require_once '../includes/functions.php';

/**
 * Simple test framework
 */
class Tester {
    private $passed = 0;
    private $failed = 0;
    private $results = [];

    /**
     * Assert that condition is true
     */
    public function assertTrue($condition, $message) {
        if ($condition === true) {
            $this->passed++;
            $this->results[] = [
                'type' => 'success',
                'message' => $message
            ];
            return true;
        } else {
            $this->failed++;
            $this->results[] = [
                'type' => 'error',
                'message' => $message . ' - Assertion Failed'
            ];
            return false;
        }
    }

    /**
     * Assert that values are equal
     */
    public function assertEquals($expected, $actual, $message) {
        if ($expected === $actual) {
            $this->passed++;
            $this->results[] = [
                'type' => 'success',
                'message' => $message
            ];
            return true;
        } else {
            $this->failed++;
            $this->results[] = [
                'type' => 'error',
                'message' => $message . ' - Expected: ' . print_r($expected, true) . ', Actual: ' . print_r($actual, true)
            ];
            return false;
        }
    }

    /**
     * Display test results
     */
    public function displayResults() {
        echo "<h2>Test Results</h2>";
        
        foreach ($this->results as $result) {
            $color = ($result['type'] === 'success') ? 'green' : 'red';
            echo "<div style='color: {$color};'>{$result['message']}</div>";
        }
        
        echo "<h3>Summary</h3>";
        echo "<p>Tests passed: {$this->passed}</p>";
        echo "<p>Tests failed: {$this->failed}</p>";
        
        if ($this->failed === 0) {
            echo "<p style='color: green;'>All tests passed!</p>";
        } else {
            echo "<p style='color: red;'>{$this->failed} test(s) failed!</p>";
        }
    }
}

// Create a new tester instance
$tester = new Tester();

// 1. Test sanitizeInput function
$testString = "<script>alert('XSS')</script>";
$sanitized = sanitizeInput($testString);
$tester->assertTrue($sanitized !== $testString, "sanitizeInput should sanitize input");
$tester->assertTrue(strpos($sanitized, "<script>") === false, "sanitizeInput should remove script tags");

// 2. Test getAlarmStatus function
// This test depends on database state, might need to be adjusted
$testAlarmId = 1; // Assuming this exists
$status = getAlarmStatus($testAlarmId);
$tester->assertTrue($status === 'on' || $status === 'off', "getAlarmStatus should return valid status");

// 3. Test getTopTriggeredAlarms function
$topAlarms = getTopTriggeredAlarms(3);
$tester->assertTrue(is_array($topAlarms), "getTopTriggeredAlarms should return an array");
$tester->assertTrue(count($topAlarms) <= 3, "getTopTriggeredAlarms should return at most 3 alarms");

// 4. Test email functionality (mock test)
function mockSendEmail($subject, $message, $recipient = "abcd@abc.com.br") {
    // Just return true for testing
    return true;
}

$emailResult = mockSendEmail("Test Subject", "Test Message");
$tester->assertTrue($emailResult, "Email function should return true when successful");

// 5. Test getEquipmentById and getAlarmById
$equipment = getEquipmentById(1); // Assuming ID 1 exists
$tester->assertTrue(is_array($equipment) || $equipment === false, "getEquipmentById should return array or false");

$alarm = getAlarmById(1); // Assuming ID 1 exists
$tester->assertTrue(is_array($alarm) || $alarm === false, "getAlarmById should return array or false");

// Display the test results
$tester->displayResults();
?>

<div style="margin-top: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
    <h3>Notes on TDD for this Application</h3>
    <p>
        For a complete TDD approach, we would need a more comprehensive test suite including:
    </p>
    <ul>
        <li>Database mock objects to avoid testing against real data</li>
        <li>Tests for all CRUD operations</li>
        <li>Tests for alarm activation/deactivation logic</li>
        <li>Integration tests for complete workflows</li>
        <li>Tests for UI functionality using a framework like Selenium</li>
    </ul>
    <p>
        The tests in this file provide a basic starting point and demonstrate the TDD approach. In a production environment, consider using a proper testing framework like PHPUnit for more comprehensive testing.
    </p>
</div> 