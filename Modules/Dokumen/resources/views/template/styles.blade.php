<style>
    /* Stats Cards Gradients */
    .stats-card {
        border-radius: 16px !important;
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-gradient-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .bg-gradient-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    /* Template Card */
    .template-card {
        position: relative;
        overflow: hidden;
        border-radius: 16px !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 2px solid #f0f0f0 !important;
    }

    .template-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea, #764ba2);
    }

    .template-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
        border-color: #667eea !important;
    }

    .template-icon-wrapper {
        width: 80px;
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border-radius: 20px;
        background: linear-gradient(135deg, #667eea15, #764ba215);
        transition: all 0.3s ease;
    }

    .template-card:hover .template-icon-wrapper {
        transform: scale(1.1);
    }

    .template-icon {
        font-size: 2.5rem;
        background: linear-gradient(135deg, #667eea, #764ba2);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    /* Badge Styles */
    .badge-format {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-format.format-pdf {
        background: linear-gradient(135deg, #f093fb, #f5576c);
        color: white;
    }

    .badge-format.format-html {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
    }

    .badge-format.format-docx {
        background: linear-gradient(135deg, #11998e, #38ef7d);
        color: white;
    }

    .badge-status {
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    /* Variables List */
    .variable-item {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 8px 12px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .variable-item:hover {
        background: #e9ecef;
        border-color: #667eea;
        transform: translateX(5px);
    }

    .variable-code {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
        color: #667eea;
        font-weight: 600;
    }

    .variable-desc {
        font-size: 0.75rem;
        color: #6c757d;
    }

    /* Editor Styles */
    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.9rem;
    }

    #template_content,
    #template_header,
    #template_footer {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 15px;
        transition: border-color 0.3s ease;
    }

    #template_content:focus,
    #template_header:focus,
    #template_footer:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.1);
    }

    /* Tab Styles */
    .nav-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 600;
        padding: 12px 24px;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: #667eea;
        background: #f8f9fa;
    }

    .nav-tabs .nav-link.active {
        color: #667eea;
        background: white;
        border-bottom: 3px solid #667eea;
    }

    /* Modal Styles */
    .modal-xl {
        max-width: 1400px;
    }

    .modal.fade .modal-dialog {
        transform: scale(0.8);
        opacity: 0;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modal.show .modal-dialog {
        transform: scale(1);
        opacity: 1;
    }

    /* Empty State */
    .empty-state {
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        font-size: 6rem;
        color: #e0e0e0;
        margin-bottom: 2rem;
    }

    /* Table Styles */
    .datatable-table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #495057;
        border-bottom: 2px solid #dee2e6;
    }

    .datatable-table td {
        vertical-align: middle;
    }

    /* Custom Data Field */
    .custom-data-row {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 12px;
        margin-bottom: 12px;
        border: 1px solid #e9ecef;
    }

    /* Preview Content */
    #previewContent {
        background: white;
        padding: 40px;
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }

    /* Format Buttons */
    .btn-outline-secondary {
        border-color: #dee2e6;
    }

    .btn-outline-secondary:hover {
        background: #667eea;
        border-color: #667eea;
        color: white;
    }

    /* Scrollbar */
    textarea::-webkit-scrollbar {
        width: 8px;
    }

    textarea::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    textarea::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 10px;
    }

    textarea::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }

    /* Loading State */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-content {
        background: white;
        padding: 30px 40px;
        border-radius: 16px;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .template-card .card-body {
            padding: 1.5rem 1rem;
        }

        .template-icon-wrapper {
            width: 60px;
            height: 60px;
        }

        .template-icon {
            font-size: 2rem;
        }

        .modal-xl {
            max-width: 100%;
        }
    }
</style>
