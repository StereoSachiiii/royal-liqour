<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Payment</title>
    <link rel="stylesheet" href="../admin.css">
</head>
<body>
    <div class="admin-container">
        <h1>Update Payment</h1>

        <!-- QUICK VIEW CARD -->
        <div id="quickViewCard" class="quick-view-card">
            <h2>Payment Overview</h2>
            <div class="modal-content">
                <h2>Payment Detail</h2>
                <div class="detail-section">
                    <h3>Basic Info</h3>
                    <div class="detail-field"><strong>Amount (Cents):</strong> <span id="detail_amount_cents">--</span></div>
                    <div class="detail-field"><strong>Currency:</strong> <span id="detail_currency">--</span></div>
                    <div class="detail-field"><strong>Gateway:</strong> <span id="detail_gateway">--</span></div>
                    <div class="detail-field"><strong>Status:</strong> <span id="detail_status">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>Order Info</h3>
                    <div class="detail-field"><strong>Order Number:</strong> <span id="detail_order_number">--</span></div>
                    <div class="detail-field"><strong>Order Status:</strong> <span id="detail_order_status">--</span></div>
                    <div class="detail-field"><strong>Order Total (Cents):</strong> <span id="detail_order_total_cents">--</span></div>
                </div>
                <div class="detail-section">
                    <h3>User Info</h3>
                    <div class="detail-field"><strong>User Name:</strong> <span id="detail_user_name">--</span></div>
                    <div class="detail-field"><strong>User Email:</strong> <span id="detail_user_email">--</span></div>
                </div>
            </div>
        </div>

        <div id="errorDiv" class="error-message"></div>
        <div id="successDiv" class="success-message"></div>

        <div class="edit-card">
            <h2>Edit Payment</h2>
            <form id="mainForm">
                <input type="hidden" id="recordId" name="id">

                <div class="form-group">
                    <label class="form-label required">Order</label>
                    <input list="ordersList" id="order_search" class="form-input" placeholder="Search order..." autocomplete="off" readonly>
                    <datalist id="ordersList"></datalist>
                    <input type="hidden" id="order_id" name="order_id">
                </div>

                <div class="form-group">
                    <label for="amount_cents" class="form-label required">Amount (Cents)</label>
                    <input type="number" id="amount_cents" name="amount_cents" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="currency" class="form-label">Currency</label>
                    <input type="text" id="currency" name="currency" class="form-input" value="LKR">
                </div>

                <div class="form-group">
                    <label for="gateway" class="form-label required">Gateway</label>
                    <input type="text" id="gateway" name="gateway" class="form-input" required>
                </div>

                <div class="form-group">
                    <label for="gateway_order_id" class="form-label">Gateway Order ID</label>
                    <input type="text" id="gateway_order_id" name="gateway_order_id" class="form-input">
                </div>

                <div class="form-group">
                    <label for="transaction_id" class="form-label">Transaction ID</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="form-input">
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">Status</label>
                    <select id="status" name="status" class="form-input">
                        <option value="pending">Pending</option>
                        <option value="captured">Captured</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                        <option value="voided">Voided</option>
                    </select>
                </div>

                <!-- ONLY THIS SECTION CHANGED — everything else is YOUR original code -->
                <div class="form-group">
                    <label class="form-label">Payload (Gateway Response)</label>

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
                        <input type="number" id="payload_status_code" class="form-input" placeholder="e.g. 2 = success">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Gateway Message</label>
                        <input type="text" id="payload_message" class="form-input" placeholder="Success, Declined, etc">
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
                        <input type="text" id="payload_admin_note" class="form-input" placeholder="Why manual entry?">
                    </div>

                    <small style="color:#888; margin-top:8px; display:block;">
                        These fields are automatically saved as JSON in the payload column
                    </small>
                </div>
                <!-- END OF CHANGE -->

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save Changes</button>
                    <a href="../../index.php" class="btn-secondary">Cancel</a>
                    <button type="button" id="softDeleteBtn" class="btn-danger">Soft Delete</button>
                    <button type="button" id="hardDeleteBtn" class="btn-danger">Hard Delete</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODALS (unchanged) -->
    <div id="confirmModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header" id="modalTitle">Confirm Delete</div>
            <div class="modal-body" id="modalMessage"></div>
            <div class="modal-actions">
                <button id="modalCancel" class="btn-secondary">Cancel</button>
                <button id="modalConfirm" class="btn-danger">Confirm</button>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">Payment Updated Successfully</div>
            <div class="modal-body" id="successModalMessage"></div>
            <div class="modal-actions">
                <button id="successModalStay" class="btn-secondary">Stay on Page</button>
                <button id="successModalReturn" class="btn-primary">Return to List</button>
            </div>
        </div>
    </div>

    <script type="module">
        const API_PAYMENTS = '/royal-liquor/api/v1/payments';
        const API_ORDERS = '/royal-liquor/api/v1/orders';

        const recordId = Number(new URLSearchParams(window.location.search).get('id'));
        if (!recordId) {
            alert('No payment ID provided');
            window.location.href = '../../index.php';
        }

        const loadUrl = `${API_PAYMENTS}/${recordId}?enriched=true`;
        const form = document.getElementById('mainForm');
        const errorDiv = document.getElementById('errorDiv');
        const modal = document.getElementById('confirmModal');
        const successModal = document.getElementById('successModal');
        const el = id => document.getElementById(id);

        let currentAction = null;

        const inputs = {
            order_id: el('order_id'),
            amount_cents: el('amount_cents'),
            currency: el('currency'),
            gateway: el('gateway'),
            gateway_order_id: el('gateway_order_id'),
            transaction_id: el('transaction_id'),
            status: el('status'),
            payload: el('payload')
        };

        const q = {
            amountCents: el('detail_amount_cents'),
            currency: el('detail_currency'),
            gateway: el('detail_gateway'),
            status: el('detail_status'),
            orderNumber: el('detail_order_number'),
            orderStatus: el('detail_order_status'),
            orderTotalCents: el('detail_order_total_cents'),
            userName: el('detail_user_name'),
            userEmail: el('detail_user_email')
        };

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
                const response = await fetch(`${API_ORDERS}?limit=500`, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Failed to load orders');
                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;
                const list = el('ordersList');
                list.innerHTML = '';
                (data.items || []).forEach(o => {
                    const opt = document.createElement('option');
                    opt.value = `Order #${o.order_number}`;
                    opt.dataset.id = o.id;
                    list.appendChild(opt);
                });
            } catch (e) { 
                console.error('Failed to load orders:', e); 
            }
        }

        async function loadPayment() {
            try {
                const response = await fetch(loadUrl, {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin'
                });
                if (!response.ok) throw new Error('Failed to load payment');
                const json = await response.json();
                const data = json.data?.data ?? json.data ?? json;

                q.amountCents.textContent = data.amount_cents || '--';
                q.currency.textContent = data.currency || '--';
                q.gateway.textContent = data.gateway || '--';
                q.status.textContent = data.status || '--';
                q.orderNumber.textContent = data.order_number || '--';
                q.orderStatus.textContent = data.order_status || '--';
                q.orderTotalCents.textContent = data.order_total_cents || '--';
                q.userName.textContent = data.user_name || '--';
                q.userEmail.textContent = data.user_email || '--';

                inputs.order_id.value = data.order_id;
                el('order_search').value = `Order #${data.order_number}`;
                inputs.amount_cents.value = data.amount_cents || 0;
                inputs.currency.value = data.currency || 'LKR';
                inputs.gateway.value = data.gateway || '';
                inputs.gateway_order_id.value = data.gateway_order_id || '';
                inputs.transaction_id.value = data.transaction_id || '';
                inputs.status.value = data.status || 'pending';

                let payload = {};
                try {
                    payload = typeof data.payload === 'object' ? data.payload : JSON.parse(data.payload || '{}');
                } catch(e) {}

                payloadInputs.ip.value         = payload.ip || '';
                payloadInputs.card_last4.value = payload.card_last4 || '';
                payloadInputs.card_brand.value = payload.card_brand || '';
                payloadInputs.status_code.value = payload.status_code || '';
                payloadInputs.message.value    = payload.message || '';
                payloadInputs.risk_score.value = payload.risk_score || '';
                payloadInputs.md5sig.value     = payload.md5sig || '';
                payloadInputs.admin_note.value = payload.admin_note || '';

                el('recordId').value = data.id;
            } catch (err) {
                errorDiv.textContent = 'Failed to load payment: ' + (err.message || 'Unknown error');
            }
        }

        form.addEventListener('submit', async e => {
            e.preventDefault();
            errorDiv.textContent = '';

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

            const payload = {
                id: recordId,
                order_id: Number(inputs.order_id.value),
                amount_cents: Number(inputs.amount_cents.value),
                currency: inputs.currency.value.trim(),
                gateway: inputs.gateway.value.trim(),
                gateway_order_id: inputs.gateway_order_id.value.trim() || null,
                transaction_id: inputs.transaction_id.value.trim() || null,
                status: inputs.status.value,
                payload: buildPayload()
            };

            try {
                const response = await fetch(`${API_PAYMENTS}/${recordId}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload)
                });
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(text || 'Failed to update payment');
                }
                const json = await response.json();
                const data = json.data ?? json;
                
                const modalMsg = el('successModalMessage');
                modalMsg.innerHTML = `
                    <div style="text-align: left;">
                        <p><strong>Updated Payment Details:</strong></p>
                        <p><strong>Amount:</strong> ${data.amount_cents} cents</p>
                        <p><strong>Gateway:</strong> ${data.gateway}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                        <p><strong>Transaction ID:</strong> ${data.transaction_id || 'N/A'}</p>
                    </div>
                `;
                successModal.style.display = 'flex';
                await loadPayment();
            } catch (err) {
                errorDiv.textContent = err.message || 'Failed to update payment';
                errorDiv.style.display = 'block';
            }
        });

        el('softDeleteBtn').onclick = () => {
            currentAction = 'soft';
            el('modalTitle').textContent = 'Soft Delete Payment';
            el('modalMessage').textContent = 'This payment will be hidden but kept in database.';
            modal.style.display = 'flex';
        };

        el('hardDeleteBtn').onclick = () => {
            currentAction = 'hard';
            el('modalTitle').textContent = 'Permanently Delete Payment';
            el('modalMessage').textContent = 'This action cannot be undone.';
            modal.style.display = 'flex';
        };

        el('modalCancel').onclick = () => modal.style.display = 'none';
        el('modalConfirm').onclick = async () => {
            modal.style.display = 'none';
            try {
                const response = await fetch(`${API_PAYMENTS}/${recordId}`, {
                    method: 'DELETE',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ id: recordId, hard: currentAction === 'hard' })
                });
                if (!response.ok) throw new Error('Delete failed');
                el('successDiv').textContent = currentAction === 'hard' ? 'Permanently deleted!' : 'Payment hidden!';
                el('successDiv').style.display = 'block';
                setTimeout(() => location.href = '../../index.php', 1500);
            } catch (err) {
                errorDiv.textContent = err.message || 'Delete failed';
                errorDiv.style.display = 'block';
            }
        };

        el('successModalStay').onclick = () => { successModal.style.display = 'none'; };
        el('successModalReturn').onclick = () => { location.href = '../../index.php'; };

        loadOrders();
        loadPayment().then(() => { form.scrollIntoView({ behavior: 'smooth' }); });
    </script>
</body>
</html>