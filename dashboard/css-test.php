<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Test</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/dashboard-style.css" rel="stylesheet">
    
    <style>
        body { background-color: lightblue !important; }
        .test-div { 
            background-color: red; 
            color: white; 
            padding: 20px; 
            margin: 20px;
        }
    </style>
</head>

<body>
    <div class="test-div">
        <h1>CSS Test Page</h1>
        <p>Bootstrap test: <span class="badge bg-primary">Primary Badge</span></p>
        <p>FontAwesome test: <i class="fas fa-home"></i> Icon</p>
        <p>Dashboard CSS test: Bu sayfa mavi arka plan olmalı</p>
        
        <div class="dashboard-container">
            <div class="dashboard-body">
                <h2>Dashboard Body Test</h2>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
