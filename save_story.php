<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$title = $_POST['title'];
		$text = $_POST['text'];
		$owner = $_POST['owner'];
		$backgroundColor = $_POST['backgroundColor'];
		$textColor = $_POST['textColor'];
		$filename = $_POST['filename'];
		$created = $lastUpdated = date('Y-m-d H:i:s');
		$column = 'to-do';
		$order = $_POST['order'] ?? 0;
		$new_story = true;
		$archived = false;

		if (empty($filename)) {
			$filename = create_slug($title) . '_' . time() . '.json';
		} else {
			$new_story = false;
			$filepath = $cardsDir . '/' . $filename;
			if (file_exists($filepath)) {
				$existingStory = json_decode(file_get_contents($filepath), true);
				$created = $existingStory['created'];
				$column = $existingStory['column'];
				$comments = $existingStory['comments'] ?? [];
				$files = $existingStory['files'] ?? [];
				$history = $existingStory['history'] ?? [];
				$archived = $existingStory['archived'] ?? false;
			}
		}

		$story = [
			'column' => $column,
			'order' => $order,
			'title' => $title,
			'text' => $text,
			'owner' => $owner,
			'backgroundColor' => $backgroundColor,
			'textColor' => $textColor,
			'created' => $created,
			'lastUpdated' => $lastUpdated,
			'comments' => $comments ?? [],
			'files' => $files ?? [],
			'history' => $history ?? [],
			'archived' => $archived
		];

		if (!empty($_FILES['files']['name'][0])) {
			foreach ($_FILES['files']['name'] as $key => $file_name) {
				$file_tmp = $_FILES['files']['tmp_name'][$key];
				$file_dest = $cardsDir . '/uploads/' . $file_name;
				if (move_uploaded_file($file_tmp, $file_dest)) {
					$story['files'][] = [
						'filename' => $file_name,
						'owner' => $_SESSION['user']
					];
				}
			}
		}

		if ($new_story) {
			$story = log_history($story, 'Created story', $_SESSION['user']);
		} else {
			$story = log_history($story, 'Edited story', $_SESSION['user']);
		}

		file_put_contents($cardsDir . '/' . $filename, json_encode($story, JSON_PRETTY_PRINT));

		$story['filename'] = $filename;
		echo json_encode($story);
	}
