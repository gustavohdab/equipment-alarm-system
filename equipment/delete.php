<?php
// Include the header
require_once '../includes/header.php';

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Redirect to equipment list
    header("Location: list.php");
    exit();
}

// Get the equipment ID
$id = (int)$_GET['id'];

// Get equipment details
$equipment = getEquipmentById($id);

// If equipment doesn't exist, redirect to list
if (!$equipment) {
    $_SESSION['message'] = '<div class="alert alert-danger">Equipment not found.</div>';
    header("Location: list.php");
    exit();
}

// Process deletion
if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] == 'yes') {
    // Check if there are alarms associated with this equipment
    $sql = "SELECT COUNT(*) as count FROM alarms WHERE equipment_id = ?";
    $alarmCount = 0;
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            $alarmCount = $row['count'];
        }
        
        mysqli_stmt_close($stmt);
    }
    
    if ($alarmCount > 0) {
        // Equipment has alarms, show warning
        $error = "Cannot delete equipment because it has $alarmCount associated alarm(s). Please delete those alarms first.";
    } else {
        // No alarms, proceed with deletion
        $sql = "DELETE FROM equipment WHERE id = ?";
        
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                // Log the action
                logAction("equipment_deleted", "Deleted equipment: {$equipment['name']} (ID: $id)");
                
                // Set success message and redirect
                $_SESSION['message'] = '<div class="alert alert-success">Equipment deleted successfully.</div>';
                header("Location: list.php");
                exit();
            } else {
                $error = "Error deleting equipment. Please try again.";
            }
            
            mysqli_stmt_close($stmt);
        }
    }
}

// Log this page view
logAction("page_view", "Viewed equipment delete page for ID: " . $id);
?>

<div class="card">
    <div class="card-header bg-danger text-white">
        <h2 class="h4 mb-0">Delete Equipment</h2>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p><strong>Warning:</strong> You are about to delete the following equipment:</p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($equipment['name']); ?></p>
                <p><strong>Serial Number:</strong> <?php echo htmlspecialchars($equipment['serial_number']); ?></p>
                <p><strong>Type:</strong> <?php echo htmlspecialchars($equipment['type']); ?></p>
                <p>This action cannot be undone. Are you sure you want to proceed?</p>
            </div>
            
            <form method="post">
                <input type="hidden" name="confirm_delete" value="yes">
                <div class="mt-3">
                    <button type="submit" class="btn btn-danger">Yes, Delete Equipment</button>
                    <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="mt-3">
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-primary">Back to Equipment Details</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 