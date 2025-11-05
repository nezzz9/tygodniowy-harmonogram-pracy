const input = document.querySelector('#data');
const searchBtn = document.querySelector('#button1');
const resetBtn = document.querySelector('#button2');
const autoRefreshCheckbox = document.querySelector('#autoRefresh');
const showDescriptionCheckbox = document.querySelector('#showDescription');
const table = document.querySelector('table');
const searchdownload = document.querySelector('#button3');
const trWidthInput = document.querySelector('#TRwidth');
const resetWidthBtn = document.querySelector('#resetWidthBtn');
const pdfBtns = document.querySelectorAll('.pdf-btn');
const colCheckboxes = [];
for (let i = 0; i < 9; i++) {
    colCheckboxes.push(document.querySelector(`#colCheckbox${i}`));
}

const DATE_CELL_INDEX = 7;

// 🔹 Filtrowanie po dacie
function filterRows() {
    const selectedDate = input.value.trim();
    const rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index === 0) return;
        const rowDate = row.cells[DATE_CELL_INDEX]?.textContent.trim();
        row.style.display = selectedDate === '' || rowDate === selectedDate ? '' : 'none';
    });
}

// 🔹 Zapis / odczyt daty
function saveDate() {
    const selectedDate = input.value.trim();
    if (autoRefreshCheckbox.checked && selectedDate !== '') {
        localStorage.setItem('selectedDate', selectedDate);
    } else {
        localStorage.removeItem('selectedDate');
    }
}

function loadDate() {
    const savedDate = localStorage.getItem('selectedDate');
    if (savedDate && autoRefreshCheckbox.checked) {
        input.value = savedDate;
        filterRows();
    }
}

// 🔹 Reset
function resetAll() {
    input.value = '';
    localStorage.removeItem('selectedDate');
    filterRows();
}

// 🔹 Zapis / odczyt stanu checkboxów
function saveCheckboxState() {
    localStorage.setItem('checkboxState', autoRefreshCheckbox.checked ? 'true' : 'false');
    localStorage.setItem('showDescriptionState', showDescriptionCheckbox.checked ? 'true' : 'false');
}

// 🔹 Zmiana szerokości pól tabeli
function updateTableWidth() {
    const width = trWidthInput.value.trim();
    if (width && !isNaN(width)) {
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                if (colCheckboxes[index] && colCheckboxes[index].checked) {
                    cell.style.width = width + 'px';
                    cell.style.fontSize = (width / 10) + 'px'; // Proporcjonalna zmiana rozmiaru czcionki
                    pdfBtns.forEach(btn => {
                    btn.style.fontSize = (parseInt(width) / 10) + 'px'; // Proporcjonalna zmiana rozmiaru czcionki przycisków PDF
                });
                }
            });
        });
        // Zmiana szerokości całej tabeli - dostosuj tylko jeśli wszystkie kolumny są zaznaczone
        const allChecked = colCheckboxes.every(cb => cb.checked);
        if (allChecked) {
            table.style.width = (parseInt) / 10 * colCheckboxes.length + 'px';
        } else {
            table.style.width = ''; // Reset if not all columns are selected
        }

    }
}

function loadCheckboxState() {
    autoRefreshCheckbox.checked = localStorage.getItem('checkboxState') === 'true';
    showDescriptionCheckbox.checked = localStorage.getItem('showDescriptionState') === 'true';
}

// 🔹 Przygotowanie opisów (owinięcie <span class="opis">)
function prepareDescriptions() {
    const zadanieCells = table.querySelectorAll('tr td:nth-child(5)');
    zadanieCells.forEach(cell => {
        if (cell.dataset.prepared) return; // unikamy ponownego owijania
        const html = cell.innerHTML;
        if (html.includes('Opis:')) {
            const parts = html.split(/<br\s*\/?>Opis:<br\s*\/?>/i);
            if (parts.length === 2) {
                cell.innerHTML = `${parts[0]}<br><span class="opis">Opis:<br>${parts[1]}</span>`;
                cell.dataset.prepared = 'true';
            }
        }
    });
}

// 🔹 Pokazywanie / ukrywanie opisów bez odświeżania
function toggleDescriptions() {
    const show = showDescriptionCheckbox.checked;
    const opisElements = table.querySelectorAll('.opis');
    opisElements.forEach(el => {
        el.style.display = show ? 'inline' : 'none';
    });
    saveCheckboxState();
}

// 🔹 PDF generowanie
function generatePDF(data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFont('Helvetica', 'bold');
    doc.setFontSize(18);
    doc.text('Szczegóły zadania', 20, 20);

    doc.setFont('Helvetica', 'normal');
    doc.setFontSize(12);

    const info = [
        `Imię: ${data.imie}`,
        `Nazwisko: ${data.nazwisko}`,
        `E-mail: ${data.email}`,
        `Zadanie: ${data.zadanie}`,
        `Czas rozpoczęcia: ${data.rozpoczecie}`,
        `Czas zakończenia: ${data.zakonczenie}`,
        `Data: ${data.data}`,
    ];

    let y = 30;
    info.forEach(line => {
        doc.text(line, 20, y);
        y += 10;
    });

    const fileName = `Zadanie - ${data.imie} - ${data.nazwisko} - ${data.data}.pdf`;
    doc.save(fileName);
}

// 🔹 Obsługa przycisków
searchBtn.addEventListener('click', () => {
    if (autoRefreshCheckbox.checked) saveDate();
    filterRows();
});

resetBtn.addEventListener('click', resetAll);

autoRefreshCheckbox.addEventListener('change', () => {
    saveCheckboxState();
    saveDate();
});

showDescriptionCheckbox.addEventListener('change', () => {
    toggleDescriptions(); // teraz działa natychmiast
});

trWidthInput.addEventListener('input', updateTableWidth);

// Add event listeners for column checkboxes
colCheckboxes.forEach((checkbox, index) => {
    checkbox.addEventListener('change', () => {
        if (checkbox.checked) {
            updateTableWidth(); // Apply width to this column
        } else {
            // Reset this column to default
            const rows = table.querySelectorAll('tr');
            rows.forEach(row => {
                const cell = row.querySelectorAll('td')[index];
                if (cell) {
                    cell.style.width = '';
                    cell.style.fontSize = '';
                }
            });
            // Check if all checkboxes are unchecked, reset table width
            const anyChecked = colCheckboxes.some(cb => cb.checked);
            if (!anyChecked) {
                table.style.width = '';
                pdfBtns.forEach(btn => {
                    btn.style.fontSize = '';
                });
            }
        }
    });
});

resetWidthBtn.addEventListener('click', () => {
    // Reset table to default widths by removing custom styles
    const cells = table.querySelectorAll('td');
    cells.forEach(cell => {
        cell.style.width = '';
        cell.style.fontSize = '';
    });
    table.style.width = '';
    pdfBtns.forEach(btn => {
        btn.style.fontSize = '';
    });
    trWidthInput.value = '130';
    // Reset all checkboxes to checked
    colCheckboxes.forEach(cb => cb.checked = true);
});

searchdownload.addEventListener('click', () => {
    saveDate();
    filterRows();

    const rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index === 0) return;
        if (row.style.display === 'none') return;

        const data = {
            imie: row.cells[1].textContent.trim(),
            nazwisko: row.cells[2].textContent.trim(),
            email: row.cells[3].textContent.trim(),
            zadanie: row.cells[4].textContent.trim(),
            rozpoczecie: row.cells[5].textContent.trim(),
            zakonczenie: row.cells[6].textContent.trim(),
            data: row.cells[7].textContent.trim(),
        };
        generatePDF(data);
    });
});

// 🔹 Inicjalizacja po załadowaniu strony
window.addEventListener('load', () => {
    loadCheckboxState();
    loadDate();
    prepareDescriptions();
    toggleDescriptions();

    // 🔹 Obsługa indywidualnych przycisków PDF
    
    pdfBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('tr');
            const data = {
                imie: row.cells[1].textContent.trim(),
                nazwisko: row.cells[2].textContent.trim(),
                email: row.cells[3].textContent.trim(),
                zadanie: row.cells[4].textContent.trim(),
                rozpoczecie: row.cells[5].textContent.trim(),
                zakonczenie: row.cells[6].textContent.trim(),
                data: row.cells[7].textContent.trim(),
            };
            generatePDF(data);
        });
    });
});

input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        if (autoRefreshCheckbox.checked) saveDate();
        filterRows();
    }
});
    