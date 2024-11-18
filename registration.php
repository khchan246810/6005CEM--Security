<?php
session_start();

include_once('include/config.php');
require 'vendor/autoload.php'; // Load PHPMailer using Composer's autoloader

define('ENCRYPTION_KEY', value: 'b7gHjQ4LpZ0e3f*J8k@m!z5Q'); // Secure encryption key

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Define a function to generate and send OTP
function sendOTP($email) {
    $otp = rand(100000, 999999); // Generate a 6-digit OTP
    $_SESSION['otp'] = $otp;
    $_SESSION['otp_email'] = $email;

    // Configure PHPMailer to send OTP
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'reubenchoo075@gmail.com'; // Replace with your SMTP username
        $mail->Password = 'mndq ayfe wrbn zokh'; // Replace with your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('reubenchoo075@gmail.com', 'hospital management system');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code for Registration';
        $mail->Body = "Your OTP code is: <strong>$otp</strong>. Please enter this code to complete your registration.";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Encrypt email function
function encryptEmail($email) {
    $cipher = "AES-128-CTR";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    $encrypted = openssl_encrypt($email, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted);
}
function encryptAddress($address) {
    $cipher = "AES-128-CTR";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher)); // Generating a random IV
    $encrypted = openssl_encrypt($address, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted); // Returning the IV and encrypted value as base64
}
function encryptCity($city) {
    $cipher = "AES-128-CTR";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher)); // Generating a random IV
    $encrypted = openssl_encrypt($city, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted); // Returning the IV and encrypted value as base64
}

function encryptGend($gender) {
    $cipher = "AES-128-CTR";
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher)); // Generating a random IV
    $encrypted = openssl_encrypt($gender, $cipher, ENCRYPTION_KEY, 0, $iv);
    return base64_encode($iv . '::' . $encrypted); // Returning the IV and encrypted value as base64
}
$name_error = ""; 
$email_error = "";  
$password_error = "";  

if (isset($_POST['submit'])) {
    $fname = $_POST['full_name'];
    $address = encryptAddress($_POST['address']);
    $city = encryptCity($_POST['city']);
    $gender = encryptGend($_POST['gender']);
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!preg_match('/^[a-zA-Z]+(?:\s[a-zA-Z]+)*$/', $fname)) {
        $name_error = "Name can only contain uppercase and lowercase letters.";
    } 
    elseif (mysqli_num_rows(mysqli_query($con, "SELECT * FROM users WHERE email = '" . encryptEmail($email) . "'")) > 0) {
        $email_error = "Email is already registered. Please use a different email address.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_error = "Please enter a valid email address.";
    } 
    elseif (strlen($password) < 8 || 
        !preg_match('/[A-Z]/', $password) ||   
        !preg_match('/\d/', $password) ||      
        !preg_match('/[@$!%*?&]/', $password)) 
    {
        $password_error = "Password must be at least 8 characters long, include at least one uppercase letter, one number, and one special character.";
    } 
    else {
        // Send OTP to user's email
        sendOTP($email);
        
        // Store user data in session for later
        $_SESSION['pending_registration'] = [
            'fname' => $fname,
            'address' => $address,
            'city' => $city,
            'gender' => $gender,
            'email' => encryptEmail($email),
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ];

        // Redirect to OTP verification page
        header("Location: otp_verification.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
	<title>User Registration</title>

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
	<link href="assets/css/custom.css" rel="stylesheet">

	<script type="text/javascript">
		function valid()
		{
			if(document.registration.password.value!= document.registration.password_again.value)
			{
				alert("Password and Confirm Password Field do not match  !!");
				document.registration.password_again.focus();
				return false;
			}
			return true;
		}
	</script>


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
							<!-- start: REGISTER BOX -->
							<div class="box-register">
								<form name="registration" id="registration"  method="post" onSubmit="return valid();">
									<fieldset>
										<legend>
										Universal Hospital Management System | Patient Registration
										</legend>

									<?php if (!empty($name_error)) : ?>
                                        <div class="card text-white bg-danger mb-3" style="max-width: 18rem;">
                                            <div class="card-body">
                                                <p class="card-text"><?php echo $name_error; ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

									<?php if (!empty($email_error)) : ?>
											<div class="card text-white bg-danger mb-3" style="max-width: 18rem;">
                                            <div class="card-body">
                                                <p class="card-text"><?php echo $email_error; ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>

									<?php if (!empty($password_error)) : ?>
                                        <div class="card text-white bg-danger mb-3" style="max-width: 18rem;">
                                            <div class="card-body">
                                                <p class="card-text"><?php echo $password_error; ?></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
										<p>
											Enter your personal details below:
										</p>
										<div class="form-group">
											<input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
										</div>
										<div class="form-group">
											<input type="text" class="form-control" name="address" placeholder="Address" required>
										</div>
										<div class="form-group">
											<input type="text" class="form-control" name="city" placeholder="City" required>
										</div>
										<div class="form-group">
											<label class="block">
												Gender
											</label>
											<div class="clip-radio radio-primary">
												<input type="radio" id="rg-female" name="gender" value="female" >
												<label for="rg-female">
													Female
												</label>
												<input type="radio" id="rg-male" name="gender" value="male">
												<label for="rg-male">
													Male
												</label>
											</div>
										</div>
										<p>
											Enter your account details below:
										</p>
										<div class="form-group">
											<span class="input-icon">
												<input type="email" class="form-control" name="email" id="email" onBlur="userAvailability()"  placeholder="Email" required>
												<span id="user-availability-status1" style="font-size:12px;"></span>
											</div>
											<div class="form-group">
												<span class="input-icon">
													<input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
												</div>
												<div class="form-group">
													<span class="input-icon">
														<input type="password" class="form-control"  id="password_again" name="password_again" placeholder="Password Again" required>
													</div>
													<div class="form-group">
														<div class="checkbox clip-check check-primary">
															<input type="checkbox" id="agree" value="agree" checked="true" readonly=" true">
															<label for="agree">
																I agree
															</label>
														</div>
													</div>
													<div class="form-actions">
														<p>
															Already have an account?
															<a href="index.php">
																Log-in
															</a>
														</p>
														<button type="submit" class="btn btn-primary pull-right" id="submit" name="submit">
															Submit <i class="fa fa-arrow-circle-right"></i>
														</button>
													</div>
												</fieldset>
											</form>

											<div class="copyright">
												&copy; <span class="current-year"></span><span class="text-bold text-uppercase"> Universal Hospital Management System</span>. <span>All rights reserved</span>
											</div>

										</div>

									</div>
								</div>
							</section>
						</div>
					</div>
				</div>


				<script>
					function userAvailability() {
						$("#loaderIcon").show();
						jQuery.ajax({
							url: "check_availability.php",
							data:'email='+$("#email").val(),
							type: "POST",
							success:function(data){
								$("#user-availability-status1").html(data);
								$("#loaderIcon").hide();
							},
							error:function (){}
						});
					}
				</script>

			</body>
			<!-- end: BODY -->
			</html>