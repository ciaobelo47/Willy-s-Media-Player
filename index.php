<?php
declare(strict_types=1);

require "Utils.php";
require "MusicFile.php";
require "vendor/autoload.php";

try {
    session_start();

    if (Utils::getFolderSize("./.tmp") > 1073741824) {
        Utils::clearTmp();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['url'])) {
            //yt play
        } elseif (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            Utils::uploadFile("./.tmp/". $_FILES['file']['name']);
        } else {
            header("Location: 500.php?err=\"POST not successful\"");
        }
    }

    if (isset($_SESSION['name'])) {
        $musicFile = new MusicFile($_SESSION['name']);
    }
} catch (\Throwable $th) {
    echo $th->getMessage();
    exit();
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
    </nav>
    <aside class="playlist_sel">
        <button class="playlist_sel_item">Miao</button>
    </aside>

    <main>
        <div class="mainBG <?php if (!isset($_SESSION['name']) || !$musicFile->hasCover()) {echo "hidden";} ?>">
            <img draggable="false" class="back" src="<?php if (isset($musicFile)) {echo $musicFile->getCoverArtPath();} ?>" alt="">
            <img draggable="false" class="backleft" src="<?php if (isset($musicFile)) {echo $musicFile->getCoverArtPath();} ?>" alt="">
            <img draggable="false" class="backright" src="<?php if (isset($musicFile)) {echo $musicFile->getCoverArtPath();} ?>" alt="">
            <img draggable="false" class="front" src="<?php if (isset($musicFile)) {echo $musicFile->getCoverArtPath();} ?>" alt="">
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
            if (isset($musicFile)) {
                if ($musicFile->hasCover()) {
                    echo $musicFile->getCoverArtPath();
                } else {
                    echo "imgs/noFile.png";
                }
            } else {
                echo "imgs/noFile.png";
            }
            ?>" alt="">
            <p class="card_player_item">
                <?php
                if (isset($musicFile)) {
                    echo $musicFile->title;
                }
                ?>
            </p>
            <p class="card_player_item">
                <?php
                if (isset($musicFile)) {
                    echo $musicFile->getArtist();
                }
                ?>
            </p>

            <div class="player_timeline">
                <span id="currentTime">00:00</span>
                <input type="range" min="0" max="<?php if (isset($musicFile)) { echo $musicFile->getDuration(); } ?>" value="0" id="timeline">
                <span><?php if (isset($musicFile)) { echo $musicFile->getReadbleDuration();} ?></span>
            </div>

            <div class="player_timeline">
                <audio src="<?php if (isset($musicFile)) { echo $musicFile->getMusicPath(); } ?>" id="audioplayer"></audio>
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
    <!--<script src="scripts/colorControl.js"></script>-->
    <script src="scripts/openMusic.js"></script>

    <script>
        window.onload = () => {
            initVolume();
            experimentalWarn();
        }
    </script>
</body>
</html>