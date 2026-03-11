<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

session_start();

$allowedImageTypes = array(
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_GIF => 'gif',
);

// Guard against direct access and invalid upload submissions.
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['submit'])) {
    redirectToIndex();
}

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals((string) $_SESSION['csrf_token'], (string) $_POST['csrf_token'])) {
    setErrorAndRedirect('Session validation failed. Refresh the page and submit again.');
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    setErrorAndRedirect('Upload could not be completed. Please choose a valid image and retry.');
}

if ((int) $_FILES['image']['size'] <= 0 || (int) $_FILES['image']['size'] > 5 * 1024 * 1024) {
    setErrorAndRedirect('Image size must be between 1 byte and 5 MB.');
}

$tmpFilePath = $_FILES['image']['tmp_name'];
if (!file_exists($tmpFilePath)) {
    setErrorAndRedirect('Temporary upload data is missing. Please try again.');
}

$sourceImageInfo = getimagesize($tmpFilePath);
if ($sourceImageInfo === false) {
    setErrorAndRedirect('Unsupported image payload. Please upload a valid image file.');
}

$sourceWidth = (int) $sourceImageInfo[0];
$sourceHeight = (int) $sourceImageInfo[1];
$imageType = (int) $sourceImageInfo[2];

if (!isset($allowedImageTypes[$imageType])) {
    setErrorAndRedirect('Unsupported format. Please upload JPG, PNG, or GIF only.');
}

$uploadDirectory = 'upload/';
ensureUploadDirectory($uploadDirectory);

$uploadedExtension = $allowedImageTypes[$imageType];
try {
    $newFileToken = bin2hex(random_bytes(16));
} catch (Exception $exception) {
    $newFileToken = sha1(uniqid('', true));
}
$outputPath = $uploadDirectory . $newFileToken . '_thump.' . $uploadedExtension;

// Build image resources, resize to user dimensions, then save to upload folder.
$sourceImage = createImageResource($tmpFilePath, $imageType);
if ($sourceImage === false) {
    setErrorAndRedirect(getCreateImageErrorMessage($imageType));
}

$resizedImage = resizeUploadedImage($sourceImage, $sourceWidth, $sourceHeight);
if ($resizedImage !== false) {
    saveResizedImage($resizedImage, $outputPath, $imageType);
    $_SESSION['picture'] = $outputPath;
}

if ($sourceImage !== false) {
    @imagedestroy($sourceImage);
}

if ($resizedImage !== false) {
    @imagedestroy($resizedImage);
}

redirectToIndex();

/**
 * Ensure the output directory exists before saving generated images.
 */
function ensureUploadDirectory(string $uploadDirectory): void
{
    if (!is_dir($uploadDirectory)) {
        mkdir($uploadDirectory, 0777, true);
    }
}

function createImageResource(string $filePath, int $imageType)
{
    switch ($imageType) {
        case IMAGETYPE_PNG:
            return @imagecreatefrompng($filePath);

        case IMAGETYPE_GIF:
            return @imagecreatefromgif($filePath);

        case IMAGETYPE_JPEG:
            return @imagecreatefromjpeg($filePath);

        default:
            return false;
    }
}

function saveResizedImage($resizedImage, string $outputPath, int $imageType): void
{
    switch ($imageType) {
        case IMAGETYPE_PNG:
            imagepng($resizedImage, $outputPath);
            break;

        case IMAGETYPE_GIF:
            imagegif($resizedImage, $outputPath);
            break;

        case IMAGETYPE_JPEG:
            imagejpeg($resizedImage, $outputPath);
            break;
    }
}

function getCreateImageErrorMessage(int $imageType): string
{
    switch ($imageType) {
        case IMAGETYPE_PNG:
            return 'Unable to read PNG image content. Please try a different file.';

        case IMAGETYPE_GIF:
            return 'Unable to read GIF image content. Please try a different file.';

        case IMAGETYPE_JPEG:
            return 'Unable to read JPEG image content. Please try a different file.';

        default:
            return 'Unsupported format. Please upload JPG, PNG, or GIF only.';
    }
}

function resizeUploadedImage($sourceImage, int $sourceWidth, int $sourceHeight)
{
    if (!isset($_POST['width'], $_POST['height'])) {
        setErrorAndRedirect('Canvas width and height are required.');
    }

    $targetWidth = (int) $_POST['width'];
    $targetHeight = (int) $_POST['height'];

    if ($targetWidth <= 0 || $targetHeight <= 0) {
        setErrorAndRedirect('Canvas dimensions must be greater than zero.');
    }

    $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($targetImage, false);
    imagesavealpha($targetImage, true);

    $transparentColor = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
    imagefill($targetImage, 0, 0, $transparentColor);

    imagecopyresampled(
        $targetImage,
        $sourceImage,
        0,
        0,
        0,
        0,
        $targetWidth,
        $targetHeight,
        $sourceWidth,
        $sourceHeight
    );

    return $targetImage;
}

function setErrorAndRedirect(string $message): void
{
    $_SESSION['error'] = $message;
    redirectToIndex();
}

function redirectToIndex(): void
{
    header('location:index.php');
    exit;
}