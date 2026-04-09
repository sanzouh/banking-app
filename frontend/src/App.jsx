import Auth from "./pages/auth";
import { BrowserRouter } from "react-router-dom";

function App() {

  return (
    <>
      <BrowserRouter>
        <Auth />
      </BrowserRouter>
    </>
  )
}

export default App