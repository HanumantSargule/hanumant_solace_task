<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if(isset($_POST["submit"])) {
    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['image']['tmp_name']; 
        
        if(!file_exists($file)) {
            $_SESSION['error'] = "File upload failed. Please try again.";
            header('location:index.php');
            exit;
        }
        
        $sourceProperties = getimagesize($file);
        if($sourceProperties === false) {
            $_SESSION['error'] = "Invalid image file. Please upload a valid image.";
            header('location:index.php');
            exit;
        }
        
        $fileNewName = time();
        $folderPath = "upload/";
        
        // Ensure upload directory exists
        if(!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }
        
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $imageType = $sourceProperties[2];


        $imageResourceId = false;
        $targetLayer = false;
        
        switch ($imageType) {
            case IMAGETYPE_PNG:
                $imageResourceId = @imagecreatefrompng($file); 
                if($imageResourceId === false) {
                    $_SESSION['error'] = "Failed to process PNG image. Please try another image.";
                    header('location:index.php');
                    exit;
                }
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                if($targetLayer !== false) {
                    imagepng($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                    $_SESSION['picture']=$folderPath. $fileNewName. "_thump.". $ext;
                }
                break;

            case IMAGETYPE_GIF:
                $imageResourceId = @imagecreatefromgif($file); 
                if($imageResourceId === false) {
                    $_SESSION['error'] = "Failed to process GIF image. Please try another image.";
                    header('location:index.php');
                    exit;
                }
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                if($targetLayer !== false) {
                    imagegif($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                    $_SESSION['picture']=$folderPath. $fileNewName. "_thump.". $ext;
                }
                break;

            case IMAGETYPE_JPEG:
                $imageResourceId = @imagecreatefromjpeg($file); 
                if($imageResourceId === false) {
                    $_SESSION['error'] = "Failed to process JPEG image. Please try another image.";
                    header('location:index.php');
                    exit;
                }
                $targetLayer = imageResize($imageResourceId,$sourceProperties[0],$sourceProperties[1]);
                if($targetLayer !== false) {
                    imagejpeg($targetLayer,$folderPath. $fileNewName. "_thump.". $ext);
                    $_SESSION['picture']=$folderPath. $fileNewName. "_thump.". $ext;
                }
                break;

            default:
                $_SESSION['error'] = "Invalid Image type. Please upload JPG, PNG, or GIF images only.";
                header('location:index.php');
                exit;
                break;
        }

        // Free memory only if resources were created
        if($imageResourceId !== false && is_resource($imageResourceId)) {
            @imagedestroy($imageResourceId);
        }
        if($targetLayer !== false && is_resource($targetLayer)) {
            @imagedestroy($targetLayer);
        }
        
        header('location:index.php');
        exit;
    } else {
        $_SESSION['error'] = "File upload error. Please select a valid image file.";
        header('location:index.php');
        exit;
    }
}


function imageResize($imageResourceId,$width,$height) {
    if(!isset($_POST['width']) || !isset($_POST['height'])) {
        $_SESSION['error'] = "Width and height are required.";
        header('location:index.php');
        exit;
    }
    
    $targetWidth = intval($_POST['width']);
    $targetHeight = intval($_POST['height']);
    
    if($targetWidth <= 0 || $targetHeight <= 0) {
        $_SESSION['error'] = "Width and height must be greater than 0.";
        header('location:index.php');
        exit;
    }

    $targetLayer = imagecreatetruecolor($targetWidth, $targetHeight);
    
    // Preserve transparency for PNG
    imagealphablending($targetLayer, false);
    imagesavealpha($targetLayer, true);
    $transparent = imagecolorallocatealpha($targetLayer, 255, 255, 255, 127);
    imagefill($targetLayer, 0, 0, $transparent);
    
    imagecopyresampled($targetLayer, $imageResourceId, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

    return $targetLayer;
}
?>