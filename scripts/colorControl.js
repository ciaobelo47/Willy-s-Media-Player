//EXPERIMENTAL
const colorInput = document.getElementById('mainColor');
const root = document.documentElement;

colorInput.addEventListener('input', () => {
    root.style.setProperty('--background_color', colorInput.value);
});

/**
 * This is probably going to disappear in the future.
 */
function experimentalWarn() {
    console.warn('[ColorControl] This feature is experimental and may not work as expected.');
}