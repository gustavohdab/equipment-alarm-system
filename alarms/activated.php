<?php
// Include the header
require_once '../includes/header.php';

// Get top 3 most frequently triggered alarms
$topAlarms = getTopTriggeredAlarms(3);

// Query to get activated alarms with equipment and alarm details
$sql = "SELECT aa.*, a.description as alarm_description, a.classification, 
               e.name as equipment_name, e.type as equipment_type 
        FROM activated_alarms aa
        JOIN alarms a ON aa.alarm_id = a.id
        JOIN equipment e ON a.equipment_id = e.id
        WHERE aa.status = 'on'
        ORDER BY aa.entry_date DESC";
$result = mysqli_query($conn, $sql);

// Log this page view
logAction("page_view", "Viewed activated alarms page");
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h2 class="h4 mb-0">Activated Alarms</h2>
                <a href="manage.php" class="btn btn-light">
                    <i class="fas fa-cogs"></i> Manage Alarms
                </a>
            </div>
            <div class="card-body">
                <p>This page shows all currently activated alarms in the system. Use the filter box to search for specific alarms.</p>
                
                <!-- Top 3 Most Triggered Alarms -->
                <div class="mb-4">
                    <h3 class="h5 mb-3">Top 3 Most Frequently Triggered Alarms</h3>
                    <?php if (empty($topAlarms)): ?>
                        <p class="text-muted">No alarms have been triggered yet.</p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($topAlarms as $index => $alarm): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card bg-light top-alarm">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            #<?php echo ($index + 1); ?> - <?php echo htmlspecialchars($alarm['description']); ?>
                                        </h5>
                                        <p class="card-text">
                                            <strong>Equipment:</strong> <?php echo htmlspecialchars($alarm['equipment_name']); ?><br>
                                            <strong>Triggered:</strong> <?php echo $alarm['total_triggers']; ?> times
                                        </p>
                                        <a href="view.php?id=<?php echo $alarm['id']; ?>" class="btn btn-sm btn-info">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Activated Alarms List -->
<div class="card">
    <div class="card-header bg-danger text-white">
        <h3 class="h5 mb-0">Currently Active Alarms</h3>
    </div>
    <div class="card-body">
        <!-- Search Filter -->
        <div class="search-container">
            <div class="input-group mb-3">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" placeholder="Filter by alarm description..." id="alarm-filter">
            </div>
        </div>
        
        <?php
        // Check if there are any activated alarms
        if (mysqli_num_rows($result) > 0) {
        ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered sortable-table" id="activated-alarms-table">
                <thead>
                    <tr>
                        <th class="sortable">ID</th>
                        <th class="sortable">Entry Date</th>
                        <th class="sortable">Duration</th>
                        <th class="sortable">Classification</th>
                        <th class="sortable">Alarm Description</th>
                        <th class="sortable">Equipment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Output data for each row
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Calculate duration from entry date to now
                        $entryDate = new DateTime($row['entry_date']);
                        $now = new DateTime();
                        $interval = $entryDate->diff($now);
                        
                        if ($interval->d > 0) {
                            $duration = $interval->format('%d days, %h hours, %i min');
                        } elseif ($interval->h > 0) {
                            $duration = $interval->format('%h hours, %i min');
                        } else {
                            $duration = $interval->format('%i min, %s sec');
                        }
                        
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
                    ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo date('Y-m-d H:i:s', strtotime($row['entry_date'])); ?></td>
                        <td><?php echo $duration; ?></td>
                        <td><?php echo $classificationFormatted; ?></td>
                        <td><?php echo htmlspecialchars($row['alarm_description']); ?></td>
                        <td><?php echo htmlspecialchars($row['equipment_name']); ?></td>
                        <td>
                            <a href="view.php?id=<?php echo $row['alarm_id']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="manage.php?action=deactivate&id=<?php echo $row['alarm_id']; ?>" class="btn btn-sm btn-secondary btn-toggle-status" data-action="deactivate" data-id="<?php echo $row['alarm_id']; ?>" data-desc="<?php echo htmlspecialchars($row['alarm_description']); ?>" data-bs-toggle="tooltip" title="Deactivate">
                                <i class="fas fa-power-off"></i>
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
            echo '<div class="alert alert-success">No alarms are currently active in the system.</div>';
        }
        ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 