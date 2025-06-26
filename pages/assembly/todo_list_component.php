<?php
session_start();
$role = $_SESSION['role'];
$production = $_SESSION['production'];
$production_location = $_SESSION['production_location'];
?>
<?php include './components/reusable/tablesorting.php'; ?>
<?php include './components/reusable/tablepagination.php'; ?>
<?php include './components/reusable/qrcodeScanner.php'; ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/sweetalert2@11.js"></script>
<script src="assets/js/html5.qrcode.js"></script>


<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="#">Pages</a></li>
            <li class="breadcrumb-item" aria-current="page">Stamping Manpower Section</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="card-title mb-0">To-do List</h6>
                        <small id="last-updated" class="text-muted" style="font-size:13px;"></small>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select id="filter-column" class="form-select">
                                <option value="" disabled selected>Select Column to Filter</option>
                                <option value="material_no">Material No</option>
                                <option value="components_name">Material Description</option>
                                <option value="stage_name">Process</option>
                                <option value="total_quantity">Total Quantity</option>
                                <option value="person_incharge">Person Incharge</option>
                                <option value="time_in">Time In</option>
                                <option value="time_out">Time Out</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input
                                type="text"
                                id="filter-input"
                                class="form-control"
                                placeholder="Type to filter..."
                                disabled />
                        </div>
                    </div>

                    <table class="table table" style="table-layout: fixed; width: 100%;">
                        <thead>
                            <tr>


                                <th style="width: 10%; text-align: center;">Material Description</th>
                                <th style="width: 5%; text-align: center;">Section</th>
                                <th style="width: 5%; text-align: center;">Process</th>
                                <th style="width: 5%; text-align: center;">Total Qty</th>
                                <th style="width: 5%; text-align: center;">Pending Qty</th>
                                <th style="width: 10%; text-align: center;">Person Incharge</th>
                                <th style="width: 7%; text-align: center;">Time</th>
                                <th style="width: 7%; text-align: center;">Action</th>
                                <th style="width: 5%; text-align: center;">View</th>
                            </tr>
                        </thead>
                        <tbody id="data-body"></tbody>
                    </table>
                    <div id="pagination" class="mt-3 d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="qrModalLabel">Scan QR Code</h5>
                </div>
                <div class="modal-body">
                    <div id="qr-reader" style="width: 100%"></div>
                    <div id="qr-result" class="mt-3 text-center fw-bold text-success"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="quantityModalLabel">Enter Quantity</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="timeoutQuantity" class="form-label">Quantity to Process</label>
                        <input type="number" class="form-control" id="timeoutQuantity" min="1" value="1">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmQuantityBtn">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let mode = null;
        let selectedRowData = null;
        let fullData = null;
        let paginator = null;
        let section = '';


        const filterColumnSelect = document.getElementById('filter-column');
        const filterInput = document.getElementById('filter-input');
        const dataBody = document.getElementById('data-body');

        const role = "<?= htmlspecialchars($role, ENT_QUOTES) ?>";
        const production = "<?= htmlspecialchars($production, ENT_QUOTES) ?>";
        const production_location = "<?= htmlspecialchars($production_location, ENT_QUOTES) ?>";


        if (role === "administrator") {
            section = "stamping";
        } else {
            section = production;
        }
        console.log(role, production, production_location)

        // function filterRows(data) {
        //     const hide = ['spot welding', 'finishing'];
        //     return data.filter(item =>
        //         item.status !== 'done' &&
        //         !hide.includes((item.section || '').toLowerCase())
        //     );
        // }
        function filterRows(data) {
            const include = ['spot welding', 'finishing'];
            return data.filter(item =>
                item.status !== 'done' &&
                include.includes((item.section || '').toLowerCase())
            );
        }

        fetch(`api/stamping/getTodoList.php`)
            .then(response => response.json())
            .then(data => {

                fullData = preprocessData(data); // ⬅️ Clean it up before paginating
                paginator = createPaginator({
                    data: fullData,
                    rowsPerPage: 10,
                    renderPageCallback: renderTable,
                    paginationContainerId: 'pagination'
                });
                paginator.render(); // Initial render
            })
            .catch(console.error);

        function preprocessData(data) {
            const grouped = {};

            data.forEach(item => {
                if (!grouped[item.reference_no]) grouped[item.reference_no] = [];
                grouped[item.reference_no].push(item);
            });

            const sorted = Object.values(grouped)
                .flatMap(group =>
                    group.sort((a, b) => (parseInt(a.stage || 0) - parseInt(b.stage || 0)))
                );

            return sorted;
        }


        function renderTable(data, page = 1) {

            const filtered = filterRows(data)
            console.log(filtered)
            /* 2) GROUP the remaining rows by batch */
            const grouped = {};
            filtered.forEach(item => {
                if (!grouped[item.batch]) grouped[item.batch] = [];
                grouped[item.batch].push(item);
            });

            const dataBody = document.getElementById('data-body');
            dataBody.innerHTML = ''; // clear the table body

            /* 3) RENDER each batch exactly as before */
            Object.entries(grouped).forEach(([batch, group]) => {
                group.sort((a, b) => parseInt(a.stage || 0) - parseInt(b.stage || 0));

                /* ── group header row ── */
                const groupRow = document.createElement('tr');
                groupRow.classList.add('group-header');
                groupRow.style.backgroundColor = '#e6e6e6';
                groupRow.style.cursor = 'pointer';

                const componentName = group[0].components_name || '<i>Null</i>';
                groupRow.setAttribute('data-batch', batch);
                groupRow.setAttribute('data-component', componentName);
                groupRow.innerHTML = `
      <td colspan="9" style="font-weight:bold;text-align:left;">
        🔽 Batch: ${batch} - ${componentName}
      </td>`;
                dataBody.appendChild(groupRow);

                /* ── child rows ── */
                group.forEach(item => {
                    if (item.status === 'done') return; // skip rows already marked done

                    const itemDataAttr = encodeURIComponent(JSON.stringify(item));
                    const hasTimeIn = !!item.time_in;
                    const hasTimeOut = !!item.time_out;

                    const actionButton = hasTimeIn && hasTimeOut ?
                        `<span class="btn btn-sm btn-primary">Done</span>` :
                        `<button type="button"
                   class="btn btn-sm btn-${hasTimeIn ? 'success' : 'primary'} time-action-btn"
                   data-item="${itemDataAttr}"
                   data-mode="${hasTimeIn ? 'time-out' : 'time-in'}">
             ${hasTimeIn ? 'Time Out' : 'Time In'}
           </button>`;

                    const row = document.createElement('tr');
                    row.classList.add(`batch-group-${batch}`, 'ref-item-row');
                    row.style.display = 'none'; // keep rows collapsed initially
                    row.innerHTML = `
        <td style="text-align:center;">${item.components_name || '<i>Null</i>'}</td>
        <td style="text-align:center;">${item.section || '<i>Null</i>'}</td>
        <td style="text-align:center;">${item.stage_name || ''}</td>
        <td style="text-align:center;">${item.total_quantity ?? '<i>Null</i>'}</td>
        <td style="text-align:center;">${item.pending_quantity ?? '<i>0</i>'}</td>
        <td style="text-align:center;">${item.person_incharge || '<i>Null</i>'}</td>
        <td style="text-align:center;">${item.time_in || '<i>Null</i>'} / ${item.time_out || '<i>Null</i>'}</td>
        <td style="text-align:center;">${actionButton}</td>
        <td style="text-align:center;">
          <button class="btn btn-sm"
                  onclick="viewStageStatus('${item.material_no}', '${item.components_name}', '${item.batch}')"
                  title="View Stages">🔍</button>
        </td>`;
                    dataBody.appendChild(row);
                });
            });

            document.getElementById('last-updated').textContent =
                `Last updated: ${new Date().toLocaleString()}`;
        }


        document.getElementById('data-body').addEventListener('click', function(event) {
            const groupRow = event.target.closest('.group-header');
            if (!groupRow) return;

            const batch = groupRow.getAttribute('data-batch');
            const rows = document.querySelectorAll(`.batch-group-${batch}`);

            if (rows.length === 0) return;

            const isVisible = rows[0].style.display !== 'none';

            rows.forEach(row => {
                row.style.display = isVisible ? 'none' : '';
            });

            const componentName = groupRow.getAttribute('data-component');
            groupRow.querySelector('td').innerHTML = `${isVisible ? '▶️' : '🔽'} Batch: ${batch} - ${componentName}`;

        });
        document.getElementById('data-body').addEventListener('click', (event) => {
            const btn = event.target.closest('.time-action-btn');
            if (!btn) return;

            const encodedItem = btn.getAttribute('data-item');
            const mode = btn.getAttribute('data-mode');

            selectedRowData = JSON.parse(decodeURIComponent(encodedItem));

            const {
                material_no,
                components_name // ← updated this line
            } = selectedRowData;

            if (mode === 'time-in') {
                const stage = parseInt(selectedRowData.stage || 0);
                const batch = selectedRowData.batch;
                const relatedItems = fullData.filter(item => item.batch === batch);
                console.log('🧾 Related Items for Batch:', batch, relatedItems);

                const maxTotalQuantity = Math.max(...relatedItems.map(i => i.total_quantity || 0));

                if (stage === 1) {
                    const stage1Items = relatedItems.filter(item => parseInt(item.stage) === 1);
                    const sumStage1Quantity = stage1Items.reduce((sum, item) => sum + (item.quantity || 0), 0);

                    if (sumStage1Quantity >= maxTotalQuantity) {
                        showConfirmation(mode, material_no, components_name).then(result => {
                            if (result.isConfirmed) openQRModal(mode);
                        });
                        return;
                    }
                }

                if (stage > 1) {
                    const prevStage = stage - 1;
                    const prevStageItems = relatedItems.filter(item => parseInt(item.stage) === prevStage);

                    const hasOngoing = prevStageItems.some(item => item.status?.toLowerCase() === 'ongoing');
                    const allDone = prevStageItems.length > 0 && prevStageItems.every(item => item.status?.toLowerCase() === 'done');
                    const quantityCompleted = prevStageItems.reduce((sum, item) => sum + (item.quantity || 0), 0);
                    const maxTotalQuantity = Math.max(...relatedItems.map(i => i.total_quantity || 0));
                    const prevStageCompleted = quantityCompleted >= maxTotalQuantity;

                    const isSpecialSection = ['finishing', 'spot welding'].includes((selectedRowData.section || '').toLowerCase());

                    // 👇 allow FINISHING / SPOT WELDING only if quantity and done
                    if (isSpecialSection) {
                        const prevStageValidItems = relatedItems.filter(item =>
                            parseInt(item.stage) === stage - 1 &&
                            item.status?.toLowerCase() === 'done' &&
                            (item.quantity || 0) > 0
                        );

                        if (prevStageValidItems.length === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: `Cannot Time-In`,
                                text: `${selectedRowData.section} stage requires previous stage to have quantity and status "done".`,
                            });
                            return;
                        }
                    }


                    // 🚫 regular logic for all other sections
                    if (!hasOngoing && !allDone && !prevStageCompleted && !isSpecialSection) {
                        Swal.fire({
                            icon: 'warning',
                            title: `Cannot Time-In for Stage ${stage}`,
                            text: `Stage ${prevStage} must be "ongoing", "done", or completed by quantity before proceeding.`,
                        });
                        return;
                    }
                }


                showConfirmation(mode, material_no, components_name).then(result => {
                    if (result.isConfirmed) openQRModal(mode);
                });

            } else if (mode === 'time-out') {
                const quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));
                document.getElementById('timeoutQuantity').value = selectedRowData.pending_quantity || 1;

                const confirmBtn = document.getElementById('confirmQuantityBtn');
                confirmBtn.onclick = () => {
                    const inputQuantity = parseInt(document.getElementById('timeoutQuantity').value, 10);

                    if (!inputQuantity || inputQuantity <= 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Invalid Quantity',
                            text: 'Please enter a valid, positive quantity greater than 0.'
                        });
                        return;
                    }

                    const referenceNo = selectedRowData.reference_no;
                    const totalQuantity = parseInt(selectedRowData.total_quantity, 10) || 0;
                    const pendingQuantity = parseInt(selectedRowData.pending_quantity, 10) || 0;

                    const sumQuantity = fullData
                        .filter(row => row.reference_no === referenceNo)
                        .reduce((sum, row) => sum + (parseInt(row.quantity, 10) || 0), 0);

                    if (inputQuantity > pendingQuantity) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Pending Quantity Limit Exceeded',
                            html: `
            <p>You entered <strong>${inputQuantity}</strong> units.</p>
            <p>But only <strong>${pendingQuantity}</strong> units are pending for processing.</p>
            <p>Please adjust your quantity accordingly.</p>
          `
                        });
                        return;
                    }

                    if (sumQuantity + inputQuantity > totalQuantity) {
                        const remaining = totalQuantity - sumQuantity;
                        Swal.fire({
                            icon: 'error',
                            title: 'Total Quantity Limit Exceeded',
                            html: `
            <p>Reference #: <strong>${referenceNo}</strong></p>
            <p>Already processed: <strong>${sumQuantity}</strong> / ${totalQuantity}</p>
            <p>Your input of <strong>${inputQuantity}</strong> would exceed the total allowed.</p>
            <p>You can only process up to <strong>${remaining}</strong> more units.</p>
          `
                        });
                        return;
                    }

                    selectedRowData.inputQuantity = inputQuantity;
                    quantityModal.hide();

                    showConfirmation(mode, material_no, components_name).then(result => {
                        if (result.isConfirmed) openQRModal(mode);
                    });
                };

                quantityModal.show();
            }
        });







        // Enable/disable filter input based on dropdown
        filterColumnSelect.addEventListener('change', () => {
            filterInput.value = '';
            filterInput.disabled = !filterColumnSelect.value;
            filterInput.focus();
            applyFilter();
        });

        filterInput.addEventListener('input', applyFilter);

        function applyFilter() {
            const column = filterColumnSelect.value;
            const filterValue = filterInput.value.trim().toLowerCase();

            if (!column) {
                paginator.setData(fullData);
                return;
            }

            const filtered = fullData.filter(item => {
                let val = item[column];
                if (val === null || val === undefined) return false;
                return String(val).toLowerCase().includes(filterValue);
            });

            paginator.setData(filtered);
        }

        function showConfirmation(mode, materialNo, componentName) {
            return Swal.fire({
                icon: 'question',
                title: `Confirm ${mode === 'time-in' ? 'Time-In' : 'Time-Out'}`,
                html: `<b>Material No:</b> ${materialNo}<br><b>Component:</b> ${componentName}`,
                showCancelButton: true,
                confirmButtonText: 'Yes, Proceed',
                cancelButtonText: 'Cancel'
            });
        }





        function viewStageStatus(materialNo, componentName, batch) {
            fetch('api/stamping/fetchStageStatus.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        material_no: materialNo,
                        components_name: componentName,
                        batch
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        let stages = data.stages || [];

                        // ✅ Remove duplicate stages by 'stage' value
                        const seen = new Set();
                        stages = stages.filter(stage => {
                            const key = stage.stage; // use stage.stage_name if preferred
                            if (seen.has(key)) return false;
                            seen.add(key);
                            return true;
                        });

                        const len = stages.length;
                        let content = '<i>No stages found</i>';

                        if (len > 0) {
                            if ((len >= 5 && len % 2 === 1) || (len >= 6 && len % 2 === 0)) {
                                const midpoint = Math.ceil(len / 2);
                                const firstRow = stages.slice(0, midpoint);
                                const secondRow = stages.slice(midpoint);

                                content = `
            <div style="display: flex; flex-direction: column; gap: 16px; padding: 10px;">
              <div style="display: flex; gap: 12px; justify-content: center;">
                ${firstRow.map(stage => renderStageBox(stage)).join('')}
              </div>
              <div style="display: flex; gap: 12px; justify-content: center;">
                ${secondRow.map(stage => renderStageBox(stage)).join('')}
              </div>
            </div>
          `;
                            } else {
                                content = `
            <div style="display: flex; gap: 16px; overflow-x: auto; padding: 10px;">
              ${stages.map(stage => renderStageBox(stage)).join('')}
            </div>
          `;
                            }
                        }

                        Swal.fire({
                            title: 'Stage Status',
                            html: content,
                            icon: 'info',
                            width: '50%'
                        });
                    } else {
                        Swal.fire('Error', data.message || 'Could not fetch stage data.', 'error');
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    Swal.fire('Error', 'Something went wrong.', 'error');
                });

            function renderStageBox(stage) {
                return `
      <div style="border: 1px solid #ccc; padding: 10px; min-width: 200px; border-radius: 8px; box-shadow: 1px 1px 5px rgba(0,0,0,0.1);">
        <b>Section:</b> ${stage.section}<br>
        <b>Stage Name:</b> ${stage.stage_name}<br>
        <b>Status:</b> <span style="color: ${stage.status === 'done' ? 'green' : 'orange'}">${stage.status}</span><br>
        <br>(${stage.stage})
      </div>
    `;
            }
        }



        let isProcessingScan = false;

        function openQRModal(mode) {
            if (!selectedRowData) {
                console.error("No selectedRowData available!");
                Swal.fire('Error', 'No data selected for processing.', 'error');
                return;
            }
            const section = "STAMPING";
            scanQRCodeForUser({
                section,
                onSuccess: ({
                    user_id,
                    full_name
                }) => {
                    // ✅ Check mismatch only for time-out
                    if (mode === 'time-out') {
                        const expectedPerson = selectedRowData.person_incharge || '';
                        if (full_name !== expectedPerson) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Person In-Charge Mismatch',
                                text: `Scanned name "${full_name}" does not match assigned person "${expectedPerson}".`,
                                confirmButtonText: 'OK'
                            });
                            return; // ⛔ Stop further execution
                        }
                    }

                    const {
                        material_no,
                        material_description,
                        id,
                        pending_quantity,
                        quantity,
                        inputQuantity,
                        stage,
                        total_quantity,
                        process_quantity
                    } = selectedRowData;

                    const postData = {
                        id,
                        material_no,
                        material_description,
                        userId: user_id,
                        name: full_name,
                        quantity,
                        inputQuantity,
                        pending_quantity,
                        total_quantity
                    };

                    const endpoint = mode === 'time-in' ?
                        'api/stamping/postTimeInTask.php' :
                        'api/stamping/postTimeOutTask.php';

                    fetch(endpoint, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(postData)
                        })
                        .then(res => res.json())
                        .then(response => {
                            if (response.status === 'success') {
                                Swal.fire('Success', response.message || `${mode.replace('-', ' ')} recorded.`, 'success');
                            } else {
                                Swal.fire('Error', response.message || 'Something went wrong.', 'error');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            Swal.fire('Error', 'Network error occurred.', 'error');
                        });
                },
                onCancel: () => {

                }
            });
        }



        function stopQRScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner.clear();
                }).catch(err => {
                    console.warn("QR scanner stop failed:", err);
                });
            }
        }
    </script>