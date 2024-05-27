<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$storyFilename = $_POST['storyFilename'];
		$commentId = $_POST['id'];
		$text = $_POST['text'];
		$user = $_SESSION['user'];
		$timestamp = date('Y-m-d H:i:s');

		$filePath = $cardsDir . '/' . $storyFilename;
		if (file_exists($filePath)) {
			$story = json_decode(file_get_contents($filePath), true);
			if (!isset($story['comments'])) {
				$story['comments'] = [];
			}

			if (empty($commentId)) {
				$commentId = uniqid();
				$story['comments'][] = [
					'id' => $commentId,
					'text' => $text,
					'user' => $user,
					'timestamp' => $timestamp,
				];
				$story = log_history($story, 'Added comment', $user);
				$isNew = true;
			} else {
				foreach ($story['comments'] as &$comment) {
					if ($comment['id'] === $commentId) {
						$comment['text'] = $text;
						$comment['timestamp'] = $timestamp;
						$story = log_history($story, 'Edited comment', $user);
						break;
					}
				}
				$isNew = false;
			}

			file_put_contents($filePath, json_encode($story, JSON_PRETTY_PRINT));
			echo json_encode([
				'id' => $commentId,
				'text' => $text,
				'user' => $user,
				'timestamp' => $timestamp,
				'storyFilename' => $storyFilename,
				'isNew' => $isNew,
			]);
		}
	}
