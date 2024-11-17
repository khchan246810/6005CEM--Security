<?php  
session_start();
error_reporting(0);
include("include/config.php");

if (isset($_GET['session_expired']) && $_GET['session_expired'] == 300) {
    echo "<script>alert('Your session has expired due to inactivity. Please log in again.');</script>";
}

if (isset($_POST['submit'])) {
    $fullName = $_POST['username'];  // Changed to fullName instead of username
    $password = $_POST['password'];

    // Initialize login attempt tracking
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_failed_attempt'] = null;
    }

    // Check if the account is locked
    if ($_SESSION['failed_attempts'] >= 3) {
        if (time() - $_SESSION['last_failed_attempt'] < 1) {
            $_SESSION['errmsg'] = "Account locked due to multiple failed login attempts. Please try again later.";
            header("Location: index.php");
            exit();
        } else {
            // Reset failed attempts after the lockout period
            $_SESSION['failed_attempts'] = 0;
        }
    }

    // Secure the SQL query using prepared statements to avoid SQL injection
    $stmt = mysqli_prepare($con, "SELECT * FROM users WHERE fullName = ?");
    if ($stmt) {
        // Bind the user input (fullName) safely as a string parameter
        mysqli_stmt_bind_param($stmt, "s", $fullName);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $num = mysqli_fetch_array($result);

        // Check if the user exists and password matches
        if ($num && password_verify($password, $num['password'])) {
            // Successful login
            $extra = "dashboard.php";
            $_SESSION['login'] = $fullName;
            $_SESSION['fullName'] = $num['fullName'];
            $_SESSION['id'] = $num['id'];

            // Reset failed attempts on successful login
            $_SESSION['failed_attempts'] = 0;

            // Log user login using a prepared statement
            $uip = $_SERVER['REMOTE_ADDR'];
            $status = 1;
            $logStmt = mysqli_prepare($con, "INSERT INTO userlog(uid, fullName, username, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($logStmt, "issi", $_SESSION['id'], $_SESSION['fullName'], $fullName, $status);
            mysqli_stmt_execute($logStmt);

            // Redirect to the dashboard
            header("Location: $extra");
            exit();
        } else {
            // Failed login
            $_SESSION['failed_attempts']++;
            $_SESSION['last_failed_attempt'] = time();

            // Log failed login attempt using prepared statement (NULL for fullName)
            $status = 0;
            $var = ""; // Empty value for fullName in log for failed attempts
            $logStmt = mysqli_prepare($con, "INSERT INTO userlog(username, fullName, status) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($logStmt, "ssi", $fullName, $var, $status);
            mysqli_stmt_execute($logStmt);

            // Set error message for invalid credentials
            $_SESSION['errmsg'] = "Invalid full name or password";

            // Redirect to the login page with error message
            header("Location: index.php");
            exit();
        }
    } else {
        // Handle error when preparing the statement
        $_SESSION['errmsg'] = "Error in SQL query.";
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User-Login</title>
    <!-- Bootstrap -->
    <link href="vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- JQVMap -->
    <link href="vendors/jqvmap/dist/jqvmap.min.css" rel="stylesheet"/>
    <!-- bootstrap-daterangepicker -->
    <link href="vendors/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="assets/css/custom.min.css" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="login">
    <div>
        <a class="hiddenanchor" id="signup"></a>
        <a class="hiddenanchor" id="signin"></a>
        <div class="login_wrapper">
            <div class="animate form login_form">
                <section class="login_content">
                    <div class="box-login">
                        <form class="form-login" method="post">
                            <fieldset>
                                <legend>
                                    Universal Hospital Management System | Patient Login
                                </legend>
                                <p>
                                    Please enter your name and password to log in.<br />
                                    <span style="color:red;">
                                        <?php 
                                        if (isset($_SESSION['errmsg']) && $_SESSION['errmsg'] != "") {
                                            echo $_SESSION['errmsg'];
                                            $_SESSION['errmsg'] = "";  // Clear the error message after displaying
                                        }
                                        ?>
                                    </span>
                                </p>
                                <div class="form-group">
                                    <span class="input-icon">
                                        <input type="text" class="form-control" name="username" placeholder="Username" required>
                                    </span>
                                </div>
                                <div class="form-group form-actions">
                                    <span class="input-icon">
                                        <input type="password" class="form-control password" name="password" placeholder="Password" required>
                                    </span>
                                    <a href="forgot-password.php">Forgot Password?</a>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary pull-right" name="submit">
                                        Login <i class="fa fa-arrow-circle-right"></i>
                                    </button>
                                </div>
                                <div class="new-account">
                                    Don't have an account yet?
                                    <a href="registration.php">Create an account</a>
                                </div>
                            </fieldset>
                        </form>

                        <div class="copyright">
                            &copy; <span class="current-year"></span><span class="text-bold text-uppercase"> Universal Hospital Management System</span>. <span>All rights reserved</span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>
