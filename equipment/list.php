<?php
// Include the header
require_once '../includes/header.php';

// Query to get all equipment
$sql = "SELECT * FROM equipment ORDER BY name ASC";
$result = mysqli_query($conn, $sql);

// Log this page view
logAction("page_view", "Viewed equipment list page");
?>

<div class="card">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h2 class="h4 mb-0">Equipment List</h2>
        <a href="create.php" class="btn btn-light">
            <i class="fas fa-plus"></i> Add New Equipment
        </a>
    </div>
    <div class="card-body">
        <?php
        // Check if there is any equipment
        if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered sortable-table">
                <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Name</th>
                        <th class="sortable">Serial Number</th>
                        <th class="sortable">Type</th>
                        <th class="sortable">Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data for each row
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Determine icon based on equipment type
                        $typeIcon = '';
                        switch ($row['type']) {
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
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['serial_number']); ?></td>
                        <td><?php echo $typeIcon . htmlspecialchars($row['type']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['registration_date'])); ?></td>
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
            echo '<div class="alert alert-info">No equipment found. Please add some equipment.</div>';
        }
        ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 