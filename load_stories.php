<?php
	include_once 'settings.php';

	$stories = [];
	$showArchived = isset($_GET['showArchived']) && $_GET['showArchived'] == 'true';

	if (is_dir($cardsDir)) {
		$files = scandir($cardsDir);

		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..' && is_file($cardsDir . '/' . $file)) {
				$filePath = $cardsDir . '/' . $file;
				$story = json_decode(file_get_contents($filePath), true);

				// Check if json_decode succeeded
				if (json_last_error() === JSON_ERROR_NONE) {
					$story['filename'] = $file;
					if (!isset($story['archived'])) {
						$story['archived'] = false;
					}

					// Check if column is in the columns array; if not, change it to the first column in the array
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

					if ($showArchived || !$story['archived']) {
						$stories[] = $story;
					}
				} else
				{
					//add a story with an error message about the broken file
					if ($showArchived || !$story['archived']) {
						$stories[] = [
							'column' => 'to-do',
							'order' => 0,
							'title' => 'Error',
							'text' => 'The file ' . $file . ' is broken and cannot be displayed.',
							'owner' => 'System',
							'backgroundColor' => '#ff0000',
							'textColor' => '#ffffff',
							'created' => date('Y-m-d H:i:s'),
							'lastUpdated' => date('Y-m-d H:i:s'),
							'filename' => $file,
							'comments' => [],
						];
					}
				}
			}
		}
	}

	echo json_encode($stories);
