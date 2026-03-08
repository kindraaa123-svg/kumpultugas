<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | CoolAdmin</title>
    
    <!-- Bootstrap 5.3.8 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 7.1.0 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.1.0/css/all.min.css" rel="stylesheet">
    
    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        /* ===== CoolAdmin Style Variables ===== */
        :root {
            --primary-color: #4272d7;
            --primary-hover: #3868cd;
            --text-color: #666666;
            --text-dark: #333333;
            --body-bg: #e5e5e5;
            --card-bg: #ffffff;
            --border-radius: 3px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-weight: 400;
            font-size: 16px;
            line-height: 1.625;
            color: var(--text-color);
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Error Card ===== */
        .error-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: 0px 2px 5px 0px rgba(0, 0, 0, 0.1);
            padding: 50px 40px;
            text-align: center;
            max-width: 520px;
            width: 100%;
            margin: 0 auto;
        }

        /* ===== SVG Illustration ===== */
        .illustration {
            width: 180px;
            height: 180px;
            margin: 0 auto 30px;
        }

        .illustration circle:first-child {
            fill: #f8f9fa;
        }

        .illustration path {
            stroke: var(--primary-color);
            stroke-width: 8;
            fill: none;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .illustration circle:not(:first-child) {
            fill: var(--primary-color);
        }

        /* ===== Error Code ===== */
        .error-code {
            font-size: 72px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 10px;
            letter-spacing: -2px;
        }

        /* ===== Error Title ===== */
        .error-title {
            font-size: 24px;
            font-weight: 500;
            color: var(--text-dark);
            text-transform: capitalize;
            margin-bottom: 15px;
        }

        /* ===== Error Message ===== */
        .error-message {
            font-size: 15px;
            color: var(--text-color);
            margin-bottom: 30px;
            line-height: 1.625;
        }

        /* ===== Action Button (CoolAdmin Style) ===== */
        .btn-primary-custom {
            display: inline-block;
            line-height: 45px;
            padding: 0 35px;
            text-transform: uppercase;
            color: #fff;
            background: var(--primary-color);
            border-radius: var(--border-radius);
            font-size: 14px;
            font-weight: 400;
            text-decoration: none;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary-custom:hover {
            color: #fff;
            background: var(--primary-hover);
            text-decoration: none;
        }

        .btn-primary-custom i {
            margin-right: 8px;
            vertical-align: baseline;
        }

        /* ===== Footer Note ===== */
        .footer-note {
            font-size: 13px;
            color: #999;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e5e5;
        }

        .footer-note a {
            color: var(--primary-color);
            text-decoration: none;
        }

        .footer-note a:hover {
            color: var(--primary-hover);
        }

        /* ===== Responsive ===== */
        @media (max-width: 576px) {
            .error-card {
                padding: 40px 25px;
            }

            .error-code {
                font-size: 56px;
            }

            .error-title {
                font-size: 20px;
            }

            .illustration {
                width: 140px;
                height: 140px;
            }
        }

        /* ===== Simple Animation ===== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-card {
            animation: fadeInUp 0.4s ease-out;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <!-- Simple SVG Illustration (CoolAdmin Style) -->
        <svg class="illustration" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <circle cx="100" cy="100" r="90" fill="#f8f9fa"/>
            <path d="M60 100 L85 125 L140 70" stroke="#4272d7" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="60" cy="70" r="8" fill="#4272d7"/>
            <circle cx="140" cy="130" r="8" fill="#4272d7"/>
        </svg>
        
        <!-- Error Code -->
        <div class="error-code">404</div>
        
        <!-- Error Title -->
        <h1 class="error-title">Page Not Found</h1>
        
        <!-- Error Message -->
        <p class="error-message">
            Oops! The page you're looking for doesn't exist or has been moved. 
            Let's get you back on track.
        </p>
        
        <!-- Action Button (CoolAdmin .au-btn style) -->
        <a href="{{ url()->previous() != url()->current() ? url()->previous() : '/home' }}" class="btn-primary-custom">
            <i class="fas fa-arrow-left"></i>Back to Dashboard
        </a>
        
        <!-- Footer Note -->
        <p class="footer-note">
            If you believe this is an error, please <a href="/support">contact support</a>.
        </p>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>