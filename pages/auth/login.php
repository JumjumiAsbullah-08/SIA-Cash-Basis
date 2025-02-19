<!DOCTYPE html>
<html lang="en">
<head>
	<title>Login - CV. Tamora Electric</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Favicon -->
	<link rel="icon" type="image/png" href="./assets/images/bg_1.jpg"/>
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/bootstrap/css/bootstrap.min.css">
	<!-- Font Awesome -->
	<link rel="stylesheet" type="text/css" href="./assets/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!-- Linearicons -->
	<link rel="stylesheet" type="text/css" href="./assets/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!-- Animate.css -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/animate/animate.css">
	<!-- Hamburgers -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/css-hamburgers/hamburgers.min.css">
	<!-- Animsition -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/animsition/css/animsition.min.css">
	<!-- Select2 -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/select2/select2.min.css">
	<!-- Daterangepicker -->
	<link rel="stylesheet" type="text/css" href="./assets/vendor/daterangepicker/daterangepicker.css">
	<!-- Util & Main CSS -->
	<link rel="stylesheet" type="text/css" href="./assets/css/util.css">
	<link rel="stylesheet" type="text/css" href="./assets/css/main.css">
</head>
<body>
	
	<div class="limiter">
		<div class="container-login100" style="background-image: url('./assets/images/bg-01.jpg');">
			<div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">
				<!-- Pastikan form diarahkan ke file proses login, misal process_login.php di root -->
				<form class="login100-form validate-form flex-sb flex-w" action="./process/proses_login.php" method="post">
					<span class="login100-form-title p-b-53">
                    Login to <strong> <br>CV. Tamora Electric</strong> 
					</span>					
					<div class="p-t-31 p-b-9">
						<span class="txt1">
							Username
						</span>
					</div>
					<div class="wrap-input100 validate-input" data-validate = "Username is required">
						<input class="input100" type="text" name="username" placeholder="username" required>
						<span class="focus-input100"></span>
					</div>
					
					<div class="p-t-13 p-b-9">
						<span class="txt1">
							Password
						</span>
						<!-- <a href="#" class="txt2 bo1 m-l-5">
							Forgot?
						</a> -->
					</div>
					<div class="wrap-input100 validate-input" data-validate = "Password is required">
						<input class="input100" type="password" name="pass" placeholder="password" required>
						<span class="focus-input100"></span>
					</div>

					<div class="container-login100-form-btn m-t-17">
						<button class="login100-form-btn">
							Sign In
						</button>
					</div>

					<div class="w-full text-center p-t-55">
						<span class="txt2">
                        &copy; 2025 CV. Tamora Electric
						</span>
					</div>
				</form>
			</div>
		</div>
	</div>
	
	<div id="dropDownSelect1"></div>
	
	<!-- jQuery -->
	<script src="./assets/vendor/jquery/jquery-3.2.1.min.js"></script>
	<!-- Animsition -->
	<script src="./assets/vendor/animsition/js/animsition.min.js"></script>
	<!-- Bootstrap JS -->
	<script src="./assets/vendor/bootstrap/js/popper.js"></script>
	<script src="./assets/vendor/bootstrap/js/bootstrap.min.js"></script>
	<!-- Select2 -->
	<script src="./assets/vendor/select2/select2.min.js"></script>
	<!-- Daterangepicker -->
	<script src="./assets/vendor/daterangepicker/moment.min.js"></script>
	<script src="./assets/vendor/daterangepicker/daterangepicker.js"></script>
	<!-- Countdown Time -->
	<script src="./assets/vendor/countdowntime/countdowntime.js"></script>
	<!-- Main JS -->
	<script src="./assets/js/main.js"></script>

</body>
</html>
