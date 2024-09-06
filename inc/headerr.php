<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .header {
            width: 100%;
            height: 150px; /* Adjust the height as needed */
            background-image: url('../../uploads/prmsuu.png'); /* Replace with your image path */
            background-size: cover;
            background-position: center;
            opacity: 0.8; /* Adjust transparency */
            position: relative;
            z-index: 1;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.2); /* Adjust overlay color and opacity */
            z-index: 1;
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="overlay"></div>
    </header>
</body>
</html>
