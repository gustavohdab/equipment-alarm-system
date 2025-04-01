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
$equipment_name = $equipment ? $equipment['name'] : 'Unknown';

// Process deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Check if the alarm is currently active
    $status = getAlarmStatus($id);
    
    if ($status == 'on') {
        $error = "Cannot delete this alarm because it is currently active. Please deactivate it first.";
    } else {
        // Delete related activation history first
        $sql = "DELETE FROM activated_alarms WHERE alarm_id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        
        // Now delete the alarm
        $sql = "DELETE FROM alarms WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the action
                logAction("alarm_deleted", "Deleted alarm: {$alarm['description']} (ID: $id)");
                
                // Set success message and redirect
                $_SESSION['message'] = '<div class="alert alert-success">Alarm deleted successfully.</div>';
                header("Location: list.php");
                exit();
            } else {
                $error = "Error deleting alarm. Please try again.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Log this page view
logAction("page_view", "Viewed alarm delete page for ID: " . $id);
?>

<div class="card">
    <div class="card-header bg-danger text-white">
        <h2 class="h4 mb-0">Delete Alarm</h2>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p><strong>Warning:</strong> You are about to delete the following alarm:</p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($alarm['description']); ?></p>
                <p><strong>Classification:</strong> <?php echo htmlspecialchars($alarm['classification']); ?></p>
                <p><strong>Equipment:</strong> <?php echo htmlspecialchars($equipment_name); ?></p>
                <p>This action cannot be undone and will remove all activation history for this alarm. Are you sure you want to proceed?</p>
            </div>
            
            <form method="post">
                <input type="hidden" name="confirm_delete" value="yes">
                <div class="mt-3">
                    <button type="submit" class="btn btn-danger">Yes, Delete Alarm</button>
                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mt-3">
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-primary">Back to Alarm Details</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 