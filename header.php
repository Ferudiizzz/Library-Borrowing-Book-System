<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
    /* Add these to your existing styles */
    .navbar-brand {
        line-height: 1.2;
        padding: 0;
    }
    
    .navbar img {
        max-height: 50px;
        width: auto;
    }
    
    /* Adjust the main content padding to account for the taller header if needed */
    .main-content {
        margin-left: 250px;
        padding: 20px;
        padding-top: 90px; 
    }
    
    
    .sidebar {
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        width: 250px;
        background-color: #343a40;
        padding-top: 70px; 
    }
   
</style>
</head>
<body>
    
<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <!-- Added Logo -->
        <div class="d-flex align-items-center">
            <img src="Logo/logo.png" alt="School Logo" height="50" class="me-2">
            <a class="navbar-brand" href="#">
                Tupi National High School<br>
                <small class="fs-6">Library Management System</small>
            </a>
        </div>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex ms-auto">
                <input class="form-control me-2" type="search" placeholder="Search">
                <button class="btn btn-outline-light" type="submit">Search</button>
            </form>
            <ul class="navbar-nav ms-3">
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-bell"></i></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#"><i class="fas fa-user"></i></a>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="list-group list-group-flush">
            <a href="index.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-home me-2"></i> Home
            </a>
            <a href="books.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-book me-2"></i> Books
            </a>
            <a href="students.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-users me-2"></i> Students 
            </a>
            <a href="transactions.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-exchange-alt me-2"></i> Borrow Books
            </a>
           
            <a href="invoices.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-file-invoice me-2"></i> Invoices
            </a>
            <a href="alerts.php" class="list-group-item list-group-item-action bg-dark text-white">
                <i class="fas fa-exclamation-circle me-2"></i> Alerts
            </a>

        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">
        <!-- In header.php -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>