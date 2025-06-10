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
<script>
    let html5QrcodeScanner = null;

function scanQRCodeForUser({ onSuccess, onCancel }) {
  const modalElement = document.getElementById('qrModal');
  const resultContainer = document.getElementById('qr-result');
  const qrReader = new Html5Qrcode("qr-reader");

  html5QrcodeScanner = qrReader;

  const modal = new bootstrap.Modal(modalElement);
  modal.show();

  resultContainer.textContent = "Waiting for QR scan...";

  qrReader.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 550 },
    async (decodedText, decodedResult) => {
      resultContainer.textContent = `QR Code Scanned: ${decodedText}`;
      qrReader.pause();

      const confirm = await Swal.fire({
        title: 'Confirm Scan',
        text: `Is this the correct QR code?\n${decodedText}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, confirm',
        cancelButtonText: 'No, rescan'
      });

      if (confirm.isConfirmed) {
        const user_id = (decodedText.match(/ID:\s*([^\n]+)/)?.[1] || '').trim();
        const full_name = (decodedText.match(/Name:\s*(.+)/)?.[1] || '').trim();

        if (user_id && full_name) {
          onSuccess({ user_id, full_name });
          modal.hide();
          cleanupQRScanner(qrReader);
        } else {
          Swal.fire('Error', 'Could not extract user ID or name.', 'error');
          qrReader.resume();
        }
      } else {
        resultContainer.textContent = "Waiting for QR scan...";
        qrReader.resume();
      }
    },
    (errorMessage) => {
      // Optional: Handle scan errors here
    }
  ).catch(err => {
    resultContainer.textContent = `Unable to start scanner: ${err}`;
  });

  modalElement.addEventListener('hidden.bs.modal', () => {
    cleanupQRScanner(qrReader);
    if (onCancel) onCancel();
  }, { once: true });
}

function cleanupQRScanner(reader) {
  if (reader) {
    reader.stop().then(() => reader.clear()).catch(err => {
      console.warn("QR scanner stop failed:", err);
    });
  }
}

</script>