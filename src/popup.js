'use strict';

import './popup.css';


(function () {

    function showOptions() {
        chrome.storage.sync.get(['lang'], result => {
            if ("lang" in result) {
                document.getElementById('lang').value = result.lang;
            } else {
                document.getElementById('lang').value = "en";
            }
        });
        chrome.storage.sync.get(['kimaiUrl'], result => {
            document.getElementById('kimaiurl').value = result.kimaiUrl;
        });
        document.getElementById('loading').style.display = "none";
        document.getElementById('kimaiframe').style.display = "none";
        document.getElementById('options').style.display = "block";
    }

    function showIframe() {
        let lang = "en";
        chrome.storage.sync.get(['lang'], result => {
            if ("lang" in result) {
                lang = result.lang;
            }
        });
        chrome.storage.sync.get(['kimaiUrl'], result => {
            let kimaiUrl = result.kimaiUrl;
            ajax(kimaiUrl, response => {
                // Check the URL is good
                if (response.status >= 400) {
                    document.getElementById("feedback").innerHTML
                        = "<b>Cannot connect to remote Kimai at:</b><br />"
                        + kimaiUrl + "<br />"
                        + "Set the Kimai URL in the option";
                } else {
                    let fullUrl = kimaiUrl + "/" + lang + "/timesheet/create";
                    chrome.tabs.query({active: true}, tabs => {
                        const tabUrl = tabs[0].url;
                        fullUrl += "?source=" + encodeURIComponent(tabUrl);
                        console.log(fullUrl);
                    });
                    document.getElementById('footer-text').innerHTML = "Kimai @ " + kimaiUrl;
                    document.getElementById('loading').style.display = "none";
                    document.getElementById('kimaiframe').style.display = "block";
                    document.getElementById('options').style.display = "none";
                    document.getElementById('kimai').setAttribute("src", fullUrl);
                }
            })
        });
    }

    function testAndSave() {
        document.getElementById('loading').style.display = "block";
        document.getElementById('kimaiframe').style.display = "none";
        document.getElementById('options').style.display = "none";
        const theUrl = document.getElementById('kimaiurl').value;
        const lang = document.getElementById('lang').value;
        chrome.storage.sync.set({lang: lang});
        ajax(theUrl, response => {
            if (response.status === 200) {
                console.log(response);
                chrome.storage.sync.set({kimaiUrl: theUrl});
                showIframe();
            } else {
                document.getElementById("feedback").innerHTML
                    = "<b>Cannot connect to remote Kimai at:</b><br />"
                    + theUrl + "<br />"
                    + response.responseText;
                document.getElementById('loading').style.display = "none";
                document.getElementById('kimaiframe').style.display = "none";
                document.getElementById('options').style.display = "block";
            }
        })
    }

    function ajax(url, callback) {
        const xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function () {
            if (this.readyState !== 4) {
                return;
            }
            if (this.status === 302) {
                const location = this.getResponseHeader("Location");
                return ajax.call(this, location, callback);
            }
            callback(this);
        };
        xmlhttp.open("GET", url, true);
        xmlhttp.send();
    }

    document.getElementById('save').addEventListener('click', () => {
        testAndSave()
    });
    document.getElementById('settings').addEventListener('click', () => {
        showOptions()
    });
    document.addEventListener('DOMContentLoaded', showIframe);
})();
