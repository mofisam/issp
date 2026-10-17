/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./views/**/*.php",
    "./public/assets/js/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        // ISSP brand colours - used as bg-issp-green, text-issp-orange, etc.
        "issp-green": {
          DEFAULT: "#1F5C3D",
          50: "#EAF3EC",
          600: "#1F5C3D",
          700: "#173F2B",
        },
        "issp-orange": {
          DEFAULT: "#D9711E",
          50: "#FBEEE3",
          600: "#D9711E",
          700: "#B2590F",
        },
      },
      fontFamily: {
        sans: ["Inter", "system-ui", "sans-serif"],
      },
    },
  },
  plugins: [],
};
