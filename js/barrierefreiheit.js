document.addEventListener('DOMContentLoaded', function () {
    const lastCacheClear = parseInt(localStorage.getItem('lastBFCacheClear') || '0');
    const lastKnownClear = parseInt(BarrierefreiheitSettings.lastCacheClear);
    var closable = false, closing = false;
    

    console.log(BarrierefreiheitSettings);

    if (lastKnownClear > lastCacheClear) {
        const keysToClear = ['barrierefreieSettings'];
        keysToClear.forEach(key => {
            localStorage.removeItem(key);
        });

        localStorage.setItem('lastBFCacheClear', lastKnownClear);
        console.log('Ausgewählte LocalStorage Einstellungen wurden geleert.');
    }

    function setLocal(settings) {
        localStorage.setItem('barrierefreieSettings', JSON.stringify(settings));
    }

    function getLocal() {
        const settings = localStorage.getItem('barrierefreieSettings');
        return settings ? JSON.parse(settings) : {
            originalElements: {},
            contrastElements: {},
            isContrastMode: false
        };
    }

    var root = document.documentElement;
    var settings = getLocal();
    var currentFontSize = parseFloat(window.getComputedStyle(root).fontSize);
    let contrastIndex = 0;
    const contrastDataAttr = "data-contrast-index";

    if (!settings.defaultFontSize) {
        settings.defaultFontSize = currentFontSize;
        setLocal(settings);
    }

    window.resetColor = function (variableName) {
        document.body.style.removeProperty('--e-global-color-' + variableName);
        var defaultColor = settings[variableName + 'DefaultColor'];
        document.getElementById(variableName + '-color').value = defaultColor;
        settings[variableName + 'Color'] = defaultColor;
        setLocal(settings);
    };

    window.resetFontSize = function () {
        currentFontSize = settings.defaultFontSize;
        changeFontSize(1);
    }

    window.toggleContrastMode = function () {
        let isContrastMode = settings['isContrastMode'];
        document.querySelectorAll(`[${contrastDataAttr}]`).forEach(el => {
            let index = el.getAttribute(contrastDataAttr);
            let colors = isContrastMode ? settings.originalElements[index] : settings.contrastElements[index];
            if (colors) {
                el.style.color = colors.text;
                el.style.backgroundColor = colors.background;
            }
        });

        settings['isContrastMode'] = !isContrastMode;
        setLocal(settings);
    }

    function changeFontSize(factor) {
        currentFontSize *= factor;
        root.style.fontSize = currentFontSize + 'px';
        settings.currentFontSize = currentFontSize;
        setLocal(settings);
    }

    function setColor(toChange) {
        if (settings[toChange + 'Color']) return;
        var color = getComputedStyle(document.getElementById('get_colors')).getPropertyValue('--e-global-color-' + toChange).trim();
        document.getElementById(toChange + '-color').value = color;
        settings[toChange + 'DefaultColor'] = color;
        setLocal(settings);
    }

    function isElementVisible(element) {
        if (!element) return false;

        while (element) {
            const style = window.getComputedStyle(element);
            if (style.display === 'none' || style.visibility === 'hidden') return false;
            element = element.parentElement;
        }
        return true;
    }

    window.resetAll = function () {
        Object.keys(settings).forEach(function (key) {
            if (key.includes('DefaultColor')) {
                var variableName = key.replace('DefaultColor', '');
                resetColor(variableName);
            }
        });
        resetFontSize();
    }

    if (BarrierefreiheitSettings.fontSizeEnabled === "1") {
        if (settings.currentFontSize) {
            currentFontSize = parseFloat(settings.currentFontSize);
            root.style.fontSize = settings.currentFontSize + 'px';
        }

        document.getElementById('increase-font-size').addEventListener('click', function () {
            changeFontSize(1.1);
        });
        document.getElementById('decrease-font-size').addEventListener('click', function () {
            changeFontSize(0.9);
        });
    }

    if (BarrierefreiheitSettings.colorChoiceEnabled === "1") {
        ['primary', 'secondary', 'accent', 'text'].forEach(function (colorName) {
            if (settings[colorName + 'Color']) {
                var storedColor = settings[colorName + 'Color'];
                document.body.style.setProperty('--e-global-color-' + colorName, storedColor, 'important');
                document.getElementById(colorName + '-color').value = storedColor;
            }
        });

        setTimeout(() => {
            setColor('primary');
            setColor('secondary');
            setColor('accent');
            setColor('text');
        }, 250);

        document.getElementById('primary-color').addEventListener('input', function (e) {
            settings.primaryColor = this.value;
            document.body.style.setProperty('--e-global-color-primary', this.value, 'important');
            setLocal(settings);
        });
        document.getElementById('secondary-color').addEventListener('input', function () {
            settings.secondaryColor = this.value;
            document.body.style.setProperty('--e-global-color-secondary', this.value, 'important');
            setLocal(settings);
        });
        document.getElementById('accent-color').addEventListener('input', function () {
            settings.accentColor = this.value;
            document.body.style.setProperty('--e-global-color-accent', this.value, 'important');
            setLocal(settings);
        });
        document.getElementById('text-color').addEventListener('input', function () {
            settings.textColor = this.value;
            document.body.style.setProperty('--e-global-color-text', this.value, 'important');
            setLocal(settings);
        });
    }

    if (BarrierefreiheitSettings.tocEnabled === "1") {

        var headers = document.querySelectorAll(BarrierefreiheitSettings.tocTag);
        var tocList = document.getElementById('tocList');

        headers.forEach(function (header, index) {
            if (isElementVisible(header)) {
                var listItem = document.createElement('li');
                var link = document.createElement('a');
                link.textContent = header.textContent;
                link.href = "#header" + index;
                header.id = "header" + index;
                listItem.appendChild(link);

                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    document.getElementById(header.id).scrollIntoView({ behavior: 'smooth' });
                });

                tocList.appendChild(listItem);
            }
        });
    }

    document.addEventListener('click', function (event) {
        var popup = document.getElementById('barrierefreiheit-popup');
        var closeButton = document.getElementById('close-popup');

        if (!popup.contains(event.target) && popup.classList.contains('active')) {
            closeButton.click();
        }
    });

    document.getElementById('barrierefreiheit-icon').addEventListener('click', function () {
        if (closing) return;

        popup = document.getElementById('barrierefreiheit-popup');
        popup.classList.add('active');
        setTimeout(() => {
            popup.style.transform = 'translateY(0)';
            document.getElementById('barrierefreiheit-icon').style.display = 'none';
            closable = true;
        }, 10)
    });
    document.getElementById('close-popup').addEventListener('click', function () {
        if (!closable) return;

        popup = document.getElementById('barrierefreiheit-popup');
        popup.style.transform = 'translateY(-100%)';
        closable = false;
        closing = true;
        setTimeout(() => {
            popup.classList.remove('active');
            closing = false;
            document.getElementById('barrierefreiheit-icon').style.removeProperty('display');
        }, 300)
    });

    if (BarrierefreiheitSettings.contrastEnabled === "1") {
        function getLuminance(rgb) {
            let a = rgb.map(function (v) {
                v /= 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
        }

        function getContrastRatio(rgb1, rgb2) {
            let lum1 = getLuminance(rgb1);
            let lum2 = getLuminance(rgb2);
            let brightest = Math.max(lum1, lum2);
            let darkest = Math.min(lum1, lum2);
            return (brightest + 0.05) / (darkest + 0.05);
        }

        function parseColor(input) {
            var m;
            if ((m = input.match(/^#([0-9a-f]{6})$/i)) !== null) {
                return [
                    parseInt(m[1].substr(0, 2), 16),
                    parseInt(m[1].substr(2, 2), 16),
                    parseInt(m[1].substr(4, 2), 16)
                ];
            } else if ((m = input.match(/^rgb\s*\(\s*(\d+),\s*(\d+),\s*(\d+)\s*\)$/)) !== null) {
                return [parseInt(m[1]), parseInt(m[2]), parseInt(m[3])];
            } else if ((m = input.match(/^rgba\s*\(\s*(\d+),\s*(\d+),\s*(\d+),\s*([0-9\.]+)\s*\)$/)) !== null) {
                return [parseInt(m[1]), parseInt(m[2]), parseInt(m[3])];
            }
            return null;
        }


        function getEffectiveBackgroundColor(element) {
            let backgroundColor = window.getComputedStyle(element).backgroundColor;
            while (element.parentElement && backgroundColor === 'rgba(0, 0, 0, 0)') {
                element = element.parentElement;
                backgroundColor = window.getComputedStyle(element).backgroundColor;
            }
            return backgroundColor;
        }

        function adjustColorForContrast(textRgb, backgroundRgb) {
            let contrastRatio = getContrastRatio(textRgb, backgroundRgb);
            if (contrastRatio >= 4.5) return null;

            let adjustedTextRgb = [...textRgb];
            let adjustedBackgroundRgb = [...backgroundRgb];
            const step = 10;

            while (contrastRatio < 4.5) {
                if (getLuminance(adjustedTextRgb) > getLuminance(adjustedBackgroundRgb)) {
                    adjustedTextRgb = adjustedTextRgb.map(c => Math.min(c + step, 255));
                    adjustedBackgroundRgb = adjustedBackgroundRgb.map(c => Math.max(c - step, 0));
                } else {
                    adjustedTextRgb = adjustedTextRgb.map(c => Math.max(c - step, 0));
                    adjustedBackgroundRgb = adjustedBackgroundRgb.map(c => Math.min(c + step, 255));
                }
                contrastRatio = getContrastRatio(adjustedTextRgb, adjustedBackgroundRgb);
            }

            return {
                text: `rgb(${adjustedTextRgb.join(", ")})`,
                background: `rgb(${adjustedBackgroundRgb.join(", ")})`
            };
        }

        function applyContrastImprovements(el, textColor, backgroundColor) {
            let originalColor = { text: '', background: '' };
            let textRgb = parseColor(textColor);
            let backgroundRgb = parseColor(backgroundColor);
            let newColors = adjustColorForContrast(textRgb, backgroundRgb);

            if (newColors) {
                if (!el.hasAttribute(contrastDataAttr)) {
                    el.setAttribute(contrastDataAttr, contrastIndex++);
                }
                let index = el.getAttribute(contrastDataAttr);
                settings['originalElements'][index] = originalColor;
                settings['contrastElements'][index] = newColors;

                setLocal(settings);
            }
        }

        function checkPageContrast() {
            let elements = document.querySelectorAll('body *');
            elements.forEach(el => {
                if (!isElementVisible(el)) return;

                let textColor = window.getComputedStyle(el).color;
                let backgroundColor = getEffectiveBackgroundColor(el);
                applyContrastImprovements(el, textColor, backgroundColor);
            });
        }

        checkPageContrast();
        if (settings['isContrastMode']) {
            settings['isContrastMode'] = !settings['isContrastMode'];
            setTimeout(() => {
                toggleContrastMode();
                document.getElementById('contrast-toggle').checked = true;
            }, 250);
        }
    }
});
