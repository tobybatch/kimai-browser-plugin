// import logo from "./logo.svg";
import "./App.css";
import {getResourceURL} from "./services/assets-service";
import {useEffect} from "react";

// import * as AssetsService from "./services/assets-service";

function KimaiIframe() {
    let iframe = <div>Loading...</div>
    useEffect(() => {
        chrome.tabs.query({
            active: true,
            currentWindow: true
        }, tabs => {
            const currentUrl = tabs[0].url || ""
            const url = "http://home.batch.org.uk:9001/en/?u=" + encodeURI(currentUrl)
            iframe = <iframe
                title="kimai-iframe"
                width=

        });
    }, []);

    return null;
}

function App() {
    return (
        <div className="App">
            <KimaiIframe/>
        </div>
    );
}

export default App;
