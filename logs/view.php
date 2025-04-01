<?php
// Include the header
require_once '../includes/header.php';

// Determine pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 20; // Number of logs per page
$offset = ($page - 1) * $limit;

// Get total count of logs
$countSql = "SELECT COUNT(*) as total FROM system_logs";
$countResult = mysqli_query($conn, $countSql);
$totalLogs = mysqli_fetch_assoc($countResult)['total'];
$totalPages = ceil($totalLogs / $limit);

// Query to get logs with pagination
$sql = "SELECT * FROM system_logs ORDER BY created_at DESC LIMIT ? OFFSET ?";
$logs = array();

if ($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $limit, $offset);
    
    if (mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $logs[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Log this page view
logAction("page_view", "Viewed system logs page");
?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h2 class="h4 mb-0">System Logs</h2>
    </div>
    <div class="card-body">
        <p>This page shows all actions performed in the system, ordered by most recent first.</p>
        
        <?php if (empty($logs)): ?>
            <div class="alert alert-info">No logs found in the system.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>User IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?php echo $log['id']; ?></td>
                            <td><?php echo date('Y-m-d H:i:s', strtotime($log['created_at'])); ?></td>
                            <td>
                                <?php 
                                    $actionClass = '';
                                    switch ($log['action_type']) {
                                        case 'alarm_activated':
                                            $actionClass = 'text-danger';
                                            break;
                                        case 'alarm_deactivated':
                                            $actionClass = 'text-success';
                                            break;
                                        case 'alarm_created':
                                        case 'equipment_created':
                                            $actionClass = 'text-primary';
                                            break;
                                        case 'alarm_updated':
                                        case 'equipment_updated':
                                            $actionClass = 'text-warning';
                                            break;
                                        case 'alarm_deleted':
                                        case 'equipment_deleted':
                                            $actionClass = 'text-danger';
                                            break;
                                        case 'email_sent':
                                            $actionClass = 'text-info';
                                            break;
                                    }
                                ?>
                                <span class="<?php echo $actionClass; ?>"><?php echo htmlspecialchars($log['action_type']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($log['description']); ?></td>
                            <td><?php echo htmlspecialchars($log['user_ip']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <nav aria-label="Logs pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" tabindex="-1" aria-disabled="<?php echo ($page <= 1) ? 'true' : 'false'; ?>">Previous</a>
                    </li>
                    
                    <?php
                    // Show pagination links
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    // Always show first page
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    // Show current range of pages
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                    }
                    
                    // Always show last page
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>
                    
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-disabled="<?php echo ($page >= $totalPages) ? 'true' : 'false'; ?>">Next</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Include the footer
require_once '../includes/footer.php';
?> 