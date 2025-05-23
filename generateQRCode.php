

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


    <!-- Multiple Tab -->
    <div class="tab-pane fade" id="multiple" role="tabpanel" aria-labelledby="multiple-tab">
      <textarea id="qrTextMultiple" class="form-control mb-3" rows="4" placeholder="Enter multiple lines (1 per QR code)"></textarea>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
 <script>
    // Update detailName on user selection
    document.getElementById('singleUserSelect').addEventListener('change', function() {
      const names = {
        '001': 'Alice Johnson',
        '002': 'Bob Smith'
      };
      document.getElementById('detailName').textContent = names[this.value] || '-';
    });

    function generateSingleQR() {
      const select = document.getElementById('singleUserSelect');
      const userId = select.value;
      const fullName = document.getElementById('detailName').textContent;

      if (!userId || fullName === '-') {
        alert("Please select a user.");
        return;
      }

      const qrContent = `ID: ${userId}\nName: ${fullName}`;

      // Clear previous QR
      const qrContainer = document.getElementById('qrcode');
      qrContainer.innerHTML = '';
      document.getElementById('downloadQR').style.display = 'none';

      // Temporary container for QR code
      const tempDiv = document.createElement('div');
      qrContainer.appendChild(tempDiv);

      const qr = new QRCode(tempDiv, {
        text: qrContent,
        width: 200,
        height: 200,
        correctLevel: QRCode.CorrectLevel.H
      });
setTimeout(() => {
  const qrImg = tempDiv.querySelector('img');
  if (!qrImg) return;

  const margin = 10;
  const qrSize = 120;
  const headerHeight = 40;
  const nameHeight = 20;

  // Calculate total needed height (header + qr + name + margins)
  const totalContentHeight = margin + headerHeight + margin + qrSize + margin + nameHeight + margin;
  const totalContentWidth = qrSize + margin * 2;

  // Choose the max dimension for square canvas
  const canvasSize = Math.max(totalContentHeight, totalContentWidth);

  const canvas = document.createElement('canvas');
  canvas.width = canvasSize;
  canvas.height = canvasSize;
  const ctx = canvas.getContext('2d');

  // White background
  ctx.fillStyle = '#fff';
  ctx.fillRect(0, 0, canvasSize, canvasSize);

  // Load header image (relative path)
  const headerImg = new Image();
  headerImg.src = 'assets/images/roberts2.png';  // <-- relative path to your image

  headerImg.onload = () => {
    // Draw header image centered horizontally
    const scale = headerHeight / headerImg.height;
    const headerWidth = headerImg.width * scale;
    const headerX = (canvasSize - headerWidth) / 3;
    const headerY = margin;
    ctx.drawImage(headerImg, headerX, headerY, headerWidth, headerHeight);

    // Draw QR code below header
    const qrX = (canvasSize - qrSize) / 2;
    const qrY = headerY + headerHeight + margin;
    ctx.drawImage(qrImg, qrX, qrY, qrSize, qrSize);

    // Draw user name below QR
    ctx.fillStyle = '#000';
    ctx.font = '14px Arial';
    ctx.textAlign = 'center';
    const nameX = canvasSize / 2;
    const nameY = qrY + qrSize + margin + 10;
    ctx.fillText(fullName, nameX, nameY);

    // Draw border around entire canvas
    const borderWidth = 1.5;
    ctx.lineWidth = borderWidth;
    ctx.strokeStyle = '#000';
    ctx.strokeRect(borderWidth / 2, borderWidth / 2, canvasSize - borderWidth, canvasSize - borderWidth);

    // Clear temp div and append canvas
    qrContainer.innerHTML = '';
    qrContainer.appendChild(canvas);

    // Setup download button
    const downloadBtn = document.getElementById('downloadQR');
    downloadBtn.style.display = 'block';
    downloadBtn.onclick = () => {
      const link = document.createElement('a');
      link.download = `qr_${userId}.png`;
      link.href = canvas.toDataURL('image/png');
      link.click();
    };
  };

  headerImg.onerror = () => {
    alert('Failed to load header image.');
  };
}, 100);

    }
  </script>