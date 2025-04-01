<?php
/**
 * Utility Functions
 * Contains helper functions used throughout the application
 */

// Include database connection
require_once 'config.php';

/**
 * Log system actions
 * 
 * @param string $action_type Type of action performed
 * @param string $description Description of the action
 */
function logAction($action_type, $description) {
    global $conn;
    
    // Get user IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // Prepare SQL statement
    $sql = "INSERT INTO system_logs (action_type, description, user_ip) VALUES (?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)) {
        // Bind variables to prepared statement
        mysqli_stmt_bind_param($stmt, "sss", $action_type, $description, $user_ip);
        
        // Execute the statement
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

/**
 * Clean and sanitize input data
 * 
 * @param string $data Data to be sanitized
 * @return string Sanitized data
 */
function sanitizeInput($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = mysqli_real_escape_string($conn, $data);
    return $data;
}

/**
 * Send email notification
 * 
 * @param string $subject Email subject
 * @param string $message Email message body
 * @param string $recipient Email recipient
 * @return bool Success or failure
 */
function sendEmail($subject, $message, $recipient = "abcd@abc.com.br") {
    // Log the email attempt
    logAction("email_sent", "Attempting to send email to {$recipient} with subject: {$subject}");
    
    // Set headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: equipment-alarm-system@example.com" . "\r\n";
    
    // For the admission test, we'll log the email details and return true
    // In a real environment, you would use the mail() function:
    // $success = mail($recipient, $subject, $message, $headers);
    
    // Log details for demonstration purposes
    logAction("email_content", "Subject: {$subject}, Message: " . substr($message, 0, 100) . "...");
    
    // For testing, we'll simulate success (this always returns true)
    // In production, replace this with actual mail() function
    return true;
}

/**
 * Get equipment details by ID
 * 
 * @param int $id Equipment ID
 * @return array|bool Equipment data or false if not found
 */
function getEquipmentById($id) {
    global $conn;
    
    $id = (int) $id; // Ensure it's an integer
    
    $sql = "SELECT * FROM equipment WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                return mysqli_fetch_assoc($result);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Get alarm details by ID
 * 
 * @param int $id Alarm ID
 * @return array|bool Alarm data or false if not found
 */
function getAlarmById($id) {
    global $conn;
    
    $id = (int) $id; // Ensure it's an integer
    
    $sql = "SELECT * FROM alarms WHERE id = ?";
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                return mysqli_fetch_assoc($result);
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Get alarm status (whether it's currently activated)
 * 
 * @param int $alarm_id Alarm ID
 * @return string|bool Status ('on', 'off') or false if error
 */
function getAlarmStatus($alarm_id) {
    global $conn;
    
    $alarm_id = (int) $alarm_id;
    
    $sql = "SELECT status FROM activated_alarms 
            WHERE alarm_id = ? 
            ORDER BY entry_date DESC 
            LIMIT 1";
            
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $alarm_id);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            if(mysqli_num_rows($result) == 1) {
                $row = mysqli_fetch_assoc($result);
                return $row['status'];
            } else {
                // No activated record found, so status is 'off'
                return 'off';
            }
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return false;
}

/**
 * Get the top frequently triggered alarms
 * 
 * @param int $limit Number of top alarms to retrieve (default 3)
 * @return array List of top alarms with counts
 */
function getTopTriggeredAlarms($limit = 3) {
    global $conn;
    
    $limit = (int) $limit;
    
    $sql = "SELECT a.id, a.description, e.name as equipment_name, 
                   SUM(aa.trigger_count) as total_triggers 
            FROM alarms a
            JOIN equipment e ON a.equipment_id = e.id
            JOIN activated_alarms aa ON a.id = aa.alarm_id
            GROUP BY a.id, a.description, e.name
            ORDER BY total_triggers DESC
            LIMIT ?";
            
    if($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $limit);
        
        if(mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            
            $topAlarms = [];
            while($row = mysqli_fetch_assoc($result)) {
                $topAlarms[] = $row;
            }
            
            return $topAlarms;
        }
        
        mysqli_stmt_close($stmt);
    }
    
    return [];
}

/**
 * Display formatted error message
 * 
 * @param string $message Error message
 */
function showError($message) {
    echo '<div class="alert alert-danger" role="alert">';
    echo $message;
    echo '</div>';
}

/**
 * Display formatted success message
 * 
 * @param string $message Success message
 */
function showSuccess($message) {
    echo '<div class="alert alert-success" role="alert">';
    echo $message;
    echo '</div>';
}
?> 