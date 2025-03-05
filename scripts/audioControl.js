const audioplayer = document.getElementById('audioplayer');
const playBTN = document.getElementById("playBTN");
const timeline = document.getElementById("timeline");
const currentTime = document.getElementById("currentTime");
const volumeSlider = document.getElementById("volumeSlider");
const volumeBTN = document.getElementById("volumeBTN");
const volIcon = volumeBTN.children[0]
var prevVol = 0.10;

playBTN.addEventListener("click", () => {
    if (audioplayer.classList.contains("playing")) {
        audioplayer.pause();
        audioplayer.classList.remove("playing");
    } else {
        audioplayer.play();
        audioplayer.classList.add("playing");
    }
})

timeline.addEventListener("input", () => {
    audioplayer.currentTime = timeline.value;
})

volumeSlider.addEventListener("input", () => {
    audioplayer.volume = (volumeSlider.value) / 100;

    if (volumeSlider.value == 0) {
        volIcon.src = "imgs/mute.svg"
    } else {
        volIcon.src = "imgs/volume_up.svg"
    }
})

volumeBTN.onclick = () => {
    if (mute(audioplayer)) {
        volIcon.src = "imgs/mute.svg"
    } else {
        volIcon.src = "imgs/volume_up.svg"
    }
}

setInterval(() => {
    if (audioplayer.classList.contains("playing")) {
        timeline.value = audioplayer.currentTime;
        currentTime.innerHTML = formatCurrentTime(audioplayer.currentTime);
    }
}, 1000)

function formatCurrentTime(seconds) {
    let m, s;
    s = seconds;
    m = s/60;
    s %= 60;

    m = parseInt(m, 10);
    s = parseInt(s, 10);

    if (s < 10) {
        s = "0" + s;
    }

    if (m < 10) {
        m = "0" + m;
    }

    return m + ":" + s;
}

function mute(audio) {
    if (audio.volume > 0) {
        prevVol = audio.volume;
        audio.volume = 0;

        return true;
    } else {
        audio.volume = prevVol;

        return false;
    }
}

/**
 * Function to set the initial volume of the audio player to an acceptable level (Trust me, your ears will thank me)
 */
function initVolume() {
    volumeSlider.value = 30;
    audioplayer.volume = 0.30;
    console.log("[AudioPlayer] Set initial volume to 30%");
}