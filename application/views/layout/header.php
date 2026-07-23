<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title : 'Recomm Admin Panel' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-navy: #0A2A4D;
            --primary-green: #4CAF50;
            --light-green: #6FCF63;
            --background: #F8F9FA;
            --white: #FFFFFF;
            --text: #1A1A1A;
            --sidebar-width: 260px;
            --topbar-height: 70px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--background);
            color: var(--text);
            overflow-x: hidden;
        }
        
        /* ========== Wrapper ========== */
        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
            position: relative;
        }
        
        /* ========== Sidebar ========== */
        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            background: var(--primary-navy);
            color: var(--white);
            transition: var(--transition);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 1050;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        #sidebar::-webkit-scrollbar {
            width: 6px;
        }
        
        #sidebar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.05);
        }
        
        #sidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.2);
            border-radius: 3px;
        }
        
        #sidebar.active {
            margin-left: calc(-1 * var(--sidebar-width));
        }
        
        #sidebar .sidebar-header {
            padding: 25px 20px;
            background: rgba(0,0,0,0.2);
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        #sidebar .sidebar-header h3 {
            color: var(--white);
            font-weight: 700;
            font-size: 26px;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        #sidebar .sidebar-header h3 i {
            color: var(--primary-green);
        }
        
        #sidebar ul.components {
            padding: 20px 0;
            list-style: none;
        }
        
        #sidebar ul li {
            margin: 5px 0;
        }
        
        #sidebar ul li a {
            padding: 14px 20px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 4px solid transparent;
            position: relative;
        }
        
        #sidebar ul li a:hover {
            color: var(--white);
            background: rgba(255,255,255,0.1);
            border-left-color: var(--primary-green);
            padding-left: 25px;
        }
        
        #sidebar ul li a.active {
            color: var(--white);
            background: rgba(76, 175, 80, 0.2);
            border-left-color: var(--primary-green);
            font-weight: 600;
        }
        
        #sidebar ul li a i {
            width: 24px;
            text-align: center;
            font-size: 18px;
        }
        
        /* ========== Content Area ========== */
        #content {
            width: calc(100% - var(--sidebar-width));
            min-height: 100vh;
            transition: var(--transition);
            margin-left: var(--sidebar-width);
            background: var(--background);
        }
        
        #content.active {
            width: 100%;
            margin-left: 0;
        }
        
        /* ========== Topbar ========== */
        .topbar {
            background: var(--white);
            padding: 0 30px;
            height: var(--topbar-height);
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1040;
        }
        
        .topbar .toggle-btn {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--primary-navy);
            cursor: pointer;
            padding: 10px;
            border-radius: 8px;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
        }
        
        .topbar .toggle-btn:hover {
            background: var(--background);
        }
        
        .topbar .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .topbar .user-info .avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-green), var(--light-green));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
            font-size: 18px;
            box-shadow: 0 4px 10px rgba(76, 175, 80, 0.3);
        }
        
        .topbar .user-info .user-details {
            display: flex;
            flex-direction: column;
        }
        
        .topbar .user-info .user-details .name {
            font-weight: 600;
            color: var(--text);
            font-size: 15px;
        }
        
        .topbar .user-info .user-details .role {
            font-size: 12px;
            color: #6c757d;
        }
        
        .topbar .user-info .dropdown-toggle {
            background: none;
            border: none;
            color: var(--text);
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .topbar .user-info .dropdown-toggle:hover {
            background: var(--background);
        }
        
        .topbar .user-info .dropdown-toggle::after {
            margin-left: 8px;
        }
        
        /* ========== Main Content ========== */
        .main-content {
            padding: 30px;
        }
        
        .page-header {
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-navy);
            margin: 0;
        }
        
        .page-header .breadcrumb {
            background: none;
            padding: 0;
            margin: 0;
        }
        
        /* ========== Cards ========== */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.06);
            margin-bottom: 30px;
            transition: var(--transition);
            background: var(--white);
        }
        
        .card:hover {
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }
        
        .card-header {
            background: var(--white);
            border-bottom: 2px solid var(--background);
            padding: 20px 25px;
            font-weight: 600;
            font-size: 18px;
            color: var(--primary-navy);
            border-radius: 16px 16px 0 0 !important;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* ========== Buttons ========== */
        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 500;
            transition: var(--transition);
            border: none;
        }
        
        .btn-primary {
            background: var(--primary-green);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background: var(--light-green);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        
        .btn-outline-primary {
            color: var(--primary-green);
            border: 2px solid var(--primary-green);
        }
        
        .btn-outline-primary:hover {
            background: var(--primary-green);
            color: var(--white);
        }
        
        /* ========== Stats Cards ========== */
        .stats-card {
            border-radius: 16px;
            padding: 30px;
            color: var(--white);
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20px;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            transition: var(--transition);
        }
        
        .stats-card:hover::before {
            transform: scale(1.2);
        }
        
        .stats-card .icon {
            font-size: 50px;
            opacity: 0.9;
            margin-bottom: 15px;
        }
        
        .stats-card h3 {
            font-size: 36px;
            font-weight: 700;
            margin: 15px 0 8px 0;
            position: relative;
            z-index: 1;
        }
        
        .stats-card p {
            margin: 0;
            opacity: 0.95;
            font-size: 16px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        /* ========== Tables ========== */
        .table-responsive {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table {
            background: var(--white);
            margin: 0;
        }
        
        .table thead {
            background: var(--primary-navy);
            color: var(--white);
        }
        
        .table thead th {
            border: none;
            padding: 16px 15px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 16px 15px;
            vertical-align: middle;
            border-bottom: 1px solid var(--background);
        }
        
        .table tbody tr:hover {
            background: rgba(76, 175, 80, 0.05);
        }
        
        /* ========== Badges ========== */
        .badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* ========== Forms ========== */
        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: var(--transition);
        }
        
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.15);
        }
        
        .form-label {
            font-weight: 600;
            color: var(--text);
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        /* ========== Alerts ========== */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
        }
        
        /* ========== Dropdown ========== */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            border-radius: 12px;
            padding: 10px;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            transition: var(--transition);
        }
        
        .dropdown-item:hover {
            background: var(--background);
        }
        
        /* ========== Mobile Responsive ========== */
        @media (max-width: 991px) {
            :root {
                --sidebar-width: 260px;
            }
            
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            
            #sidebar.active {
                margin-left: 0;
            }
            
            #content {
                width: 100%;
                margin-left: 0;
            }
            
            #content.active {
                width: 100%;
            }
            
            .main-content {
                padding: 20px 15px;
            }
            
            .topbar {
                padding: 0 15px;
            }
            
            .page-header h1 {
                font-size: 24px;
            }
            
            .stats-card {
                margin-bottom: 20px;
            }
        }
        
        @media (max-width: 768px) {
            .topbar .user-info .user-details {
                display: none;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stats-card h3 {
                font-size: 28px;
            }
            
            .card-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 15px 10px;
            }
            
            .topbar {
                height: 60px;
                padding: 0 10px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            .stats-card {
                padding: 20px;
            }
            
            .stats-card .icon {
                font-size: 40px;
            }
            
            .stats-card h3 {
                font-size: 24px;
            }
        }
        
        /* ========== Sidebar Overlay for Mobile ========== */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        @media (max-width: 991px) {
            #sidebar.active ~ .sidebar-overlay {
                display: block;
            }
        }
    </style>
</head>
<body>

<div class="wrapper">