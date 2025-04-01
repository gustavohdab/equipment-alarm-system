<?php
// Include the header
require_once '../includes/header.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to alarms list
    header("Location: list.php");
    exit();
}

// Get the alarm ID
$id = (int)$_GET['id'];

// Get alarm details
$alarm = getAlarmById($id);

// If alarm doesn't exist, redirect to list
if (!$alarm) {
    $_SESSION['message'] = '<div class="alert alert-danger">Alarm not found.</div>';
    header("Location: list.php");
    exit();
}

// Get equipment details
$equipment = getEquipmentById($alarm['equipment_id']);

// Get current alarm status
$status = getAlarmStatus($id);

// Get activation history
$activationHistory = array();
$sql = "SELECT * FROM activated_alarms WHERE alarm_id = ? ORDER BY entry_date DESC";

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $activationHistory[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Calculate total number of activations
$totalActivations = count($activationHistory);

// Format classification with colors
$classificationFormatted = '';
switch ($alarm['classification']) {
    case 'Urgent':
        $classificationFormatted = '<span class="classification-urgent">Urgent</span>';
        break;
    case 'Emergency':
        $classificationFormatted = '<span class="classification-emergency">Emergency</span>';
        break;
    case 'Ordinary':
        $classificationFormatted = '<span class="classification-ordinary">Ordinary</span>';
        break;
}

// Format status
$statusFormatted = '';
if ($status == 'on') {
    $statusFormatted = '<span class="status-on"><i class="fas fa-circle"></i> Active</span>';
} else {
    $statusFormatted = '<span class="status-off"><i class="far fa-circle"></i> Inactive</span>';
}

// Log this page view
logAction("page_view", "Viewed alarm details for ID: " . $id);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Alarm Details</h2>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h3 class="h5 text-primary"><?php echo htmlspecialchars($alarm['description']); ?></h3>
                </div>
                
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td><?php echo $alarm['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Description</th>
                        <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                    </tr>
                    <tr>
                        <th>Classification</th>
                        <td><?php echo $classificationFormatted; ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?php echo $statusFormatted; ?></td>
                    </tr>
                    <tr>
                        <th>Equipment</th>
                        <td>
                            <?php if ($equipment): ?>
                                <a href="../equipment/view.php?id=<?php echo $equipment['id']; ?>">
                                    <?php echo htmlspecialchars($equipment['name']); ?>
                                </a>
                            <?php else: ?>
                                <span class="text-danger">Equipment not found</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Registration Date</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($alarm['registration_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($alarm['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($alarm['updated_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Total Activations</th>
                        <td><?php echo $totalActivations; ?></td>
                    </tr>
                </table>
                
                <div class="mt-3">
                    <a href="list.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit.php?id=<?php echo $alarm['id']; ?>" class="btn btn-warning">Edit</a>
                    <a href="delete.php?id=<?php echo $alarm['id']; ?>" class="btn btn-danger btn-delete">Delete</a>
                    
                    <?php if ($status == 'off'): ?>
                    <a href="manage.php?action=activate&id=<?php echo $alarm['id']; ?>" class="btn btn-success btn-toggle-status" data-action="activate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                        <i class="fas fa-power-off"></i> Activate
                    </a>
                    <?php else: ?>
                    <a href="manage.php?action=deactivate&id=<?php echo $alarm['id']; ?>" class="btn btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $alarm['id']; ?>" data-desc="<?php echo htmlspecialchars($alarm['description']); ?>">
                        <i class="fas fa-power-off"></i> Deactivate
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h3 class="h4 mb-0">Activation History</h3>
            </div>
            <div class="card-body">
                <?php if (empty($activationHistory)): ?>
                    <p class="text-muted">This alarm has never been activated.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Entry Date</th>
                                    <th>Exit Date</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activationHistory as $history): 
                                    // Calculate duration if exit date exists
                                    $duration = '';
                                    if (!empty($history['exit_date'])) {
                                        $entry = new DateTime($history['entry_date']);
                                        $exit = new DateTime($history['exit_date']);
                                        $interval = $entry->diff($exit);
                                        
                                        if ($interval->d > 0) {
                                            $duration = $interval->format('%d days, %h hours, %i minutes');
                                        } elseif ($interval->h > 0) {
                                            $duration = $interval->format('%h hours, %i minutes');
                                        } else {
                                            $duration = $interval->format('%i minutes, %s seconds');
                                        }
                                    } else {
                                        $duration = 'Ongoing';
                                    }
                                    
                                    // Format status
                                    $historyStatus = '';
                                    if ($history['status'] == 'on') {
                                        $historyStatus = '<span class="status-on"><i class="fas fa-circle"></i> Active</span>';
                                    } else {
                                        $historyStatus = '<span class="status-off"><i class="far fa-circle"></i> Inactive</span>';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo date('Y-m-d H:i:s', strtotime($history['entry_date'])); ?></td>
                                    <td>
                                        <?php echo !empty($history['exit_date']) ? date('Y-m-d H:i:s', strtotime($history['exit_date'])) : '-'; ?>
                                    </td>
                                    <td><?php echo $duration; ?></td>
                                    <td><?php echo $historyStatus; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 