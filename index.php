<?php

require __DIR__ . "/assets/config/dbconfig.php";

session_start();

if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
  $departmentCode = $_SESSION['department_code'] ?? 'default';}

if(isset($_SESSION['user_id'])){
  header("Location: home.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>U-Plug Login / Sign Up</title>
  <link rel="stylesheet" href="/assets/css/index.css">
  <script src="https://unpkg.com/just-validate@latest/dist/just-validate.production.min.js" defer></script>
  <script src="/assets/javascript/validation.js" defer></script>
  <link rel="icon" href="/assets/images/client/UplugLogo.png" type="image/png">
</head>
<body>

    <div id="welcome-screen">
      <div class="welcome-message">
        <img src="/assets/images/client/UplugLogo.png" alt="Uplug Logo"> <br>
        <?php if (isset($_SESSION['department_code'])): ?>
          <img src="/assets/images/client/department/<?= htmlspecialchars($_SESSION['department_code']) ?>.png" alt="<?= htmlspecialchars($_SESSION['department_code']) ?> Logo">
        <?php endif; ?>
        <img id="loading" src="/assets/images/client/Loading.gif" alt="Loading">
      </div>
    </div>

  <div class="auth-container">


    <div class="glass-card" id="login-card">

      <h2>Login</h2>

      <form action="/assets/server/login-process.php" class="auth-form" id="login-form" autocomplete="off" method="POST" novalidate>
        <div class="form-group">
          <select name="login_role" id="login_role" required>
              <option value="" disabled selected>Select Account Type</option>
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
              <option value="admin">Admin</option>
            </select>
        </div>
        <div class="form-group"><input type="text" name="login_email" id="login_email" placeholder="Email" required></div>
        <div class="form-group password-wrapper">
          <input type="password" name="login_password" id="login-password" placeholder="Password" required>
          <span class="toggle-password" data-target="login-password">
            <img src="/assets/images/client/hidden_password.png" alt="Show Password" class="eye-icon">
          </span>
        </div>
        <button type="submit" class="login-btn">Login</button>
      </form>

      
      <div class="switch-link">
        <span>Forgot your password?</span>
        <button id="show-forgot" type="button">Reset</button>
      </div>
      <div class="switch-link">
        <span>Don't have an account?</span>
        <button id="show-signup" type="button">Sign Up</button>
      </div>


    </div>

    <div class="glass-card" id="signup-card" style="display:none;">

      <h2>Sign Up</h2>

      <div class="center-wrapper">
      <form action="/assets/server/signup-process.php" class="auth-form" id="signup-form" autocomplete="off" method="POST" novalidate>
        <div class="form-group">
          <select name="signup_role" id="signup_role" required>
            <option value="" disabled selected>Select Account Type</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
          </select>
        </div>
        <div class="form-group">
          <select name="department" id="department" required>
            <option value="" disabled selected>Select Department</option>
            <option value="SHS">SHS - Senior Highschool</option>
            <option value="CITE">CITE - College of Information Technology Education</option>
            <option value="CCJE">CCJE - College of Criminal Justice Education</option>
            <option value="CAHS">CAHS - College of Allied Health Sciences</option>
            <option value="CAS">CAS - College of Arts and Sciences</option>
            <option value="CEA">CEA - College of Engineering and Architecture</option>
            <option value="CELA">CELA - College of Education and Liberal Arts</option>
            <option value="CMA">CMA - College of Management and Accountancy</option>
            <option value="COL">COL - College of Law</option>
          </select>
        </div>
        <div class="form-group"><input type="text" name="first_name" placeholder="First Name" id="first_name" required></div>
        <div class="form-group"><input type="text" name="last_name" placeholder="Last Name" id="last_name" required></div>
        <!-- <div class="form-group"><input type="email" name="signup_email" placeholder="Email" id="signup_email" required></div> -->
        <div class="form-group">
          <div class="email-wrapper">
            <input type="text" id="signup_email" name="signup_email" placeholder="Your Phinmaed email" autocomplete="off" required>
            <div id="email-preview" class="email-preview"></div>
            <span class="email-domain">@phinmaed.com</span>
          </div>
        </div>
        <div class="form-group password-wrapper"><input type="password" name="signup_password" placeholder="Password" id="signup-password" required>
        <!-- <small class="email-hint">Passwords must atleast contain (1 Uppercase letter, 1 Lowercase letter, 1 Number, Must be at least 8 characters)</small> -->
        <span class="toggle-password" data-target="signup-password">
          <img src="/assets/images/client/hidden_password.png" alt="Show Password" class="eye-icon">
        </span>
        </div>
        <div class="form-group password-wrapper"><input type="password" name="password_confirmation" placeholder="Confirm Password" id="password_confirmation" required>
        <span class="toggle-password" data-target="password_confirmation">
          <img src="/assets/images/client/hidden_password.png" alt="Show Password" class="eye-icon">
        </span>
        </div>
        <button type="submit" class="signup-btn">Sign Up</button>
      </form>
      </div>

      <div class="switch-link">
        <span>Already have an account?</span>
        <button id="show-login" type="button">Login</button>
      </div>

    </div>

    <div class="glass-card" id="forgot-card" style="display:none;">
        <h2>Reset Password</h2>
        <form action="/assets/server/request-reset.php" class="auth-form" id="forgot-form" autocomplete="off" method="POST" novalidate>
          <div class="form-group">
            <select name="forgot_role" id="forgot_role" required>
              <option value="" disabled selected>Select Account Type</option>
              <option value="student">Student</option>
              <option value="faculty">Faculty</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="form-group">
            <input type="email" name="forgot_email" id="forgot_email" placeholder="Your Phinmaed email" required>
          </div>
          <button type="submit" class="signup-btn">Send Reset Link</button>
        </form>

        <div class="switch-link">
          <span>Remembered your password?</span>
          <button id="back-to-login" type="button">Login</button>
        </div>
      </div>

  </div>

  <!---FOR JS--->
  
  <script>
    // Toggle between login, signup, and forgot password
    document.getElementById('show-signup').onclick = function () {
      document.getElementById('login-card').style.display = 'none';
      document.getElementById('signup-card').style.display = 'flex';
      document.getElementById('forgot-card').style.display = 'none';
    };

    document.getElementById('show-login').onclick = function () {
      document.getElementById('login-card').style.display = 'flex';
      document.getElementById('signup-card').style.display = 'none';
      document.getElementById('forgot-card').style.display = 'none';
    };

    document.getElementById('show-forgot').onclick = function () {
      document.getElementById('login-card').style.display = 'none';
      document.getElementById('signup-card').style.display = 'none';
      document.getElementById('forgot-card').style.display = 'flex';
    };

    document.getElementById('back-to-login').onclick = function () {
      document.getElementById('login-card').style.display = 'flex';
      document.getElementById('signup-card').style.display = 'none';
      document.getElementById('forgot-card').style.display = 'none';
    };

    document.querySelectorAll('.toggle-password').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const targetId = toggle.getAttribute('data-target');
        const input = document.getElementById(targetId);
        const icon = toggle.querySelector('img');
        const isHidden = input.type === 'password';
      
        input.type = isHidden ? 'text' : 'password';
        icon.src = isHidden 
          ? '/assets/images/client/show_password.png' 
          : '/assets/images/client/hidden_password.png';
        icon.alt = isHidden ? 'Hide Password' : 'Show Password';
      });
    });
  </script>
</body>
</html>