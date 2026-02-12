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
                "primary": "rgb(var(--color-primary) / <alpha-value>)",
                "primary-dark": "rgb(var(--color-primary-dark) / <alpha-value>)", // Use for hover states
                "secondary": "rgb(var(--color-secondary) / <alpha-value>)",
                "accent": "#fee46d", // Kournikova (Yellow/Gold) - Keep static for now
                "background-light": "#f6f8f6",
                "background-dark": "rgb(var(--color-background-dark) / <alpha-value>)",
                "surface-dark": "rgb(var(--color-surface-dark) / <alpha-value>)",

                // Login Page Specific (Unified with Main App - Resmi/Dinas)
                "login-primary": "rgb(var(--color-primary) / <alpha-value>)",
                "login-primary-dark": "rgb(var(--color-primary-dark) / <alpha-value>)",
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
