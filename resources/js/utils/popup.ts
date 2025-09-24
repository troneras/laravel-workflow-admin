/**
 * Open a URL in a popup window with specified dimensions and features
 */
export function openPopup(url: string, title: string = 'Popup', width: number = 1200, height: number = 800) {
    const left = Math.max(0, (screen.width - width) / 2);
    const top = Math.max(0, (screen.height - height) / 2);

    const features = [
        `width=${width}`,
        `height=${height}`,
        `left=${left}`,
        `top=${top}`,
        'scrollbars=yes',
        'resizable=yes',
        'toolbar=no',
        'menubar=no',
        'location=no',
        'status=no'
    ].join(',');

    const popup = window.open(url, title, features);

    // Focus the popup if it was successfully opened
    if (popup) {
        popup.focus();
    }

    return popup;
}

/**
 * Open Dify interface in a popup window
 */
export function openDifyPopup(difyUrl: string) {
    return openPopup(difyUrl, 'Dify Interface', 1400, 900);
}

/**
 * Open Horizon queue monitor in a popup window
 */
export function openHorizonPopup(horizonUrl: string = '/horizon') {
    return openPopup(horizonUrl, 'Queue Monitor', 1200, 800);
}