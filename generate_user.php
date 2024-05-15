<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$username = preg_replace('/\s+/', '', $_POST['username']);
		$username = preg_replace('/[^\w\-]/', '', $username);
		$password = $_POST['password'];

		$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

		$response = [
			'username' => $username,
			'password' => $hashedPassword
		];

		echo "['username' => '".$username."', 'password' => '".$hashedPassword."'],";
	}
