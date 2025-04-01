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

// Initialize variables
$description = "";
$classification = "";
$equipment_id = 0;
$error = "";
$success = "";

// Get alarm details
$alarm = getAlarmById($id);

// If alarm doesn't exist, redirect to list
if (!$alarm) {
    $_SESSION['message'] = '<div class="alert alert-danger">Alarm not found.</div>';
    header("Location: list.php");
    exit();
}

// Set initial form values
$description = $alarm['description'];
$classification = $alarm['classification'];
$equipment_id = $alarm['equipment_id'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input fields
    $description = sanitizeInput($_POST["description"]);
    $classification = sanitizeInput($_POST["classification"]);
    $equipment_id = (int)$_POST["equipment_id"];
    
    // Simple validation
    if (empty($description)) {
        $error = "Alarm description is required";
    } elseif (empty($classification)) {
        $error = "Classification is required";
    } elseif (empty($equipment_id)) {
        $error = "Equipment is required";
    } else {
        // Check if the equipment exists
        $equipment = getEquipmentById($equipment_id);
        if (!$equipment) {
            $error = "Selected equipment does not exist";
        } else {
            // Prepare an update statement
            $sql = "UPDATE alarms SET description = ?, classification = ?, equipment_id = ? WHERE id = ?";
            
            if ($stmt = mysqli_prepare($conn, $sql)) {
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "ssii", $description, $classification, $equipment_id, $id);
                
                // Attempt to execute the prepared statement
                if (mysqli_stmt_execute($stmt)) {
                    // Log the action
                    logAction("alarm_updated", "Updated alarm ID: $id, Description: $description");
                    
                    // Set success message
                    $success = "Alarm updated successfully!";
                } else {
                    $error = "Oops! Something went wrong. Please try again later.";
                }
                
                // Close statement
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Get all equipment for dropdown
$equipmentList = array();
$sql = "SELECT id, name, type FROM equipment ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $equipmentList[] = $row;
    }
}

// Log this page view
logAction("page_view", "Viewed alarm edit page for ID: " . $id);
?>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h2 class="h4 mb-0">Edit Alarm</h2>
    </div>
    <div class="card-body">
        <?php 
        if (!empty($error)) {
            showError($error);
        }
        if (!empty($success)) {
            showSuccess($success);
        }
        ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id); ?>" method="post">
            <div class="mb-3">
                <label for="description" class="form-label required-field">Alarm Description</label>
                <input type="text" class="form-control" id="description" name="description" value="<?php echo htmlspecialchars($description); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="classification" class="form-label required-field">Classification</label>
                <select class="form-select" id="classification" name="classification" required>
                    <option value="" <?php echo empty($classification) ? 'selected' : ''; ?>>Select Classification</option>
                    <option value="Urgent" <?php echo $classification == "Urgent" ? 'selected' : ''; ?>>Urgent</option>
                    <option value="Emergency" <?php echo $classification == "Emergency" ? 'selected' : ''; ?>>Emergency</option>
                    <option value="Ordinary" <?php echo $classification == "Ordinary" ? 'selected' : ''; ?>>Ordinary</option>
                </select>
                <small class="text-muted">Urgent alarms will send email notifications when activated</small>
            </div>
            
            <div class="mb-3">
                <label for="equipment_id" class="form-label required-field">Related Equipment</label>
                <select class="form-select" id="equipment_id" name="equipment_id" required>
                    <option value="" <?php echo empty($equipment_id) ? 'selected' : ''; ?>>Select Equipment</option>
                    <?php foreach ($equipmentList as $equipment): ?>
                    <option value="<?php echo $equipment['id']; ?>" <?php echo $equipment_id == $equipment['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($equipment['name']); ?> (<?php echo htmlspecialchars($equipment['type']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-warning">Update Alarm</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 