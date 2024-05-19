<?php
	include_once 'settings.php';

	$stories = [];
	if (is_dir($cardsDir)) {
		$files = scandir($cardsDir);
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$filePath = $cardsDir . '/' . $file;
				$story = json_decode(file_get_contents($filePath), true);
				$story['filename'] = $file;
				//check if column is in the columns array if not change it to first column in the array
				$columnExists = false;
				foreach ($columns as $column) {
					if ($column['id'] === $story['column']) {
						$columnExists = true;
						break;
					}
				}
				if (!$columnExists) {
					$story['column'] = $columns[0]['id'];
				}
				$stories[] = $story;
			}
		}
	}
	echo json_encode($stories);
