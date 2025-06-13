import "./bootstrap";
import React from "react";
import { createRoot } from "react-dom/client";
import RegisterForm from "./components/RegisterForm";

// 会員登録フォームのマウント
document.addEventListener("DOMContentLoaded", function () {
    const registerFormElement = document.getElementById("register-form");
    if (registerFormElement) {
        const root = createRoot(registerFormElement);
        root.render(React.createElement(RegisterForm));
    }
});
