// /**
//  * Rupiah Formatter for Rencana Kerja Form
//  * Formats input values as Indonesian Rupiah (IDR)
//  */

// document.addEventListener('DOMContentLoaded', function() {
//     // Initial setup for existing budget inputs and setup mutation observer
//     setupRupiahFormattingForExistingInputs();
//     setupMutationObserverForNewInputs();
// });

// /**
//  * Sets up Rupiah formatting for existing inputs
//  */
// function setupRupiahFormattingForExistingInputs() {
//     // Target all anggaran inputs in Program, Kegiatan, and Sub Kegiatan sections
//     const budgetInputs = document.querySelectorAll('input[name*="anggaran"]');
//     budgetInputs.forEach(input => {
//         setupRupiahInput(input);
//     });
// }

// /**
//  * Sets up a mutation observer to detect and format new budget inputs added dynamically
//  */
// function setupMutationObserverForNewInputs() {
//     // Create a mutation observer to watch for new elements
//     const observer = new MutationObserver(function(mutations) {
//         mutations.forEach(function(mutation) {
//             if (mutation.addedNodes && mutation.addedNodes.length > 0) {
//                 // Check if any added node contains budget inputs
//                 mutation.addedNodes.forEach(function(node) {
//                     if (node.nodeType === 1 && node.tagName) { // Is an element node
//                         // Find budget inputs within this new node
//                         const newBudgetInputs = node.querySelectorAll('input[name*="anggaran"]');
//                         if (newBudgetInputs.length > 0) {
//                             newBudgetInputs.forEach(input => {
//                                 setupRupiahInput(input);
//                             });
//                         }
//                     }
//                 });
//             }
//         });
//     });
    
//     // Start observing the form container for added nodes
//     const formContainer = document.querySelector('form') || document.body;
//     observer.observe(formContainer, { 
//         childList: true, 
//         subtree: true 
//     });
// }

// /**
//  * Sets up a single input for Rupiah formatting
//  * @param {HTMLElement} input - The input element to set up
//  */
// function setupRupiahInput(input) {
//     // Skip if already initialized
//     if (input.dataset.rupiahInitialized === 'true') return;
    
//     // Mark as initialized to prevent duplicate handlers
//     input.dataset.rupiahInitialized = 'true';
    
//     // Add class for easy identification
//     input.classList.add('rupiah-input');
    
//     // Set placeholder to indicate Rupiah format
//     if (input.placeholder === 'Anggaran') {
//         input.placeholder = 'Anggaran (Rp)';
//     }
    
//     // Add event listeners
//     input.addEventListener('input', handleRupiahInput);
//     input.addEventListener('focus', handleRupiahFocus);
//     input.addEventListener('blur', handleRupiahBlur);
    
//     // Format any existing value
//     if (input.value) {
//         // Store the raw value as data attribute
//         input.dataset.rawValue = input.value;
//         // Display formatted value
//         input.value = formatToRupiah(input.value);
//     } else {
//         input.dataset.rawValue = '0';
//     }
// }

// /**
//  * Handles input events on Rupiah inputs
//  * @param {Event} e - The input event
//  */
// function handleRupiahInput(e) {
//     // Get input value and remove non-digits
//     let rawValue = e.target.value.replace(/[^\d]/g, '');
    
//     // Store the raw value
//     e.target.dataset.rawValue = rawValue || '0';
    
//     // Format the value for display
//     e.target.value = formatToRupiah(rawValue);
// }

// /**
//  * Handles focus events on Rupiah inputs
//  * @param {Event} e - The focus event
//  */
// function handleRupiahFocus(e) {
//     // Display the raw value without formatting for easier editing
//     e.target.value = e.target.dataset.rawValue || '';
// }

// /**
//  * Handles blur events on Rupiah inputs
//  * @param {Event} e - The blur event
//  */
// function handleRupiahBlur(e) {
//     // Get the raw value
//     let rawValue = e.target.dataset.rawValue || '0';
    
//     // Format for display
//     e.target.value = formatToRupiah(rawValue);
// }

// /**
//  * Formats a numeric string as Indonesian Rupiah
//  * @param {string} value - The numeric string to format
//  * @returns {string} Formatted Rupiah string
//  */
// function formatToRupiah(value) {
//     if (!value || value === '0') return 'Rp 0';
    
//     // Format with thousand separator (dot for Indonesian format)
//     return 'Rp ' + parseInt(value, 10).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
// }

// /**
//  * Add event listener to form submission to ensure raw values are submitted
//  */
// document.addEventListener('DOMContentLoaded', function() {
//     const form = document.querySelector('form');
//     if (form) {
//         form.addEventListener('submit', function(e) {
//             // Convert all Rupiah inputs back to raw values for submission
//             const rupiahInputs = document.querySelectorAll('.rupiah-input');
//             rupiahInputs.forEach(input => {
//                 input.value = input.dataset.rawValue || '0';
//             });
//         });
//     }
// });

// /**
//  * Function to automatically setup the urutan field values when adding new items
//  * This can be integrated with your existing addProgram, addKegiatan, etc. functions
//  */
// function updateUrutanValues() {
//     // Update Program ordering
//     document.querySelectorAll('.program-container').forEach(container => {
//         const programItems = container.querySelectorAll('.program-item');
//         programItems.forEach((item, index) => {
//             const urutanInput = item.querySelector('input[name*="urutan"]');
//             if (urutanInput) {
//                 urutanInput.value = index + 1;
//             }
//         });
//     });
    
//     // Update Kegiatan ordering
//     document.querySelectorAll('.kegiatan-container').forEach(container => {
//         const kegiatanItems = container.querySelectorAll('.kegiatan-item');
//         kegiatanItems.forEach((item, index) => {
//             const urutanInput = item.querySelector('input[name*="urutan"]');
//             if (urutanInput) {
//                 urutanInput.value = index + 1;
//             }
//         });
//     });
    
//     // This would need to be called after adding/removing items
// }