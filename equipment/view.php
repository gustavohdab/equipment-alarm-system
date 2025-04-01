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

// Get associated alarms
$sql = "SELECT * FROM alarms WHERE equipment_id = ? ORDER BY registration_date DESC";
$alarms = array();

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $alarms[] = $row;
        }
    }
}

// Log this page view
logAction("page_view", "Viewed equipment details for ID: " . $id);

// Determine icon based on equipment type
$typeIcon = '';
switch ($equipment['type']) {
    case 'Voltage':
        $typeIcon = '<i class="fas fa-bolt icon-voltage"></i> ';
        break;
    case 'Current':
        $typeIcon = '<i class="fas fa-exchange-alt icon-current"></i> ';
        break;
    case 'Oil':
        $typeIcon = '<i class="fas fa-oil-can icon-oil"></i> ';
        break;
}
?>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h2 class="h4 mb-0">Equipment Details</h2>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h3 class="h5 text-primary"><?php echo htmlspecialchars($equipment['name']); ?></h3>
                </div>
                
                <table class="table table-bordered">
                    <tr>
                        <th>ID</th>
                        <td><?php echo $equipment['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?php echo htmlspecialchars($equipment['name']); ?></td>
                    </tr>
                    <tr>
                        <th>Serial Number</th>
                        <td><?php echo htmlspecialchars($equipment['serial_number']); ?></td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td><?php echo $typeIcon . htmlspecialchars($equipment['type']); ?></td>
                    </tr>
                    <tr>
                        <th>Registration Date</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($equipment['registration_date'])); ?></td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($equipment['created_at'])); ?></td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td><?php echo date('Y-m-d H:i', strtotime($equipment['updated_at'])); ?></td>
                    </tr>
                </table>
                
                <div class="mt-3">
                    <a href="list.php" class="btn btn-secondary">Back to List</a>
                    <a href="edit.php?id=<?php echo $equipment['id']; ?>" class="btn btn-warning">Edit</a>
                    <a href="delete.php?id=<?php echo $equipment['id']; ?>" class="btn btn-danger btn-delete">Delete</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                <h3 class="h4 mb-0">Associated Alarms</h3>
                <a href="../alarms/create.php?equipment_id=<?php echo $equipment['id']; ?>" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Add Alarm
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($alarms)): ?>
                    <p class="text-muted">No alarms associated with this equipment.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Classification</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($alarms as $alarm): 
                                    // Get current status
                                    $status = getAlarmStatus($alarm['id']);
                                    
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
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                                    <td><?php echo $classificationFormatted; ?></td>
                                    <td><?php echo $statusFormatted; ?></td>
                                    <td>
                                        <a href="../alarms/view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../alarms/edit.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
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
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 