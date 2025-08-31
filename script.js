// Dynamische Auswahlmenüs und Datenanzeige
document.addEventListener('DOMContentLoaded', function() {
    // Revier-Auswahl für Sichtungen
    const revierSelect = document.getElementById('revier');
    if (revierSelect) {
        revierSelect.addEventListener('change', function() {
            const revierId = this.value;
            if (revierId) {
                showTierForm(true);
                showSichtungsDetails(true);
            } else {
                showTierForm(false);
                showSichtungsDetails(false);
            }
        });
    }

    // Revier-Auswahl für Abschüsse
    const revierAbschussSelect = document.getElementById('revierabschuss');
    if (revierAbschussSelect) {
        revierAbschussSelect.addEventListener('change', function() {
            const revierId = this.value;
            if (revierId) {
                showTierFormAbschuss(true);
                showAbschussDetails(true);
            } else {
                showTierFormAbschuss(false);
                showAbschussDetails(false);
            }
        });
    }
});

// Filter für Übersicht
window.filterData = function() {
    const revierId = document.getElementById('filterrevier').value;
    const tierId = document.getElementById('filtertier').value;
    
    // Filter für Sichtungen-Tabelle
    filterTable('sichtungentable', revierId, tierId, 1, 2); // Spalte 1 = Revier, Spalte 2 = Tier
    
    // Filter für Abschüsse-Tabelle  
    filterTable('abschuessetable', revierId, tierId, 1, 2); // Spalte 1 = Revier, Spalte 2 = Tier
}

// Hilfsfunktion zum Filtern von Tabellen
function filterTable(tableId, revierFilter, tierFilter, revierColumn, tierColumn) {
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        if (cells.length > Math.max(revierColumn, tierColumn)) {
            const revierText = cells[revierColumn].textContent.trim();
            const tierText = cells[tierColumn].textContent.trim();
            
            let showRow = true;
            
            if (revierFilter && !revierText.includes(revierFilter)) {
                showRow = false;
            }
            
            if (tierFilter && tierText !== tierFilter) {
                showRow = false;
            }
            
            rows[i].style.display = showRow ? '' : 'none';
        }
    }
}

// Tier-Auswahl für Sichtungen
window.showTierForm = function(show) {
    const tierFormContainer = document.getElementById('tierformcontainer');
    if (tierFormContainer) {
        tierFormContainer.style.display = show ? 'block' : 'none';
    }
}

// Tier-Auswahl für Abschüsse
window.showTierFormAbschuss = function(show) {
    const tierFormContainer = document.getElementById('tierformabschusscontainer');
    if (tierFormContainer) {
        tierFormContainer.style.display = show ? 'block' : 'none';
    }
}

// Sichtungserfassung anzeigen
window.showSichtungsDetails = function(show) {
    const sichtungsDetails = document.getElementById('sichtungsdetails');
    if (sichtungsDetails) {
        sichtungsDetails.style.display = show ? 'block' : 'none';
    }
}

// Abschusserfassung anzeigen
window.showAbschussDetails = function(show) {
    const abschussDetails = document.getElementById('abschussdetails');
    if (abschussDetails) {
        abschussDetails.style.display = show ? 'block' : 'none';
    }
}