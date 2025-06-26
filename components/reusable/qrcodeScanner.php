<!-- Reusable Modal for Selecting User -->
<div class="modal fade" id="accountSelectModal" tabindex="-1" aria-labelledby="accountSelectLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="accountSelectLabel">Select Account</h5>
            </div>
            <div class="modal-body">
                <input type="text" id="account-search" class="form-control mb-3" placeholder="Search by name or ID...">
                <div id="account-list" style="max-height: 400px; overflow-y: auto;">
                    <p class="text-muted text-center">Loading accounts...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function scanQRCodeForUser({
        onSuccess,
        onCancel,
        section = null,
        role = null
    }) {
        const modalElement = document.getElementById('accountSelectModal');
        const searchInput = document.getElementById('account-search');
        const listContainer = document.getElementById('account-list');
        const modal = new bootstrap.Modal(modalElement);

        modal.show();
        listContainer.innerHTML = `<p class="text-muted text-center">Loading accounts...</p>`;
        searchInput.value = '';

        let accountData = [];

        fetch('/mes/api/accounts/getAccounts.php')
            .then(res => res.json())
            .then(data => {
                if (section) {
                    accountData = data.filter(acc => {
                        const okSection = !section || (acc.production ?? '').toUpperCase() === section.toUpperCase();
                        const okRole = !role || (Array.isArray(role) ?
                            role.map(r => r.toUpperCase()).includes((acc.role ?? '').toUpperCase()) :
                            (acc.role ?? '').toUpperCase() === role.toUpperCase());
                        return okSection && okRole;
                    });

                } else {
                    accountData = data;
                }
                renderAccountList(accountData);
            })
            .catch(err => {
                listContainer.innerHTML = `<p class="text-danger text-center">Failed to load accounts.</p>`;
            });

        // Render list
        function renderAccountList(data) {
            if (!data.length) {
                listContainer.innerHTML = `<p class="text-muted text-center">No accounts found.</p>`;
                return;
            }

            listContainer.innerHTML = data.map(acc => `
        <button class="list-group-item list-group-item-action" data-userid="${acc.user_id}" data-name="${acc.name}">
          <strong>${acc.name}</strong><br><small>ID: ${acc.user_id}</small>
        </button>
      `).join('');
        }

        // Handle selection
        listContainer.addEventListener('click', e => {
            if (e.target.closest('button')) {
                const btn = e.target.closest('button');
                const user_id = btn.dataset.userid;
                const full_name = btn.dataset.name;
                if (onSuccess) onSuccess({
                    user_id,
                    full_name
                });
                modal.hide();
            }
        });

        // Handle search
        searchInput.addEventListener('input', () => {
            const query = searchInput.value.toLowerCase();
            const filtered = accountData.filter(acc =>
                acc.name.toLowerCase().includes(query) || acc.user_id.toLowerCase().includes(query)
            );
            renderAccountList(filtered);
        });

        // On modal close
        modalElement.addEventListener('hidden.bs.modal', () => {
            if (onCancel) onCancel();
            listContainer.innerHTML = '';
        }, {
            once: true
        });
    }
</script>