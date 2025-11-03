const input = document.querySelector('#data');
const searchBtn = document.querySelector('#button1');
const resetBtn = document.querySelector('#button2');
const tableBody = document.querySelector('table tbody');

function filterRows() {
    const selectedDate = input.value.trim();
    localStorage.setItem('selectedDate', selectedDate);
    const rows = tableBody.querySelectorAll('tr');
    rows.forEach((row, index) => {
        if(index === 0) return;
        const rowDate = row.cells[6].textContent.trim();
        row.style.display = selectedDate === '' || rowDate === selectedDate ? '' : 'none';
    });
}

searchBtn.addEventListener('click', filterRows);

resetBtn.addEventListener('click', () => {
    input.value = '';
    localStorage.removeItem('selectedDate');
    filterRows();
});

window.addEventListener('load', () => {
    const savedDate = localStorage.getItem('selectedDate');
    if(savedDate) {
        input.value = savedDate;
        filterRows();
    }
});
