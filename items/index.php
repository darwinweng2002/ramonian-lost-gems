<?php
include '../config.php'; // Ensure this file contains the correct database connection details

// Establish database connection using PDO
try {
    $con = new PDO("mysql:host=localhost;dbname=u450897284_lfis_db", 'u450897284_root', 'Lfisgemsdb1234');
    $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Items</title>
    <style>
        body {
            font-family: "Open Sans", sans-serif;
            margin: 0;
            padding: 0;
        }
        .form-cont {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .result-img img {
            max-width: 100px;
            height: auto;
        }
        .h1 {
            text-align: center;
            margin-bottom: 15px;
            font-size: 35px;
        }
        label {
            font-weight: bold;
        }
        input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #007bff;
            outline: none;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .container {
            text-align: center;
            margin-top: 20px;
        }
        .card {
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            margin: 15px;
        }
        .card-img-top {
            width: 100%;
            height: auto;
        }
        .card-body {
            padding: 15px;
        }
        .card-title {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .card-text {
            font-size: 14px;
            color: #555;
        }
        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="form-cont">
        <form method="POST">
            <div class="h1">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search">
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.3-4.3"/>
                </svg>
                <span>Search Items</span>
            </div>
            <input type="text" id="search" name="search" placeholder="Enter keywords...">
            <input type="submit" name="submit" value="Find">
        </form>
    </div>

    <?php
    if (isset($_POST["submit"])) {
        $search = trim($_POST["search"]);
        
        // Prepare SQL query
        $sql = "SELECT * FROM `item_list` WHERE `status` = 1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (title LIKE :search OR description LIKE :search OR fullname LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        // Execute the query
        try {
            $sth = $con->prepare($sql);
            $sth->setFetchMode(PDO::FETCH_OBJ);
            
            foreach ($params as $key => &$value) {
                $sth->bindParam($key, $value);
            }
            
            $sth->execute();

            if ($sth->rowCount() > 0) {
                echo '<h1 class="pageTitle">Search Results</h1>';
                echo '<div class="container">';
                echo '<div class="row">';
                
                while ($row = $sth->fetch()) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card">';
                    echo '<a href="view.php?id=' . htmlspecialchars($row->id) . '">';
                    echo '<img src="' . htmlspecialchars($row->image_path) . '" class="card-img-top" alt="Item Image">';
                    echo '</a>';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row->title) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars($row->description) . '</p>';
                    echo '<a href="view.php?id=' . htmlspecialchars($row->id) . '" class="btn-primary">View Details</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</div>';
            } else {
                echo "<p style='text-align: center;'>Results not found</p>";
            }
        } catch (PDOException $e) {
            echo "<p style='text-align: center;'>An error occurred: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    ?>
</div>
</body>
</html>
