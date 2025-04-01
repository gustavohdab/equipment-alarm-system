<?php
// Include the header
require_once 'includes/header.php';

// Get counts for dashboard
$equipmentCount = 0;
$alarmsCount = 0;
$activeAlarmsCount = 0;
$urgentAlarmsCount = 0;

// Count equipment
$sql = "SELECT COUNT(*) as count FROM equipment";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $equipmentCount = $row['count'];
}

// Count all alarms
$sql = "SELECT COUNT(*) as count FROM alarms";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $alarmsCount = $row['count'];
}

// Count active alarms
$sql = "SELECT COUNT(*) as count FROM activated_alarms WHERE status = 'on'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $activeAlarmsCount = $row['count'];
}

// Count urgent alarms that are active
$sql = "SELECT COUNT(*) as count FROM activated_alarms aa
        JOIN alarms a ON aa.alarm_id = a.id
        WHERE aa.status = 'on' AND a.classification = 'Urgent'";
$result = mysqli_query($conn, $sql);
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $urgentAlarmsCount = $row['count'];
}

// Get top 3 triggered alarms
$topAlarms = getTopTriggeredAlarms(3);

// Log this page view
logAction("page_view", "Viewed dashboard page");
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">Equipment Alarm System Dashboard</h1>
            </div>
            <div class="card-body">
                <p>Welcome to the Equipment Alarm System. Use the navigation menu to manage equipment and alarms.</p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Equipment Count -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Equipment</h5>
                <p class="display-4"><?php echo $equipmentCount; ?></p>
                <a href="equipment/list.php" class="btn btn-sm btn-outline-primary">View Equipment</a>
            </div>
        </div>
    </div>
    
    <!-- All Alarms Count -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Total Alarms</h5>
                <p class="display-4"><?php echo $alarmsCount; ?></p>
                <a href="alarms/list.php" class="btn btn-sm btn-outline-primary">View Alarms</a>
            </div>
        </div>
    </div>
    
    <!-- Active Alarms Count -->
    <div class="col-md-3">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Active Alarms</h5>
                <p class="display-4"><?php echo $activeAlarmsCount; ?></p>
                <a href="alarms/activated.php" class="btn btn-sm btn-outline-primary">View Active Alarms</a>
            </div>
        </div>
    </div>
    
    <!-- Urgent Alarms Count -->
    <div class="col-md-3">
        <div class="card dashboard-card urgent">
            <div class="card-body">
                <h5 class="card-title">Urgent Alarms</h5>
                <p class="display-4"><?php echo $urgentAlarmsCount; ?></p>
                <a href="alarms/manage.php" class="btn btn-sm btn-outline-danger">Manage Alarms</a>
            </div>
        </div>
    </div>
</div>

<!-- Top Triggered Alarms -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h2 class="h5 mb-0">Top 3 Most Frequently Triggered Alarms</h2>
            </div>
            <div class="card-body">
                <?php if (empty($topAlarms)): ?>
                    <p class="text-muted">No alarm has been triggered yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Alarm Description</th>
                                    <th>Equipment</th>
                                    <th>Total Triggers</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topAlarms as $alarm): ?>
                                <tr class="top-alarm">
                                    <td><?php echo htmlspecialchars($alarm['description']); ?></td>
                                    <td><?php echo htmlspecialchars($alarm['equipment_name']); ?></td>
                                    <td><?php echo $alarm['total_triggers']; ?></td>
                                    <td>
                                        <a href="alarms/view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info">
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
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h2 class="h5 mb-0">Quick Actions</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="equipment/create.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-plus"></i> Add Equipment
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="alarms/create.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-bell"></i> Add Alarm
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="alarms/manage.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-cogs"></i> Manage Alarms
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="logs/view.php" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-list"></i> View System Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include the footer
require_once 'includes/footer.php';
?> 