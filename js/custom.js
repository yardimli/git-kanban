function loadStories(showArchived = false) {
	//empty the columns
	$('.kanban-column-ul').empty();
	
	$.get('load_stories.php', { showArchived: showArchived }, function (data) {
		const stories = JSON.parse(data);
		
		// Group stories by column
		const groupedStories = stories.reduce((acc, story) => {
			if (!acc[story.column]) {
				acc[story.column] = [];
			}
			acc[story.column].push(story);
			return acc;
		}, {});
		
		// Iterate through each column
		Object.keys(groupedStories).forEach(column => {
			// Sort stories by order within each column
			groupedStories[column].sort((a, b) => a.order - b.order);
			
			// Append sorted stories to the respective column
			groupedStories[column].forEach(story => {
				const card = createCard(story);
				$(`.kanban-column-ul[data-column="${column}"]`).append(card);
			});
		});
	});
}

function formatRelativeTime(dateTime) {
	return moment.utc(dateTime).local().fromNow();
}

function createCard(story) {
	const createdTime = formatRelativeTime(story.created);
	const updatedTime = formatRelativeTime(story.lastUpdated);
	const numComments = story.comments ? story.comments.length : 0;
	const numFiles = story.files ? story.files.length : 0;
	
	let numCommentsText = '';
	if (numComments > 0) {
		numCommentsText = numComments === 1 ? '1 Comment <br>' : `${numComments} Comments <br>`;
	}
	
	let numFilesText = '';
	if (numFiles > 0) {
		numFilesText = numFiles === 1 ? '1 File <br>' : `${numFiles} Files <br>`;
	}
	
	let truncatedText;
	if (story.text.length > 128) {
		const words = story.text.split(' ');
		let charCount = 0;
		truncatedText = '';
		for (const word of words) {
			if ((charCount + word.length + 1) > 128) {
				truncatedText += '...';
				break;
			}
			truncatedText += (truncatedText.length ? ' ' : '') + word;
			charCount += word.length + 1;
		}
	} else {
		truncatedText = story.text;
	}
	
	const archiveButton = story.archived ?
		`<button class="btn btn-sm btn-warning archive-btn" onclick="unarchiveStory(event, '${story.filename}')">Unarchive</button>` :
		`<button class="btn btn-sm btn-secondary archive-btn" onclick="archiveStory(event, '${story.filename}')">Archive</button>`;
	
	const archivedLabel = story.archived ? '<span class="badge bg-secondary">Archived</span> ' : '';
	
	
	return `<li data-filename="${story.filename}" onclick="editStory('${story.filename}')" style="cursor:pointer;"><div class="kanban-card" data-filename="${story.filename}" style="background-color: ${story.backgroundColor}; color: ${story.textColor}">
				<button class="btn btn-sm btn-info move-to-top-btn" onclick="moveToTop(event, '${story.filename}')">Top</button>
			  ${archiveButton}
				${archivedLabel}
        <h5>${story.title}</h5>
        <p>${truncatedText}</p>
        <p><strong>Owner:</strong> ${story.owner} <br>${numCommentsText}${numFilesText}<strong>Created:</strong> <span title="${moment.utc(story.created).local().format('LLLL')}">${createdTime}</span> <br><strong>Updated:</strong> <span title="${moment.utc(story.lastUpdated).local().format('LLLL')}">${updatedTime}</span></p>
    </div></li>`;
}

function createCommentHtml(comment) {
	const commentTime = formatRelativeTime(comment.timestamp);
	const editDeleteButtons = comment.user === currentUser ? `
        <button class="btn btn-sm btn-warning" onclick="editComment(event, '${comment.id}', '${comment.storyFilename}')">Edit</button>
        <button class="btn btn-sm btn-danger" onclick="deleteComment(event, '${comment.id}', '${comment.storyFilename}')">Delete</button>
    ` : '';
	return `
        <div class="comment mb-2" data-id="${comment.id}">
            <p>${comment.text}</p>
            <p><strong>${comment.user}</strong> <span title="${moment.utc(comment.timestamp).local().format('LLLL')}">${commentTime}</span></p>
            ${editDeleteButtons}
        </div>`;
}

function showCommentModal(event, storyFilename) {
	event.stopPropagation();
	$('#commentStoryFilename').val(storyFilename);
	$('#commentId').val('');
	$('#commentText').val('');
	$('#commentModalLabel').text('Add Comment');
	$('#commentModal').modal('show');
}

function editComment(event, commentId, storyFilename) {
	event.stopPropagation();
	const comment = $(`.comment[data-id="${commentId}"]`);
	const commentText = comment.find('p:first').text();
	$('#commentStoryFilename').val(storyFilename);
	$('#commentId').val(commentId);
	$('#commentText').val(commentText);
	$('#commentModalLabel').text('Edit Comment');
	$('#commentModal').modal('show');
}

function deleteComment(event, commentId, storyFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this comment?')) {
		$.post('delete_comment.php', {id: commentId, storyFilename: storyFilename}, function (response) {
			if (response.success) {
				$(`.comment[data-id="${commentId}"]`).remove();
			}
		}, 'json');
	}
}

function saveComment() {
	const commentData = {
		storyFilename: $('#commentStoryFilename').val(),
		id: $('#commentId').val(),
		text: $('#commentText').val(),
	};
	$.post('save_comment.php', commentData, function (response) {
		$('#commentModal').modal('hide');
		$('#commentForm')[0].reset();
		const comment = JSON.parse(response);
		comment.storyFilename = commentData.storyFilename; // Add storyFilename to the comment
		const commentsList = $('#commentsList');
		$('.comments-section').show();
		if (comment.isNew) {
			commentsList.append(createCommentHtml(comment));
		} else {
			const commentElement = commentsList.find(`.comment[data-id="${comment.id}"]`);
			commentElement.replaceWith(createCommentHtml(comment));
		}
	});
}

function saveStory() {
	const formData = new FormData(document.getElementById('storyForm'));
	formData.append('filename', $('#storyFilename').val());
	formData.append('title', $('#storyTitle').val());
	formData.append('text', $('#storyText').val());
	formData.append('owner', $('#storyOwner').val());
	formData.append('backgroundColor', $('#storyBackgroundColor').val());
	formData.append('textColor', $('#storyTextColor').val());
	
	let files = $('#storyFiles')[0].files;
	for (let i = 0; i < files.length; i++) {
		formData.append('files[]', files[i]);
	}
	
	$.ajax({
		url: 'save_story.php',
		type: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function (response) {
			$('#save_result').html('<div class="alert alert-success">Story saved successfully!</div>');
			const story = JSON.parse(response);
			const cardSelector = `.kanban-card[data-filename="${story.filename}"]`;
			const existingCard = $(cardSelector);
			if (existingCard.length) {
				existingCard.off('click'); // Unbind the click event
				existingCard.replaceWith(createCard(story));
			} else {
				console.log('New story:', story);
				const insertColumn = $('.kanban-column-ul[data-column="' + defaultColumn + '"]');
				insertColumn.prepend(createCard(story));
				
				insertColumn.children().each(function (index) {
					const filename = $(this).data('filename');
					updateStoryColumn(filename, defaultColumn, index);
				});
				//scroll to top of document
				setTimeout(function () {
					$("#storyModal").modal('hide');
				}, 400);
				
				$('html, body').animate({scrollTop: 0}, 200);
			}
			updateFilesList(story);
		}
	});
}

function deleteFile(event, filename, storyFilename) {
	event.stopPropagation();
	if (confirm('Are you sure you want to delete this file?')) {
		$.post('delete_file.php', {filename: filename, storyFilename: storyFilename}, function (response) {
			if (response.success) {
				$(`.file[data-filename="${filename}"]`).remove();
			}
		}, 'json');
	}
}

function editStory(filename) {
	fetch(`${cardsDirName}/${filename}`)
		.then(response => response.json())
		.then(story => {
			$('#save_result').html('');
			$('#storyFilename').val(filename);
			$('#storyTitle').val(story.title);
			$('#storyText').val(story.text);
			$('#storyOwner').val(story.owner);
			$('#storyBackgroundColor').val(story.backgroundColor);
			$('#storyTextColor').val(story.textColor);
			$('#storyFiles').val('');
			
			$('#showCommentModal').show();
			
			const commentsList = $('#commentsList');
			commentsList.empty(); // Clear existing comments
			$('.comments-section').hide();
			if (story.comments) {
				story.comments.forEach(comment => {
					$('.comments-section').show();
					comment.storyFilename = filename; // Add storyFilename to each comment
					commentsList.append(createCommentHtml(comment));
				});
			}
			updateFilesList(story, filename);
			
			$('#storyModal').modal('show');
		})
		.catch(error => console.error('Error loading story:', error));
}

function addStory() {
	$('#save_result').html('');
	$('#storyFilename').val('');
	$('#storyTitle').val('');
	$('#storyText').val('');
	$('#storyOwner').val('');
	$('#storyFiles').val('');
	
	const commentsList = $('#commentsList');
	commentsList.empty(); // Clear existing comments
	$('.comments-section').hide();
	
	const filesList = $('#filesList');
	filesList.empty(); // Clear existing files
	$('.files-section').hide();
	
	$('#colorPalette button').removeClass('active').first().click(); // Reset the color selection to default
	
	//hide the add comment button
	$('#showCommentModal').hide();
	
	$('#storyModal').modal('show');
}

function updateStoryColumn(filename, newColumn, newOrder) {
	$.post('update_story_column.php', {filename: filename, column: newColumn, order: newOrder}, function (response) {
		const story = JSON.parse(response);
		console.log(story);
	});
}

function createFileHtml(file) {
	const isImage = /\.(jpg|jpeg|png|gif)$/i.test(file.filename);
	const deleteButton = file.owner === currentUser ? `<button class="btn btn-sm btn-danger" onclick="deleteFile(event, '${file.filename}', '${file.storyFilename}')">Delete</button>` : '';
	const fileLink = `${cardsDirName}/uploads/${file.filename}`;
	
	let fileHtml = `<div class="file mb-2 col-4" style="border: 1px solid #ccc; padding: 5px;" data-filename="${file.filename}">`;
	
	if (isImage) {
		fileHtml += `<a href="${fileLink}" target="_blank"><img src="${fileLink}" alt="${file.filename}" style="max-width: 100px; max-height: 100px; margin-right: 10px;"></a>`;
	}
	
	fileHtml += `<a href="${fileLink}" target="_blank">${file.filename}</a> ${deleteButton}</div>`;
	
	return fileHtml;
}

function updateFilesList(story, storyFilename) {
	const filesList = $('#filesList');
	filesList.empty(); // Clear existing files
	$(".files-section").hide();
	
	if (story.files) {
		story.files.forEach(file => {
			$(".files-section").show();
			file.storyFilename = storyFilename;
			filesList.append(createFileHtml(file));
		});
	}
}

function autoScroll() {
	if (!isDragging) return;
	
	clearTimeout(scrollTimeout);
	
	const scrollSensitivity = 60; // Distance from the edge of the viewport to start scrolling
	const scrollSpeed = 200; // Speed at which the page scrolls
	const viewportHeight = window.innerHeight;
	if (lastMouseY < scrollSensitivity) {
		// Scroll up
		window.scrollBy(0, -scrollSpeed);
		scrollTimeout = setTimeout(() => {
			autoScroll();
		}, 100);
	} else if (lastMouseY > viewportHeight - scrollSensitivity) {
		// Scroll down
		window.scrollBy(0, scrollSpeed);
		scrollTimeout = setTimeout(() => {
			autoScroll();
		}, 100);
	}
}

function moveToTop(event, filename) {
	event.stopPropagation(); // Prevent triggering the editStory function
	
	const card = $(`.kanban-card[data-filename="${filename}"]`).closest('li');
	const column = card.closest('.kanban-column-ul');
	
	// Move card to the top of the column
	card.prependTo(column);
	
	// Update the order of all items in the column
	column.children().each(function (index) {
		const filename = $(this).data('filename');
		updateStoryColumn(filename, column.data('column'), index);
	});
}

function applyTheme(theme) {
	if (theme === 'dark') {
		$('body').addClass('dark-mode');
		$('#modeIcon').removeClass('bi-sun').addClass('bi-moon');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Light Mode');
	} else {
		$('body').removeClass('dark-mode');
		$('#modeIcon').removeClass('bi-moon').addClass('bi-sun');
		$('#modeToggleBtn').attr('aria-label', 'Switch to Dark Mode');
	}
}

function deleteStory() {
	const filename = $('#storyFilename').val();
	$.post('delete_story.php', {filename: filename}, function (response) {
		if (response.success) {
			$(`.kanban-card[data-filename="${filename}"]`).closest('li').remove();
			$('#storyModal').modal('hide');
			$('#deleteConfirmationModal').modal('hide');
			$('#save_result').html('<div class="alert alert-success">Story deleted successfully!</div>');
		} else {
			$('#save_result').html('<div class="alert alert-danger">Failed to delete the story: ' + response.message + '</div>');
		}
	}, 'json');
}

function createHistoryHtml(history) {
	const historyTime = formatRelativeTime(history.timestamp);
	return `
        <div class="history-entry mb-1">
            ${moment.utc(history.timestamp).local().format('LLLL')} <strong>${history.user}</strong> ${history.action}
        </div>`;
}

function showHistoryModal(event, storyFilename) {
	event.stopPropagation();
	fetch(`${cardsDirName}/${storyFilename}`)
		.then(response => response.json())
		.then(story => {
			const historyList = $('#historyList');
			historyList.empty(); // Clear existing history
			if (story.history) {
				story.history.forEach(entry => {
					historyList.append(createHistoryHtml(entry));
				});
			}
			$('#historyModal').modal('show');
		})
		.catch(error => console.error('Error loading history:', error));
}

function createAllHistoryHtml(history) {
	const historyTime = formatRelativeTime(history.timestamp);
	return `
        <div class="history-entry mb-1">
            ${moment.utc(history.timestamp).local().format('LLLL')} <strong>${history.title}</strong> <strong>${history.user}</strong> ${history.action}
        </div>`;
}

function showAllHistoryModal() {
	$.get('fetch_all_history.php', function (data) {
		const histories = JSON.parse(data);
		const allHistoryList = $('#allHistoryList');
		allHistoryList.empty(); // Clear existing history
		histories.forEach(entry => {
			allHistoryList.append(createAllHistoryHtml(entry));
		});
		$('#allHistoryModal').modal('show');
	});
}

function archiveStory(event, filename) {
	event.stopPropagation();
	$.post('archive_story.php', { filename: filename, archived: true }, function (response) {
		if (response.success) {
			$(`.kanban-card[data-filename="${filename}"]`).closest('li').remove();
		}
	}, 'json');
}

function unarchiveStory(event, filename) {
	event.stopPropagation();
	$.post('archive_story.php', { filename: filename, archived: false }, function (response) {
		if (response.success) {
			loadStories(true);
			// $(`.kanban-card[data-filename="${filename}"]`).closest('li').remove();
		}
	}, 'json');
}


//----------------------------------------------------
//----------------- Global Variables -----------------

let isDragging = false;
let lastMouseY = 0;
let scrollTimeout = null;
let savedTheme = localStorage.getItem('theme') || 'light';


//----------------------------------------------------
//----------------- Event Listeners ------------------

$(document).ready(function () {
	// Populate the owner dropdown with existing users
	const storyOwnerSelect = $('#storyOwner');
	users.forEach(user => {
		storyOwnerSelect.append(new Option(user, user));
	});
	
	applyTheme(savedTheme);
	
	$('#modeToggleBtn').on('click', function () {
		const currentTheme = $('body').hasClass('dark-mode') ? 'dark' : 'light';
		const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
		localStorage.setItem('theme', newTheme);
		applyTheme(newTheme);
	});

// Create color buttons
	const colorPalette = $('#colorPalette');
	colorOptions.forEach(option => {
		const button = $(`<button type="button" class="btn m-1" style="background-color: ${option.background}; color: ${option.text};">${option.text}</button>`);
		button.on('click', function () {
			$('#storyBackgroundColor').val(option.background);
			$('#storyTextColor').val(option.text);
			$('#colorPalette button').removeClass('active');
			$(this).addClass('active');
		});
		colorPalette.append(button);
	});
//set default color
	$('#colorPalette button').first().click();
	
	loadStories();
	
	$('#toggleArchivedBtn').on('click', function (e) {
		e.preventDefault();
		const isShowingArchived = $(this).html() === '<i class="bi bi-archive"></i>';
		$(this).html(isShowingArchived ? '<i class="bi bi-archive-fill"></i>' : '<i class="bi bi-archive"></i>');
		loadStories(isShowingArchived);
	});
	
	
	$('#showAllHistoryBtn').on('click', function (e) {
		e.preventDefault();
		showAllHistoryModal();
	});

	$('#showHistoryModal').on('click', function (e) {
		e.preventDefault();
		showHistoryModal(e, $('#storyFilename').val());
	});
	
	$('#deleteStoryBtn').on('click', function (e) {
		e.preventDefault();
		$('#deleteConfirmationModal').modal('show');
	});
	
	// Attach click event to confirm delete button in the confirmation modal
	$('#confirmDeleteBtn').on('click', function (e) {
		e.preventDefault();
		deleteStory();
	});
	
	$('#storyForm').on('submit', function (e) {
		e.preventDefault();
		saveStory();
	});
	
	$("#showCommentModal").on('click', function (e) {
		e.preventDefault();
		showCommentModal(e, $('#storyFilename').val());
	});
	
	$('#commentForm').on('submit', function (e) {
		e.preventDefault();
		saveComment();
	});
	
	$('#addStoryBtn').on('click', function (e) {
		e.preventDefault();
		addStory();
	});
	
	$('#storyModal').on('shown.bs.modal', function () {
		$('#storyTitle').focus();
	});
	
	$('#commentModal').on('shown.bs.modal', function () {
		$('#commentText').focus();
	});
	
	
	// Initialize Sortable for each kanban column
	$('.kanban-column-ul').each(function () {
		new Sortable(this, {
			group: 'kanban', // set the same group for all columns
			animation: 150,
			scroll: false,
			onStart: function () {
				isDragging = true;
				console.log('Dragging started');
			},
			onEnd: function (evt) {
				isDragging = false;
				console.log('Dragging ended');
				
				const item = evt.item;
				const newColumn = $(item).closest('.kanban-column-ul').data('column');
				const filename = $(item).data('filename');
				const newOrder = $(item).index(); // Get the new index/order
				
				// Update the order of all items in the column
				$(item).closest('.kanban-column-ul').children().each(function (index) {
					const filename = $(this).data('filename');
					updateStoryColumn(filename, newColumn, index);
				});
			}
		});
	});
	
	// Add User Modal
	$('#generateUser').on('click', function () {
		const userName = $('#userName').val().replace(/\s+/g, '').replace(/[^\w\-]/g, '');
		const userPassword = $('#userPassword').val();
		
		if (userName && userPassword) {
			$.post('generate_user.php', {username: userName, password: userPassword}, function (response) {
				$('#userJsonOutput').text(response);
			});
		}
	});
	
	// Copy to clipboard
	$('#copyUserJson').on('click', function () {
		const textToCopy = $('#userJsonOutput').text();
		navigator.clipboard.writeText(textToCopy).then(function () {
			alert('Copied to clipboard!');
		}, function (err) {
			console.error('Could not copy text: ', err);
		});
	});
	
	// Attach mousemove event to track mouse position
	document.addEventListener('drag', function (event) {
		if (lastMouseY === event.clientY) return;
		lastMouseY = event.clientY;
		
		autoScroll();
	});
	
	
	// Manage z-index for multiple modals
	$('.modal').on('show.bs.modal', function () {
		const zIndex = 1040 + (10 * $('.modal:visible').length);
		$(this).css('z-index', zIndex);
		setTimeout(function () {
			$('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
		}, 0);
	});
	
	$('.modal').on('hidden.bs.modal', function () {
		if ($('.modal:visible').length) {
			// Adjust the backdrop z-index when closing a modal
			$('body').addClass('modal-open');
		}
	});
	
});
