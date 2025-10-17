<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SonicPesa Direct Payment</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white flex items-center justify-center min-h-screen p-4">
  <div class="bg-gray-800 p-6 rounded-2xl shadow-lg w-full max-w-md">
    <h1 class="text-2xl font-bold mb-4 text-center text-green-400">SonicPesa Payment</h1>

    <div id="alert" class="hidden mb-4 text-sm text-center"></div>

    <input id="name" type="text" placeholder="Jina kamili" class="w-full mb-2 p-2 rounded bg-gray-700 text-white">
    <input id="email" type="email" placeholder="mfano@gmail.com" class="w-full mb-2 p-2 rounded bg-gray-700 text-white">
    <input id="phone" type="tel" placeholder="2557xxxxxxxx" class="w-full mb-2 p-2 rounded bg-gray-700 text-white">
    <input id="amount" type="number" placeholder="Kiasi (Tsh)" class="w-full mb-4 p-2 rounded bg-gray-700 text-white">

    <button id="payBtn" class="w-full bg-green-500 hover:bg-green-600 py-2 rounded font-bold">Lipa Sasa</button>

    <p id="status" class="mt-4 text-sm text-center text-blue-300"></p>
  </div>

<script>
const API_KEY = 'YOUR_API_KEY'; // Badilisha API key yako
const CREATE_URL = 'https://sonicpesa.com/api/payment/create';
const STATUS_URL = 'https://sonicpesa.com/api/payment/status';

function showAlert(text, type='info') {
  const el = document.getElementById('alert');
  el.className = '';
  el.classList.add('mb-4', 'text-sm', 'text-center', 'p-2', 'rounded');
  if (type === 'error') el.classList.add('bg-red-600', 'text-white');
  else if (type === 'success') el.classList.add('bg-green-600', 'text-white');
  else el.classList.add('bg-blue-600', 'text-white');
  el.textContent = text;
  el.classList.remove('hidden');
  setTimeout(()=> el.classList.add('hidden'), 8000);
}

async function createPayment(payload) {
  const res = await fetch(CREATE_URL, {
    method: 'POST',
    headers: { 
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + API_KEY
    },
    body: JSON.stringify(payload),
  });
  return res.json();
}

async function checkStatus(order_id) {
  const res = await fetch(STATUS_URL, {
    method: 'POST',
    headers: { 
      'Content-Type': 'application/json',
      'Authorization': 'Bearer ' + API_KEY
    },
    body: JSON.stringify({ order_id }),
  });
  return res.json();
}

document.getElementById('payBtn').addEventListener('click', async () => {
  const name = document.getElementById('name').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const amount = document.getElementById('amount').value.trim();

  if (!name || !email || !phone || !amount) {
    showAlert('Tafadhali jaza fomu yote.', 'error');
    return;
  }

  const payBtn = document.getElementById('payBtn');
  payBtn.disabled = true;
  payBtn.textContent = 'Inatuma...';
  document.getElementById('status').textContent = 'Tunasindika ombi la malipo...';

  try {
    const payload = { name, email, phone, amount: Number(amount) };
    const createRes = await createPayment(payload);

    if (createRes.success && createRes.data) {
      const orderId = createRes.data.order_id;
      document.getElementById('status').textContent = 'Malipo yameanzishwa. Subiri uthibitisho...';

      let attempts = 0;
      const maxAttempts = 12;
      const intervalMs = 5000;

      const poll = setInterval(async () => {
        attempts++;
        try {
          const st = await checkStatus(orderId);
          const s = st.data?.status ?? null;
          document.getElementById('status').textContent = 'Status ya sasa: ' + (s ?? 'unknown');

          if (s === 'completed') {
            clearInterval(poll);
            showAlert('Malipo yamekamilika! Unapelekwa kwenye videox.com', 'success');
            setTimeout(()=> window.location.href = 'https://videox.com', 1500);
          } else if (attempts >= maxAttempts) {
            clearInterval(poll);
            showAlert('Imeshindikana kuthibitisha malipo. Jaribu tena.', 'error');
          }
        } catch (err) {
          clearInterval(poll);
          showAlert('Hitilafu wakati wa kuthibitisha status.', 'error');
        }
      }, intervalMs);

    } else {
      showAlert(createRes.message || 'Imeshindikana kuanzisha malipo', 'error');
      document.getElementById('status').textContent = '';
    }

  } catch(err) {
    console.error(err);
    showAlert('Kuna tatizo kwenye maombi ya malipo.', 'error');
    document.getElementById('status').textContent = '';
  } finally {
    payBtn.disabled = false;
    payBtn.textContent = 'Lipa Sasa';
  }
});
</script>

</body>
</html>