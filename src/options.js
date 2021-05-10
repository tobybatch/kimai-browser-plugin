
import {ajax} from "./common";

function testAndSave() {
    document.getElementById('loading').style.display = "block";
    document.getElementById('kimaiframe').style.display = "none";
    document.getElementById('options').style.display = "none";
    const theUrl = document.getElementById('kimaiurl').value;
    ajax(theUrl, response => {
        if (response.status === 200) {
            // save url
            chrome.storage.sync.set({kimaiUrl: value,});
        } else {
            document.getElementById("feedback").innerHTML
                = "<b>Cannot connect to remote Kimai at:</b><br />"
                + theUrl + "<br />"
                + response.responseText;
        }
    })
}
    document.getElementById('save').addEventListener('click', () => {
        testAndSave()
    });