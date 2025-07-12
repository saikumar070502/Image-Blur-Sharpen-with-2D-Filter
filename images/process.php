<?php
function loadImage($path) {
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            return imagecreatefromjpeg($path);
        case 'png':
            return imagecreatefrompng($path);
        case 'gif':
            return imagecreatefromgif($path);
        default:
            die("Unsupported image format.");
    }
}

function saveImage($image, $path, $ext) {
    switch ($ext) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($image, $path);
            break;
        case 'png':
            imagepng($image, $path);
            break;
        case 'gif':
            imagegif($image, $path);
            break;
    }
}

function applyFilter($srcPath, $filterType) {
    $ext = strtolower(pathinfo($srcPath, PATHINFO_EXTENSION));
    $image = loadImage($srcPath);

    // Resize if large (optional)
    $maxSize = 500;
    $width = imagesx($image);
    $height = imagesy($image);
    if ($width > $maxSize || $height > $maxSize) {
        $scale = min($maxSize / $width, $maxSize / $height);
        $newWidth = floor($width * $scale);
        $newHeight = floor($height * $scale);
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        imagedestroy($image);
        $image = $resized;
    }

    // Apply filter
    if ($filterType === 'blur') {
        imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
    } elseif ($filterType === 'sharpen') {
        $sharpenMatrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1]
        ];
        imageconvolution($image, $sharpenMatrix, 8, 0);
    }

    $outputPath = 'output/processed_' . uniqid() . '.' . $ext;
    saveImage($image, $outputPath, $ext);
    imagedestroy($image);
    return $outputPath;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!file_exists('uploads')) mkdir('uploads');
    if (!file_exists('output')) mkdir('output');

    $imageFile = $_FILES['image'];
    $filter = $_POST['filter'];

    $fileName = basename($imageFile['name']);
    $uploadPath = 'uploads/' . $fileName;
    move_uploaded_file($imageFile['tmp_name'], $uploadPath);

    $resultPath = applyFilter($uploadPath, $filter);

    echo "<h2>Original Image:</h2><img src='$uploadPath' style='max-width:400px;'><br><br>";
    echo "<h2>Filtered Image:</h2><img src='$resultPath' style='max-width:400px;'><br><br>";
    echo "<a href='index.html'>Go Back</a>";
}
?>
