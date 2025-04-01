<?php
// Include the header
require_once '../includes/header.php';

// Process activate/deactivate actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = sanitizeInput($_GET['action']);
    $id = (int)$_GET['id'];
    
    // Get alarm details
    $alarm = getAlarmById($id);
    
    if ($alarm) {
        // Get current status
        $currentStatus = getAlarmStatus($id);
        
        if ($action == 'activate' && $currentStatus == 'off') {
            // Create a new activated alarm record
            $sql = "INSERT INTO activated_alarms (alarm_id, status) VALUES (?, 'on')";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    // If this is an urgent classification, send email
                    if ($alarm['classification'] == 'Urgent') {
                        $subject = "URGENT ALARM ACTIVATED: " . $alarm['description'];
                        $message = "
                            <h2>Urgent Alarm Activated</h2>
                            <p><strong>Alarm:</strong> {$alarm['description']}</p>
                            <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                            <p>Please take immediate action.</p>
                        ";
                        
                        sendEmail($subject, $message);
                    }
                    
                    // Log the action
                    logAction("alarm_activated", "Activated alarm: {$alarm['description']} (ID: $id)");
                    
                    // Set success message
                    $_SESSION['message'] = '<div class="alert alert-success">Alarm activated successfully.</div>';
                } else {
                    $_SESSION['message'] = '<div class="alert alert-danger">Error activating alarm.</div>';
                }
                
                mysqli_stmt_close($stmt);
            }
        } elseif ($action == 'deactivate' && $currentStatus == 'on') {
            // Find the latest active record for this alarm
            $sql = "SELECT id FROM activated_alarms 
                    WHERE alarm_id = ? AND status = 'on' 
                    ORDER BY entry_date DESC 
                    LIMIT 1";
                    
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "i", $id);
                
                if (mysqli_stmt_execute($stmt)) {
                    $result = mysqli_stmt_get_result($stmt);
                    
                    if ($row = mysqli_fetch_assoc($result)) {
                        $activatedId = $row['id'];
                        
                        // Update the record with exit date and status
                        $updateSql = "UPDATE activated_alarms 
                                      SET exit_date = CURRENT_TIMESTAMP, status = 'off' 
                                      WHERE id = ?";
                                      
                        if ($updateStmt = mysqli_prepare($conn, $updateSql)) {
                            mysqli_stmt_bind_param($updateStmt, "i", $activatedId);
                            
                            if (mysqli_stmt_execute($updateStmt)) {
                                // Log the action
                                logAction("alarm_deactivated", "Deactivated alarm: {$alarm['description']} (ID: $id)");
                                
                                // Set success message
                                $_SESSION['message'] = '<div class="alert alert-success">Alarm deactivated successfully.</div>';
                            } else {
                                $_SESSION['message'] = '<div class="alert alert-danger">Error deactivating alarm.</div>';
                            }
                            
                            mysqli_stmt_close($updateStmt);
                        }
                    }
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            // Trying to activate already active or deactivate already inactive
            if ($action == 'activate' && $currentStatus == 'on') {
                $_SESSION['message'] = '<div class="alert alert-warning">Alarm is already active.</div>';
            } elseif ($action == 'deactivate' && $currentStatus == 'off') {
                $_SESSION['message'] = '<div class="alert alert-warning">Alarm is already inactive.</div>';
            }
        }
    } else {
        $_SESSION['message'] = '<div class="alert alert-danger">Alarm not found.</div>';
    }
    
    // Redirect back to the referring page or list
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'list.php';
    header("Location: $redirect");
    exit();
}

// Query to get all alarms with equipment names and current status
$sql = "SELECT a.*, e.name as equipment_name 
        FROM alarms a 
        JOIN equipment e ON a.equipment_id = e.id 
        ORDER BY a.classification ASC, a.description ASC";
$result = mysqli_query($conn, $sql);

// Prepare arrays for different alarm classifications
$urgentAlarms = array();
$emergencyAlarms = array();
$ordinaryAlarms = array();

// Process the results and sort by classification
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Get current status
        $row['status'] = getAlarmStatus($row['id']);
        
        // Add to appropriate array based on classification
        switch ($row['classification']) {
            case 'Urgent':
                $urgentAlarms[] = $row;
                break;
            case 'Emergency':
                $emergencyAlarms[] = $row;
                break;
            case 'Ordinary':
                $ordinaryAlarms[] = $row;
                break;
        }
    }
}

// Log this page view
logAction("page_view", "Viewed alarm management page");
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Alarm Management</h2>
            </div>
            <div class="card-body">
                <p>Use this page to activate or deactivate alarms. Toggle the status using the buttons next to each alarm.</p>
            </div>
        </div>
    </div>
</div>

<!-- Urgent Alarms -->
<div class="card mb-4">
    <div class="card-header bg-danger text-white">
        <h3 class="h5 mb-0">Urgent Alarms</h3>
    </div>
    <div class="card-body">
        <?php if (empty($urgentAlarms)): ?>
            <p class="text-muted">No urgent alarms found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($urgentAlarms as $alarm): 
                            // Format status
                            $statusFormatted = '';
                            if ($alarm['status'] == 'on') {
                                $statusFormatted = '<span class="status-on"><i class="fas fa-circle"></i> Active</span>';
                            } else {
                                $statusFormatted = '<span class="status-off"><i class="far fa-circle"></i> Inactive</span>';
                            }
                        ?>
                        <tr>
                            <td><?php echo $alarm['id']; ?></td>
                            <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                            <td><?php echo htmlspecialchars($alarm['equipment_name']); ?></td>
                            <td><?php echo $statusFormatted; ?></td>
                            <td>
                                <?php if ($alarm['status'] == 'off'): ?>
                                <a href="manage.php?action=activate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-success btn-toggle-status" data-action="activate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Activate
                                </a>
                                <?php else: ?>
                                <a href="manage.php?action=deactivate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Deactivate
                                </a>
                                <?php endif; ?>
                                <a href="view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Emergency Alarms -->
<div class="card mb-4">
    <div class="card-header bg-warning text-dark">
        <h3 class="h5 mb-0">Emergency Alarms</h3>
    </div>
    <div class="card-body">
        <?php if (empty($emergencyAlarms)): ?>
            <p class="text-muted">No emergency alarms found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emergencyAlarms as $alarm): 
                            // Format status
                            $statusFormatted = '';
                            if ($alarm['status'] == 'on') {
                                $statusFormatted = '<span class="status-on"><i class="fas fa-circle"></i> Active</span>';
                            } else {
                                $statusFormatted = '<span class="status-off"><i class="far fa-circle"></i> Inactive</span>';
                            }
                        ?>
                        <tr>
                            <td><?php echo $alarm['id']; ?></td>
                            <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                            <td><?php echo htmlspecialchars($alarm['equipment_name']); ?></td>
                            <td><?php echo $statusFormatted; ?></td>
                            <td>
                                <?php if ($alarm['status'] == 'off'): ?>
                                <a href="manage.php?action=activate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-success btn-toggle-status" data-action="activate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Activate
                                </a>
                                <?php else: ?>
                                <a href="manage.php?action=deactivate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Deactivate
                                </a>
                                <?php endif; ?>
                                <a href="view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Ordinary Alarms -->
<div class="card">
    <div class="card-header bg-primary text-white">
        <h3 class="h5 mb-0">Ordinary Alarms</h3>
    </div>
    <div class="card-body">
        <?php if (empty($ordinaryAlarms)): ?>
            <p class="text-muted">No ordinary alarms found.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Equipment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ordinaryAlarms as $alarm): 
                            // Format status
                            $statusFormatted = '';
                            if ($alarm['status'] == 'on') {
                                $statusFormatted = '<span class="status-on"><i class="fas fa-circle"></i> Active</span>';
                            } else {
                                $statusFormatted = '<span class="status-off"><i class="far fa-circle"></i> Inactive</span>';
                            }
                        ?>
                        <tr>
                            <td><?php echo $alarm['id']; ?></td>
                            <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                            <td><?php echo htmlspecialchars($alarm['equipment_name']); ?></td>
                            <td><?php echo $statusFormatted; ?></td>
                            <td>
                                <?php if ($alarm['status'] == 'off'): ?>
                                <a href="manage.php?action=activate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-success btn-toggle-status" data-action="activate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Activate
                                </a>
                                <?php else: ?>
                                <a href="manage.php?action=deactivate&id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                                    <i class="fas fa-power-off"></i> Deactivate
                                </a>
                                <?php endif; ?>
                                <a href="view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 