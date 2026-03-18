<?php
// Start session for error/success messages
session_start();
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
$success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';

// If success, display success message
if ($success === '1') {
    $success = 'Registration successful! You can now login.';
}

// Check if this page is being included in dashboard
$is_included = defined('DASHBOARD_INCLUDED') && DASHBOARD_INCLUDED === true;

if (!$is_included) {
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Registration - RSRL4</title>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  
  <style>
    :root {
      --blue-deep: #1a237e;
      --blue-mid: #283593;
      --blue-light: #3949ab;
      --white-10: rgba(255,255,255,0.10);
      --white-15: rgba(255,255,255,0.15);
      --white-20: rgba(255,255,255,0.20);
      --white-60: rgba(255,255,255,0.60);
      --white-85: rgba(255,255,255,0.85);
      --bg-dark: #0d1321;
      --bg-card: #1a2332;
      --danger: #f44336;
      --success: #4caf50;
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, var(--bg-dark) 0%, var(--blue-deep) 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .register-container {
      width: 100%;
      max-width: 480px;
      animation: slideUp 0.4s ease-out;
    }

    @keyframes slideUp {
      from {
        opacity: 0;
        transform: translateY(30px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .register-card {
      background: var(--bg-card);
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.4);
      border: 1px solid var(--white-10);
    }

    .register-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .register-icon {
      width: 64px;
      height: 64px;
      background: linear-gradient(135deg, var(--blue-mid), var(--blue-light));
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      font-size: 28px;
      color: #fff;
      box-shadow: 0 8px 24px rgba(40, 53, 147, 0.4);
    }

    .register-title {
      font-size: 1.5rem;
      font-weight: 700;
      color: #fff;
      margin-bottom: 6px;
    }

    .register-subtitle {
      font-size: 0.875rem;
      color: var(--white-60);
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 0.875rem;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-error {
      background: rgba(244, 67, 54, 0.15);
      border: 1px solid rgba(244, 67, 54, 0.3);
      color: #ef9a9a;
    }

    .alert-success {
      background: rgba(76, 175, 80, 0.15);
      border: 1px solid rgba(76, 175, 80, 0.3);
      color: #a5d6a7;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 0.8125rem;
      font-weight: 600;
      color: var(--white-85);
      margin-bottom: 8px;
      letter-spacing: 0.3px;
    }

    .form-input {
      width: 100%;
      padding: 14px 16px;
      background: var(--white-10);
      border: 1px solid var(--white-15);
      border-radius: 10px;
      color: #fff;
      font-size: 0.9375rem;
      font-family: inherit;
      transition: all 0.2s ease;
    }

    .form-input:focus {
      outline: none;
      border-color: var(--blue-light);
      background: var(--white-15);
      box-shadow: 0 0 0 3px rgba(57, 73, 171, 0.2);
    }

    .form-input::placeholder {
      color: var(--white-60);
    }

    .input-group {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--white-60);
      font-size: 1rem;
      pointer-events: none;
      transition: color 0.2s;
    }

    .form-input:focus + .input-icon,
    .form-input:focus ~ .input-icon {
      color: var(--blue-light);
    }

    .form-input.has-icon {
      padding-left: 44px;
    }

    .password-toggle {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--white-60);
      cursor: pointer;
      padding: 4px;
      font-size: 1.1rem;
      transition: color 0.2s;
    }

    .password-toggle:hover {
      color: var(--white-85);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    @media (max-width: 500px) {
      .form-row {
        grid-template-columns: 1fr;
      }
    }

    .btn-register {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, var(--blue-mid), var(--blue-light));
      border: none;
      border-radius: 10px;
      color: #fff;
      font-size: 1rem;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: all 0.2s ease;
      margin-top: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-register:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(40, 53, 147, 0.4);
    }

    .btn-register:active {
      transform: translateY(0);
    }

    .register-footer {
      text-align: center;
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--white-10);
    }

    .register-footer p {
      font-size: 0.875rem;
      color: var(--white-60);
    }

    .register-footer a {
      color: var(--blue-light);
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s;
    }

    .register-footer a:hover {
      color: #fff;
    }

    .password-strength {
      height: 4px;
      background: var(--white-10);
      border-radius: 2px;
      margin-top: 8px;
      overflow: hidden;
    }

    .password-strength-bar {
      height: 100%;
      width: 0;
      transition: width 0.3s, background 0.3s;
      border-radius: 2px;
    }

    .password-strength-bar.weak {
      width: 33%;
      background: var(--danger);
    }

    .password-strength-bar.medium {
      width: 66%;
      background: #ff9800;
    }

    .password-strength-bar.strong {
      width: 100%;
      background: var(--success);
    }
  </style>
</head>
<body>

  <div class="register-container">
    <div class="register-card">
      <div class="register-header">
        <div class="register-icon">
          <i class="bi bi-person-plus-fill"></i>
        </div>
        <h1 class="register-title">Create Account</h1>
        <p class="register-subtitle">Register a new user to the system</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-error">
          <i class="bi bi-exclamation-circle"></i>
          <?= $error ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="bi bi-check-circle"></i>
          <?= $success ?>
        </div>
      <?php endif; ?>

      <form action="../database/backend/register_u.php" method="POST" id="registerForm">
        <div class="form-group">
          <label class="form-label" for="full_name">Full Name</label>
          <div class="input-group">
            <input 
              type="text" 
              id="full_name" 
              name="full_name" 
              class="form-input has-icon" 
              placeholder="Enter full name"
              required
              autocomplete="name"
            >
            <i class="bi bi-person input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-group">
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input has-icon" 
              placeholder="Choose a username"
              required
              autocomplete="username"
            >
            <i class="bi bi-at input-icon"></i>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="email">Email Address</label>
            <div class="input-group">
              <input 
                type="email" 
                id="email" 
                name="email" 
                class="form-input has-icon" 
                placeholder="Enter email"
                required
                autocomplete="email"
              >
              <i class="bi bi-envelope input-icon"></i>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <div class="input-group">
              <input 
                type="tel" 
                id="phone" 
                name="phone" 
                class="form-input has-icon" 
                placeholder="Enter phone"
                required
                autocomplete="tel"
              >
              <i class="bi bi-phone input-icon"></i>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="input-group">
              <input 
                type="password" 
                id="password" 
                name="password" 
                class="form-input has-icon" 
                placeholder="Create password"
                required
                autocomplete="new-password"
                minlength="6"
              >
              <i class="bi bi-lock input-icon"></i>
              <button type="button" class="password-toggle" onclick="togglePassword('password')">
                <i class="bi bi-eye-slash" id="toggle-password"></i>
              </button>
            </div>
            <div class="password-strength">
              <div class="password-strength-bar" id="strength-bar"></div>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="confirm_password">Confirm Password</label>
            <div class="input-group">
              <input 
                type="password" 
                id="confirm_password" 
                name="confirm_password" 
                class="form-input has-icon" 
                placeholder="Confirm password"
                required
                autocomplete="new-password"
              >
              <i class="bi bi-lock-fill input-icon"></i>
              <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                <i class="bi bi-eye-slash" id="toggle-confirm_password"></i>
              </button>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-register">
          <i class="bi bi-person-plus"></i>
          Register User
        </button>
      </form>

      <div class="register-footer">
        <p>Already have an account? <a href="#">Contact administrator</a></p>
      </div>
    </div>
  </div>

  <script>
    // Toggle password visibility
    function togglePassword(inputId) {
      const input = document.getElementById(inputId);
      const icon = document.getElementById('toggle-' + inputId);
      
      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
      } else {
        input.type = 'password';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
      }
    }

    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strength-bar');

    passwordInput.addEventListener('input', function() {
      const password = this.value;
      strengthBar.className = 'password-strength-bar';
      
      if (password.length === 0) {
        strengthBar.style.width = '0';
      } else if (password.length < 6) {
        strengthBar.classList.add('weak');
      } else if (password.length < 10) {
        strengthBar.classList.add('medium');
      } else {
        strengthBar.classList.add('strong');
      }
    });

    // Form validation
    const form = document.getElementById('registerForm');
    
    form.addEventListener('submit', function(e) {
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;
      
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
      }
      
      if (password.length < 6) {
        e.preventDefault();
        alert('Password must be at least 6 characters!');
        return false;
      }
      
      return true;
    });
  </script>

<?php if (!$is_included) { ?>
</body>
</html>
<?php } ?>
