<?php
declare(strict_types=1);
require "vendor/autoload.php";

use YoutubeDl\YoutubeDl;
use YoutubeDl\Options;
use Kiwilan\Audio\Audio;

session_start();
setlocale(LC_ALL, 'ja_JP');

const TMP_DIR = ".tmp";
global $audio;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    clearTmp();

    if (isset($_POST['url'])) {
        playFilefromYT();
    } elseif ($_FILES['file']['error'] == 0) {
        uploadFile();
    } else {
        echo "Eror";
        return;
    }
}

if (isset($_SESSION["name"])) {
    $audio = metadataAudio();
}

function uploadFile(): void
{
    $uploadedFile = ".tmp" . DIRECTORY_SEPARATOR . $_FILES['file']['name'];

    if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadedFile)) {
        $_SESSION["name"] = $uploadedFile;
        header("Location: index.php");
    } else {
        header("Location: 500.php");
    }
}

function playFileFromYT(): void
{
    $yt = new YoutubeDl();

    $list = $yt
        ->setBinPath("./bin/yt-dlp")
        ->download(
            Options::create()
                ->ffmpegLocation("./bin/ffmpeg")
                ->downloadPath(TMP_DIR)
                ->extractAudio(true)
                ->audioFormat(Options::AUDIO_FORMAT_MP3)
                ->audioQuality('0')
                ->addMetadata(true)
                ->embedThumbnail(true)
                ->output('%(title)s.%(ext)s')
                ->cookies("cookies.txt")
                ->url($_POST["url"])
        );

    foreach ($list->getVideos() as $video) {
        if ($video->getError() !== null) {
            echo "ALLAAHHHHHHHHHHHHHHHHHHHHH {$video->getError()}";
        } else {
            $_SESSION["name"] = $video->getFilename();
        }
    }
}

function metadataAudio(): Audio|null
{
    try {
        global $audio;

        $audio = Audio::read($_SESSION["name"]);
        $targetCover = TMP_DIR . DIRECTORY_SEPARATOR . "cover" . '.png';

        if ($audio->hasCover()) {
            $cover = fopen($targetCover, 'w') or die("can't open file");
            file_put_contents($targetCover, $audio->getCover()->getContents());
            fclose($cover);
        }
    } catch (\Throwable $th) {
        throw $th;
    }

    try {
        cropCover();
    } catch (\Throwable $th) {
        if (!strpos($th->getMessage(), "false given")) {
            echo $th->getMessage();
        }
    }

    return $audio;
}

function getFileName(Audio $audio): string
{
    $path = $audio->getPath();
    return str_replace([TMP_DIR, DIRECTORY_SEPARATOR, ".mp3"], "", $path);
}

function clearTmp(): void
{
    array_map('unlink', glob(".tmp/*.*"));
}

function cropCover(): void
{
    $cover = imagecreatefromstring(file_get_contents(".tmp/cover.png"));
    if ($cover == false) {
        global $hasCover;
        $hasCover = false;
        return;
    }
    $width = imagesx($cover);
    if ($width < 720) {
        copy(".tmp/cover.png", ".tmp/cover_cropped.png");
        return;
    }

    $img = imagecrop($cover, ['x' => (($width - 720) / 2), 'y' => 0, 'width' => 720, 'height' => 720]);
    imagepng($img, ".tmp/cover_cropped.png");
    imagedestroy($cover);

    $hasCover = true;
}

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Willy's Media Player</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style/style.css">
    <link rel="shortcut icon" href="imgs/favicon.png">
    <script src="https://jscolor.com/release/2.4.5/jscolor.js"></script>
</head>

<body>
    <nav class="nav">
        <img src="imgs/logo.svg" alt="fullLogo" class="fullLogo">
        <button type="button" class="nav_item">File</button>
        <ul class="dropdown">
            <li>
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post"
                    enctype="multipart/form-data">
                    <label for="file">Open</label>
                    <input id="file" name="file" type="file" style="display: none;">
                    <input type="submit" id="submitFile" style="display: none;">
                </form>
            </li>
            <li><label id="linkLabel">Open Link</label></li>
        </ul>
        <button type="button" class="nav_item">View</button>
        <button type="button" class="nav_item">Tools</button>
        <button type="button" class="nav_item">Help</button>
        <input class="jscolor" name="mainColor" id="mainColor">
    </nav>
    <aside class="playlist_sel">
        <button class="playlist_sel_item">Miao</button>
    </aside>

    <main>
        <div class="mainBG <?php if (!isset($_SESSION["name"]))
            echo "hidden"; if (!$hasCover) echo "hidden" ?>">
            <img draggable="false" class="back" src=".tmp/cover.png" alt="">
            <img draggable="false" class="backleft" src=".tmp/cover.png" alt="">
            <img draggable="false" class="backright" src=".tmp/cover.png" alt="">
            <img draggable="false" class="front" src=".tmp/cover.png" alt="">
        </div>
        <section class="card_player">
            <div class="card_player_top">
                <h2 style="margin-right: auto">Now Playing</h2>

                <button style="float: right" id="repeat_button">
                    <img src="imgs/repeat_icon.svg" alt="">
                </button>

                <button style="float: right" id="shuffle_button">
                    <img src="imgs/shuffle_icon.svg" alt="">
                </button>
            </div>

            <img style="width: 256px;" src="<?php
            if (isset($audio)) {
                if ($audio->hasCover()) {
                    echo ".tmp/cover_cropped.png";
                } else {
                    echo "imgs/noFile.png";
                }
            } else {
                echo "imgs/noFile.png";
            }
            ?>" alt="">
            <p class="card_player_item">
                <?php
                if (isset($audio)) {
                    if ($audio->getTitle() != null) {
                        echo $audio->getTitle();
                    } else {
                        echo getFileName($audio);
                    }
                }
                ?>
            </p>
            <p class="card_player_item">
                <?php
                if (isset($audio)) {
                    if ($audio->getArtist() != null) {
                        echo $audio->getArtist();
                    } else {
                        echo "";
                    }
                }
                ?>
            </p>

            <div class="player_timeline">
                <span id="currentTime">00:00</span>
                <input type="range" min="0" max="<?php if (isset($audio)) {
                    echo $audio->getDuration();
                } ?>" value="0" id="timeline">
                <span><?php if (isset($audio)) {
                    echo gmdate("i:s", intval($audio->getDuration()));
                } ?></span>
            </div>

            <div class="player_timeline">
                <audio src="<?php echo $_SESSION["name"]; ?>" id="audioplayer"></audio>
                <button id="prevBNT">
                    <img src="imgs/skip_previous.svg" alt="" srcset="">
                </button>

                <button id="playBTN">
                    <img src="imgs/play_pause.svg" alt="" srcset="">
                </button>

                <button id="nextBTN">
                    <img src="imgs/skip_next.svg" alt="" srcset="">
                </button>

                <button id="volumeBTN">
                    <img src="imgs/volume_up.svg" alt="" srcset="">
                </button>

                <input type="range" min="0" max="100" value="50" id="volumeSlider">
            </div>
        </section>
    </main>

    <div id="overlay" class="hidden">
        <div id="openLinkWindow">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="post" enctype="multipart/form-data">
                <label for="url" style="display: block; text-align: center;">URL</label>
                <br>
                <input type="text" name="url" id="url" placeholder="Enter a Compatible URL" required>
                <input type="submit" value="Submit" id="submit">
            </form>
        </div>
    </div>

    <script>
        const buttons = document.getElementsByTagName("button");
        for (const button of buttons) {
            button.classList.add("ripple");
        }
    </script>
    <script src="scripts/audioControl.js"></script>
    <script src="scripts/colorControl.js"></script>
    <script src="scripts/openMusic.js"></script>

    <script>
        window.onload = () => {
            initVolume();
            experimentalWarn();
        }
    </script>
</body>

</html>