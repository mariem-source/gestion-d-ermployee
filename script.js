/**
 * Employee Management System - Main JavaScript File
 * Contains all the client-side functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize the application
    initApp();
});

function initApp() {
    // Initialize form validations
    initFormValidations();
    
    // Initialize interactive elements
    initInteractiveElements();
    
    // Initialize data tables
    initDataTables();
    
    // Initialize modals
    initModals();
}

/**
 * Initialize form validations
 */
function initFormValidations() {
    // Employee form validation
    const employeeForm = document.getElementById('employee-form');
    if (employeeForm) {
        employeeForm.addEventListener('submit', function(e) {
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            if (!validateEmail(email)) {
                e.preventDefault();
                showAlert('Please enter a valid email address', 'error');
                return false;
            }
            
            if (!validatePhone(phone)) {
                e.preventDefault();
                showAlert('Please enter a valid phone number (8-15 digits)', 'error');
                return false;
            }
        });
    }
    
    // Department form validation
    const departmentForm = document.getElementById('department-form');
    if (departmentForm) {
        departmentForm.addEventListener('submit', function(e) {
            const name = document.getElementById('name').value;
            
            if (name.trim() === '') {
                e.preventDefault();
                showAlert('Department name is required', 'error');
                return false;
            }
        });
    }
}

/**
 * Initialize interactive elements
 */
function initInteractiveElements() {
    // Confirm before delete actions
    const deleteButtons = document.querySelectorAll('.delete-action');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });
    
    // Toggle password visibility
    const togglePassword = document.querySelector('.toggle-password');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }
    
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('.table tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }
}

/**
 * Initialize data tables
 */
function initDataTables() {
    // Simple client-side sorting for tables
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.getAttribute('data-sort');
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const isAsc = this.classList.contains('asc');
                
                // Clear all sort classes
                headers.forEach(h => {
                    h.classList.remove('asc', 'desc');
                });
                
                // Sort rows
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`td:nth-child(${column})`).textContent;
                    const bValue = b.querySelector(`td:nth-child(${column})`).textContent;
                    
                    return isAsc 
                        ? bValue.localeCompare(aValue) 
                        : aValue.localeCompare(bValue);
                });
                
                // Toggle sort direction
                this.classList.toggle(isAsc ? 'desc' : 'asc');
                
                // Rebuild table
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
}

/**
 * Initialize modals
 */
function initModals() {
    // Modal toggle functionality
    const modalTriggers = document.querySelectorAll('[data-modal-target]');
    const modals = document.querySelectorAll('.modal');
    const modalCloses = document.querySelectorAll('.modal-close');
    
    // Open modal
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const target = this.getAttribute('data-modal-target');
            document.getElementById(target).classList.add('active');
        });
    });
    
    // Close modal
    modalCloses.forEach(close => {
        close.addEventListener('click', function() {
            this.closest('.modal').classList.remove('active');
        });
    });
    
    // Close when clicking outside
    modals.forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
}

/**
 * Helper function to validate email
 */
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

/**
 * Helper function to validate phone number (8–15 digits only)
 */
function validatePhone(phone) {
    const re = /^[0-9]{8,15}$/;
    return re.test(phone);
}

/**
 * Show alert message
 */
function showAlert(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${type}`;
    alertDiv.textContent = message;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.classList.add('fade-out');
        setTimeout(() => alertDiv.remove(), 500);
    }, 3000);
}

/**
 * AJAX helper function
 */
function makeRequest(url, method = 'GET', data = null) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open(method, url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status >= 200 && this.status < 300) {
                try {
                    const response = JSON.parse(this.response);
                    resolve(response);
                } catch (e) {
                    resolve(this.response);
                }
            } else {
                reject({
                    status: this.status,
                    statusText: this.statusText
                });
            }
        };
        
        xhr.onerror = function() {
            reject({
                status: this.status,
                statusText: this.statusText
            });
        };
        
        xhr.send(data);
    });
}