const fullBtn = document.getElementById('fullscreenBtn');

if (fullBtn) {

    fullBtn.onclick = () => {

        if (!document.fullscreenElement) {

            document.documentElement.requestFullscreen();

        } else {

            document.exitFullscreen();

        }

    };

}