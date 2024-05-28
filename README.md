## Git Kanban

Git Kanban is a simple kanban board that uses git as a backend. So you can use git to manage your tasks, and each time you commit your changes, the board will be updated automatically. Each story is stored in a separate file in the `cards` directory. The file name is the story title slug with a timestamp. The story file contains the story title, description, status, and other information. The story files are formatted as JSON.

### Features

- **Load Stories:** Stories are loaded from the backend and displayed in their respective columns. Stories can be filtered to show/hide archived stories.

- **Create Story Cards:** Create cards for each story with relevant details, including title, text, owner, created time, updated time, comments, and files.

- **Archive/Unarchive Stories:** Stories can be archived and unarchived. Archived stories are hidden from the main view unless explicitly shown.

- **Move Stories:** Stories can be moved to different columns using drag-and-drop. The order of stories within a column can also be updated.

- **Edit and Delete Comments:** Add, edit, and delete comments for each story. Comments are displayed with their creation time and the user who posted them.

- **Upload and Delete Files:** Users can upload files to a story. Uploaded files can be images or other types of files. Files can also be deleted.

- **Edit Story Details:** Edit the story's title, text, owner, background color, text color, and files. Changes are saved and the story card is updated.

- **History Tracking:** Track and display the history of actions performed on a story, such as creating, editing, moving, archiving, and unarchiving.

- **Theming:** Switch between light and dark themes.

- **User Management:** Generate new users with usernames and passwords. The generated users can then log in and manage stories.

- **Sortable Columns:** Columns are sortable, allowing easy organization of stories by drag-and-drop.

### Example Story File

Here is an example story file:

```json
{
  "column": "finished",
  "order": "3",
  "title": "Archive Story",
  "text": "Add button to archive a story, the story will not show up unless the user clicks on the show Archived stories. Archived stories should have a label that shows this. User can un-archive an archived story.",
  "owner": "Ekim",
  "backgroundColor": "#D7AEFB",
  "textColor": "#000000",
  "created": "2024-05-16 10:03:03",
  "lastUpdated": "2024-05-27 14:37:38",
  "comments": [],
  "files": [],
  "history": [
    {
      "action": "Moved card to finished",
      "user": "Ekim",
      "timestamp": "2024-05-27 14:37:38"
    }
  ]
}
```
### Screenshots

![Simple View](https://github.com/yardimli/git-kan-ban/blob/main/images/git-kanban.jpg?raw=true)

### Installation

1. Clone the repository to your web servers public directory.
   ```sh
   git clone https://github.com/yardimli/git-kanban.git
   cd git-kanban
   ```

2. Customize by editing the top part of action.php file. Change the `$users` array to include your own users and passwords. You can generate username and password pairs from the `Add user button`. Or you can use the default users. Set `$autoLoginUser` to not have to login.

3. Setup your project folder. Change the `$cardsDirName` variable to the name of your project.
    ```php
    $cardsDirName = 'my-project';
    $users = [...];
    $autoLoginUser = 'Admin'; 
    ```
   
4. define your own columns in the `columns` array. 
    ```php
    $columns = [
        'backlog' => 'Backlog',
        'to-do' => 'To Do',
        'in-progress' => 'In Progress',
        'finished' => 'Finished',
    ];
    $defaultColumn = 'to-do';
    ```
   
5. Define your own card colors. 
    ```php
	$colorOptions = [
		['background' => '#F28B82', 'text' => '#000000'],
		['background' => '#FBBC04', 'text' => '#000000'],
    ...
    ```

### Usage

1. Start your web server.
2. Open your browser and navigate to http://localhost/myproject/git-kanban
3. Log in using the username and password. Default credentials are:

   ```plaintext
   Username: Admin
   Password: 123456
   ```

4. Add, edit, archive, and move stories as required.

### Contributing

We welcome contributions! Please fork the repository and submit pull requests for any improvements or fixes.

### License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.
