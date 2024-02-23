<?php

$host = 'localhost';
$dbname = 'artconnect';
$username = 'root';
$password = '';

try {

    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Database connection failed: ' . $e->getMessage();
    exit();
}

function updateArtistVerification($phone, $pdo)
{
    try {
        $stmt = $pdo->prepare('UPDATE artist SET verified = 2 WHERE phone = ?');
        $stmt->execute([$phone]);
    } catch (PDOException $e) {
        echo 'Error updating artist verification status: ' . $e->getMessage();
    }
}

function storeVerificationData($name, $email, $phone, $address, $citizenshipFront, $citizenshipBack, $currentPhoto, $cv, $pdo)
{
    // Check if the phone number already exists in the verification_check table
    $stmt = $pdo->prepare('SELECT * FROM verification_check WHERE phone = ?');
    $stmt->execute([$phone]);
    $existingVerification = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingVerification) {
        // Phone number does not exist, proceed with storing verification data
        try {
            $stmt = $pdo->prepare('INSERT INTO verification_check (name, email, phone, address, citizenship_front, citizenship_back, current_photo, cv) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $email, $phone, $address, $citizenshipFront, $citizenshipBack, $currentPhoto, $cv]);
        } catch (PDOException $e) {
            echo 'Error storing verification data: ' . $e->getMessage();
        }
    } else {
        // Phone number already exists in the verification_check table
        echo 'You are already under verification. Please wait for the processing.';
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    $stmt = $pdo->prepare('SELECT * FROM artist WHERE phone = ?');
    $stmt->execute([$phone]);
    $artist = $stmt->fetch(PDO::FETCH_ASSOC);


    if ($artist) {
        updateArtistVerification($phone, $pdo);

        $uploadsDirectory = 'uploads/';

        $citizenshipFront = handleFileUpload('citizenship_front', $uploadsDirectory);
        $citizenshipBack = handleFileUpload('citizenship_back', $uploadsDirectory);
        $currentPhoto = handleFileUpload('current_photo', $uploadsDirectory);
        $cv = handleFileUpload('cv', $uploadsDirectory, 'pdf');

        // Store verification data only if the phone number is not already in verification_check
        storeVerificationData($name, $email, $phone, $address, $citizenshipFront, $citizenshipBack, $currentPhoto, $cv, $pdo);

        header('Location:/ArtConnect/message/verifyartist/under-verification.html');
    } else {
        echo 'Phone number does not match any artist account. Please use the phone number used during signup.';
    }


}

function handleFileUpload($fileInputName, $directory, $allowedExtension = null)
{
    $uploadedFile = $_FILES[$fileInputName];
    $originalFilename = $uploadedFile['name'];
    $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

    // Check if the file has a valid extension
    if ($allowedExtension && $extension !== $allowedExtension) {
        echo 'Invalid file extension for ' . $fileInputName;
        exit();
    }

    // Generate a unique filename to avoid conflicts
    $uniqueFilename = generateUniqueFilename($directory, $originalFilename);

    $targetPath = $directory . $uniqueFilename;

    // Move the uploaded file to the specified directory
    if (move_uploaded_file($uploadedFile['tmp_name'], $targetPath)) {
        return $uniqueFilename;
    } else {
        echo 'Error uploading file for ' . $fileInputName;
        exit();
    }
}

// Function to generate a unique filename
function generateUniqueFilename($directory, $filename)
{
    $fullPath = $directory . $filename;

    // If the file already exists, append a unique identifier
    $counter = 1;
    while (file_exists($fullPath)) {
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);
        $newFilename = $filenameWithoutExtension . '_' . $counter . '.' . pathinfo($filename, PATHINFO_EXTENSION);
        $fullPath = $directory . $newFilename;
        $counter++;
    }

    return pathinfo($fullPath, PATHINFO_BASENAME);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Yourself as an Artist</title>
    <style>
        body {
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        form {
            width: 500px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 4px 4px 8px #d3d3d3, -4px -4px 8px #ffffff;
            padding: 20px;
            margin: 20px;
            box-sizing: border-box;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: none;
            border-radius: 8px;
            background-color: #f0f0f0;
            box-shadow: inset 4px 4px 8px #d3d3d3, inset -4px -4px 8px #ffffff;
            color: #333;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background-color: grey;
            color: #fff;
            cursor: pointer;
            box-shadow: 4px 4px 8px #d3d3d3, -4px -4px 8px #ffffff;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }

        button:hover {
            background-color: black;
        }
    </style>
</head>

<body>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
        <label for="name">Full Name:</label>
        <input type="text" name="name" id="name" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="phone">Phone Number:</label>
        <input type="number" name="phone" id="phone" required><br>

        <label for="address">Mailing Address:</label>
        <input type="text" name="address" id="address" required><br>

        <label for="citizenship_front">Citizenship Front Photo:</label>
        <input type="file" name="citizenship_front" id="citizenship_front" accept="image/*" required><br>

        <label for="citizenship_back">Citizenship Back Photo:</label>
        <input type="file" name="citizenship_back" id="citizenship_back" accept="image/*" required><br>

        <label for="current_photo">Current Photo:</label>
        <input type="file" name="current_photo" id="current_photo" accept="image/*" required><br>

        <label for="cv">CV (PDF only):</label>
        <input type="file" name="cv" id="cv" accept=".pdf" required><br>

        <button type="submit">Submit Verification</button>
    </form>
</body>

</html>