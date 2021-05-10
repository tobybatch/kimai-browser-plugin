
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

export default function ajax;