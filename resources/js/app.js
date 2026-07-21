import './bootstrap';

import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import 'bootstrap-icons/font/bootstrap-icons.css';
import '@fortawesome/fontawesome-free/css/all.min.css';

//add sidebar js
import './sidebar';

//add common js
import './common';

//add the jquery
import $ from 'jquery';
window.$ = window.jQuery = $;

//add purchase js
import './transaction';

//add purchase js
import './sales-return';


document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("fullscreenBtn");

    if (btn) {

        btn.addEventListener("click", function () {

            if (!document.fullscreenElement) {

                document.documentElement.requestFullscreen();

            } else {

                document.exitFullscreen();

            }

        });

    }

});