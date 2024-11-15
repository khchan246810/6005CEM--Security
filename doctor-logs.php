<?php
session_start();
error_reporting(0);
include('include/config.php');
include('include/checklogin.php');
include ('include/session_check_admin.php');

check_login();
define(constant_name: 'ENCRYPTION_KEY', value: 'b7gHjQ4LpZ0e3f*J8k@m!z5Q'); // Secure encryption key


function decrypt($dataDecrypt) {
    list($iv, $encrypted) = explode('::', base64_decode($dataDecrypt), 2);
    $cipher = "AES-128-CTR";
    return openssl_decrypt($encrypted, $cipher, ENCRYPTION_KEY, 0, $iv);
}

if(isset($_POST['submit'])) {
    // Delete all doctor session logs
    $sql = mysqli_query($con, "DELETE FROM doctorslog");
    if($sql) {
        $_SESSION['msg'] = "Doctor session logs deleted successfully!";
    }
}
if (!defined('SESSION_TIMEOUT')) {
	define('SESSION_TIMEOUT', 300);
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Doctor Session Logs</title>
    <!-- Bootstrap -->
    <link href="../vendors/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="../vendors/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <!-- NProgress -->
    <link href="../vendors/nprogress/nprogress.css" rel="stylesheet">
    <!-- iCheck -->
    <link href="../vendors/iCheck/skins/flat/green.css" rel="stylesheet">
    <!-- bootstrap-progressbar -->
    <link href="../vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="../assets/css/custom.min.css" rel="stylesheet">
</head>
<body class="nav-md">
    <?php
    $page_title = 'Admin | Doctor Session Logs';
    $x_content = true;
    ?>
    <?php include('include/header.php'); ?>
    <div class="row">
        <div class="col-md-12">
            <p style="color:red;"><?php echo htmlentities($_SESSION['msg']) ?>
            <?php echo htmlentities($_SESSION['msg'] = ""); ?></p>
            <div class="panel-heading">
                <h5 class="panel-title">Delete all doctor logs</h5>
            </div>
            <div class="panel-body">
                <form method="POST" onSubmit="if(!confirm('Do you really want to delete all doctor session logs?')){return false;}">
                    <button type="submit" name="submit" class="btn btn-danger">Delete All Logs</button>
                </form>
            </div>

            <!-- Scrollable Table -->
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover" id="sample-table-1">
                    <thead>
                        <tr>
                            <th class="center">#</th>
                            <th class="hidden-xs">User ID</th>
                            <th>Username</th>
                            <th>Specialization</th>
                            <th>Email</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = mysqli_query($con, "SELECT * FROM doctorslog ");
                        $cnt = 1;
                        while ($row = mysqli_fetch_array($sql)) {
                            ?>
                            <tr>
                                <td class="center"><?php echo $cnt; ?>.</td>
                                <td class="hidden-xs"><?php echo $row['uid']; ?></td>
                                <td class="hidden-xs"><?php echo $row['doctorName'];?></td>
                                <td class="hidden-xs"><?php echo $row['specilization'];?></td>
                                <td class="hidden-xs"><?php echo $row['username']; ?></td>
                                <td><?php echo $row['loginTime']; ?></td>
                                <td><?php echo $row['logout']; ?></td>
                                <td><?php echo $row['status'] == 1 ? 'Success' : 'Failed'; ?></td>
                            </tr>
                            <?php
                            $cnt++;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php include('include/footer.php'); ?>
    <!-- jQuery -->
    <script src="../vendors/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="../vendors/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Theme Scripts -->
    <script src="../assets/js/custom.min.js"></script>
    <script>
		let timeout = <?php echo SESSION_TIMEOUT; ?>;
		let countdown = timeout;

		function updateCountdown() {
			countdown--;
			document.getElementById('countdown').innerText = countdown;

			if (countdown <= 0) {
				alert("Your session has expired. Please log in again.");
				window.location.href = "admin/index.php?session_expired=1";
			}
		}


		setInterval(updateCountdown, 1000);
	</script>
</body>
</html>
