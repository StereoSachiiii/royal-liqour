<?php
/**
 * MyAccount - Addresses
 * Manage delivery addresses
 */
$pageName = 'addresses';
$pageTitle = 'My Addresses - Royal Liquor';
require_once __DIR__ . "/_layout.php";
?>

<div class="page-header-row">
    <h1 class="account-page-title">Saved Addresses</h1>
    <button class="btn btn-gold" id="addAddressBtn">+ Add New Address</button>
</div>

<!-- Addresses Grid -->
<div class="addresses-grid" id="addressesGrid">
    <!-- Sample Address Card -->
    <div class="address-card card" data-address-id="1">
        <div class="address-badge">Default</div>
        <div class="address-content">
            <h3 class="address-name">Home</h3>
            <p class="address-line">John Doe</p>
            <p class="address-line">123 Galle Road, Apt 4B</p>
            <p class="address-line">Colombo 03</p>
            <p class="address-line">Sri Lanka</p>
            <p class="address-phone">+94 77 123 4567</p>
        </div>
        <div class="address-actions">
            <button class="btn btn-sm btn-outline btn-edit">Edit</button>
            <button class="btn btn-sm btn-outline btn-delete">Delete</button>
        </div>
    </div>

    <div class="address-card card" data-address-id="2">
        <div class="address-content">
            <h3 class="address-name">Office</h3>
            <p class="address-line">John Doe</p>
            <p class="address-line">456 Duplication Road, Floor 5</p>
            <p class="address-line">Colombo 04</p>
            <p class="address-line">Sri Lanka</p>
            <p class="address-phone">+94 11 234 5678</p>
        </div>
        <div class="address-actions">
            <button class="btn btn-sm btn-outline">Set as Default</button>
            <button class="btn btn-sm btn-outline btn-edit">Edit</button>
            <button class="btn btn-sm btn-outline btn-delete">Delete</button>
        </div>
    </div>

    <!-- Add Address Card -->
    <div class="address-card address-add" id="addAddressCard">
        <div class="add-icon">+</div>
        <p>Add New Address</p>
    </div>
</div>

<!-- Address Modal -->
<div class="modal-overlay" id="addressModal">
    <div class="modal-content">
        <button class="modal-close" id="closeAddressModal">&times;</button>
        <h2 class="modal-title" id="addressModalTitle">Add New Address</h2>
        
        <form id="addressForm" class="address-form">
            <div class="form-row">
                <div class="input-group">
                    <label class="label">Label (e.g., Home, Office)</label>
                    <input type="text" name="label" class="input" placeholder="Home" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label class="label">Full Name</label>
                    <input type="text" name="full_name" class="input" required>
                </div>
                <div class="input-group">
                    <label class="label">Phone Number</label>
                    <input type="tel" name="phone" class="input" required>
                </div>
            </div>
            
            <div class="input-group">
                <label class="label">Address Line 1</label>
                <input type="text" name="line1" class="input" placeholder="Street address" required>
            </div>
            
            <div class="input-group">
                <label class="label">Address Line 2</label>
                <input type="text" name="line2" class="input" placeholder="Apartment, suite, etc. (optional)">
            </div>
            
            <div class="form-row">
                <div class="input-group">
                    <label class="label">City</label>
                    <input type="text" name="city" class="input" required>
                </div>
                <div class="input-group">
                    <label class="label">Postal Code</label>
                    <input type="text" name="postal_code" class="input">
                </div>
            </div>
            
            <div class="input-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="is_default">
                    Set as default address
                </label>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-outline" id="cancelAddressBtn">Cancel</button>
                <button type="submit" class="btn btn-gold">Save Address</button>
            </div>
        </form>
    </div>
</div>

<style>
.page-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-xl);
}

.page-header-row .account-page-title {
    margin: 0;
}

.addresses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: var(--space-lg);
}

.address-card {
    position: relative;
    padding: var(--space-xl);
}

.address-badge {
    position: absolute;
    top: var(--space-md);
    right: var(--space-md);
    background: var(--gold);
    color: var(--black);
    font-size: 0.75rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: var(--radius-full);
}

.address-content {
    margin-bottom: var(--space-lg);
}

.address-name {
    font-size: 1.125rem;
    font-weight: 600;
    margin-bottom: var(--space-sm);
}

.address-line {
    color: var(--gray-600);
    margin: 0;
    line-height: 1.6;
}

.address-phone {
    color: var(--gray-500);
    margin-top: var(--space-sm);
    font-size: 0.9rem;
}

.address-actions {
    display: flex;
    gap: var(--space-sm);
    flex-wrap: wrap;
}

.address-add {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    border: 2px dashed var(--gray-200);
    cursor: pointer;
    transition: all var(--duration-fast) var(--ease-out);
}

.address-add:hover {
    border-color: var(--gold);
    background: rgba(212, 175, 55, 0.05);
}

.add-icon {
    font-size: 2rem;
    color: var(--gray-400);
    margin-bottom: var(--space-sm);
}

.address-add p {
    color: var(--gray-500);
    font-weight: 500;
}

.address-form .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}

.address-form .input-group {
    margin-bottom: var(--space-md);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: var(--space-md);
    justify-content: flex-end;
    margin-top: var(--space-lg);
    padding-top: var(--space-lg);
    border-top: 1px solid var(--gray-100);
}

@media (max-width: 640px) {
    .page-header-row {
        flex-direction: column;
        gap: var(--space-md);
        align-items: stretch;
    }
    .address-form .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<script type="module">
import { toast } from '<?= BASE_URL ?>utils/toast.js';

const modal = document.getElementById('addressModal');
const form = document.getElementById('addressForm');
const addressGrid = document.querySelector('.address-grid');

let addresses = [];
let editingId = null;

// Load addresses from localStorage
const loadAddresses = () => {
    addresses = JSON.parse(localStorage.getItem('userAddresses') || '[]');
    renderAddresses();
};

// Save addresses to localStorage
const saveAddresses = () => {
    localStorage.setItem('userAddresses', JSON.stringify(addresses));
};

// Render addresses grid
const renderAddresses = () => {
    const addCard = addressGrid.querySelector('.add-address-card');
    addressGrid.innerHTML = '';
    
    addresses.forEach(addr => {
        const card = document.createElement('div');
        card.className = `address-card card${addr.isDefault ? ' default' : ''}`;
        card.innerHTML = `
            ${addr.isDefault ? '<span class="default-badge">Default</span>' : ''}
            <div class="address-text">
                <strong>${addr.name}</strong><br>
                ${addr.addressLine1}<br>
                ${addr.addressLine2 ? addr.addressLine2 + '<br>' : ''}
                ${addr.city}, ${addr.state} ${addr.postalCode}<br>
                ${addr.country}<br>
                <span class="address-phone">${addr.phone}</span>
            </div>
            <div class="address-actions">
                ${!addr.isDefault ? `<button class="btn btn-sm btn-outline" data-action="default" data-id="${addr.id}">Set Default</button>` : ''}
                <button class="btn btn-sm btn-outline" data-action="edit" data-id="${addr.id}">Edit</button>
                <button class="btn btn-sm btn-outline btn-danger" data-action="delete" data-id="${addr.id}">Delete</button>
            </div>
        `;
        addressGrid.appendChild(card);
    });
    
    // Re-add the "Add Address" card
    const newAddCard = document.createElement('div');
    newAddCard.className = 'add-address-card card';
    newAddCard.id = 'addAddressCard';
    newAddCard.innerHTML = `
        <span class="add-icon">+</span>
        <span class="add-text">Add New Address</span>
    `;
    addressGrid.appendChild(newAddCard);
    
    // Attach event listener
    newAddCard.addEventListener('click', () => openModal());
    
    // Add styling for address cards
    if (!document.getElementById('address-card-styles')) {
        const style = document.createElement('style');
        style.id = 'address-card-styles';
        style.textContent = `
            .address-card { padding: var(--space-lg); position: relative; }
            .address-card.default { border-color: var(--gold); background: rgba(212, 175, 55, 0.05); }
            .default-badge { position: absolute; top: 12px; right: 12px; background: var(--gold); color: var(--black); font-size: 0.7rem; font-weight: 700; padding: 4px 8px; border-radius: 4px; text-transform: uppercase; }
            .address-text { line-height: 1.6; color: var(--gray-700); margin-bottom: var(--space-md); }
            .address-phone { color: var(--gray-500); font-size: 0.9rem; }
            .address-actions { display: flex; gap: var(--space-sm); flex-wrap: wrap; }
            .address-actions .btn-danger { color: var(--error); border-color: var(--error); }
            .address-actions .btn-danger:hover { background: var(--error); color: white; }
        `;
        document.head.appendChild(style);
    }
};

// Open modal
const openModal = (address = null) => {
    editingId = address?.id || null;
    document.getElementById('addressModalTitle').textContent = address ? 'Edit Address' : 'Add New Address';
    
    if (address) {
        form.recipient_name.value = address.name;
        form.phone.value = address.phone;
        form.address_line1.value = address.addressLine1;
        form.address_line2.value = address.addressLine2 || '';
        form.city.value = address.city;
        form.state.value = address.state;
        form.postal_code.value = address.postalCode;
        form.country.value = address.country;
        form.is_default.checked = address.isDefault;
    } else {
        form.reset();
        form.country.value = 'Sri Lanka';
    }
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
};

// Close modal
const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
    editingId = null;
};

// Event listeners
document.getElementById('addAddressBtn')?.addEventListener('click', () => openModal());
document.getElementById('addAddressCard')?.addEventListener('click', () => openModal());
document.getElementById('closeAddressModal')?.addEventListener('click', closeModal);
document.getElementById('cancelAddressBtn')?.addEventListener('click', closeModal);

// Close on overlay click
modal.querySelector('.modal-overlay')?.addEventListener('click', closeModal);

// Address grid actions (edit, delete, set default)
addressGrid.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    
    const action = btn.dataset.action;
    const id = parseInt(btn.dataset.id);
    
    switch (action) {
        case 'edit':
            const addr = addresses.find(a => a.id === id);
            if (addr) openModal(addr);
            break;
            
        case 'delete':
            if (confirm('Are you sure you want to delete this address?')) {
                addresses = addresses.filter(a => a.id !== id);
                saveAddresses();
                renderAddresses();
                toast.success('Address deleted');
            }
            break;
            
        case 'default':
            addresses = addresses.map(a => ({ ...a, isDefault: a.id === id }));
            saveAddresses();
            renderAddresses();
            toast.gold('Default address updated');
            break;
    }
});

// Form submission
form.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const addressData = {
        id: editingId || Date.now(),
        name: form.recipient_name.value.trim(),
        phone: form.phone.value.trim(),
        addressLine1: form.address_line1.value.trim(),
        addressLine2: form.address_line2.value.trim(),
        city: form.city.value.trim(),
        state: form.state.value.trim(),
        postalCode: form.postal_code.value.trim(),
        country: form.country.value.trim(),
        isDefault: form.is_default?.checked || false,
        updatedAt: new Date().toISOString()
    };
    
    // Validation
    if (!addressData.name || !addressData.phone || !addressData.addressLine1 || !addressData.city || !addressData.state || !addressData.postalCode) {
        toast.error('Please fill in all required fields');
        return;
    }
    
    if (editingId) {
        addresses = addresses.map(a => a.id === editingId ? addressData : a);
        toast.success('Address updated!');
    } else {
        // If new and marked as default, unset others
        if (addressData.isDefault) {
            addresses = addresses.map(a => ({ ...a, isDefault: false }));
        }
        // If first address, make it default
        if (addresses.length === 0) {
            addressData.isDefault = true;
        }
        addresses.push(addressData);
        toast.success('Address added!');
    }
    
    saveAddresses();
    renderAddresses();
    closeModal();
});

loadAddresses();
console.log('[Addresses] Addresses page ready');
</script>

<?php require_once __DIR__ . "/_layout_end.php"; ?>
