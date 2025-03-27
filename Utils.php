<?php

class Utils {
    public static function cropImage($imagePath, $width, $height) {
        try {
            // informazioni sul file (ext, filename)
            $pathInfo = pathinfo($imagePath);

            // apro l'immagine originale e ne prendo la width
            $image = imagecreatefromstring(file_get_contents($imagePath));
            $imW = imagesx($image);

            // controllo se l'immagine originale è più piccola della finale
            if ($imW < $width) {
                // apro una nuova immagine con le dimensioni date e ci incollo l'originale
                $newImage = imagecreatetruecolor($width, $height);
                imagecopyresampled($newImage, $image, 0, 0, 0, 0,$width, $height, $imW, imagesy($image));
                imagedestroy($image);
                $image = $newImage;
            }

            // ricalcolo la width neccessaria nel passaggio successivo
            $imW = imagesx($image);

            // effetuo il crop prendendo sempre la parte centrale
            $img = imagecrop($image, ['x' => ($imW - $width) / 2, 'y' => 0, 'width' => $width, 'height' => $height]);
            $cropImgPath = "./.tmp/.covers/" . $pathInfo['filename'] . "_cropped." . $pathInfo['extension'];
            imagepng($img, $cropImgPath);
            imagedestroy($image);

            // cancello l'immagine originale per risparmiare spazio
            // TODO:Disattivabile
            unlink($imagePath);

            // ritorno la striga contenente il path dell'immagine croppata
            return $cropImgPath;
        } catch (\Throwable $th) {
            echo $th->getMessage();
            exit(500);
        }
    }

    public static function getFileName($file) {
        return str_replace([".tmp", "/", ".mp3"], "", $file);
    }

    public static function uploadFile($destination) {
        try {
            if (move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
                $_SESSION['name'] = $destination;
                header("Location: index.php");
            } else {
                header("Location: 500.php?err=\"could not upload file\"");
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
            exit();
        }
    }

    public static function getFolderSize(string $folder) {
        $size = 0;
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
        return $size;
    }

    //experimental
    public static function clearTmp() {
        array_map('unlink', glob(".tmp/*.*"));
        array_map('unlink', glob(".tmp/.covers/*.*"));
    }
}