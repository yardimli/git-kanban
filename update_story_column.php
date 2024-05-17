<?php
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$filename = $_POST['filename'];
		$column = $_POST['column'];
		$order = $_POST['order'];  // New line to get the order
		$filepath = __DIR__ . '/cards/' . $filename;
		if (file_exists($filepath)) {
			$story = json_decode(file_get_contents($filepath), true);
			$dontUpdateTime = false;
			if ($story['column'] === $column) {
				$dontUpdateTime = true;
			}
			$story['column'] = $column;
			$story['order'] = $order;  // New line to set the order
			if (!$dontUpdateTime) {
				$story['lastUpdated'] = date('Y-m-d H:i:s');
			}
			file_put_contents($filepath, json_encode($story, JSON_PRETTY_PRINT));
			echo json_encode($story);
		}
	}
