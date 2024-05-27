<?php
	include_once 'settings.php';

	$allHistories = [];

	foreach (glob($cardsDir . '/*.json') as $filePath) {
		$story = json_decode(file_get_contents($filePath), true);
		if (isset($story['history'])) {
			foreach ($story['history'] as $history) {
				$history['title'] = $story['title'];
				$allHistories[] = $history;
			}
		}
	}

// Sort histories by most recent first
	usort($allHistories, function ($a, $b) {
		return strtotime($b['timestamp']) - strtotime($a['timestamp']);
	});

	echo json_encode($allHistories);
