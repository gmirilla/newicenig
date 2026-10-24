{{-- Overrides the compiled Tailwind color variables from app.css — must render AFTER the @vite stylesheet tag so this wins. --}}
<style>:root { {{ $cssVariables }} }</style>
