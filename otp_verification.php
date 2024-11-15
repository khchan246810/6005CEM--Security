<?php
session_start();
include_once('include/config.php');

if (!isset($_SESSION['otp']) || !isset($_SESSION['pending_registration'])) {
    echo "<script>alert('No OTP request found.'); window.location='registration.php';</script>";
    exit();
}

$error_message = "";

if (isset($_POST['verify_otp'])) {
    $entered_otp = $_POST['otp'];

    // Check if OTP matches
    if ($entered_otp == $_SESSION['otp']) {
        // OTP is correct, insert user data into database
        $user_data = $_SESSION['pending_registration'];
        $query = mysqli_query($con, "INSERT INTO users (fullname, address, city, gender, email, password) VALUES ('{$user_data['fname']}', '{$user_data['address']}', '{$user_data['city']}', '{$user_data['gender']}', '{$user_data['email']}', '{$user_data['password']}')");

        if ($query) {
            echo "<script>alert('Successfully Registered. You can log in now'); window.location='index.php';</script>";

            // Clear session data after successful registration
            unset($_SESSION['otp']);
            unset($_SESSION['pending_registration']);
        }
    } else {
        $error_message = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>OTP Verification</title>
    <style>
    /* Basic reset for padding and margin */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: Arial, sans-serif;
    }

    /* Centering the form on the page */
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      background-color: #f4f4f9;
    }

    /* Styling the form container */
    form {
      width: 320px;
      padding: 20px;
      background-color: #ffffff;
      border-radius: 8px;
      box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
      text-align: center;
    }

    /* Heading styling */
    form h2 {
      font-size: 24px;
      color: #333333;
      margin-bottom: 15px;
    }

    /* Input field styling */
    form input[type="text"] {
      width: 100%;
      padding: 10px;
      border: 1px solid #dddddd;
      border-radius: 5px;
      margin-bottom: 15px;
      font-size: 16px;
    }

    /* Submit button styling */
    form button[type="submit"] {
      width: 100%;
      padding: 10px;
      background-color: #4CAF50;
      color: #ffffff;
      border: none;
      border-radius: 5px;
      font-size: 16px;
      cursor: pointer;
      transition: background-color 0.3s;
    }

    /* Button hover effect */
    form button[type="submit"]:hover {
      background-color: #45a049;
    }
  </style>
</head>
<body>
    <form method="post">
    <h2>Enter OTP</h2>
        <input type="text" name="otp" required>
        <button type="submit" name="verify_otp">Verify</button>
    </form>

    <?php if (!empty($error_message)) : ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
</body>
</html>