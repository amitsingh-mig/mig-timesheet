<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MIG-TimeSheet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body class="auth-bg">
    <nav class="navbar navbar-dark bg-transparent flex-shrink-0" style="height: 60px;">
        <div class="container-fluid">
            <span class="navbar-brand fw-bold text-truncate">
                <i class="bi bi-clock me-2"></i>
                <span class="d-none d-sm-inline">MIG-TimeSheet</span>
                <span class="d-sm-none">MIG-TS</span>
            </span>
        </div>
    </nav>
    <div class="container-fluid d-flex align-items-center justify-content-center flex-grow-1" style="min-height: calc(100vh - 60px);">
        <div class="row w-100 justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-4">
                {{ $slot }}
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
