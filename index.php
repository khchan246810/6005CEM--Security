<?php
session_start();
error_reporting(0);
include("include/config.php");
if (isset($_GET['session_expired']) && $_GET['session_expired'] == 1) {
    echo "<script>alert('Your session has expired due to inactivity. Please log in again.');</script>";
}
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Initialize login attempt tracking
    if (!isset($_SESSION['failed_attempts'])) {
        $_SESSION['failed_attempts'] = 0;
        $_SESSION['last_failed_attempt'] = null;
    }

    // Check if the account is locked
    if ($_SESSION['failed_attempts'] >= 3) {
        if (time() - $_SESSION['last_failed_attempt'] < 300) {
            $_SESSION['errmsg'] = "Account locked due to multiple failed login attempts. Please try again later.";
            header("Location: index.php");
            exit();
        } else {
            // Reset failed attempts after the lockout period
            $_SESSION['failed_attempts'] = 0;
        }
    }

    // Authenticate the user
    $ret = mysqli_query($con, "SELECT * FROM users WHERE email='$username' AND password='$password'");
    $num = mysqli_fetch_array($ret);

    if ($num) {
        // Successful login
        $extra = "dashboard.php";
        $_SESSION['login'] = $username;
        $_SESSION['fullName'] = $num['fullName'];
        $_SESSION['id'] = $num['id'];

        // Reset failed attempts on successful login
        $_SESSION['failed_attempts'] = 0;

        // Log user login
        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 1;
        $fullName = $_SESSION['fullName']; // Get fullName from session
        mysqli_query($con, "INSERT INTO userlog(uid, fullName, username, status) VALUES('".$_SESSION['id']."', '$fullName', '$username', '$status')");

        header("Location: $extra");
        exit();
    } else {
        // Failed login
        $_SESSION['failed_attempts']++;
        $_SESSION['last_failed_attempt'] = time();

        $uip = $_SERVER['REMOTE_ADDR'];
        $status = 0;
        $fullName = ''; // For failed login, fullName might be unknown
        mysqli_query($con, "INSERT INTO userlog(username, fullName, status) VALUES('$username', '$fullName', '$status')");

        $_SESSION['errmsg'] = "Invalid username or password";
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
</head>
<body class="login">
	<div>
		<a class="hiddenanchor" id="signup"></a>
		<a class="hiddenanchor" id="signin"></a>
		<div class="login_wrapper">
			<div class="animate form login_form">
				<section class="login_content">
					<div class="box-login">
						<div class="box-login">
							<form class="form-login" method="post">

								<fieldset>
									<legend>
										Universal Hospital Management System | Patient Login
									</legend>
									<p>
										Please enter your name and password to log in.<br />
										<span style="color:red;"><?php echo $_SESSION['errmsg']; ?><?php echo $_SESSION['errmsg']="";?></span>
									</p>
									<div class="form-group">
										<span class="input-icon">
											<input type="text" class="form-control" name="username" placeholder="Username">
										</div>
										<div class="form-group form-actions">
											<span class="input-icon">
												<input type="password" class="form-control password" name="password" placeholder="Password">
											</span><a href="forgot-password.php">
												Forgot Password ?
											</a>
										</div>
										<div class="form-actions">

											<button type="submit" class="btn btn-primary pull-right" name="submit">
												Login <i class="fa fa-arrow-circle-right"></i>
											</button>
										</div>
										<div class="new-account">
											Don't have an account yet?
											<a href="registration.php">
												Create an account
											</a>
										</div>
									</fieldset>
								</form>

								<div class="copyright">
									&copy; <span class="current-year"></span><span class="text-bold text-uppercase"> HMS</span>. <span>All rights reserved</span>
								</div>

							</div>

						</div>
					</section>
				</div>
			</body>
			<!-- end: BODY -->
			</html>