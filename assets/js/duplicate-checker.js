// Duplicate Checker JavaScript
function openInVSCode(filePath) {
    // Try multiple VS Code URL formats
    const formats = [
        'vscode://file/' + encodeURI(filePath),
        'vscode://file' + filePath,
        'code://file/' + encodeURI(filePath)
    ];
    
    // Try each format
    let opened = false;
    formats.forEach(function(url, index) {
        if (!opened) {
            try {
                const link = document.createElement('a');
                link.href = url;
                link.click();
                console.log('Trying format ' + (index + 1) + ': ' + url);
                opened = true;
            } catch (e) {
                console.log('Format ' + (index + 1) + ' failed: ' + e.message);
            }
        }
    });
    
    // Always copy to clipboard as backup
    if (navigator.clipboard) {
        navigator.clipboard.writeText(filePath).then(function() {
            alert('File path copied to clipboard: ' + filePath + '\n\nIf VS Code didn\'t open, paste this path in VS Code File > Open');
        });
    } else {
        alert('File path: ' + filePath + '\n\nCopy this path and open in VS Code');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('TradePress Duplicate Checker loaded');
});