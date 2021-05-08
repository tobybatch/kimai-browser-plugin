'use strict';

import './popup.css';

(function () {
    const kimaiUrlStorage = {
        get: cb => {
            chrome.storage.sync.get(['kimaiUrl'], result => {
                cb(result.kimaiUrl);
            });
        },
        set: (value, cb) => {
            chrome.storage.sync.set(
                {
                    kimaiUrl: value,
                },
                () => {
                    cb();
                }
            );
        },
    };

    function showIframe(kimaiUrl) {
        document.getElementById(
            'app'
        ).innerHTML = ""
    }

    function showIframe() {
        // Restore count value
        debugger;
        kimaiUrlStorage.get(kimaiUrl => {
            if (typeof kimaiUrl === 'undefined') {
                options();
            } else {
                showIframe(kimaiUrl);
            }
        });
    }

    function options() {
        document.getElementById('loading').style.display = "none";
        document.getElementById('kimaiframe').style.display = "none";
        document.getElementById('options').style.display = "block";
    }

    document.getElementById('save').addEventListener('click', () => {
        // test and save
    });
    debugger;
    document.addEventListener('DOMContentLoaded', showIframe);

})();
