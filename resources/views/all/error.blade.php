<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>404 - Page Not Found | FreeDash Lite</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  
  <!-- Google Fonts (Inter - commonly used in admin templates) -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
  
  <style>
    :root {
      --fd-primary: #5d87ff;
      --fd-primary-hover: #4f75e6;
      --fd-bg-light: #f6f9fc;
      --fd-text-muted: #7c8fac;
      --fd-card-shadow: 0px 2px 8px rgba(0,0,0,0.08);
    }
    
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--fd-bg-light);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    
    .error-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: var(--fd-card-shadow);
      padding: 3rem 2rem;
      max-width: 500px;
      width: 100%;
      text-align: center;
    }
    
    .error-code {
      font-size: 5rem;
      font-weight: 700;
      color: var(--fd-primary);
      line-height: 1;
      margin-bottom: 0.5rem;
      animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
      40% { transform: translateY(-15px); }
      60% { transform: translateY(-10px); }
    }
    
    .error-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2a3547;
      margin-bottom: 1rem;
    }
    
    .error-message {
      color: var(--fd-text-muted);
      margin-bottom: 2rem;
      line-height: 1.6;
    }
    
    .btn-primary-custom {
      background-color: var(--fd-primary);
      border-color: var(--fd-primary);
      color: #fff;
      padding: 0.625rem 1.5rem;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.2s ease;
      text-decoration: none;
      display: inline-block;
    }
    
    .btn-primary-custom:hover {
      background-color: var(--fd-primary-hover);
      border-color: var(--fd-primary-hover);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3);
      color: #fff;
    }
    
    .footer-note {
      margin-top: 2rem;
      font-size: 0.875rem;
      color: var(--fd-text-muted);
    }
    
    .illustration {
      max-width: 180px;
      margin: 0 auto 1.5rem;
      opacity: 0.9;
    }
  </style>
</head>
<body>
  <div class="error-card">
    <!-- Simple SVG Illustration -->
    <svg class="illustration" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
      <circle cx="100" cy="100" r="90" fill="#eef2f7"/>
      <path d="M60 100 L85 125 L140 70" stroke="#5d87ff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
      <circle cx="60" cy="70" r="8" fill="#5d87ff"/>
      <circle cx="140" cy="130" r="8" fill="#5d87ff"/>
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
    
    <!-- Action Button -->
    <a  href="{{ url()->previous() != url()->current() ? url()->previous() : '/home' }}" class="btn-primary-custom">
      ← Back to Dashboard
    </a>
    
    <!-- Footer Note -->
    <p class="footer-note">
      If you believe this is an error, please contact support.
    </p>
  </div>

  <!-- Bootstrap JS Bundle -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>