<?php
	session_start();
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = $_POST['username'] ?? '';
		$password = $_POST['password'] ?? '';

		$userFound = false;

		foreach ($users as $user) {
			if ($user['username'] === $username && password_verify($password, $user['password'])) {
				$_SESSION['user'] = $username;
				$userFound = true;
				header('Location: index.php');
				exit();
			}
		}

		if (!$userFound) {
			$error = "Invalid username or password";
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<link href="css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<body style=" background: #e91e63 !important;">
<!-- Login Form -->
<div class="container">
	<h1 class="text-center mt-5 text-white">
		Git Kanban Board
	</h1>
	<div class="row justify-content-center mt-5">
		<div class="col-lg-4 col-md-6 col-sm-6">
			<div class="card shadow">
				<div class="card-title text-center border-bottom">
					<h2 class="p-3">Login</h2>
				</div>
				<div class="card-body">
					<?php if (isset($error)): ?>
						<div class="alert alert-danger" role="alert">
							<?php echo $error; ?>
						</div>
					<?php endif; ?>
					<form method="POST" action="login.php">
						<div class="mb-4">
							<label for="username" class="form-label">Username</label>
							<input type="text" class="form-control" id="username" name="username" />
						</div>
						<div class="mb-4">
							<label for="password" class="form-label">Password</label>
							<input type="password" class="form-control" id="password" name="password" />
						</div>
						<div class="d-grid">
							<button type="submit" class="btn btn-primary text-light main-bg">Login</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>
</body>


<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
