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

// Initialize variables
$name = "";
$serial_number = "";
$type = "";
$error = "";
$success = "";

// Get equipment details
$equipment = getEquipmentById($id);

// If equipment doesn't exist, redirect to list
if (!$equipment) {
    $_SESSION['message'] = '<div class="alert alert-danger">Equipment not found.</div>';
    header("Location: list.php");
    exit();
}

// Set initial form values
$name = $equipment['name'];
$serial_number = $equipment['serial_number'];
$type = $equipment['type'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize input fields
    $name = sanitizeInput($_POST["name"]);
    $serial_number = sanitizeInput($_POST["serial_number"]);
    $type = sanitizeInput($_POST["type"]);
    
    // Simple validation
    if (empty($name)) {
        $error = "Equipment name is required";
    } elseif (empty($serial_number)) {
        $error = "Serial number is required";
    } elseif (empty($type)) {
        $error = "Equipment type is required";
    } else {
        // Check if serial number already exists (excluding current equipment)
        $sql = "SELECT id FROM equipment WHERE serial_number = ? AND id != ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "si", $serial_number, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Serial number already exists. Please use a different one.";
            } else {
                // Prepare an update statement
                $sql = "UPDATE equipment SET name = ?, serial_number = ?, type = ? WHERE id = ?";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sssi", $name, $serial_number, $type, $id);
                    
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Log the action
                        logAction("equipment_updated", "Updated equipment ID: $id, Name: $name");
                        
                        // Set success message
                        $success = "Equipment updated successfully!";
                    } else {
                        $error = "Oops! Something went wrong. Please try again later.";
                    }
                    
                    // Close statement
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// Log this page view
logAction("page_view", "Viewed equipment edit page for ID: " . $id);
?>

<div class="card">
    <div class="card-header bg-warning text-dark">
        <h2 class="h4 mb-0">Edit Equipment</h2>
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
                <label for="name" class="form-label required-field">Equipment Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="serial_number" class="form-label required-field">Serial Number</label>
                <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo htmlspecialchars($serial_number); ?>" required>
                <small class="text-muted">Must be unique</small>
            </div>
            
            <div class="mb-3">
                <label for="type" class="form-label required-field">Equipment Type</label>
                <select class="form-select" id="type" name="type" required>
                    <option value="" <?php echo empty($type) ? 'selected' : ''; ?>>Select Type</option>
                    <option value="Voltage" <?php echo $type == "Voltage" ? 'selected' : ''; ?>>Voltage</option>
                    <option value="Current" <?php echo $type == "Current" ? 'selected' : ''; ?>>Current</option>
                    <option value="Oil" <?php echo $type == "Oil" ? 'selected' : ''; ?>>Oil</option>
                </select>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-warning">Update Equipment</button>
                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 