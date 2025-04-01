<?php
// Include the header
require_once '../includes/header.php';

// Query to get all alarms with equipment names
$sql = "SELECT a.*, e.name as equipment_name 
        FROM alarms a 
        JOIN equipment e ON a.equipment_id = e.id 
        ORDER BY a.registration_date DESC";
$result = mysqli_query($conn, $sql);

// Log this page view
logAction("page_view", "Viewed alarms list page");
?>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="h4 mb-0">Alarms List</h2>
        <a href="create.php" class="btn btn-light">
            <i class="fas fa-plus"></i> Add New Alarm
        </a>
    </div>
    <div class="card-body">
        <?php
        // Check if there are any alarms
        if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered sortable-table">
                <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Description</th>
                        <th class="sortable">Classification</th>
                        <th class="sortable">Equipment</th>
                        <th class="sortable">Registration Date</th>
                        <th class="sortable">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data for each row
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Get alarm status
                        $status = getAlarmStatus($row['id']);
                        
                        // Format classification with colors
                        $classificationFormatted = '';
                        switch ($row['classification']) {
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
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $classificationFormatted; ?></td>
                        <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['registration_date'])); ?></td>
                        <td><?php echo $statusFormatted; ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" data-bs-toggle="tooltip" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="delete.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger btn-delete" data-bs-toggle="tooltip" title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                            <?php if ($status == 'off'): ?>
                            <a href="manage.php?action=activate&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-success btn-toggle-status" data-action="activate" data-id="<?php echo $row['id']; ?>" data-desc="<?php echo htmlspecialchars($row['description']); ?>" data-bs-toggle="tooltip" title="Activate">
                                <i class="fas fa-power-off"></i>
                            </a>
                            <?php else: ?>
                            <a href="manage.php?action=deactivate&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $row['id']; ?>" data-desc="<?php echo htmlspecialchars($row['description']); ?>" data-bs-toggle="tooltip" title="Deactivate">
                                <i class="fas fa-power-off"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <?php
        } else {
            echo '<div class="alert alert-info">No alarms found. Please add some alarms.</div>';
        }
        ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 