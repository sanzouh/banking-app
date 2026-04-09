import { Route, Routes } from "react-router-dom";
import Login from "./login";
import Register from "./register";

export default function Auth() {
  return (
    <Routes>
      <Route path="/" element={<Login />} />
      <Route path="/register" element={<Register />} />
    </Routes>
  )
}