<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$fileName = $_POST['filename'];
		$column = $_POST['column'];
		$order = $_POST['order'];
		$filePath = $cardsDir . '/' . $fileName;
		$user = $_SESSION['user'];

		if (file_exists($filePath)) {
			$story = json_decode(file_get_contents($filePath), true);
			$dontUpdateTime = false;
			if ($story['column'] === $column) {
				$dontUpdateTime = true;
			}
			$story['column'] = $column;
			$story['order'] = $order;
			if (!$dontUpdateTime) {
				$story['lastUpdated'] = date('Y-m-d H:i:s');
				$story = log_history($story, 'Moved card to ' . $column, $user);
			}
			file_put_contents($filePath, json_encode($story, JSON_PRETTY_PRINT));
			echo json_encode($story);
		}
	}
