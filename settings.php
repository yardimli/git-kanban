<?php
	session_start();

	$cardsDirName = 'git-kanban-project';

	//json string for users, Admin password is 123456
	$users =
		[
			['username' => 'Admin', 'password' => '$2y$10$kMdhKRcawdXC9JhayVRhS.mZ/T5Va7K1wfck7FcM6uff1BGfd1qym'],
			['username' => 'Ekim', 'password' => '$2y$10$DIbIGXf43w/583AeGtCtMuiGFJZvNn6CNqatLrYYqOzzDdgeu62Kq'],
		];

	$autoLoginUser = ''; //leave this empty if you want to allow all users to login

	$colorOptions = [
		['background' => '#F28B82', 'text' => '#000000'],
		['background' => '#FBBC04', 'text' => '#000000'],
		['background' => '#FFF475', 'text' => '#000000'],
		['background' => '#CCFF90', 'text' => '#000000'],
		['background' => '#A7FFEB', 'text' => '#000000'],
		['background' => '#CBF0F8', 'text' => '#000000'],
		['background' => '#AECBFA', 'text' => '#000000'],
		['background' => '#D7AEFB', 'text' => '#000000'],
		['background' => '#FDCFE8', 'text' => '#000000'],
		['background' => '#E6C9A8', 'text' => '#000000'],
		['background' => '#E8EAED', 'text' => '#000000'],
		['background' => '#FFFFFF', 'text' => '#000000']
	];

// Define default columns
	$columns = [
		['id' => 'parking-lot', 'title' => 'Parking-Lot'],
		['id' => 'to-do', 'title' => 'To-Do'],
		['id' => 'in-progress', 'title' => 'In-Progress'],
		['id' => 'finished', 'title' => 'Finished'],
	];

	$defaultColumn = 'to-do';

	//------------------DO NOT MODIFY BELOW THIS LINE------------------
	$cardsDir = __DIR__ . '/' . $cardsDirName;

	if (!file_exists($cardsDir)) {
		mkdir($cardsDir, 0777, true);
	}

	if (!file_exists($cardsDir. '/uploads')) {
		mkdir($cardsDir. '/uploads', 0777, true);
	}


	if ($autoLoginUser !== '' && basename($_SERVER['PHP_SELF']) === 'login.php') {
		$_SESSION['user'] = $autoLoginUser;
		header('Location: index.php');
		exit();
	}

	if (empty($_SESSION['user'])) {
		//dont redirect to login.php if the request url is alread login.php
		if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
			header('Location: login.php');
			exit();
		}
	}

	function log_history($story, $action, $user) {
		$story['history'][] = [
			'action' => $action,
			'user' => $user,
			'timestamp' => date('Y-m-d H:i:s')
		];
		return $story;
	}

	function create_slug($string) {
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		return $slug;
	}
