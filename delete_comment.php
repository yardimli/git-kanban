<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$storyFilename = $_POST['storyFilename'];
		$commentId = $_POST['id'];

		$filePath = $cardsDir . '/' . $storyFilename;
		if (file_exists($filePath)) {
			$story = json_decode(file_get_contents($filePath), true);
			if (isset($story['comments'])) {
				$story['comments'] = array_values(array_filter($story['comments'], function ($comment) use ($commentId) {
					return $comment['id'] !== $commentId;
				}));
				file_put_contents($filePath, json_encode($story, JSON_PRETTY_PRINT));
				echo json_encode(['success' => true]);
			}
		}
	}
