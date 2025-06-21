<script>
    function setupSearchFilter({
        filterColumnSelector,
        filterInputSelector,
        data,
        onFilter,
        customColumnHandler = null
    }) {
        const columnSelect = document.querySelector(filterColumnSelector);
        const input = document.querySelector(filterInputSelector);

        if (!columnSelect || !input) return;

        columnSelect.addEventListener('change', () => {
            input.disabled = !columnSelect.value;
            input.value = '';
            onFilter(data); // Reset to full data
        });

        input.addEventListener('input', () => {
            const selectedColumn = columnSelect.value;
            const inputVal = input.value.toLowerCase();

            if (!selectedColumn || !inputVal) {
                onFilter(data); // Show all
                return;
            }

            const filtered = data.filter(row => {
                let val = row[selectedColumn] ?? '';

                if (customColumnHandler && customColumnHandler[selectedColumn]) {
                    val = customColumnHandler[selectedColumn](row);
                }

                return val.toString().toLowerCase().includes(inputVal);
            });

            onFilter(filtered);
        });
    }
</script>