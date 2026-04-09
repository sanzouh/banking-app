import Auth from "./pages/auth";
import { BrowserRouter } from "react-router-dom";
import { MantineProvider } from '@mantine/core';

function App() {

  return (
    <>
      <MantineProvider>
        <BrowserRouter>
          <Auth />
        </BrowserRouter>
      </MantineProvider>
    </>
  )
}

export default App