<div>
    <button class="submit">Click</button>
    <table class='tables' hidden border="1">
        <tr>
            <td>First Name</td>
            <td>Last Name</td>
        </tr>
    </table>
</div>

<script>
let firstName = "RON";
let lastName = "DEL MUNDO";
let data = [firstName, lastName];  // use correct variable names

const submit = document.querySelector('.submit');

submit.addEventListener('click', (e) => {
    e.preventDefault();

    const tables = document.querySelector('.tables');

    // Show the table
    tables.hidden = false;

    // Create a new row
    const newRow = document.createElement('tr');

    // Loop over the data array and create cells
    data.forEach(value => {
        const newCell = document.createElement('td');
        newCell.textContent = value;
        newRow.appendChild(newCell);
    });

    // Append the new row to the table
    tables.appendChild(newRow);
});
</script>
