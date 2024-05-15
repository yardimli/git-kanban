<?php
	session_start();
	include_once 'settings.php';

	if (empty($_SESSION['user'])) {
		header('Location: login.php');
		exit();
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Git Kanban Board</title>
	<!-- Bootstrap CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->
	<meta name="csrf-token" content="{{ csrf_token() }}">

	<script>
		var csrf_token = "{{ csrf_token() }}";
	</script>
</head>
<body>
<header>
	<!-- Bootstrap Navbar or custom header content here -->
</header>

<main class="py-4">

	<div class="container mt-5">
		<h1 class="text-center">Git Kanban Board</h1>
		<div>
			<div class="my-3 d-inline-block">
				Hello <?php echo $_SESSION['user']; ?>,
			</div>
			<div class="my-3 d-inline-block float-end">
				<a href="logout.php" class="btn btn-danger">Log Out</a>
				<button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#storyModal">Add Story</button>
			</div>
		</div>

		<div class="kanban-board" id="kanbanBoard">
			<div class="kanban-column">
				<h3>To-Do</h3>
				<ul class="kanban-column-ul" id="to-do-column" data-column="to-do">
				</ul>
			</div>
			<div class="kanban-column">
				<h3>In-Progress</h3>
				<ul class="kanban-column-ul" id="in-progress-column" data-column="in-progress">
				</ul>
			</div>
			<div class="kanban-column">
				<h3>Finished</h3>
				<ul class="kanban-column-ul" id="finished-column" data-column="finished">
				</ul>
			</div>
			<div class="kanban-column">
				<h3>Parking-Lot</h3>
				<ul class="kanban-column-ul" id="parking-lot-column" data-column="parking-lot">
				</ul>
			</div>
		</div>

		<!-- Button to add user -->
		<div class="text-end my-3">
			<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addUserModal">Add User</button>
		</div>

	</div>

	<!-- Modal for Adding/Editing Stories -->
	<div class="modal fade" id="storyModal" tabindex="-1" aria-labelledby="storyModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="storyModalLabel">Add Story</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<form id="storyForm">
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
							<input type="text" class="form-control" id="storyOwner" required>
						</div>
						<div class="mb-3">
							<label class="form-label">Background Color</label>
							<div id="colorPalette" class="d-flex flex-wrap">
								<!-- Color buttons will be inserted here dynamically -->
							</div>
						</div>
						<input type="hidden" id="storyBackgroundColor">
						<input type="hidden" id="storyTextColor">
						<button type="submit" class="btn btn-primary">Save Story</button>
					</form>
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
