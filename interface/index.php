<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Responsive UI with Swipe Navigation</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <!-- Navigation Buttons (Optional) -->
    <div class="nav-buttons">
        <button class="nav-btn" data-index="0">Page 1</button>
        <button class="nav-btn" data-index="1">Page 2</button>
        <button class="nav-btn" data-index="2">Page 3</button>
    <!-- Add more buttons for more pages -->
    </div>

    <!-- Page Content (Columns) -->
    <div class="page-container">
        <div class="page" id="page-0">
            <h1>Page 1 Content</h1>
            <p>This is the content of the first page.</p>
        </div>
        <div class="page" id="page-1">
            <h1>Page 2 Content</h1>
            <p>This is the content of the second page.</p>
        </div>
        <div class="page" id="page-2">
            <h1>Page 3 Content</h1>
            <p>This is the content of the third page.</p>
        </div>
    <!-- Add more content like above here for more pages -->
    </div>

    <script src="js/index.js"></script>
</body>
</html>
