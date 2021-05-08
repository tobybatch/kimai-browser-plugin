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

    function showIframe() {
        // Restore count value
        debugger;
        kimaiUrlStorage.get(kimaiUrl => {
            if (typeof kimaiUrl === 'undefined') {
                options();
            } else {
                document.getElementById('loading').style.display = "none";
                document.getElementById('kimaiframe').style.display = "block";
                document.getElementById('options').style.display = "none";
                document.getElementById('kimai').setAttribute("src",kimaiUrl );
            }
        });
    }

    function options() {
        document.getElementById('loading').style.display = "none";
        document.getElementById('kimaiframe').style.display = "none";
        document.getElementById('options').style.display = "block";
    }

    function ajax(url, callback) {
        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState !== 4) {
                return;
            }

            if (this.status === 302) {
                const location = this.getResponseHeader("Location");
                return ajax.call(this, location /*params*/, callback);
            }

            callback(this);
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }

    document.getElementById('save').addEventListener('click', () => {
        testAndSave()
    });

    function testAndSave() {
        const theUrl = document.getElementById('kimaiurl').value;
        ajax(theUrl, response => {
            if (response.status === 200) {
                // save url
                kimaiUrlStorage.set(theUrl);
                showIframe();
            }
            else {
                // TODO Added feeedback
            }
        })
    }

    document.addEventListener('DOMContentLoaded', showIframe);

})();
