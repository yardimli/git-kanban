<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$filename = $_POST['filename'];
		$archived = filter_var($_POST['archived'], FILTER_VALIDATE_BOOLEAN);
		$filePath = $cardsDir . '/' . $filename;
		$user = $_SESSION['user'];

		if (file_exists($filePath)) {
			$story = json_decode(file_get_contents($filePath), true);
			$story['archived'] = $archived;

			if ($archived) {
				$story = log_history($story, 'Archived the story', $user);
			} else {
				$story = log_history($story, 'Unarchived the story', $user);
			}

			file_put_contents($filePath, json_encode($story, JSON_PRETTY_PRINT));
			echo json_encode(['success' => true]);
		} else {
			echo json_encode(['success' => false, 'message' => 'Story not found']);
		}
	}
