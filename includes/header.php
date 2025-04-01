<?php
// Start the session
session_start();

// Include the functions file
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Alarm System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link href="/equipment-alarm-system/assets/css/style.css" rel="stylesheet">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="/equipment-alarm-system/index.php">
                <i class="fas fa-bell"></i> Equipment Alarm System
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="/equipment-alarm-system/index.php">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="equipmentDropdown" role="button" data-bs-toggle="dropdown">
                            Equipment
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/equipment-alarm-system/equipment/list.php">List Equipment</a></li>
                            <li><a class="dropdown-item" href="/equipment-alarm-system/equipment/create.php">Add Equipment</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="alarmsDropdown" role="button" data-bs-toggle="dropdown">
                            Alarms
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="/equipment-alarm-system/alarms/list.php">List Alarms</a></li>
                            <li><a class="dropdown-item" href="/equipment-alarm-system/alarms/create.php">Add Alarm</a></li>
                            <li><a class="dropdown-item" href="/equipment-alarm-system/alarms/activated.php">Activated Alarms</a></li>
                            <li><a class="dropdown-item" href="/equipment-alarm-system/alarms/manage.php">Manage Alarms</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/equipment-alarm-system/logs/view.php">System Logs</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container"><?php
// Check for flashed messages
if(isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']); // Remove message after displaying
}
?> 