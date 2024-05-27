<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$storyFilename = $_POST['storyFilename'];
		$delete_filename = $_POST['filename'];
		$delete_filePath = $cardsDir . '/uploads/' . $delete_filename;
		$user = $_SESSION['user'];

		if (file_exists($delete_filePath)) {
			unlink($delete_filePath); // Delete the file
		}

		$storyfile_path = $cardsDir . '/' . $storyFilename;
		if (file_exists($storyfile_path)) {
			$story = json_decode(file_get_contents($storyfile_path), true);
			$story['files'] = array_values(array_filter($story['files'], function ($file) use ($delete_filename) {
				return $file['filename'] !== $delete_filename;
			}));
			$story = log_history($story, 'Deleted file '.$delete_filename, $user);
			file_put_contents($storyfile_path, json_encode($story, JSON_PRETTY_PRINT));
		}

		echo json_encode(['success' => true]);
	}
