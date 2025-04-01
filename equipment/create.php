<?php
// Include the header
require_once '../includes/header.php';

// Initialize variables
$name = "";
$serial_number = "";
$type = "";
$error = "";
$success = "";

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
        // Check if serial number already exists
        $sql = "SELECT id FROM equipment WHERE serial_number = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $serial_number);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) > 0) {
                $error = "Serial number already exists. Please use a different one.";
            } else {
                // Prepare an insert statement
                $sql = "INSERT INTO equipment (name, serial_number, type) VALUES (?, ?, ?)";
                
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    // Bind variables to the prepared statement as parameters
                    mysqli_stmt_bind_param($stmt, "sss", $name, $serial_number, $type);
                    
                    // Attempt to execute the prepared statement
                    if (mysqli_stmt_execute($stmt)) {
                        // Get the ID of the newly created equipment
                        $equipment_id = mysqli_insert_id($conn);
                        
                        // Log the action
                        logAction("equipment_created", "Created new equipment: $name (ID: $equipment_id)");
                        
                        // Set success message
                        $success = "Equipment created successfully!";
                        
                        // Reset the form
                        $name = "";
                        $serial_number = "";
                        $type = "";
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
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h2 class="h4 mb-0">Add New Equipment</h2>
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
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-3">
                <label for="name" class="form-label required-field">Equipment Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo $name; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="serial_number" class="form-label required-field">Serial Number</label>
                <input type="text" class="form-control" id="serial_number" name="serial_number" value="<?php echo $serial_number; ?>" required>
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
                <button type="submit" class="btn btn-primary">Create Equipment</button>
                <a href="list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 