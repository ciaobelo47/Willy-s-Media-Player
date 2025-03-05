document.getElementById('overlay').addEventListener('click', (event) => {
    if (event.target === document.getElementById('overlay')) {
        if (document.getElementById('overlay').style.display == 'none') {
            document.getElementById('overlay').style.display = 'block';
        } else {
            document.getElementById('overlay').style.display = 'none';
        }
    }
});

document.getElementById('linkLabel').addEventListener('click', () => {
    document.getElementById('overlay').style.display = 'block';
});

document.getElementById('file').addEventListener('change', () => {
    document.getElementById('submitFile').click();
});