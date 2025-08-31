/**
 * Wildtiermanagement JavaScript
 * Enthält alle Client-seitigen Funktionalitäten
 */

// Warte bis das DOM vollständig geladen ist
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeFormValidation();
});

/**
 * Initialisiert alle Event-Listener
 */
function initializeEventListeners() {
    // Revier-Auswahl für Sichtungen
    const revierSelect = document.getElementById('revier');
    if (revierSelect) {
        revierSelect.addEventListener('change', function() {
            const revierId = this.value;
            if (revierId) {
                // Erst alle Formen ausblenden
                hideAllTierForms();
                // Dann Tierauswahl anzeigen
                document.getElementById('tierauswahl').style.display = 'block';
                loadTiereForRevier(revierId, 'lebend');
            } else {
                document.getElementById('tierauswahl').style.display = 'none';
                hideAllTierForms();
            }
        });
    }

    // Revier-Auswahl für Abschüsse
    const revierAbschussSelect = document.getElementById('revierabschuss');
    if (revierAbschussSelect) {
        revierAbschussSelect.addEventListener('change', function() {
            const revierId = this.value;
            if (revierId) {
                // Erst alle Formen ausblenden
                hideAllTierForms();
                // Dann Tierauswahl anzeigen
                document.getElementById('tierauswahl').style.display = 'block';
                loadTiereForRevier(revierId, 'abschuss');
            } else {
                document.getElementById('tierauswahl').style.display = 'none';
                hideAllTierForms();
            }
        });
    }

    // Rest des Codes bleibt unverändert
    // ...
}

// Bestehende Tier-Auswahl für Sichtungen
    const bestehendestierSelect = document.getElementById('bestehendestier');
    if (bestehendestierSelect) {
        bestehendestierSelect.addEventListener('change', function() {
            handleTierSelection(this);
        });
    }
    
    // Bestehende Tier-Auswahl für Abschüsse
    const bestehendestierAbschussSelect = document.getElementById('bestehendestier');
    if (bestehendestierAbschussSelect) {
        bestehendestierAbschussSelect.addEventListener('change', function() {
            handleTierSelectionForAbschuss(this);
        });
    }

    // Abschuss Datum/Zeit Validierung
    const abschussdatumInput = document.getElementById('abschussdatum');
    const abschusszeitInput = document.getElementById('abschusszeit');
    
    if (abschussdatumInput && abschusszeitInput) {
        [abschussdatumInput, abschusszeitInput].forEach(input => {
            input.addEventListener('change', function() {
                validateAbschussDateTime();
            });
        });
    }

    // Filter-Buttons
    const filterButton = document.querySelector('button[onclick="filterData()"]');
    if (filterButton) {
        filterButton.removeAttribute('onclick');
        filterButton.addEventListener('click', filterData);
    }

    // Neue Tier-Buttons
    const neuesTierBtn = document.getElementById('neuestier-btn');
    if (neuesTierBtn) {
        neuesTierBtn.removeAttribute('onclick');
        neuesTierBtn.addEventListener('click', showNeuesTierForm);
    }

    // Tier speichern und weiter-Buttons
    const saveTierButtons = document.querySelectorAll('button[onclick="saveTierAndContinue()"]');
    saveTierButtons.forEach(button => {
        button.removeAttribute('onclick');
        button.addEventListener('click', saveTierAndContinue);
    });

    // Responsive Tabellen für mobile Ansicht
    makeTabellenResponsive();
}

/**
 * Initialisiert Formularvalidierung
 */
function initializeFormValidation() {
    // Validiere alle Formulare bei Submit
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!validateForm(this)) {
                event.preventDefault();
            }
        });
    });

    // Aktiviere Live-Validierung für alle Formularelemente
    const formElements = document.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
        element.addEventListener('blur', function() {
            validateElement(this);
        });
    });
}

/**
 * Validiert ein einzelnes Formularelement
 * @param {HTMLElement} element Das zu validierende Element
 * @returns {boolean} True wenn das Element gültig ist
 */
function validateElement(element) {
    let isValid = true;
    const errorClass = 'error';
    
    // Entferne bestehende Fehlermeldungen
    const existingError = element.parentNode.querySelector('.validation-error');
    if (existingError) {
        existingError.remove();
    }
    
    element.classList.remove(errorClass);
    
    // Prüfe ob ein Pflichtfeld leer ist
    if (element.hasAttribute('required') && !element.value.trim()) {
        isValid = false;
        showValidationError(element, 'Dieses Feld ist erforderlich');
    }
    
    // Validiere nach Elementtyp
    switch (element.type) {
        case 'date':
            if (element.value && !isValidDate(element.value)) {
                isValid = false;
                showValidationError(element, 'Bitte geben Sie ein gültiges Datum ein');
            }
            break;
            
        case 'time':
            if (element.value && !isValidTime(element.value)) {
                isValid = false;
                showValidationError(element, 'Bitte geben Sie eine gültige Zeit ein');
            }
            break;
            
        case 'url':
            if (element.value && !isValidUrl(element.value)) {
                isValid = false;
                showValidationError(element, 'Bitte geben Sie eine gültige URL ein');
            }
            break;
            
        case 'number':
            if (element.value) {
                const min = element.hasAttribute('min') ? Number(element.getAttribute('min')) : null;
                const max = element.hasAttribute('max') ? Number(element.getAttribute('max')) : null;
                const value = Number(element.value);
                
                if (isNaN(value)) {
                    isValid = false;
                    showValidationError(element, 'Bitte geben Sie eine gültige Zahl ein');
                } else if (min !== null && value < min) {
                    isValid = false;
                    showValidationError(element, `Der Wert muss größer oder gleich ${min} sein`);
                } else if (max !== null && value > max) {
                    isValid = false;
                    showValidationError(element, `Der Wert muss kleiner oder gleich ${max} sein`);
                }
            }
            break;
    }
    
    return isValid;
}

/**
 * Zeigt eine Validierungsfehlermeldung an
 * @param {HTMLElement} element Das Element mit dem Fehler
 * @param {string} message Die Fehlermeldung
 */
function showValidationError(element, message) {
    element.classList.add('error');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'validation-error';
    errorElement.textContent = message;
    errorElement.style.color = '#721c24';
    errorElement.style.fontSize = '0.8em';
    errorElement.style.marginTop = '-10px';
    errorElement.style.marginBottom = '10px';
    
    element.parentNode.insertBefore(errorElement, element.nextSibling);
}

/**
 * Validiert ein komplettes Formular
 * @param {HTMLFormElement} form Das zu validierende Formular
 * @returns {boolean} True wenn das Formular gültig ist
 */
function validateForm(form) {
    let isValid = true;
    const elements = form.querySelectorAll('input, select, textarea');
    
    elements.forEach(element => {
        if (!validateElement(element)) {
            isValid = false;
        }
    });
    
    // Spezielle Validierung für Abschussformular
    if (form.id === 'abschussform') {
        if (!validateAbschussDateTime()) {
            isValid = false;
        }
    }
    
    return isValid;
}

/**
 * Prüft ob ein String ein gültiges Datum ist
 * @param {string} dateString Zu prüfender String
 * @returns {boolean} True wenn gültiges Datum
 */
function isValidDate(dateString) {
    const regEx = /^\d{4}-\d{2}-\d{2}$/;
    if (!regEx.test(dateString)) return false;
    
    const d = new Date(dateString);
    const dNum = d.getTime();
    if (!dNum && dNum !== 0) return false;
    
    return d.toISOString().slice(0, 10) === dateString;
}

/**
 * Prüft ob ein String eine gültige Zeit ist
 * @param {string} timeString Zu prüfender String
 * @returns {boolean} True wenn gültige Zeit
 */
function isValidTime(timeString) {
    const regEx = /^([01]\d|2[0-3]):([0-5]\d)$/;
    return regEx.test(timeString);
}

/**
 * Prüft ob ein String eine gültige URL ist
 * @param {string} urlString Zu prüfende URL
 * @returns {boolean} True wenn gültige URL
 */
function isValidUrl(urlString) {
    try {
        new URL(urlString);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Globale Variable für Tierdaten (für Abschussvalidierung)
 */
let currentTierData = null;

/**
 * Lädt Tiere für ein ausgewähltes Revier
 * @param {string} revierId Die Revier-ID
 * @param {string} type Typ der Tiere ('lebend' oder 'abschuss')
 */
function loadTiereForRevier(revierId, type = 'lebend') {
    // Zeige Ladeindikator im Tierauswahl-Container
    const tiereContainer = document.getElementById('bestehende-tiere');
    if (tiereContainer) {
        tiereContainer.innerHTML = '<p>Lade Tiere...</p>';
        tiereContainer.style.display = 'block';
    }
    
    fetch('managetiere.php?revier=' + encodeURIComponent(revierId) + '&type=' + encodeURIComponent(type))
        .then(response => {
            if (!response.ok) {
                throw new Error('Netzwerkantwort war nicht OK');
            }
            return response.json();
        })
        .then(tiere => {
            currentTierData = tiere;
            const select = document.getElementById('bestehendestier');
            
            if (!select) return;
            
            select.innerHTML = '<option value="">-- Tier auswählen --</option>';
            
            if (tiere.length > 0) {
                tiere.forEach(tier => {
                    const option = document.createElement('option');
                    option.value = tier.id;
                    option.textContent = tier.display + ' (' + tier.id + ')';
                    select.appendChild(option);
                });
                
                const tiereContainer = document.getElementById('bestehende-tiere');
                if (tiereContainer) {
                    tiereContainer.style.display = 'block';
                }
            } else {
                const tiereContainer = document.getElementById('bestehende-tiere');
                if (tiereContainer) {
                    tiereContainer.innerHTML = '<p>Keine Tiere für dieses Revier gefunden.</p>';
                    tiereContainer.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Fehler beim Laden der Tiere:', error);
            
            const tiereContainer = document.getElementById('bestehende-tiere');
            if (tiereContainer) {
                tiereContainer.innerHTML = '<p>Fehler beim Laden der Tiere. Bitte versuchen Sie es erneut.</p>';
                tiereContainer.style.display = 'block';
            }
        });
}

/**
 * Handhabt die Auswahl eines Tieres für Sichtungen
 * @param {HTMLSelectElement} select Das Select-Element
 */
function handleTierSelection(select) {
    const tierID = select.value;
    if (!tierID) return;
    
    document.getElementById('tierid').value = tierID;
    const tierText = select.options[select.selectedIndex].text;
    document.getElementById('tier-display').textContent = tierText;
    document.getElementById('selected-tier-info').style.display = 'block';
    document.getElementById('sichtungsdetails').style.display = 'block';
    document.getElementById('tierformcontainer').style.display = 'none';
}

/**
 * Handhabt die Auswahl eines Tieres für Abschüsse
 * @param {HTMLSelectElement} select Das Select-Element
 */
function handleTierSelectionForAbschuss(select) {
    const tierID = select.value;
    if (!tierID) return;
    
    const selectedTier = currentTierData.find(t => t.id === tierID);
    if (!selectedTier) return;
    
    document.getElementById('tierid').value = tierID;
    const tierText = select.options[select.selectedIndex].text;
    document.getElementById('tier-display').textContent = tierText;
    
    // Zeige letzte Sichtung an
    const letzteInfo = document.getElementById('letzte-sichtung-info');
    if (letzteInfo) {
        if (selectedTier.letzte_sichtung) {
            letzteInfo.textContent = 'Letzte Sichtung: ' + selectedTier.letzte_sichtung;
            letzteInfo.style.display = 'block';
            
            // Setze Mindestdatum für Abschuss
            const [datum, zeit] = selectedTier.letzte_sichtung.split(' ');
            document.getElementById('abschussdatum').min = datum;
        } else {
            letzteInfo.style.display = 'none';
            document.getElementById('abschussdatum').min = '';
        }
    }
    
    document.getElementById('selected-tier-info').style.display = 'block';
    document.getElementById('abschussdetails').style.display = 'block';
    document.getElementById('tierformabschusscontainer').style.display = 'none';
}

/**
 * Validiert Abschussdatum und -zeit
 * @returns {boolean} True wenn gültig
 */
function validateAbschussDateTime() {
    const abschussdatum = document.getElementById('abschussdatum');
    const abschusszeit = document.getElementById('abschusszeit');
    const tierid = document.getElementById('tierid');
    
    if (!abschussdatum || !abschusszeit || !tierid || !tierid.value) {
        return true;
    }
    
    if (!abschussdatum.value || !abschusszeit.value) {
        return true;
    }
    
    if (!currentTierData) return true;
    
    const selectedTier = currentTierData.find(t => t.id === tierid.value);
    if (!selectedTier || !selectedTier.letzte_sichtung) {
        return true;
    }
    
    const abschussDateTime = new Date(abschussdatum.value + 'T' + abschusszeit.value);
    const sichtungsDateTime = new Date(selectedTier.letzte_sichtung.replace(' ', 'T'));
    
    if (abschussDateTime < sichtungsDateTime) {
        // Entferne bestehende Fehlermeldung
        const existingError = abschussdatum.parentNode.querySelector('.validation-error');
        if (existingError) {
            existingError.remove();
        }
        
        // Zeige Fehlermeldung
        showValidationError(abschussdatum, 'Der Abschuss kann nicht vor der letzten Sichtung (' + selectedTier.letzte_sichtung + ') stattgefunden haben!');
        return false;
    }
    
    return true;
}

/**
 * Zeigt/Versteckt das Tierformular für Sichtungen
 * @param {boolean} show True zum Anzeigen, False zum Verstecken
 */
function showTierForm(show) {
    const tierFormContainer = document.getElementById('tierformcontainer');
    if (tierFormContainer) {
        tierFormContainer.style.display = show ? 'block' : 'none';
    }
}

/**
 * Zeigt/Versteckt das Tierformular für Abschüsse
 * @param {boolean} show True zum Anzeigen, False zum Verstecken
 */
function showTierFormAbschuss(show) {
    const tierFormContainer = document.getElementById('tierformabschusscontainer');
    if (tierFormContainer) {
        tierFormContainer.style.display = show ? 'block' : 'none';
    }
}

/**
 * Zeigt/Versteckt die Sichtungsdetails
 * @param {boolean} show True zum Anzeigen, False zum Verstecken
 */
function showSichtungsDetails(show) {
    const sichtungsDetails = document.getElementById('sichtungsdetails');
    if (sichtungsDetails) {
        sichtungsDetails.style.display = show ? 'block' : 'none';
    }
}

/**
 * Zeigt/Versteckt die Abschussdetails
 * @param {boolean} show True zum Anzeigen, False zum Verstecken
 */
function showAbschussDetails(show) {
    const abschussDetails = document.getElementById('abschussdetails');
    if (abschussDetails) {
        abschussDetails.style.display = show ? 'block' : 'none';
    }
}

/**
 * Zeigt das Formular für ein neues Tier an
 */
function showNeuesTierForm() {
    document.getElementById('tierformcontainer').style.display = 'block';
    document.getElementById('sichtungsdetails').style.display = 'none';
    
    // Falls für Abschüsse
    const abschussDetails = document.getElementById('abschussdetails');
    if (abschussDetails) {
        abschussDetails.style.display = 'none';
    }
}

/**
 * Versteckt alle Tierformulare
 */
function hideAllTierForms() {
    const elements = [
        'tierformcontainer',
        'sichtungsdetails',
        'selected-tier-info',
        'tierformabschusscontainer',
        'abschussdetails'
    ];
    
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.style.display = 'none';
        }
    });
}

/**
 * Speichert ein neues Tier und fährt dann mit der Erfassung fort
 */
function saveTierAndContinue() {
    // Ermittle welcher Formtyp verwendet wird
    const isAbschuss = document.getElementById('tierartabschuss') !== null;
    
    const formData = new FormData();
    formData.append('revier', document.getElementById(isAbschuss ? 'revierabschuss' : 'revier').value);
    formData.append('tierart', document.getElementById(isAbschuss ? 'tierartabschuss' : 'tierart').value);
    formData.append('tiergeschlecht', document.getElementById(isAbschuss ? 'tiergeschlechtabschuss' : 'tiergeschlecht').value);
    formData.append('tieralter', document.getElementById(isAbschuss ? 'tieralterabschuss' : 'tieralter').value);
    formData.append('tierbesonderheiten', document.getElementById(isAbschuss ? 'tierbesonderheitenabschuss' : 'tierbesonderheiten').value);
    
    // Validiere Eingaben
    if (!formData.get('tierart')) {
        alert('Bitte wählen Sie eine Tierart aus.');
        return;
    }
    
    // Zeige Ladeindikator
    const button = document.querySelector('button[onclick="saveTierAndContinue()"]');
    if (button) {
        button.disabled = true;
        button.textContent = 'Speichere...';
    }
    
    fetch('managetiere.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Netzwerkantwort war nicht OK');
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            document.getElementById('tierid').value = result.tierID;
            document.getElementById('tier-display').textContent = result.tierName + ' (' + result.tierID + ')';
            document.getElementById('selected-tier-info').style.display = 'block';
            
            // Behandle spezifisch für Abschuss oder Sichtung
            if (isAbschuss) {
                document.getElementById('letzte-sichtung-info').style.display = 'none';
                document.getElementById('tierformabschusscontainer').style.display = 'none';
                document.getElementById('abschussdetails').style.display = 'block';
            } else {
                document.getElementById('tierformcontainer').style.display = 'none';
                document.getElementById('sichtungsdetails').style.display = 'block';
            }
        } else {
            alert('Fehler beim Speichern des Tieres: ' + (result.message || 'Unbekannter Fehler'));
        }
    })
    .catch(error => {
        console.error('Fehler:', error);
        alert('Fehler beim Speichern des Tieres: ' + error.message);
    })
    .finally(() => {
        // Ladeindikator zurücksetzen
        const button = document.querySelector('button[onclick="saveTierAndContinue()"]');
        if (button) {
            button.disabled = false;
            button.textContent = 'Tier speichern und weiter';
        }
    });
}

/**
 * Filtert Daten in Tabellen basierend auf ausgewählten Filtern
 */
function filterData() {
    const revierId = document.getElementById('filterrevier').value;
    const tierId = document.getElementById('filtertier').value;
    
    // Filter für Sichtungen-Tabelle
    filterTable('sichtungentable', revierId, tierId, 1, 2); // Spalte 1 = Revier, Spalte 2 = Tier
    
    // Filter für Abschüsse-Tabelle
    filterTable('abschuessetable', revierId, tierId, 1, 2); // Spalte 1 = Revier, Spalte 2 = Tier
    
    // Filter für Tiere-Tabelle
    filterTable('tieretable', revierId, tierId, 1, 2); // Spalte 1 = Revier, Spalte 2 = Tierart
}

/**
 * Filtert eine Tabelle basierend auf ausgewählten Kriterien
 * @param {string} tableId ID der Tabelle
 * @param {string} revierFilter Filter für Revier
 * @param {string} tierFilter Filter für Tier
 * @param {number} revierColumn Spaltenindex für Revier
 * @param {number} tierColumn Spaltenindex für Tier
 */
function filterTable(tableId, revierFilter, tierFilter, revierColumn, tierColumn) {
    const table = document.getElementById(tableId);
    if (!table) return;
    
    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    
    const rows = tbody.getElementsByTagName('tr');
    let visibleCount = 0;
    
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        if (cells.length > Math.max(revierColumn, tierColumn)) {
            const revierText = cells[revierColumn].textContent.trim();
            const tierText = cells[tierColumn].textContent.trim();
            
            let showRow = true;
            
            if (revierFilter && !revierText.includes(revierFilter)) {
                showRow = false;
            }
            
            if (tierFilter && !tierText.includes(tierFilter)) {
                showRow = false;
            }
            
            rows[i].style.display = showRow ? '' : 'none';
            if (showRow) visibleCount++;
        }
    }
    
    // Zeige Nachricht wenn keine Einträge gefunden wurden
    let noDataMsg = table.parentNode.querySelector('.no-data-message');
    
    if (visibleCount === 0) {
        if (!noDataMsg) {
            noDataMsg = document.createElement('p');
            noDataMsg.className = 'no-data-message';
            noDataMsg.style.fontStyle = 'italic';
            noDataMsg.style.color = '#666';
            table.parentNode.insertBefore(noDataMsg, table.nextSibling);
        }
        noDataMsg.textContent = 'Keine Einträge gefunden, die den Filterkriterien entsprechen.';
        noDataMsg.style.display = 'block';
    } else if (noDataMsg) {
        noDataMsg.style.display = 'none';
    }
}

/**
 * Macht Tabellen für mobile Ansicht responsiv
 */
function makeTabellenResponsive() {
    // Nur auf kleinen Bildschirmen
    if (window.innerWidth > 768) return;
    
    const tables = document.querySelectorAll('table:not(.no-responsive)');
    
    tables.forEach(table => {
        table.classList.add('responsive-table');
        
        const headerCells = table.querySelectorAll('thead th');
        const headerTexts = Array.from(headerCells).map(cell => cell.textContent);
        
        const bodyCells = table.querySelectorAll('tbody td');
        
        bodyCells.forEach((cell, index) => {
            const headerIndex = index % headerTexts.length;
            cell.setAttribute('data-label', headerTexts[headerIndex]);
        });
    });
}

/**
 * Initialisiert die UI-Komponenten
 */
function initializeUI() {
    // Mobile Navigation Toggle
    const navToggleBtn = document.createElement('button');
    navToggleBtn.className = 'nav-toggle';
    navToggleBtn.setAttribute('aria-label', 'Menü öffnen/schließen');
    navToggleBtn.innerHTML = '<span></span><span></span><span></span>';
    
    const nav = document.querySelector('nav');
    if (nav) {
        nav.insertBefore(navToggleBtn, nav.firstChild);
        
        navToggleBtn.addEventListener('click', function() {
            nav.classList.toggle('open');
            this.setAttribute('aria-expanded', nav.classList.contains('open'));
        });
    }
    
    // Dropdown Keyboard Navigation
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            toggle.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
                    e.preventDefault();
                    dropdown.classList.add('show');
                    menu.querySelector('a').focus();
                }
            });
            
            menu.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    dropdown.classList.remove('show');
                    toggle.focus();
                }
            });
        }
    });
    
    // Automatisches Schließen von Benachrichtigungen nach 5 Sekunden
    const messages = document.querySelectorAll('.success-message, .error-message, .warning-message, .info-message');
    messages.forEach(message => {
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                message.style.display = 'none';
            }, 500);
        }, 5000);
    });
}

// Füge den UI-Initialisierer zum DOMContentLoaded-Event hinzu
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    initializeFormValidation();
    initializeUI();
});