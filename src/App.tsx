// import logo from "./logo.svg";
import "./App.css";
import {useEffect, useState} from "react";

// import * as AssetsService from "./services/assets-service";

function App() {

    const [kimaiUrl, setKimaiUrl] = useState('');

    useEffect(() => {
        if (chrome.tabs !== undefined) {
            chrome.tabs.query({
                active: true,
                currentWindow: true
            }, tabs => {
                const currentUrl = tabs[0].url || ""
                setKimaiUrl(getKimaiUrl(currentUrl))
            });
        } else {
            setKimaiUrl(getKimaiUrl("https://github.com/tobybatch/kimai2/issues/247"))
        }
    }, []);

    const getKimaiUrl = (currentUrl) => {
        return "https://kimai.neontribe.org/en/timesheet/create?source=" + encodeURI(currentUrl);
    }

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
