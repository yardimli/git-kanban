<?php
	session_start();

	$cardsDirName = 'git-kanban-project';

	//json string for users, Admin password is 123456
	$users =
		[
			['username' => 'Admin', 'password' => '$2y$10$kMdhKRcawdXC9JhayVRhS.mZ/T5Va7K1wfck7FcM6uff1BGfd1qym'],
			['username' => 'Ekim', 'password' => '$2y$10$DIbIGXf43w/583AeGtCtMuiGFJZvNn6CNqatLrYYqOzzDdgeu62Kq'],
		];

	$autoLoginUser = ''; //leave this empty if you want to allow all users to login

	$colorOptions = [
		['background' => '#F28B82', 'text' => '#000000'],
		['background' => '#FBBC04', 'text' => '#000000'],
		['background' => '#FFF475', 'text' => '#000000'],
		['background' => '#CCFF90', 'text' => '#000000'],
		['background' => '#A7FFEB', 'text' => '#000000'],
		['background' => '#CBF0F8', 'text' => '#000000'],
		['background' => '#AECBFA', 'text' => '#000000'],
		['background' => '#D7AEFB', 'text' => '#000000'],
		['background' => '#FDCFE8', 'text' => '#000000'],
		['background' => '#E6C9A8', 'text' => '#000000'],
		['background' => '#E8EAED', 'text' => '#000000'],
		['background' => '#FFFFFF', 'text' => '#000000']
	];

// Define default columns
	$columns = [
		['id' => 'parking-lot', 'title' => 'Parking-Lot'],
		['id' => 'to-do', 'title' => 'To-Do'],
		['id' => 'in-progress', 'title' => 'In-Progress'],
		['id' => 'finished', 'title' => 'Finished'],
	];

	$defaultColumn = 'to-do';

	//------------------DO NOT MODIFY BELOW THIS LINE------------------
	$cardsDir = __DIR__ . '/' . $cardsDirName;

	if (!file_exists($cardsDir)) {
		mkdir($cardsDir, 0777, true);
	}

	if (!file_exists($cardsDir . '/uploads')) {
		mkdir($cardsDir . '/uploads', 0777, true);
	}


	if ($autoLoginUser !== '') {
		$_SESSION['user'] = $autoLoginUser;
		header('Location: index.html');
		exit();
	}

	if (empty($_SESSION['user'])) {
		$post_action = $_POST['action'] ?? '';
		if ($post_action !== 'login') {
			header('Location: login.html');
			exit();
		}
	}

	function log_history($story, $action, $user)
	{
		$story['history'][] = [
			'action' => $action,
			'user' => $user,
			'timestamp' => date('Y-m-d H:i:s')
		];
		return $story;
	}

	function create_slug($string)
	{
		$slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
		return $slug;
	}

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		$action = $_POST['action'] ?? '';

		$storyFilename = $_POST['storyFilename'] ?? null;
		$storyFilePath = $cardsDir . '/' . $storyFilename;
		$user = $_SESSION['user'];
		$id = $_POST['id'] ?? null;
		$uploadFilename = $_POST['uploadFilename'] ?? null;

		switch ($action) {

			case 'fetch_initial_data':
				echo json_encode([
					'colorOptions' => $colorOptions,
					'cardsDirName' => $cardsDirName,
					'users' => array_column($users, 'username'),
					'currentUser' => $_SESSION['user'],
					'defaultColumn' => $defaultColumn,
					'columns' => $columns,
				]);
				break;

			//-----------------------------//
			case 'archive_story':
				$archived = filter_var($_POST['archived'], FILTER_VALIDATE_BOOLEAN);

				if (file_exists($storyFilePath)) {
					$story = json_decode(file_get_contents($storyFilePath), true);
					$story['archived'] = $archived;

					if ($archived) {
						$story = log_history($story, 'Archived the story', $user);
					} else {
						$story = log_history($story, 'Unarchived the story', $user);
					}

					file_put_contents($storyFilePath, json_encode($story, JSON_PRETTY_PRINT));
					echo json_encode(['success' => true]);
				} else {
					echo json_encode(['success' => false, 'message' => 'Story not found']);
				}
				break;

			//-----------------------------//
			case 'delete_comment':

				if (file_exists($storyFilePath)) {
					$story = json_decode(file_get_contents($storyFilePath), true);
					if (isset($story['comments'])) {
						$story['comments'] = array_values(array_filter($story['comments'], function ($comment) use ($id) {
							return $comment['id'] !== $id;
						}));
						$story = log_history($story, 'Deleted comment', $user);

						file_put_contents($storyFilePath, json_encode($story, JSON_PRETTY_PRINT));
						echo json_encode(['success' => true]);
					}
				}
				break;

			//-----------------------------//
			case 'delete_file':
				$delete_filename = $_POST['uploadFilename'];
				$delete_filePath = $cardsDir . '/uploads/' . $delete_filename;

				if (file_exists($delete_filePath)) {
					unlink($delete_filePath); // Delete the file
				}

				$storyfile_path = $cardsDir . '/' . $storyFilename;
				if (file_exists($storyfile_path)) {
					$story = json_decode(file_get_contents($storyfile_path), true);
					$story['files'] = array_values(array_filter($story['files'], function ($file) use ($delete_filename) {
						return $file['uploadFilename'] !== $delete_filename;
					}));
					$story = log_history($story, 'Deleted file ' . $delete_filename, $user);
					file_put_contents($storyfile_path, json_encode($story, JSON_PRETTY_PRINT));
				}

				echo json_encode(['success' => true]);
				break;

			//-----------------------------//
			case 'delete_story':

				if (file_exists($storyFilePath)) {
					// Load the story to get the list of attachments
					$story = json_decode(file_get_contents($storyFilePath), true);

					// Delete attachments if they exist
					if (!empty($story['files'])) {
						foreach ($story['files'] as $file) {
							$attachmentPath = $cardsDir . '/uploads/' . $file['uploadFilename'];
							if (file_exists($attachmentPath)) {
								unlink($attachmentPath);
							}
						}
					}

					// Delete the story file itself
					unlink($storyFilePath);
					echo json_encode(['success' => true]);
				} else {
					echo json_encode(['success' => false, 'message' => 'File not found']);
				}

				break;

			//-----------------------------//
			case 'fetch_all_history':
				$allHistories = [];

				foreach (glob($cardsDir . '/*.json') as $storyFilePaths) {
					$story = json_decode(file_get_contents($storyFilePaths), true);
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
				break;

			//-----------------------------//
			case 'generate_user':
				$username = preg_replace('/\s+/', '', $_POST['username']);
				$username = preg_replace('/[^\w\-]/', '', $username);
				$password = $_POST['password'];

				$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

				$response = [
					'username' => $username,
					'password' => $hashedPassword
				];

				echo "['username' => '" . $username . "', 'password' => '" . $hashedPassword . "'],";
				break;

			//-----------------------------//
			case 'load_stories':
				$stories = [];
				$showArchived = isset($_GET['showArchived']) && $_GET['showArchived'] == 'true';

				if (is_dir($cardsDir)) {
					$files = scandir($cardsDir);

					foreach ($files as $storyFile) {
						if ($storyFile !== '.' && $storyFile !== '..' && is_file($cardsDir . '/' . $storyFile)) {
							$storyFilePaths = $cardsDir . '/' . $storyFile;
							$story = json_decode(file_get_contents($storyFilePaths), true);

							// Check if json_decode succeeded
							if (json_last_error() === JSON_ERROR_NONE) {
								$story['storyFilename'] = $storyFile;
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
							} else {
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
										'storyFilename' => $file,
										'comments' => [],
									];
								}
							}
						}
					}
				}

				echo json_encode($stories);
				break;

			//-----------------------------//
			case 'save_comment':
				$text = $_POST['text'];
				$timestamp = date('Y-m-d H:i:s');

				if (file_exists($storyFilePath)) {
					$story = json_decode(file_get_contents($storyFilePath), true);
					if (!isset($story['comments'])) {
						$story['comments'] = [];
					}

					if (empty($id)) {
						$id = uniqid();
						$story['comments'][] = [
							'id' => $id,
							'text' => $text,
							'user' => $user,
							'timestamp' => $timestamp,
						];
						$story = log_history($story, 'Added comment', $user);
						$isNew = true;
					} else {
						foreach ($story['comments'] as &$comment) {
							if ($comment['id'] === $id) {
								$comment['text'] = $text;
								$comment['timestamp'] = $timestamp;
								$story = log_history($story, 'Edited comment', $user);
								break;
							}
						}
						$isNew = false;
					}

					file_put_contents($storyFilePath, json_encode($story, JSON_PRETTY_PRINT));
					echo json_encode([
						'id' => $id,
						'text' => $text,
						'user' => $user,
						'timestamp' => $timestamp,
						'storyFilename' => $storyFilename,
						'isNew' => $isNew,
					]);
				}
				break;

			//-----------------------------//
			case 'save_story':
				$title = $_POST['title'];
				$text = $_POST['text'];
				$owner = $_POST['owner'];
				$backgroundColor = $_POST['backgroundColor'];
				$textColor = $_POST['textColor'];
				$created = $lastUpdated = date('Y-m-d H:i:s');
				$column = 'to-do';
				$order = $_POST['order'] ?? 0;
				$new_story = true;
				$archived = false;

				if (empty($storyFilename)) {
					$storyFilename = create_slug($title) . '_' . time() . '.json';
				} else {
					$new_story = false;
					if (file_exists($storyFilePath)) {
						$existingStory = json_decode(file_get_contents($storyFilePath), true);
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
					foreach ($_FILES['files']['name'] as $key => $uploadFilename) {
						$file_tmp = $_FILES['files']['tmp_name'][$key];
						$file_dest = $cardsDir . '/uploads/' . $uploadFilename;
						if (move_uploaded_file($file_tmp, $file_dest)) {
							$story['files'][] = [
								'uploadFilename' => $uploadFilename,
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

				file_put_contents($cardsDir . '/' . $storyFilename, json_encode($story, JSON_PRETTY_PRINT));

				$story['storyFilename'] = $storyFilename;
				echo json_encode($story);
				break;

			//-----------------------------//
			case 'update_story_column':
				$column = $_POST['column'];
				$order = $_POST['order'];

				if (file_exists($storyFilePath)) {
					$story = json_decode(file_get_contents($storyFilePath), true);
					$dontUpdateTime = false;
					if ($story['column'] === $column) {
						$dontUpdateTime = true;
					}
					$story['column'] = $column;
					$story['order'] = $order;
					if (!$dontUpdateTime) {
						$story['lastUpdated'] = date('Y-m-d H:i:s');
						$story = log_history($story, 'Moved card to ' . $column, $user);
					}
					file_put_contents($storyFilePath, json_encode($story, JSON_PRETTY_PRINT));
					echo json_encode($story);
				}
				break;


			//-----------------------------//
			case 'login':
				$username = $_POST['username'] ?? '';
				$password = $_POST['password'] ?? '';

				$userFound = false;

				foreach ($users as $user) {
					if ($user['username'] === $username && password_verify($password, $user['password'])) {
						$_SESSION['user'] = $username;
						$userFound = true;
						header('Location: index.html');
						exit();
					}
				}

				if (!$userFound) {
					$error = "Invalid username or password";
					header("Location: login.html?error=" . urlencode($error));
					exit();
				}
				break;

			//-----------------------------//
			case 'logout':
				session_destroy();
				header('Location: login.html');
				exit();
				break;
		}
	}
