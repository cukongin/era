/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#003e29", // Kaitoke Green (Deep Green) - Main App
                "secondary": "#467061", // Como (Greenish Gray)
                "accent": "#fee46d", // Kournikova (Yellow/Gold)
                "background-light": "#f6f8f6",
                "background-dark": "#002a1c", // Darker Green for bg

                // Login Page Specific (Unified with Main App - Resmi/Dinas)
                "login-primary": "#003e29", // Matches Primary
                "login-primary-dark": "#002318", // Darker shade for hover
            },
            fontFamily: {
                "display": ["Inter", "Amiri", "sans-serif"],
                "sans": ["Inter", "Amiri", "sans-serif"],
                "serif": ["Amiri", "Times New Roman", "serif"]
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
        require('@tailwindcss/container-queries'),
    ],
}
