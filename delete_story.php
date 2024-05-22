<?php
	include_once 'settings.php';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$filename = $_POST['filename'];
		$filePath = $cardsDir . '/' . $filename;

		if (file_exists($filePath)) {
			// Load the story to get the list of attachments
			$story = json_decode(file_get_contents($filePath), true);

			// Delete attachments if they exist
			if (!empty($story['files'])) {
				foreach ($story['files'] as $file) {
					$attachmentPath = $cardsDir . '/uploads/' . $file['filename'];
					if (file_exists($attachmentPath)) {
						unlink($attachmentPath);
					}
				}
			}

			// Delete the story file itself
			unlink($filePath);
			echo json_encode(['success' => true]);
		} else {
			echo json_encode(['success' => false, 'message' => 'File not found']);
		}
	}
