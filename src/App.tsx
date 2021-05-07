// import logo from "./logo.svg";
import "./App.css";
import {getResourceURL} from "./services/assets-service";
import {useEffect, useState} from "react";

// import * as AssetsService from "./services/assets-service";

function App() {

    const [kimaiUrl, setKimaiUrl] = useState( '' );

        useEffect(() => {
            chrome.tabs.query({
                active: true,
                currentWindow: true
            }, tabs => {
                const currentUrl = tabs[0].url || ""
                setKimaiUrl("https://kimai.neontribe.org/en/timesheet/create?source=" + encodeURI(currentUrl))
            });
        }, []);

    return (
        <div className="App">
            <iframe
            title="kimai-iframe"
            width="750"
            height="580"
            src={kimaiUrl}
        />
        </div>
    );
}

export default App;
