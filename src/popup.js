'use strict';

import './popup.css';
import {ajax} from "./common";

(function () {

    function showIframe() {
        chrome.storage.sync.get(['kimaiUrl'], result => {
            const kimaiUrl = result.kimaiUrl;
            ajax(kimaiUrl, response => {
                // Check the URL is good
                if (response.status >= 400) {
                    document.getElementById("feedback").innerHTML
                        = "<b>Cannot connect to remote Kimai at:</b><br />"
                        + kimaiUrl + "<br />"
                        + "Set the Kimai URL in the option";
                } else {
                    console.log(response);
                    console.log("kimaiUrl 2", kimaiUrl)
                    document.getElementById('loading').style.display = "none";
                    document.getElementById('kimaiframe').style.display = "block";
                    document.getElementById('options').style.display = "none";
                    document.getElementById('kimai').setAttribute("src", kimaiUrl);
                }
            })
        });
    }


    document.addEventListener('DOMContentLoaded', showIframe);
})();
