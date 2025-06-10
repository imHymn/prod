<script src="assets/js/qrcode.min.js"></script>
<script src="assets/js/jspdf.umd.min.js"></script>

<div class="modal fade" id="generateQR" tabindex="-1" aria-labelledby="generateQRLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="generateQRLabel">QR Code Generator</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
<div class="modal-body">

  <!-- Nav tabs -->
  <ul class="nav nav-tabs mb-3" id="qrTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab" aria-controls="single" aria-selected="true">Single</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="multiple-tab" data-bs-toggle="tab" data-bs-target="#multiple" type="button" role="tab" aria-controls="multiple" aria-selected="false">Multiple</button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab" aria-controls="all" aria-selected="false">All</button>
    </li>
  </ul>

  <div class="tab-content" id="qrTabContent">

<div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
<select id="singleUserSelect" class="form-select mb-3">
  <option value="" disabled selected>Select User ID</option>
</select>
<div id="userDetails" class="mt-3" style="display: none;">
   <p><strong>Full Name:</strong> <span id="detailName"></span></p>
  <p><strong>Production:</strong> <span id="detailProduction"></span></p>
  <p><strong>Role:</strong> <span id="detailRole"></span></p>

</div>

<button id="downloadQR" class="btn btn-outline-secondary w-100 mt-2" style="display:none">Download QR Image</button>

 <button class="btn btn-primary w-100" onclick="generateSingleQR()">Generate Single QR</button>

</div>


<div class="tab-pane fade" id="multiple" role="tabpanel" aria-labelledby="multiple-tab">
  <label for="multiUserSearch" class="form-label">Search Users:</label>
  <input type="text" id="multiUserSearch" class="form-control mb-2" placeholder="Search by name or ID...">

  <!-- Filtered User List -->
  <ul id="userSearchResults" class="list-group mb-2" style="max-height: 200px; overflow-y: auto;"></ul>

  <!-- Selected Users Badge List -->
  <div id="selectedUserBadges" class="mb-3 d-flex flex-wrap gap-2"></div>

  <!-- Hidden actual <select> to avoid breaking existing structure -->
  <select id="multiUserSelect" class="form-select mb-2 d-none" multiple size="6">
    <!-- Populated by JS -->
  </select>

  <button class="btn btn-success w-100" onclick="generateMultiple()">Generate Multiple QR</button>
</div>



    <!-- All Tab -->
    <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
      <button class="btn btn-dark w-100" onclick="generateAll()">Generate All QR</button>
    </div>
  </div>

<div id="qrcode" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>

</div>


      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button id="generateBtn" type="button" class="btn btn-primary">Generate</button>
      </div>

    </div>
  </div>
</div>


<script>
let userData = [];
let selectedUsers = [];

fetch('api/controllers/accounts/getAccounts.php')
  .then(res => res.json())
  .then(data => {
    userData = data.filter(u => u.user_id && u.name);
    populateUserSelect('singleUserSelect', userData, false);
    populateUserSelect('multiUserSelect', userData, true);
    setupSingleSelectListener();
    setupMultiSelectListener();
  });

// Populate <select> options helper
function populateUserSelect(selectId, users, multiple) {
  const select = document.getElementById(selectId);
  select.innerHTML = ''; // clear previous

  if (!multiple) {
    const placeholder = document.createElement('option');
    placeholder.textContent = 'Select User ID';
    placeholder.disabled = true;
    placeholder.selected = true;
    select.appendChild(placeholder);
  }

  users.forEach(user => {
    const opt = document.createElement('option');
    opt.value = user.user_id;
    opt.textContent = multiple ? `${user.user_id} - ${user.name}` : user.user_id;
    select.appendChild(opt);
  });
}
function setupSingleSelectListener() {
  const singleSelect = document.getElementById('singleUserSelect');
  singleSelect.addEventListener('change', () => {
    const user = userData.find(u => u.user_id === singleSelect.value);
    const details = document.getElementById('userDetails');
    console.log(user);
    if (user) {
      details.style.display = 'block';
      document.getElementById('detailName').textContent = user.name;
      document.getElementById('detailProduction').textContent = user.production || '-';
      document.getElementById('detailRole').textContent = user.role || '-';
    } else {
      details.style.display = 'none';
    }
  });
}
function setupMultiSelectListener() {
  const input = document.getElementById('multiUserSearch');
  input.addEventListener('input', () => {
    const query = input.value.toLowerCase();
    const results = userData.filter(u =>
      (u.name && u.name.toLowerCase().includes(query)) ||
      (u.user_id && u.user_id.toLowerCase().includes(query))
    );
    displayUserSearchResults(results);
  });
}

function displayUserSearchResults(results) {
  const resultContainer = document.getElementById('userSearchResults');
  resultContainer.innerHTML = '';

  results.forEach(user => {
    if (selectedUsers.some(u => u.user_id === user.user_id)) return;

    const li = document.createElement('li');
    li.className = 'list-group-item d-flex justify-content-between align-items-center';
    li.innerHTML = `
      ${user.user_id} - ${user.name}
      <button class="btn btn-sm btn-primary" onclick='addUserToSelected(${JSON.stringify(user)})'>+</button>
    `;
    resultContainer.appendChild(li);
  });
}

function addUserToSelected(user) {
  if (selectedUsers.find(u => u.user_id === user.user_id)) return;

  selectedUsers.push(user);
  renderSelectedBadges();

  // Sync with hidden <select> to preserve compatibility
  const select = document.getElementById('multiUserSelect');
  const option = Array.from(select.options).find(o => o.value === user.user_id);
  if (option) option.selected = true;

  document.getElementById('multiUserSearch').value = '';
  displayUserSearchResults([]);
}

function renderSelectedBadges() {
  const badgeContainer = document.getElementById('selectedUserBadges');
  badgeContainer.innerHTML = '';

  selectedUsers.forEach(user => {
    const badge = document.createElement('div');
    badge.className = 'd-flex align-items-center bg-light border rounded-pill px-3 py-1 shadow-sm';
    badge.style.gap = '0.75rem';

    badge.innerHTML = `
      <strong class="text-dark">${user.user_id}</strong> 
      <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 ms-auto" onclick="removeUserFromSelected('${user.user_id}')">x</button>
    `;

    badgeContainer.appendChild(badge);
  });
}



function removeUserFromSelected(userId) {
  selectedUsers = selectedUsers.filter(u => u.user_id !== userId);
  renderSelectedBadges();

  // Also update hidden select
  const select = document.getElementById('multiUserSelect');
  const option = Array.from(select.options).find(o => o.value === userId);
  if (option) option.selected = false;
}
async function generateSingleQR() {
  const select = document.getElementById('singleUserSelect');
  const userId = select.value;
  const fullName = document.getElementById('detailName').textContent;

  if (!userId || fullName === '-') {
    alert("Please select a user.");
    return;
  }

  const canvas = await createQRCodeCanvas(userId, fullName);

  const qrContainer = document.getElementById('qrcode');
  qrContainer.innerHTML = '';
  qrContainer.appendChild(canvas);

  const downloadBtn = document.getElementById('downloadQR');
  downloadBtn.style.display = 'block';
  downloadBtn.onclick = () => {
    const link = document.createElement('a');
    link.download = `qr_${userId}.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
  };
}

async function generateMultiple() {
  const select = document.getElementById('multiUserSelect');
  const selectedIds = Array.from(select.selectedOptions).map(opt => opt.value);
  if (selectedIds.length === 0) {
    alert("Please select at least one user.");
    return;
  }

  const users = userData.filter(u => selectedIds.includes(u.user_id));
  await generateQRCodePDF(users, { showOnPage: true, saveAs: 'multiple_qr_codes.pdf' });
}


async function generateAll() {
  const users = userData.filter(u => u.user_id && u.name);
  await generateQRCodePDF(users, { showOnPage: false, saveAs: 'all_qr_codes.pdf' });
}
async function generateQRCodePDF(users, { showOnPage = false, saveAs = 'qr_codes.pdf' } = {}) {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();

  const qrContainer = document.getElementById('qrcode');
  if (showOnPage) qrContainer.innerHTML = '';

  const qrSize = 50;
  const gap = 10;
  const qrPerRow = 3;
  const totalWidth = qrPerRow * qrSize + (qrPerRow - 1) * gap;
  const pageWidth = doc.internal.pageSize.getWidth();
  let x = (pageWidth - totalWidth) / 2;
  const startX = x;
  let y = 10, count = 0;

  for (const user of users) {
    if (!user.user_id || !user.name) continue;

    try {
      const canvas = await createQRCodeCanvas(user.user_id, user.name);

      if (showOnPage) {
        const wrapper = document.createElement('div');
        wrapper.className = 'text-center';
        wrapper.appendChild(canvas);

        const caption = document.createElement('p');
        caption.className = 'small mt-1 mb-4';
        caption.textContent = user.name;
        wrapper.appendChild(caption);
        qrContainer.appendChild(wrapper);
      }

      const imgData = canvas.toDataURL('image/png');
      doc.addImage(imgData, 'PNG', x, y, qrSize, qrSize);

      count++;
      x += qrSize + gap;
      if (count % qrPerRow === 0) {
        x = startX;
        y += qrSize + 6;
        if (y > 250) {
          doc.addPage();
          y = 10;
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  doc.save(saveAs);
}


function createQRCodeCanvas(userId, fullName) {
  return new Promise((resolve, reject) => {
    const qrContent = `ID: ${userId}\nName: ${fullName}`;
    const tempDiv = document.createElement('div');

    new QRCode(tempDiv, {
      text: qrContent,
      width: 200,
      height: 200,
      correctLevel: QRCode.CorrectLevel.H
    });

    setTimeout(() => {
      const qrImg = tempDiv.querySelector('img');
      if (!qrImg) return reject('QR code image not found.');

      const margin = 10;
      const qrSize = 120;
      const headerHeight = 40;
      const nameHeight = 20;
      const canvasSize = Math.max(margin + headerHeight + margin + qrSize + margin + nameHeight + margin, qrSize + margin * 2);

      const canvas = document.createElement('canvas');
      canvas.width = canvasSize;
      canvas.height = canvasSize;
      const ctx = canvas.getContext('2d');

      // Background white
      ctx.fillStyle = '#fff';
      ctx.fillRect(0, 0, canvasSize, canvasSize);

      // Header image
      const headerImg = new Image();
      headerImg.src = 'assets/images/roberts2.png';

      headerImg.onload = () => {
        const scale = headerHeight / headerImg.height;
        const headerWidth = headerImg.width * scale;
        const headerX = (canvasSize - headerWidth) / 3;
        const headerY = margin;
        ctx.drawImage(headerImg, headerX, headerY, headerWidth, headerHeight);

        // QR code image
        const qrX = (canvasSize - qrSize) / 2;
        const qrY = headerY + headerHeight + margin;
        ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);

        // Text below QR
        ctx.fillStyle = '#000';
        ctx.font = '14px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(fullName, canvasSize / 2, qrY + qrSize + margin + 10);

        // Border
        ctx.lineWidth = 1.5;
        ctx.strokeStyle = '#000';
        ctx.strokeRect(0.75, 0.75, canvasSize - 1.5, canvasSize - 1.5);

        resolve(canvas);
      };

      headerImg.onerror = () => reject('Failed to load header image.');
    }, 100);
  });
}

</script>
