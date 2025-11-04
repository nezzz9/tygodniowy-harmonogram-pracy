const input = document.querySelector('#data');
const searchBtn = document.querySelector('#button1');
const resetBtn = document.querySelector('#button2');
const autoRefreshCheckbox = document.querySelector('#autoRefresh');
const table = document.querySelector('table');
const searchdownload = document.querySelector('#button3');

//  Filtrowanie tabeli 
function filterRows() {
    const selectedDate = input.value.trim();
    const rows = table.querySelectorAll('tr');

    rows.forEach((row, index) => {
        if (index === 0) return;
        const rowDate = row.cells[6].textContent.trim();
        row.style.display = selectedDate === '' || rowDate === selectedDate ? '' : 'none';
    });
}

//  Zapis daty do localStorage tylko jeśli checkbox zaznaczony 
function saveDate() {
    const selectedDate = input.value.trim();
    if (autoRefreshCheckbox.checked && selectedDate !== '') {
        localStorage.setItem('selectedDate', selectedDate);
    } else {
        localStorage.removeItem('selectedDate');
    }
}

//  Wczytanie daty 
function loadDate() {
    const savedDate = localStorage.getItem('selectedDate');
    if (savedDate && autoRefreshCheckbox.checked) {
        input.value = savedDate;
        filterRows();
    }
}

//  Reset 
function resetAll() {
    input.value = '';
    localStorage.removeItem('selectedDate');
    filterRows();
}

// Zapis i odczyt stanu checkboxa 
function saveCheckboxState() {
    localStorage.setItem('checkboxState', autoRefreshCheckbox.checked ? 'true' : 'false');
}

function loadCheckboxState() {
    const saved = localStorage.getItem('checkboxState');
    autoRefreshCheckbox.checked = saved === 'true';
}


function attachRowClickEvents() {
    const rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index === 0) return;
        const pdfButton = row.querySelector('.pdf-btn');
        if (!pdfButton) return;

        pdfButton.addEventListener('click', () => {
            const data = {
                imie: row.cells[0].textContent.trim(),
                nazwisko: row.cells[1].textContent.trim(),
                email: row.cells[2].textContent.trim(),
                zadanie: row.cells[3].textContent.trim(),
                rozpoczecie: row.cells[4].textContent.trim(),
                zakonczenie: row.cells[5].textContent.trim(),
                data: row.cells[6].textContent.trim(),
            };
            generatePDF(data);
        });
    });
}

// PDF
function generatePDF(data) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFont('arial', 'bold');
    doc.setFontSize(18);
    doc.text('Szczegoly zadania', 20, 20);

    doc.setFont('arial', 'normal');
    doc.setFontSize(12);

    const info = [
        `Imie: ${data.imie}`,
        `Nazwisko: ${data.nazwisko}`,
        `E-mail: ${data.email}`,
        `Zadanie: ${data.zadanie}`,
        `Czas rozpoczecia: ${data.rozpoczecie}`,
        `Czas zakonczenia: ${data.zakonczenie}`,
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

//  Obsługa zdarzeń 
searchBtn.addEventListener('click', () => {
    if(autoRefreshCheckbox.checked) saveDate();
    filterRows();
});

resetBtn.addEventListener('click', resetAll);

autoRefreshCheckbox.addEventListener('change', () => {
    saveCheckboxState();
    saveDate();
});
searchdownload.addEventListener('click', () => {
    saveDate();
    filterRows();

    const rows = table.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if (index === 0) return; // pomijamy nagłówek
        if (row.style.display === 'none') return; // tylko widoczne wiersze

        const data = {
            imie: row.cells[0].textContent.trim(),
            nazwisko: row.cells[1].textContent.trim(),
            email: row.cells[2].textContent.trim(),
            zadanie: row.cells[3].textContent.trim(),
            rozpoczecie: row.cells[4].textContent.trim(),
            zakonczenie: row.cells[5].textContent.trim(),
            data: row.cells[6].textContent.trim(),
        };

        generatePDF(data);
    });
});
//  Po załadowaniu strony 
window.addEventListener('load', () => {
    loadCheckboxState();
    loadDate();
    attachRowClickEvents();
});
input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        if(autoRefreshCheckbox.checked) saveDate();
        filterRows();
    }}
);