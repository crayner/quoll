let tailwindcss = require('tailwindcss');

module.exports = {
    plugins: [
        tailwindcss('tailwind.js'), // your tailwind.js configuration file path
        require('autoprefixer'),
    ]
}
