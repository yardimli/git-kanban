<?php
	$dir = __DIR__ . '/cards';
	$stories = [];
	if (is_dir($dir)) {
		$files = scandir($dir);
		foreach ($files as $file) {
			if ($file !== '.' && $file !== '..') {
				$filepath = $dir . '/' . $file;
				$story = json_decode(file_get_contents($filepath), true);
				$story['filename'] = $file;
				$stories[] = $story;
			}
		}
	}
	echo json_encode($stories);
