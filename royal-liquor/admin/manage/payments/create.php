<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Payment</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Create Payment</h1>

        <div id="errorDiv" class="error-message"></div>

        <div class="edit-card">
            <h2>New Payment</h2>
            <form id="mainForm">
                <div class="form-group">
                    <label class="form-label required">Order</label>
                    <input list="ordersList" id="order_search" class="form-input" placeholder="Search order..." autocomplete="off" required>
                    <datalist id="ordersList"></datalist>
                    <input type="hidden" id="order_id" name="order_id">
                </div>

                <div class="form-group">
                    <label for="amount_cents" class="form-label required">Amount (Cents)</label>
                    <input type="number" id="amount_cents" name="amount_cents" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="currency" class="form-label">Currency</label>
                    <input type="text" id="currency" name="currency" class="form-input" value="LKR" readonly>
                </div>

                <div class="form-group">
                    <label for="gateway" class="form-label required">Gateway</label>
                    <select id="gateway" class="form-input" required>
                        <option value="">-- Select Gateway --</option>
                        <option value="payhere">PayHere</option>
                        <option value="stripe">Stripe</option>
                        <option value="manual">Manual / Bank Transfer</option>
                        <option value="cash">Cash on Delivery</option>
                        <option value="gift">Gift / Compensation</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gateway_order_id" class="form-label">Gateway Order ID</label>
                    <input type="text" id="gateway_order_id" class="form-input" placeholder="e.g. 320012345678">
                </div>

                <div class="form-group">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" id="transaction_id" class="form-input" placeholder="e.g. PAYHERE-2025-001234">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label required">Status</label>
                    <select id="status" class="form-input">
                        <option value="pending">Pending</option>
                        <option value="captured" selected>Captured (Success)</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                        <option value="voided">Voided</option>
                    </select>
                </div>

                <!-- STRUCTURED PAYLOAD FIELDS — replaces the old textarea -->
                <div class="form-group">
                    <label class="form-label">Gateway Response Details (Optional)</label>

                    <div class="form-group">
                        <label class="form-label">Customer IP</label>
                        <input type="text" id="payload_ip" class="form-input" placeholder="e.g. 110.145.32.198">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Last 4</label>
                        <input type="text" id="payload_card_last4" class="form-input" maxlength="4" placeholder="1111">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Brand</label>
                        <input type="text" id="payload_card_brand" class="form-input" placeholder="visa, mastercard...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gateway Status Code</label>
                        <input type="number" id="payload_status_code" class="form-input" placeholder="e.g. 2">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gateway Message</label>
                        <input type="text" id="payload_message" class="form-input" placeholder="Success, Declined...">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Risk Score</label>
                        <input type="number" id="payload_risk_score" class="form-input" min="0" max="100">
                    </div>

                    <div class="form-group">
                        <label class="form-label">MD5 Signature (PayHere)</label>
                        <input type="text" id="payload_md5sig" class="form-input">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Admin Note</label>
                        <input type="text" id="payload_admin_note" class="form-input" placeholder="Why was this created manually?">
                    </div>

                    <small style="color:#888; margin-top:8px; display:block;">
                        All fields above are automatically saved as JSON in the payload
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Create Payment</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- SUCCESS MODAL -->
    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Payment Created Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalClose" class="btn-secondary">Create Another</button>
                <button id="successModalView" class="btn-primary">View Payment</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_ORDERS = '/royal-liquor/api/v1/orders';
        const API_PAYMENTS = '/royal-liquor/api/v1/payments';

        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const successModal = document.getElementById('successModal');
        const el = id => document.getElementById(id);

        let createdPaymentId = null;

        // Payload field references
        const payloadInputs = {
            ip:            el('payload_ip'),
            card_last4:    el('payload_card_last4'),
            card_brand:    el('payload_card_brand'),
            status_code:   el('payload_status_code'),
            message:       el('payload_message'),
            risk_score:    el('payload_risk_score'),
            md5sig:        el('payload_md5sig'),
            admin_note:    el('payload_admin_note')
        };

        async function loadOrders() {
            try {
                const res = await fetch(`${API_ORDERS}?limit=500`, { credentials: 'same-origin' });
                const json = await res.json();
                const data = json.data?.data ?? json.data ?? json;
                const list = el('ordersList');
                list.innerHTML = '';
                (data.items || []).forEach(o => {
                    const opt = document.createElement('option');
                    opt.value = `Order #${o.order_number} - ${o.user_name || 'Guest'} (${(o.total_cents/100).toFixed(2)} LKR)`;
                    opt.dataset.id = o.id;
                    list.appendChild(opt);
                });
            } catch (e) { console.error(e); }
        }

        // Auto-fill amount when order selected
        el('order_search').addEventListener('input', function() {
            const selected = [...el('ordersList').options].find(o => o.value === this.value);
            el('order_id').value = selected ? selected.dataset.id : '';
            if (selected) {
                const match = this.value.match(/\((\d+\.\d{2}) LKR\)/);
                if (match) el('amount_cents').value = Math.round(parseFloat(match[1]) * 100);
            }
        });

        // Build payload object from fields
        function buildPayload() {
            const p = {};
            if (payloadInputs.ip.value.trim()) p.ip = payloadInputs.ip.value.trim();
            if (payloadInputs.card_last4.value.trim()) p.card_last4 = payloadInputs.card_last4.value.trim();
            if (payloadInputs.card_brand.value.trim()) p.card_brand = payloadInputs.card_brand.value.trim();
            if (payloadInputs.status_code.value.trim()) p.status_code = Number(payloadInputs.status_code.value);
            if (payloadInputs.message.value.trim()) p.message = payloadInputs.message.value.trim();
            if (payloadInputs.risk_score.value.trim()) p.risk_score = Number(payloadInputs.risk_score.value);
            if (payloadInputs.md5sig.value.trim()) p.md5sig = payloadInputs.md5sig.value.trim();
            if (payloadInputs.admin_note.value.trim()) p.admin_note = payloadInputs.admin_note.value.trim();
            return Object.keys(p).length > 0 ? p : null;
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';
            errorDiv.style.display = 'none';

            if (!el('order_id').value) {
                errorDiv.textContent = 'Please select a valid order';
                errorDiv.style.display = 'block';
                return;
            }

            const data = {
                order_id: Number(el('order_id').value),
                amount_cents: Number(el('amount_cents').value),
                currency: el('currency').value.trim(),
                gateway: el('gateway').value,
                gateway_order_id: el('gateway_order_id').value.trim() || null,
                transaction_id: el('transaction_id').value.trim() || null,
                status: el('status').value,
                payload: buildPayload()
            };

            try {
                const res = await fetch(API_PAYMENTS, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(data)
                });
                if (!res.ok) throw new Error(await res.text() || 'Failed');

                const json = await res.json();
                const payment = json.data?.data ?? json.data ?? json;
                createdPaymentId = payment.id;

                el('successModalMessage').innerHTML = `
                    <p><strong>Success!</strong> Payment created.</p>
                    <p>Amount: ${(payment.amount_cents/100).toFixed(2)} LKR</p>
                    <p>Gateway: ${payment.gateway}</p>
                    <p>Status: ${payment.status.toUpperCase()}</p>
                `;
                successModal.style.display = 'flex';
            } catch (err) {
                errorDiv.textContent = err.message;
                errorDiv.style.display = 'block';
            }
        });

        el('successModalClose').onclick = () => {
            successModal.style.display = 'none';
            form.reset();
            el('order_search').value = '';
            el('order_id').value = '';
        };

        el('successModalView').onclick = () => {
            location.href = `update.php?id=${createdPaymentId}`;
        };

        loadOrders();
    </script>
</body>
</html>