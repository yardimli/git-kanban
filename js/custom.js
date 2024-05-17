// Define color options
const colorOptions = [
	{background: '#F28B82', text: '#000000'},
	{background: '#FBBC04', text: '#000000'},
	{background: '#FFF475', text: '#000000'},
	{background: '#CCFF90', text: '#000000'},
	{background: '#A7FFEB', text: '#000000'},
	{background: '#CBF0F8', text: '#000000'},
	{background: '#AECBFA', text: '#000000'},
	{background: '#D7AEFB', text: '#000000'},
	{background: '#FDCFE8', text: '#000000'},
	{background: '#E6C9A8', text: '#000000'},
];

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

function loadStories() {
	$.get('load_stories.php', function (data) {
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
	return `<li data-filename="${story.filename}"><div class="kanban-card" data-filename="${story.filename}" style="background-color: ${story.backgroundColor}; color: ${story.textColor}">
        <h5>${story.title}</h5>
        <p>${story.text}</p>
        <button class="btn btn-sm btn-secondary" onclick="editStory('${story.filename}')">Edit</button>
        <p><strong>Owner:</strong> ${story.owner} <br><strong>Created:</strong> <span title="${moment.utc(story.created).local().format('LLLL')}">${createdTime}</span> <br><strong>Updated:</strong> <span title="${moment.utc(story.lastUpdated).local().format('LLLL')}">${updatedTime}</span></p>
    </div></li>`;
}

function saveStory() {
	const formData = {
		filename: $('#storyFilename').val(),
		title: $('#storyTitle').val(),
		text: $('#storyText').val(),
		owner: $('#storyOwner').val(),
		backgroundColor: $('#storyBackgroundColor').val(),
		textColor: $('#storyTextColor').val(),
	};
	$.post('save_story.php', formData, function (response) {
		$('#storyModal').modal('hide');
		$('#storyForm')[0].reset();
		const story = JSON.parse(response);
		const cardSelector = `.kanban-card[data-filename="${story.filename}"]`;
		const existingCard = $(cardSelector);
		
		if (existingCard.length) {
			existingCard.replaceWith(createCard(story));
		} else {
			$(`.kanban-column-ul[data-column="${story.column}"]`).append(createCard(story));
		}
	});
}


function editStory(filename) {
	fetch(`cards/${filename}`)
		.then(response => response.json())
		.then(story => {
			$('#storyFilename').val(filename);
			$('#storyTitle').val(story.title);
			$('#storyText').val(story.text);
			$('#storyOwner').val(story.owner);
			$('#storyBackgroundColor').val(story.backgroundColor);
			$('#storyTextColor').val(story.textColor);
			$('#storyModal').modal('show');
		})
		.catch(error => console.error('Error loading story:', error));
}

function updateStoryColumn(filename, newColumn, newOrder) {
	$.post('update_story_column.php', { filename: filename, column: newColumn, order: newOrder }, function (response) {
		const story = JSON.parse(response);
		console.log(story);
	});
}

$(document).ready(function () {
	loadStories();
	
	$('#storyForm').on('submit', function (e) {
		e.preventDefault();
		saveStory();
	});
	
	// Initialize Sortable for each kanban column
	$('.kanban-column-ul').each(function () {
		new Sortable(this, {
			group: 'kanban', // set the same group for all columns
			animation: 150,
			onEnd: function (evt) {
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
			$.post('generate_user.php', { username: userName, password: userPassword }, function (response) {
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
	
	
});
