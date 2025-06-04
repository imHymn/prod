

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

  <!-- Tab content -->
  <div class="tab-content" id="qrTabContent">
    <!-- Single Tab -->
    <!-- Single Tab -->
<div class="tab-pane fade show active" id="single" role="tabpanel" aria-labelledby="single-tab">
<select id="singleUserSelect" class="form-select mb-3">
  <option value="" disabled selected>Select User ID</option>
</select>
<div id="userDetails" class="mt-3" style="display: none;">
   <p><strong>Full Name:</strong> <span id="detailName"></span></p>
  <p><strong>Email:</strong> <span id="detailEmail"></span></p>
  <p><strong>Department:</strong> <span id="detailDepartment"></span></p>
  <p><strong>Section:</strong> <span id="detailSection"></span></p>
</div>

<button id="downloadQR" class="btn btn-outline-secondary w-100 mt-2" style="display:none">Download QR Image</button>

 <button class="btn btn-primary w-100" onclick="generateSingleQR()">Generate Single QR</button>

</div>


<div class="tab-pane fade" id="multiple" role="tabpanel" aria-labelledby="multiple-tab">
  <label for="multiUserSelect" class="form-label">Select Multiple Users:</label>
  <select id="multiUserSelect" class="form-select mb-2" multiple size="6">
    <!-- Populated by JS -->
  </select>

  <!-- Selected Users Badge List -->
  <div id="selectedUserBadges" class="mb-3 d-flex flex-wrap gap-2"></div>

  <button class="btn btn-success w-100" onclick="generateMultiple()">Generate Multiple QR</button>
</div>



    <!-- All Tab -->
    <div class="tab-pane fade" id="all" role="tabpanel" aria-labelledby="all-tab">
      <button class="btn btn-dark w-100" onclick="generateAll()">Generate All QR</button>
    </div>
  </div>

  <!-- QR code container -->
<div id="qrcode" class="d-flex flex-wrap justify-content-center gap-3 mt-4"></div>

</div>


      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button id="generateBtn" type="button" class="btn btn-primary">Generate</button>
      </div>

    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script src="lib/lib/qrcode/qrcode.min.js"></script>
<script>
let userData = [];

// Fetch and populate selects
fetch('api/accounts/getAccounts.php')
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

// Single user select listener: show details
function setupSingleSelectListener() {
  const singleSelect = document.getElementById('singleUserSelect');
  singleSelect.addEventListener('change', () => {
    const user = userData.find(u => u.user_id === singleSelect.value);
    const details = document.getElementById('userDetails');
    console.log(user);
    if (user) {
      details.style.display = 'block';
      document.getElementById('detailName').textContent = user.name;
      document.getElementById('detailEmail').textContent = user.email || '-';
      document.getElementById('detailDepartment').textContent = user.department || '-';
      document.getElementById('detailSection').textContent = user.section || '-';
    } else {
      details.style.display = 'none';
    }
  });
}

// Multiple user select listener: show badges
function setupMultiSelectListener() {
  const multiSelect = document.getElementById('multiUserSelect');
  const badgeContainer = document.getElementById('selectedUserBadges');
  multiSelect.addEventListener('change', () => {
    const selected = Array.from(multiSelect.selectedOptions);
    badgeContainer.innerHTML = '';
    selected.forEach(opt => {
      const user = userData.find(u => u.user_id === opt.value);
      if (user) {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary';
        badge.textContent = `${user.user_id} - ${user.name}`;
        badgeContainer.appendChild(badge);
      }
    });
  });
}

// Reusable QR code canvas generator, returns Promise<canvas>
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


// Generate single QR and show with download button
async function generateSingleQR() {
  const select = document.getElementById('singleUserSelect');
  const userId = select.value;
  const fullName = document.getElementById('detailName').textContent;

  if (!userId || fullName === '-') {
    alert("Please select a user.");
    return;
  }

  const qrContainer = document.getElementById('qrcode');
  qrContainer.innerHTML = '';
  const downloadBtn = document.getElementById('downloadQR');
  downloadBtn.style.display = 'none';

  try {
    const canvas = await createQRCodeCanvas(userId, fullName);
    qrContainer.appendChild(canvas);

    downloadBtn.style.display = 'block';
    downloadBtn.onclick = () => {
      const link = document.createElement('a');
      link.download = `qr_${userId}.png`;
      link.href = canvas.toDataURL('image/png');
      link.click();
    };
  } catch (e) {
    alert(e);
  }
}

// Generate multiple QR codes, display, AND save PDF
async function generateMultiple() {
  const select = document.getElementById('multiUserSelect');
  const selectedIds = Array.from(select.selectedOptions).map(opt => opt.value);
  if (selectedIds.length === 0) {
    alert("Please select at least one user.");
    return;
  }

  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  const qrSize = 50;
  const gap = 10;
  let x = 10, y = 10, count = 0;

  const qrContainer = document.getElementById('qrcode');
  qrContainer.innerHTML = '';

  for (const userId of selectedIds) {
    const user = userData.find(u => u.user_id === userId);
    if (!user) continue;

    try {
      const canvas = await createQRCodeCanvas(user.user_id, user.name);

      // Show on page
      const wrapper = document.createElement('div');
      wrapper.className = 'text-center';
      wrapper.appendChild(canvas);

      const caption = document.createElement('p');
      caption.className = 'small mt-1 mb-4';
      caption.textContent = user.name;
      wrapper.appendChild(caption);
      qrContainer.appendChild(wrapper);

      // Add to PDF
      const imgData = canvas.toDataURL('image/png');
      doc.addImage(imgData, 'PNG', x, y, qrSize, qrSize);

      doc.setFontSize(10);
      doc.text(user.name, x + qrSize / 2, y + qrSize + 7, { align: 'center' });

      count++;
      x += qrSize + gap;
      if (count % 3 === 0) {
        x = 10;
        y += qrSize + 20;
        if (y > 250) {
          doc.addPage();
          y = 10;
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  doc.save('multiple_qr_codes.pdf');
}

// Generate all QR codes in PDF only
async function generateAll() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF();
  let x = 5, y = 10, count = 0;

  for (const user of userData) {
    if (!user.user_id || !user.name) continue;

    try {
      const canvas = await createQRCodeCanvas(user.user_id, user.name);
      const imgData = canvas.toDataURL('image/png');
      doc.addImage(imgData, 'PNG', x, y, 50, 50);

      x += 55;
      count++;
      if (count % 3 === 0) {
        x = 10;
        y += 60;
        if (y > 250) {
          doc.addPage();
          y = 10;
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  doc.save('all_qr_codes.pdf');
}
</script>
