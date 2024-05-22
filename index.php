<?php
	include_once 'settings.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Git Kanban Board</title>

	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="icon" href="images/favicon.ico" type="image/x-icon">
	<link rel="apple-touch-icon" sizes="152x152" href="images/apple-touch-icon-152x152.png">
	<link rel="apple-touch-icon" sizes="120x120" href="images/apple-touch-icon-120x120.png">
	<link rel="apple-touch-icon" sizes="76x76" href="images/apple-touch-icon-76x76.png">
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png">
	<link rel="icon" href="images/apple-touch-icon.png" type="image/x-icon">

	<!-- Bootstrap CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<link href="css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->

	<script>
		var colorOptions = <?php echo json_encode($colorOptions); ?>;
		var cardsDirName = "<?php echo $cardsDirName; ?>";
		const users = <?php echo json_encode(array_column($users, 'username')); ?>;
		var currentUser = "<?php echo $_SESSION['user']; ?>";
		var defaultColumn = "<?php echo $defaultColumn; ?>";

	</script>
</head>
<body>
<header>
	<div class="container-fluid mt-2">
		<button id="modeToggleBtn" class="btn btn-secondary float-end">
			<i id="modeIcon" class="bi bi-sun"></i>
		</button>
	</div>
</header>

<main class="py-4">

	<div class="container mt-2">
		<h1 style="margin:10px;" class="text-center"><img src="images/android-chrome-192x192.png" style="height: 64px;"> Git
			Kanban Board</h1>
		<div>
			<div class="my-3 d-inline-block">
				Hello <?php echo $_SESSION['user']; ?>,
			</div>
			<div class="my-3 d-inline-block float-end">
				<a href="logout.php" class="btn btn-danger">Log Out</a>
				<button class="btn btn-primary" id="addStoryBtn">Add Story</button>
			</div>
		</div>

		<div class="kanban-board" id="kanbanBoard">
			<?php foreach ($columns as $column): ?>
				<div class="kanban-column">
					<h3><?php echo htmlspecialchars($column['title']); ?></h3>
					<ul class="kanban-column-ul" id="<?php echo htmlspecialchars($column['id']); ?>-column" data-column="<?php echo htmlspecialchars($column['id']); ?>">
					</ul>
				</div>
			<?php endforeach; ?>
		</div>

		<!-- Button to add user -->
		<div class="text-end my-3">
			<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
		</div>

	</div>

	<!-- Modal for Adding/Editing Stories -->
	<div class="modal modal-lg fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="storyModalLabel">Add Story</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="storyForm" enctype="multipart/form-data">
						<input type="hidden" id="storyFilename">
						<div class="mb-3">
							<label for="storyTitle" class="form-label">Title</label>
							<input type="text" class="form-control" id="storyTitle" required>
						</div>
						<div class="mb-3">
							<label for="storyText" class="form-label">Text</label>
							<textarea class="form-control" id="storyText" rows="3" required></textarea>
						</div>
						<div class="mb-3">
							<label for="storyOwner" class="form-label">Owner</label>
							<select class="form-control" id="storyOwner" required>
								<!-- Options will be populated dynamically -->
							</select>
						</div>
						<div class="mb-3">
							<label class="form-label">Background Color</label>
							<div id="colorPalette" class="d-flex flex-wrap">
								<!-- Color buttons will be inserted here dynamically -->
							</div>
						</div>
						<input type="hidden" id="storyBackgroundColor">
						<input type="hidden" id="storyTextColor">
						<div class="mb-3">
							<label for="storyFiles" class="form-label">Upload Files</label>
							<input type="file" class="form-control" id="storyFiles" name="storyFiles[]" multiple>
						</div>
						<div id="save_result"></div>
						<button type="submit" class="btn btn-primary">Save Story</button>
						<button class="btn btn-secondary" id="showCommentModal">Add Comment</button>
						<button type="button" class="btn btn-danger" id="deleteStoryBtn">Delete Story</button>
					</form>
					<div class="comments-section">
						<hr>
						<h5>Comments</h5>
						<div id="commentsList"></div>
					</div>
					<div class="files-section">
						<hr>
						<h5>Files</h5>
						<div id="filesList" class="row"></div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<!-- Modal for Adding User -->
	<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="addUserModalLabel">Add User</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="addUserForm">
						<div class="mb-3">
							<label for="userName" class="form-label">Username</label>
							<input type="text" class="form-control" id="userName" required>
						</div>
						<div class="mb-3">
							<label for="userPassword" class="form-label">Password</label>
							<input type="password" class="form-control" id="userPassword" required>
						</div>
						<button type="button" class="btn btn-primary" id="generateUser">Generate</button>
					</form>
					<div class="mt-3">
						<pre id="userJsonOutput"></pre>
						<button type="button" class="btn btn-secondary" id="copyUserJson">Copy</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal for Adding Comment -->
	<div class="modal fade" id="commentModal" tabindex="-1" aria-labelledby="commentModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="commentModalLabel">Add Comment</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="commentForm">
						<input type="hidden" id="commentStoryFilename">
						<input type="hidden" id="commentId">
						<div class="mb-3">
							<label for="commentText" class="form-label">Comment</label>
							<textarea class="form-control" id="commentText" rows="3" required></textarea>
						</div>
						<button type="submit" class="btn btn-primary">Save Comment</button>
					</form>
				</div>
			</div>
		</div>
	</div>

	<!-- Confirmation Modal for Deleting Story -->
	<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="deleteConfirmationModalLabel">Confirm Deletion</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete this story? This action cannot be undone.
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
				</div>
			</div>
		</div>
	</div>

</main>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/moment.min.js"></script>
<script src="js/sortable.min.js"></script>

<!-- Your custom scripts -->
<script src="js/custom.js"></script> <!-- If you have custom JS -->
</body>
</html>
