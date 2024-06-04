<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation Index</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 20px;
        }
        h1 {
            color: #343a40;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            margin: 5px 0;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>API Documentation Index</h1>
    <ul id="file-list"></ul>

    <script>
        // Function to fetch JSON file names from the server
        async function fetchJsonFiles() {
            try {
                const response = await fetch('/api/api-doc/swagger-routes');
                const files = await response.json();

                const fileListElement = document.getElementById('file-list');
                files.forEach(file => {
                    const listItem = document.createElement('li');
                    const link = document.createElement('a');
                    link.href = `{{ route('view.swagger.routes', '') }}/${file}`;
                    link.textContent = file;
                    listItem.appendChild(link);
                    fileListElement.appendChild(listItem);
                });
            } catch (error) {
                console.error('Error fetching JSON files:', error);
            }
        }

        // Fetch the JSON files on page load
        window.onload = fetchJsonFiles;
    </script>
</body>
</html>
