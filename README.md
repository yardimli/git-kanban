## Git Kanban

Git Kanban is a simple kanban board that uses git as a backend. So you can use git to manage your tasks and each time you commit your changes, the board will be updated automatically. 

Each story is stored in a separate file in the `cards` directory. The file name is the story title slug with a timestamp. The story file contains the story title, description, status, and other information.

The story files are formatted as JSON. Here is an example story file:

```json
{
  "column": "parking-lot",
  "title": "Add edit history",
  "text": "In the JSON file add edit history.\nIn the story modal add a button to show history.",
  "owner": "Ekim",
  "backgroundColor": "#AECBFA",
  "textColor": "#000000",
  "created": "2024-05-14 16:49:31",
  "lastUpdated": "2024-05-14 16:49:34"
}
```

### Screenshots

![Simple View](https://github.com/yardimli/git-kan-ban/blob/main/images/git-kanban.jpg?raw=true)
