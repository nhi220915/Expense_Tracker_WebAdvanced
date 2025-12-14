import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
      "./resources/**/*.blade.php",
      "./resources/**/*.js",
      "./resources/**/*.vue",
    ],

    theme: {
      extend: {
        colors: {
          mint: {
            dark: "#00a896",
            medium: "#00d1c1",
            light: "#e6f7f5",
          },
          emerald: "#27ae60",
          warning: "#e74c3c",
        },
        boxShadow: {
          soft: "0 5px 15px rgba(0, 0, 0, 0.08)",
        },
      },
    },
    plugins: [
      require("@tailwindcss/forms"),
    ],
};
