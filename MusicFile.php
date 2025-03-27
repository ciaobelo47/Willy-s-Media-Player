<?php
use Kiwilan\Audio\Audio;

class MusicFile {
    public string $musicPath;
    public string $title;
    public string $artist;
    public string $coverArtPath;

    private Audio $musicData;

    public function __construct($filePath) {
        $this->musicPath = $filePath;
        $this->musicData = Audio::read($filePath);
        $this->title = $this->musicData->getTitle();
        $this->artist = $this->musicData->getArtist();

        if ($this->musicData->hasCover()) {
            try {
                $this->coverArtPath = "./.tmp/.covers/" . $this->title . ".png";
                if (!is_dir(dirname($this->coverArtPath))) {
                    mkdir(dirname($this->coverArtPath), 0777, true) or die("Could not create folder");
                }
                $tmpCover = fopen($this->coverArtPath, "w") or die("Could not open/write file");
                file_put_contents($this->coverArtPath, $this->musicData->getCover()->getContents());
                fclose($tmpCover);

                $this->cropCover();
            } catch (\Throwable $th) {
                echo $th->getMessage();
                exit(500);
            }
        }
    }

    public function getDuration() {
        return $this->musicData->getDuration();
    }

    public function getReadbleDuration() {
        return gmdate("i:s", intval($this->getDuration()));
    }

    public function cropCover() {
        $this->coverArtPath = Utils::cropImage($this->coverArtPath, 720, 720);
    }

    public function getMusicFileName() {
        return Utils::getFileName($this->musicPath);
    }

    public function getTitle() {
        if ($this->title == null) {
            return Utils::getFileName($this->musicPath);
        }

        return $this->title;
    }

    public function getArtist() {
        if ($this->artist == null) {
            return "";
        }

        return $this->artist;
    }

    public function getCoverArtPath() {
        return $this->coverArtPath;
    }

    public function hasCover() {
        return $this->musicData->hasCover();
    }

    public function getMusicPath() {
        return $this->musicPath;
    }
}